<?php

namespace App\Repositories\Contracts;

interface TicketInterface
{
    public function getAllTickets(array $filters, string $currentRouteName, int $userId = null);
    public function getTicketById(int $id);
    public function createTicket(array $data);
    public function updateTicket(int $id, array $data);
    public function updateTicketStatus(int $id, array $data);
    public function createTicketHistory(array $data);
    public function getTicketHistory(int $ticketId);
    public function getTicketByTicketId(string $ticketId);
    public function getTicketUsers(int $userType);
}