<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Helpers\TimeHelper;
use Illuminate\Support\Facades\Storage;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Modules\Communication\app\Models\Templates;
use Illuminate\Support\Str;
use App\Http\Requests\TicketRequest;
use App\Http\Requests\TicketHistoryRequest;
use App\Http\Requests\TicketStatusRequest;
use App\Repositories\Contracts\TicketInterface;

class TicketController extends Controller
{
    protected $ticketRepository;

    public function __construct(TicketInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function ticketindex(Request $request): View|JsonResponse
    {
        $currentRouteName = Route::currentRouteName();
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        
        $rules = [
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string|in:id,name,code,status',
            'search' => 'nullable|string',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filters = [
            'order_by' => $request['order_by'] ?? 'desc',
            'sort_by' => $request['sort_by'] ?? 'id',
            'search' => $request['search'] ?? null,
            'sql_date_format' => $sqlDateFormat,
        ];

        $userId = Auth::id();
        $data['ticketdata'] = $this->ticketRepository->getAllTickets($filters, $currentRouteName, $userId);
        $data['userlist'] = $this->ticketRepository->getTicketUsers(5);
        $data['authUserId'] = $userId ?: Cache::get('auth_user_id', 1);

        if ($currentRouteName == 'user.ticket') {
            return view('ticket.userticket', compact('data'));
        } else if ($currentRouteName == 'provider.ticket' || $currentRouteName == 'staff.ticket') {
            return view('ticket.providerticket', compact('data'));
        } else {
            return view('ticket.adminticket', compact('data'));
        }
    }

    public function store(TicketRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $data = $request->only(['subject', 'status', 'user_type', 'priority', 'description', 'user_id']);
            $data['created_by'] = $request['user_id'];

            if ($request->input('id')) {
                $ticket = $this->ticketRepository->updateTicket($request->input('id'), $data);
                $message = 'Ticket updated successfully.';
            } else {
                $ticket = $this->ticketRepository->createTicket($data);
                $message = 'Ticket created successfully.';
            }

            $ticketdata = $this->ticketRepository->getTicketById($ticket->id);
            
            // Handle profile image URL
            if ($ticketdata['profile_image'] && file_exists(public_path('storage/profile/' . $ticketdata['profile_image']))) {
                $ticketdata['profile_image'] = url('storage/profile/' . $ticketdata['profile_image']);
            } else {
                $ticketdata['profile_image'] = url('assets/img/profile-default.png');
            }
            
            $ticketdata['updated_at_relative'] = TimeHelper::getRelativeTime($ticket['created_at']);
            
            // Notification handling
            $this->handleTicketNotification($ticketdata, $request['user_id'], 'New Ticket');

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => $message,
                'data' => $ticket,
                'ticketdata' => $ticketdata
            ], 200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function storehistory(TicketHistoryRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $data = [
                'ticket_id' => $request['ticket_id'],
                'description' => $request['description'],
                'user_id' => $request['user_id'],
                'created_by' => $request['user_id'],
                'created_at' => Carbon::now()
            ];

            $history = $this->ticketRepository->createTicketHistory($data);
            
            $userlist = User::leftjoin('user_details', 'user_details.user_id', '=', 'users.id')
                ->select('users.id', DB::raw("CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                 END AS user_name"), 'user_details.profile_image')
                ->where('users.id', $request['user_id'])
                ->first();
                
            $data1 = [
                'user' => $userlist,
                'comment' => $request['description'],
                'createdat' => Carbon::now()
            ];
            
            $commentHtml = view('ticket.comments', ['hval' => $data1])->render();
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Comments Added successfully.',
                'data' => $history,
                'comments' => $commentHtml
            ], 200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function ticketdetails(Request $request): View
    {
        $currentRouteName = Route::currentRouteName();
        $data['authUserId'] = Auth::id() ?: Cache::get('auth_user_id', 1);
        
        $ticket = $this->ticketRepository->getTicketByTicketId($request->ticket_id);
        $ticketId = $ticket->id ?? Session::get('ticket_id');

        $data['ticketdata'] = $this->ticketRepository->getTicketById($ticketId);
        $data['tickethistory'] = $this->ticketRepository->getTicketHistory($ticketId);
        $data['userlist'] = $this->ticketRepository->getTicketUsers(5);

        if ($currentRouteName == 'user.ticketdetails') {
            return view('ticket.userticket-details', compact('data'));
        } else if ($currentRouteName == 'provider.ticketdetails' || $currentRouteName == 'staff.ticket_details') {
            return view('ticket.providerticket-details', compact('data'));
        } else {
            return view('ticket.ticket-details', compact('data'));
        }
    }

    public function gettickethistory(Request $request)
    {
        try {
            $data['tickethistory'] = $this->ticketRepository->getTicketHistory($request['ticketId']);
            return response()->json([
                'code' => 200,
                'message' => __('truelysell_validation.success_response.success'),
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while retrive data!'], 500);
        }
    }

    public function getticketdetails(Request $request)
    {
        try {
            $currentRouteName = Route::currentRouteName();
            $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
            $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
            
            $filters = [
                'order_by' => $request['order_by'] ?? 'desc',
                'sort_by' => $request['sort_by'] ?? 'id',
                'search' => $request['search'] ?? null,
                'sql_date_format' => $sqlDateFormat,
            ];
            
            $userId = Auth::id();
            $query = $this->ticketRepository->getAllTickets($filters, $currentRouteName, $userId);
            
            $data['ticketdata'] = $query->get();
            
            return response()->json([
                'code' => 200,
                'message' => __('truelysell_validation.success_response.success'),
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while retrive data!'], 500);
        }
    }

    public function storeTicketId(Request $request)
    {
        Session::put('ticket_id', $request->ticket_id);
        return response()->json(['success' => true]);
    }

    public function updateticketstatus(TicketStatusRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $updateData = ['status' => $request['status'], 'updated_at' => Carbon::now()];
            
            if (isset($request['type']) && $request['type'] == 'assignticket') {
                $updateData['assignee_id'] = $request['user_id'];
            } elseif (isset($request['assign_id']) && $request['assign_id'] != '') {
                $updateData['assignee_id'] = $request['assign_id'];
            }
            
            $this->ticketRepository->updateTicketStatus($request['id'], $updateData);
            
            $statusMap = [
                1 => ['status' => 'Open', 'source' => 'New Ticket'],
                2 => ['status' => 'Assigned', 'source' => 'Assign Ticket'],
                3 => ['status' => 'InProgress', 'source' => 'Ticket Inprogress'],
                4 => ['status' => 'Closed', 'source' => 'Ticket Closed']
            ];
            
            $statusInfo = $statusMap[$request['status']] ?? ['status' => 'Unknown', 'source' => ''];
            $status = $statusInfo['status'];
            $source = $statusInfo['source'];
            
            $ticketdata = $this->getTicketStatusData($request['id']);
            
            if ($ticketdata) {
                $this->handleStatusNotification($ticketdata, $request, $source);
                
                if ($ticketdata['assinee_profile_image'] && file_exists(public_path('storage/profile/' . $ticketdata['assinee_profile_image']))) {
                    $ticketdata['assinee_profile_image'] = url('storage/profile/' . $ticketdata['assinee_profile_image']);
                } else {
                    $ticketdata['assinee_profile_image'] = url('assets/img/profile-default.png');
                }
            }
            
            return response()->json([
                'code' => 200, 
                'message' => 'Ticket ' . $status . ' Successfully', 
                'data' => $ticketdata
            ], 200);
            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function getTicketStatusData($ticketId)
    {
        return Ticket::select(
            'tickets.ticket_id',
            'tickets.id',
            'tickets.user_id',
            'tickets.assignee_id',
            'tickets.updated_at as ticket_updated_at',
            DB::raw("(SELECT CASE 
                 WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                     THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                 ELSE users.name 
             END
          FROM users
          LEFT JOIN user_details ON user_details.user_id = users.id
          WHERE users.id = tickets.assignee_id AND users.deleted_at IS NULL LIMIT 1) AS assignee_name"),
            DB::raw("(SELECT CASE 
              WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                  THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
              ELSE users.name 
          END
        FROM users
        LEFT JOIN user_details ON user_details.user_id = users.id
        WHERE users.id = tickets.user_id AND users.deleted_at IS NULL LIMIT 1) AS user_name"),
            DB::raw("(SELECT user_details.profile_image
        FROM users
        LEFT JOIN user_details ON user_details.user_id = users.id
        WHERE users.id = tickets.assignee_id AND users.deleted_at IS NULL LIMIT 1) AS assinee_profile_image")
        )->where('tickets.id', $ticketId)->first();
    }

    protected function handleTicketNotification($ticketdata, $userId, $source)
    {
        $controller = new Controller();
        $notificationsettings = $controller->getnotificationsettings(3, $source);
        
        if ($notificationsettings == 1) {
            $gettemplate = Templates::select('templates.subject', 'templates.content')
                ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                ->where('notification_types.type', $source)
                ->where('recipient_type', 2)
                ->where('templates.type', 3)
                ->where('templates.status', 1)
                ->first();
                
            if ($gettemplate && $ticketdata) {
                $tempdata = [
                    '{{user_name}}' => $ticketdata->username,
                    '{{ticketid}}' => $ticketdata->ticket_id,
                ];
                
                $todescription = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
                
                $getfromtemplate = Templates::select('templates.subject', 'templates.content')
                    ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                    ->where('notification_types.type', $source)
                    ->where('recipient_type', 1)
                    ->where('templates.type', 3)
                    ->where('templates.status', 1)
                    ->first();
                    
                $fromdescription = "";
                if ($getfromtemplate) {
                    $fromtempdata = [
                        '{{user_name}}' => $ticketdata->username,
                        '{{ticketid}}' => $ticketdata->ticket_id,
                    ];
                    $fromdescription = Str::replace(array_keys($fromtempdata), array_values($fromtempdata), $getfromtemplate->content);
                }
                
                $touser = $ticketdata->user_id;
                $Userdata = User::where('user_type', 1)->select('id')->first();
                if ($Userdata) {
                    $touser = $Userdata->id;
                }
                
                $data = [
                    'communication_type' => '3',
                    'source' => $source,
                    'reference_id' => $ticketdata->id,
                    'user_id' => $userId,
                    'to_user_id' => $touser,
                    'to_description' => $todescription,
                    'from_description' => $fromdescription
                ];

                try {
                    $request = new Request($data);
                    $notification = new NotificationController();
                    $notification->Storenotification($request);
                } catch (\Exception $e) {
                    Log::error('Error storing notification: ' . $e->getMessage());
                }
            }
        }
    }

    protected function handleStatusNotification($ticketdata, $request, $source)
    {
        $controller = new Controller();
        $notificationsettings = $controller->getnotificationsettings(3, $source);
        
        if ($notificationsettings == 1) {
            $gettemplate = Templates::select('templates.subject', 'templates.content')
                ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                ->where('notification_types.type', $source)
                ->where('recipient_type', 2)
                ->where('templates.type', 3)
                ->where('templates.status', 1)
                ->first();
                
            if ($gettemplate && $ticketdata) {
                $username = $source == 'Assign Ticket' ? $ticketdata->assignee_name : $ticketdata->user_name;
                
                $tempdata = [
                    '{{user_name}}' => $username,
                    '{{ticketid}}' => $ticketdata->ticket_id,
                ];
                
                $todescription = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
                
                $getfromtemplate = Templates::select('templates.subject', 'templates.content')
                    ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                    ->where('notification_types.type', $source)
                    ->where('recipient_type', 1)
                    ->where('templates.type', 3)
                    ->where('templates.status', 1)
                    ->first();
                    
                $fromdescription = "";
                if ($getfromtemplate) {
                    $fromtempdata = [
                        '{{user_name}}' => $username,
                        '{{ticketid}}' => $ticketdata->ticket_id,
                    ];
                    $fromdescription = Str::replace(array_keys($fromtempdata), array_values($fromtempdata), $getfromtemplate->content);
                }
                
                $touser = $source == 'Assign Ticket' ? $ticketdata->assignee_id : $ticketdata->user_id;
                $userid = $source == 'Assign Ticket' ? $request['auth_id'] : ($request['user_id'] ?? $request['auth_id']);
                
                $data = [
                    'communication_type' => '3',
                    'source' => $source,
                    'reference_id' => $ticketdata->id,
                    'user_id' => $userid,
                    'to_user_id' => $touser,
                    'to_description' => $todescription,
                    'from_description' => $fromdescription
                ];
                
                try {
                    $request = new Request($data);
                    $notification = new NotificationController();
                    $notification->Storenotification($request);
                } catch (\Exception $e) {
                    Log::error('Error storing notification: ' . $e->getMessage());
                }
                
                if (in_array($source, ['Ticket Closed', 'Ticket Inprogress'])) {
                    $this->handleAdminNotifications($ticketdata, $source, $request);
                }
            }
        }
    }

    protected function handleAdminNotifications($ticketdata, $source, $request)
    {
        $Userdata = User::where('user_type', 1)->where('id', $request['user_id'])->get();
        
        if (count($Userdata) < 1) {
            $getadmintemplate = Templates::select('templates.subject', 'templates.content')
                ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                ->where('notification_types.type', $source)
                ->where('recipient_type', 3)
                ->where('templates.type', 3)
                ->where('templates.status', 1)
                ->first();
                
            if ($getadmintemplate) {
                $username = $ticketdata->user_name;
                if ($source == 'Assign Ticket') {
                    $username = $ticketdata->assignee_name;
                }
                
                $admintempdata = [
                    '{{user_name}}' => $username,
                    '{{ticketid}}' => $ticketdata->ticket_id,
                ];
                
                $admindescription = Str::replace(array_keys($admintempdata), array_values($admintempdata), $getadmintemplate->content);
                
                $AdminUsers = User::where('user_type', 1)->select('id')->get();
                
                foreach ($AdminUsers as $admin) {
                    $data = [
                        'communication_type' => '3',
                        'source' => $source,
                        'reference_id' => $ticketdata->id,
                        'user_id' => '0',
                        'to_user_id' => $admin->id,
                        'to_description' => $admindescription,
                        'from_description' => $source
                    ];

                    try {
                        $request = new Request($data);
                        $notification = new NotificationController();
                        $notification->Storenotification($request);
                    } catch (\Exception $e) {
                        Log::error('Error storing admin notification: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    function mapDateFormatToSQL($phpFormat)
    {
        $replacements = [
            'd' => '%d',
            'D' => '%a',
            'j' => '%e',
            'l' => '%W',
            'F' => '%M',
            'm' => '%m',
            'M' => '%b',
            'n' => '%c',
            'Y' => '%Y',
            'y' => '%y',
        ];

        return strtr($phpFormat, $replacements);
    }
}