<?php

namespace Modules\GoogleCalendarSync\Repositories\Contracts;

interface GoogleCalendarSyncRepositoryInterface
{
    /**
     * Get all synced calendar events for a user or provider.
     *
     * @param int $userId
     * @return array
     */
    public function getEvents(int $userId): array;

    /**
     * Sync a new event to Google Calendar.
     *
     * @param int $userId
     * @param array $eventData
     * @return mixed
     */
    public function syncEvent(int $userId, array $eventData);

    /**
     * Update an existing event in Google Calendar.
     *
     * @param int $userId
     * @param string $eventId
     * @param array $eventData
     * @return mixed
     */
    public function updateEvent(int $userId, string $eventId, array $eventData);

    /**
     * Delete an event from Google Calendar.
     *
     * @param int $userId
     * @param string $eventId
     * @return bool
     */
    public function deleteEvent(int $userId, string $eventId): bool;

    /**
     * Fetch Google Calendar details for the user.
     *
     * @param int $userId
     * @return array
     */
    public function getCalendarDetails(int $userId): array;
}
