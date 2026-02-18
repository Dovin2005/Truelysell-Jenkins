<?php

namespace App\Repositories\Eloquent;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Repositories\Contracts\TicketInterface;
use Illuminate\Support\Facades\DB;

class TicketRepository implements TicketInterface
{
    public function getAllTickets(array $filters, string $currentRouteName, int $userId = null)
    {
        $query = Ticket::select(
            DB::raw("DATE_FORMAT(tickets.created_at, '{$filters['sql_date_format']}') AS ticket_date"),
            DB::raw("DATE_FORMAT(tickets.updated_at, '{$filters['sql_date_format']}') AS updatedat"),
            DB::raw("
                CASE
                    WHEN tickets.status = 1 THEN 'Open'
                    WHEN tickets.status = 2 THEN 'Assigned'
                    WHEN tickets.status = 3 THEN 'InProgress'
                    WHEN tickets.status = 4 THEN 'Closed'
                    ELSE 'Unknown'
                END AS ticket_status
            "),
            'tickets.*',
            DB::raw('(SELECT profile_image FROM user_details WHERE user_details.user_id = tickets.user_id and user_details.deleted_at is NULL LIMIT 1) as profile_image'),
            DB::raw('(SELECT profile_image FROM user_details WHERE user_details.user_id = tickets.assignee_id and user_details.deleted_at is NULL LIMIT 1) as assign_profileimage'),
            DB::raw("(SELECT 
                CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                END
             FROM users
             LEFT JOIN user_details ON user_details.user_id = users.id
             WHERE users.id = tickets.assignee_id AND users.deleted_at IS NULL LIMIT 1) AS assignee_name")
        );

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ticket_id', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('subject', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($currentRouteName == 'user.ticket' || $currentRouteName == 'provider.ticket' || $currentRouteName == 'staff.ticket') {
            $query->where('tickets.user_id', $userId);
        }

        if ($currentRouteName == 'staff.tickets') {
            $query->where('tickets.assignee_id', $userId);
        }

        return $query->orderBy($filters['sort_by'], $filters['order_by'])
                    ->paginate($currentRouteName == 'user.ticket' ? 5 : 8);
    }

    public function getTicketById(int $id)
    {
        return Ticket::select(
            DB::raw("
                CASE
                    WHEN tickets.status = 1 THEN 'Open'
                    WHEN tickets.status = 2 THEN 'Assigned'
                    WHEN tickets.status = 3 THEN 'InProgress'
                    WHEN tickets.status = 4 THEN 'Closed'
                    ELSE 'Unknown'
                END AS ticket_status
            "),
            'tickets.*',
            DB::raw('(SELECT email FROM users WHERE users.id = tickets.user_id and users.deleted_at is NULL LIMIT 1) as email'),
            DB::raw('(SELECT profile_image FROM user_details WHERE user_details.user_id = tickets.user_id and user_details.deleted_at is NULL LIMIT 1) as profile_image'),
            DB::raw("(SELECT 
                CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                END
             FROM users
             JOIN user_details ON user_details.user_id = users.id
             WHERE users.id = tickets.assignee_id AND users.deleted_at IS NULL LIMIT 1) AS assignee_name"),
            DB::raw("(SELECT 
                CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                END
             FROM users
             LEFT JOIN user_details ON user_details.user_id = users.id
             WHERE users.id = tickets.user_id AND users.deleted_at IS NULL LIMIT 1) AS username"),
            DB::raw('(SELECT profile_image FROM user_details WHERE user_details.user_id = tickets.assignee_id and user_details.deleted_at is NULL LIMIT 1) as assign_profileimage')
        )->where('tickets.id', $id)->first();
    }

    public function createTicket(array $data)
    {
        $year = date('Y');
        $latestTicket = Ticket::count();
        $nextNumber = $latestTicket ? $latestTicket + 1 : 1;
        $ticketNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $data['ticket_id'] = $year . '-' . $ticketNumber;

        return Ticket::create($data);
    }

    public function updateTicket(int $id, array $data)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);
        return $ticket;
    }

    public function updateTicketStatus(int $id, array $data)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);
        return $ticket;
    }

    public function createTicketHistory(array $data)
    {
        return TicketHistory::create($data);
    }

    public function getTicketHistory(int $ticketId)
    {
        return TicketHistory::select(
            'ticket_history.*',
            DB::raw('(SELECT profile_image FROM user_details WHERE user_details.user_id = ticket_history.user_id and user_details.deleted_at is NULL LIMIT 1) as profile_image'),
            DB::raw("(SELECT 
                CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                END
             FROM users
             LEFT JOIN user_details ON user_details.user_id = users.id
             WHERE users.id = ticket_history.user_id AND users.deleted_at IS NULL LIMIT 1) AS username")
        )->where('ticket_history.ticket_id', $ticketId)->get();
    }

    public function getTicketByTicketId(string $ticketId)
    {
        return Ticket::where('ticket_id', $ticketId)->first();
    }

    public function getTicketUsers(int $userType)
    {
        return User::join('user_details', 'user_details.user_id', '=', 'users.id')
            ->where('user_type', $userType)
            ->select(
                'users.id',
                DB::raw("CASE 
                    WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL 
                        THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
                    ELSE users.name 
                END AS user_name"),
                'user_details.profile_image'
            )->get();
    }
}