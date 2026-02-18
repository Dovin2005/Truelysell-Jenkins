<?php

namespace Modules\GoogleCalendarSync\Repositories\Eloquent;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Modules\GoogleCalendarSync\Repositories\Contracts\GoogleCalendarSyncRepositoryInterface;

class GoogleCalendarSyncRepository implements GoogleCalendarSyncRepositoryInterface
{
    protected Google_Client $client;
    protected Google_Service_Calendar $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes([Google_Service_Calendar::CALENDAR]);
        $this->client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $this->client->setAccessType('offline');

        $this->service = new Google_Service_Calendar($this->client);
    }

    public function getEvents(int $userId): array
    {
        // Replace 'primary' with a calendar ID if needed
        $events = $this->service->events->listEvents('primary');

        return $events->getItems();
    }

    public function syncEvent(int $userId, array $eventData)
    {
        $event = new Google_Service_Calendar_Event([
            'summary'     => $eventData['summary'] ?? 'No Title',
            'description' => $eventData['description'] ?? null,
            'start'       => ['dateTime' => $eventData['start'], 'timeZone' => $eventData['timeZone'] ?? 'UTC'],
            'end'         => ['dateTime' => $eventData['end'], 'timeZone' => $eventData['timeZone'] ?? 'UTC'],
        ]);

        return $this->service->events->insert('primary', $event);
    }

    public function updateEvent(int $userId, string $eventId, array $eventData)
    {
        $event = $this->service->events->get('primary', $eventId);

        if (isset($eventData['summary'])) {
            $event->setSummary($eventData['summary']);
        }
        if (isset($eventData['description'])) {
            $event->setDescription($eventData['description']);
        }
        if (isset($eventData['start'])) {
            $event->setStart(['dateTime' => $eventData['start'], 'timeZone' => $eventData['timeZone'] ?? 'UTC']);
        }
        if (isset($eventData['end'])) {
            $event->setEnd(['dateTime' => $eventData['end'], 'timeZone' => $eventData['timeZone'] ?? 'UTC']);
        }

        return $this->service->events->update('primary', $event->getId(), $event);
    }

    public function deleteEvent(int $userId, string $eventId): bool
    {
        $this->service->events->delete('primary', $eventId);
        return true;
    }

    public function getCalendarDetails(int $userId): array
    {
        $calendar = $this->service->calendars->get('primary');
        return $calendar->toSimpleObject();
    }
}
