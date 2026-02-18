<?php

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class TrackUserDevice
{

    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $agent = $request->header('User-Agent');
            $ip = $request->ip();
            $browser = $this->getBrowser($agent);
            $device = $this->getDevice($agent);
            $location = $this->getLocation($ip);

            // Add or update the device entry
            DB::table('user_devices')->updateOrInsert(
                ['user_id' => $user->id, 'ip_address' => $ip],
                [
                    'browser' => $browser,
                    'device' => $device,
                    'location' => $location,
                    'last_seen' => now(),
                ]
            );

            // Limit to the last 5 entries
            $deviceCount = DB::table('user_devices')
                ->where('user_id', $user->id)
                ->count();

            if ($deviceCount > 5) {
                DB::table('user_devices')
                    ->where('user_id', $user->id)
                    ->orderBy('last_seen', 'asc')
                    ->take($deviceCount - 5)
                    ->delete();
            }
        }

        return $next($request);
    }

    private function getBrowser($agent)
    {
        if (strpos($agent, 'Firefox') !== false) return 'Firefox';
        if (strpos($agent, 'Chrome') !== false) return 'Chrome';
        if (strpos($agent, 'Safari') !== false) return 'Safari';
        if (strpos($agent, 'Opera') !== false) return 'Opera';
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'Trident') !== false) return 'Internet Explorer';
        return 'Unknown';
    }

    private function getDevice($agent)
    {
        if (preg_match('/Mobile/i', $agent)) return 'Mobile';
        if (preg_match('/Tablet/i', $agent)) return 'Tablet';
        return 'Desktop';
    }
    private function getLocation($ip)
    {
        // Use an external API like ipstack.com or ipinfo.io for location lookup
        // Example with ipinfo.io:
        try {
            $response = file_get_contents("http://ipinfo.io/{$ip}/json");
            $data = json_decode($response);
    
            // Check if the country is India (IN)
            if (isset($data->country)) {
                if ($data->country == 'IN') {
                    return 'India';
                }
    
                // If the country is not India, return Asia
                return 'Asia';
            }
    
            // Return city and region if available, or 'Unknown' if not
            return isset($data->city) && isset($data->region)
                ? $data->city . ', ' . $data->region
                : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
}
