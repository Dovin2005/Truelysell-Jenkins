<?php

namespace Modules\GoogleCalendarSync\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;
use Google\Service\Calendar;
use App\Models\Bookings;
use Modules\Product\app\Models\Product;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarSyncController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user(); 
        $adminSettings = User::find(Auth::id()); 
        
        return view('googlecalendarsync::index', [
            'user' => $user,
            'credentials' => $adminSettings,
        ]);
    }

    
        /**
     * Initializes and configures the Google API Client.
     * @return GoogleClient
     */
    private function getGoogleClient()
    { 
        // For this example, we assume the admin (user with ID 1) holds the credentials.
        // A better approach is to store these in config/services.php or .env file.
        $adminSettings = User::find(1); // Or your logic to get app settings
        $client = new GoogleClient();
        $client->setClientId($adminSettings->google_client_id ?? '');
        $client->setClientSecret($adminSettings->google_client_secret ?? '');
        $client->setRedirectUri(route('provider.google.callback'));
        $client->setAccessType('offline'); // Required to get a refresh token
        $client->setPrompt('consent');   // Forces prompt for consent to get refresh token every time
        $client->addScope('https://www.googleapis.com/auth/calendar');
        $client->addScope('https://www.googleapis.com/auth/userinfo.email');

        return $client;
    }

    public function showCalendar()
    {
        $user = Auth::user();
        
        return view('googlecalendarsync::provider.calendar_settings', [
            'user' => $user
        ]);
    }

    /**
     * Display the Google Calendar Sync settings page.
     */
   
    public function updateCalendarStatus(Request $request)
    {
        try {
            $status = $request->input('status');
            $user = Auth::user();
            
            // Update sync status
            $user->calendar_sync_status = $status;
            $user->save();

            if ($status == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Google Calendar Connected successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Google Calendar disconnected successfully!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Operation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Save Google Client ID and Client Secret.
     * Note: This is a simplified implementation. Storing these on a user record
     * is not recommended for production. Use a dedicated settings table or .env.
     */
    public function saveGoogleCredentials(Request $request)
    {
        $request->validate([
            'google_client_id' => 'required|string',
            'google_client_secret' => 'required|string',
        ]);

        // We are updating the authenticated user's record.
        // Ensure this action is restricted to admins only.
        $admin = User::find(Auth::id());

        $admin->google_client_id = $request->input('google_client_id');
        $admin->google_client_secret = $request->input('google_client_secret');
        $admin->save();

        return redirect()->route('admin.googlecalendarsync')->with('success', 'Google credentials saved successfully.');
    }


    /**
     * Redirect the user to Google's authentication page.
     */
    public function redirectToGoogle()
    {
        $client = $this->getGoogleClient();
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    /**
     * Handle the callback from Google after user authorization.
     */
    public function handleGoogleCallback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('admin.googlecalendarsync')->with('error', 'Authorization code not found.');
        }

        try {
            $client = $this->getGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
            
            if (isset($token['error'])) {
                 return redirect()->route('admin.googlecalendarsync')->with('error', $token['error_description']);
            }

            // Get user's email
            $oauth2 = new GoogleOauth2($client);
            $userInfo = $oauth2->userinfo->get();

            // Save the tokens and email to the authenticated provider's record
            $provider = User::find(Auth::id());
            $provider->google_access_token = $token['access_token'];
            if (isset($token['refresh_token'])) {
                // Refresh token is only provided on the first authorization
                $provider->google_refresh_token = $token['refresh_token'];
            }
            $provider->google_calendar_email = $userInfo->email;
            $provider->save(); // Save the changes to the provider
            
            return redirect()->route('admin.googlecalendarsync')->with('success', 'Google Calendar connected successfully!');

        } catch (Exception $e) {
            return redirect()->route('admin.googlecalendarsync')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect the user's Google Calendar.
     */
    public function disconnectGoogleCalendar()
    {
        $provider = Auth::user();
        
        try {
            $client = $this->getGoogleClient();
            // It's good practice to revoke the token on Google's end
            if ($provider->google_access_token) {
                 $client->revokeToken($provider->google_access_token);
            }
        } catch(Exception $e) {
            // If token is already expired or invalid, Google will throw an error.
            // We can ignore it and proceed with clearing our database.
        }

        // Clear the fields from the database
        $provider->google_access_token = null;
        $provider->google_refresh_token = null;
        $provider->google_calendar_email = null;
        $provider->save();

        return redirect()->route('admin.googlecalendarsync')->with('success', 'Google Calendar has been disconnected.');
    }

    public function syncGoogleCalendar(Bookings $booking, $serviceId, $endDate, $endTime = null)
    {
        Log::info('=== GOOGLE CALENDAR SYNC STARTED ===', [
            'booking_id' => $booking->id,
            'service_id' => $serviceId
        ]);

        try {
            // 1. Get provider from product
            $provider = Product::select('created_by')->where('id', $serviceId)->first();
            if (!$provider || !$provider->created_by) {
                Log::warning('No provider found - stopping');
                return false;
            }
            $providerUser = User::find($provider->created_by);

            // Check if provider has calendar sync enabled
            if (!$providerUser || $providerUser->calendar_sync_status != 1) {
                Log::warning('Provider calendar sync is disabled - stopping', [
                    'provider_id' => $providerUser->id ?? 'unknown',
                    'calender_sync_status' => $providerUser->calendar_sync_status ?? 'unknown'
                ]);
                return false;
            }

            // 2. Get admin credentials (user id = 1)
            $adminUser = 1; // Change this to your admin ID
            $admin = User::find($adminUser);

            // Add debugging
            if (!$admin) {
                Log::warning('Admin user not found with ID: ' . $adminUser);
                return false;
            }

            if (!$admin->google_access_token) {
                Log::warning('Admin Google access token missing for user ID: ' . $adminUser);
                return false;
            }

            if (!$admin->google_refresh_token) {
                Log::warning('Admin Google refresh token missing for user ID: ' . $adminUser);
                return false;
            }

            // 3. Setup Google Client using admin credentials
            $client = new GoogleClient();
            $client->setClientId($admin->google_client_id);
            $client->setClientSecret($admin->google_client_secret);
            $client->setRedirectUri($admin->google_redirect_uri ?? 'http://127.0.0.1:8003/oauth/google/callback');
            $client->setAccessType('offline');
            $client->addScope(GoogleCalendar::CALENDAR);

            $client->setAccessToken([
                'access_token' => $admin->google_access_token,
                'refresh_token' => $admin->google_refresh_token,
            ]);

            if ($client->isAccessTokenExpired()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($admin->google_refresh_token);
                if (!isset($newToken['access_token'])) {
                    Log::error('Admin token refresh failed');
                    return false;
                }
                $client->setAccessToken($newToken);
            }

            $service = new GoogleCalendar($client);

            // 4. Create calendar for provider if not exists
            if (!$providerUser->google_calendar_id) {
                $calendar = new \Google\Service\Calendar\Calendar();
                $calendar->setSummary("Calendar - {$providerUser->name}");
                $calendar->setTimeZone(config('app.timezone', 'UTC'));

                $createdCalendar = $service->calendars->insert($calendar);

                // Construct calendar link manually
                $calendarId = $createdCalendar->getId();
                $calendarLink = "https://calendar.google.com/calendar/u/0/r?cid=" . urlencode($calendarId);

                // Save calendar ID and link to provider
                $providerUser->google_calendar_id = $calendarId;
                $providerUser->google_calendar_link = $calendarLink;
                $providerUser->save();

                Log::info('Provider calendar created', [
                    'provider_id' => $providerUser->id,
                    'calendar_id' => $calendarId,
                    'calendar_link' => $calendarLink
                ]);
            }

            $calendarId = $providerUser->google_calendar_id;

            // 5. Prepare event data
            $serviceDetails = Product::find($serviceId);
            $user = User::find($booking->user_id);

            $startDateTime = $booking->booking_date . ' ' . ($booking->from_time ?? '09:00:00');
            $endDateTime = $endDate . ' ' . ($endTime ?? '10:00:00');

            $event = new GoogleEvent([
                'summary' => ($serviceDetails->source_name ?? 'Service Booking') . ' - ' . ($booking->order_id ?? ''),
                'description' => "Booking ID: " . ($booking->order_id ?? 'N/A') . "\n" .
                                 "Service: " . ($serviceDetails->source_name ?? 'Service') . "\n" .
                                 "Customer: " . ($user->name ?? 'N/A') . "\n" .
                                 "Phone: " . ($booking->user_phone ?? 'N/A'),
                'start' => [
                    'dateTime' => Carbon::parse($startDateTime)->toIso8601String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ],
                'end' => [
                    'dateTime' => Carbon::parse($endDateTime)->toIso8601String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ],
                'attendees' => [
                    ['email' => $booking->user_email ?? ''],
                    ['email' => $providerUser->email],
                ],
            ]);

            // 6. Insert event into provider's calendar
            $createdEvent = $service->events->insert($calendarId, $event, ['sendUpdates' => 'none']);

            // 7. Update booking with event details
            $eventId = $createdEvent->getId();
            $eventLink = $createdEvent->htmlLink;
            
            $booking->update([
                'google_calendar_event_id' => $eventId,
                'google_calendar_link' => $eventLink
            ]);

            Log::info('Event created successfully', [
                'booking_id' => $booking->id,
                'calendar_id' => $calendarId,
                'event_id' => $eventId,
                'event_link' => $eventLink
            ]);

            // Verify the event ID was saved
            $booking->refresh();
            Log::info('Booking updated with calendar event details', [
                'booking_id' => $booking->id,
                'stored_event_id' => $booking->google_calendar_event_id,
                'stored_event_link' => $booking->google_calendar_link
            ]);

            Log::info('=== GOOGLE CALENDAR SYNC COMPLETED SUCCESSFULLY ===');
            return true;

        } catch (\Throwable $e) {
            Log::error('GOOGLE CALENDAR SYNC FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_id' => $booking->id
            ]);
            return false;
        }
    }

    /**
     * Cancel/Delete a Google Calendar event when booking is cancelled
     */
    public function cancelGoogleCalendarEvent(Bookings $booking)
    {
        Log::info('=== GOOGLE CALENDAR CANCELLATION STARTED ===', [
            'booking_id' => $booking->id,
            'event_id' => $booking->google_calendar_event_id ?? 'none',
            'event_link' => $booking->google_calendar_link ?? 'none',
            'product_id' => $booking->product_id,
            'booking_status' => $booking->booking_status
        ]);

        try {
            // Check if booking has a calendar event ID
            if (!$booking->google_calendar_event_id) {
                Log::info('No calendar event ID found - skipping cancellation', [
                    'booking_id' => $booking->id
                ]);
                return true; // No event to cancel
            }

            // 1. Get provider from product
            $provider = Product::select('created_by')->where('id', $booking->product_id)->first();
            if (!$provider || !$provider->created_by) {
                Log::warning('No provider found for booking - stopping cancellation', [
                    'booking_id' => $booking->id,
                    'product_id' => $booking->product_id
                ]);
                return false;
            }
            $providerUser = User::find($provider->created_by);

            Log::info('Provider found for cancellation', [
                'booking_id' => $booking->id,
                'provider_id' => $providerUser->id ?? 'unknown',
                'provider_name' => $providerUser->name ?? 'unknown'
            ]);

            // Check if provider has calendar sync enabled
            if (!$providerUser || $providerUser->calendar_sync_status != 1) {
                Log::info('Provider calendar sync is disabled - skipping cancellation', [
                    'provider_id' => $providerUser->id ?? 'unknown',
                    'calendar_sync_status' => $providerUser->calendar_sync_status ?? 'unknown'
                ]);
                return true; // Sync disabled, skip
            }

            // Check if provider has a calendar ID
            if (!$providerUser->google_calendar_id) {
                Log::warning('Provider has no calendar ID - cannot cancel event', [
                    'provider_id' => $providerUser->id,
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // 2. Get admin credentials (user id = 1)
            $adminUser = 1; // Change this to your admin ID
            $admin = User::find($adminUser);

            if (!$admin) {
                Log::error('Admin user not found with ID: ' . $adminUser);
                return false;
            }

            if (!$admin->google_access_token) {
                Log::error('Admin Google access token missing for user ID: ' . $adminUser);
                return false;
            }

            if (!$admin->google_refresh_token) {
                Log::error('Admin Google refresh token missing for user ID: ' . $adminUser);
                return false;
            }

            Log::info('Admin credentials found, setting up Google Client', [
                'admin_id' => $admin->id,
                'has_access_token' => !empty($admin->google_access_token),
                'has_refresh_token' => !empty($admin->google_refresh_token)
            ]);

            // 3. Setup Google Client using admin credentials
            $client = new GoogleClient();
            $client->setClientId($admin->google_client_id);
            $client->setClientSecret($admin->google_client_secret);
            $client->setRedirectUri($admin->google_redirect_uri ?? 'http://127.0.0.1:8003/oauth/google/callback');
            $client->setAccessType('offline');
            $client->addScope(GoogleCalendar::CALENDAR);

            $client->setAccessToken([
                'access_token' => $admin->google_access_token,
                'refresh_token' => $admin->google_refresh_token,
            ]);

            if ($client->isAccessTokenExpired()) {
                Log::info('Access token expired, refreshing token');
                $newToken = $client->fetchAccessTokenWithRefreshToken($admin->google_refresh_token);
                if (!isset($newToken['access_token'])) {
                    Log::error('Admin token refresh failed during cancellation');
                    return false;
                }
                $client->setAccessToken($newToken);
                
                // Update admin token in database
                $admin->google_access_token = $newToken['access_token'];
                $admin->save();
                
                Log::info('Token refreshed successfully');
            }

            $service = new GoogleCalendar($client);
            $calendarId = $providerUser->google_calendar_id;
            $eventId = $booking->google_calendar_event_id;

            Log::info('Attempting to delete calendar event', [
                'booking_id' => $booking->id,
                'calendar_id' => $calendarId,
                'event_id' => $eventId
            ]);

            // 4. Try to delete the event
            try {
                $service->events->delete($calendarId, $eventId);
                Log::info('Calendar event deleted successfully', [
                    'booking_id' => $booking->id,
                    'calendar_id' => $calendarId,
                    'event_id' => $eventId
                ]);
            } catch (\Google\Service\Exception $e) {
                // Handle specific Google API errors
                if ($e->getCode() == 404) {
                    Log::info('Calendar event not found (already deleted or never existed)', [
                        'booking_id' => $booking->id,
                        'event_id' => $eventId,
                        'error_code' => $e->getCode()
                    ]);
                    // This is not an error - event might have been deleted manually
                } else if ($e->getCode() == 410) {
                    Log::info('Calendar event already deleted', [
                        'booking_id' => $booking->id,
                        'event_id' => $eventId,
                        'error_code' => $e->getCode()
                    ]);
                    // This is not an error - event was already deleted
                } else {
                    Log::error('Google API error during event deletion', [
                        'booking_id' => $booking->id,
                        'event_id' => $eventId,
                        'error_code' => $e->getCode(),
                        'error_message' => $e->getMessage()
                    ]);
                    return false;
                }
            }

            // 5. Clear calendar event data from booking
            $booking->update([
                'google_calendar_event_id' => null,
                'google_calendar_link' => null
            ]);

            Log::info('Booking calendar data cleared', [
                'booking_id' => $booking->id
            ]);

            Log::info('=== GOOGLE CALENDAR CANCELLATION COMPLETED SUCCESSFULLY ===');
            return true;

        } catch (\Throwable $e) {
            Log::error('GOOGLE CALENDAR CANCELLATION FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_id' => $booking->id
            ]);
            return false;
        }
    }

}
