<?php

namespace App\NativeComponents;

use App\Models\CalendarEvent;
use Illuminate\View\View;
use Native\Mobile\Attributes\On;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\PushNotifications;
use Native\Mobile\Facades\System;
use NativePHP\LocalNotifications\Events\PermissionGranted;
use NativePHP\LocalNotifications\Facades\LocalNotifications;

class EventDetail extends NativeComponent
{
    public string $eventId = '';

    public string $message = '';

    public bool $awaitingPermission = false;

    public bool $permissionDenied = false;

    public int $permissionDeadline = 0;

    public function mount(): void
    {
        $this->eventId = (string) $this->param('id');
    }

    public function remind(): void
    {
        if (! CalendarEvent::query()->whereKey($this->eventId)->exists()) {
            return;
        }

        $this->message = '';
        $this->permissionDenied = false;

        if (PushNotifications::checkPermission() === 'granted') {
            $this->scheduleReminder();

            return;
        }

        $this->awaitingPermission = true;
        $this->permissionDeadline = now()->timestamp + 20;
        $this->message = 'Allow notifications to receive your reminder.';
        LocalNotifications::requestPermission();
    }

    #[Poll(1000)]
    public function checkPendingPermission(): void
    {
        if (! $this->awaitingPermission) {
            return;
        }

        if (PushNotifications::checkPermission() === 'granted') {
            $this->permissionResult(true);
        } elseif (now()->timestamp >= $this->permissionDeadline) {
            $this->permissionResult(false);
        }
    }

    #[On(PermissionGranted::class)]
    public function permissionResult(bool $granted): void
    {
        if (! $this->awaitingPermission) {
            return;
        }

        $this->awaitingPermission = false;

        if (! $granted) {
            $this->permissionDenied = true;
            $this->message = 'Notifications are off. Enable them in app settings, then try again.';

            return;
        }

        $this->scheduleReminder();
    }

    public function openSettings(): void
    {
        System::appSettings();
    }

    private function scheduleReminder(): void
    {
        $event = CalendarEvent::query()->find($this->eventId);

        if ($event === null) {
            return;
        }

        LocalNotifications::send('event-'.$event->id)
            ->title($event->title)
            ->body('Your Daymark reminder. Tap to see the event.')
            ->url($this->route('events.show', ['id' => $event->id]))
            ->delay(30);

        $this->message = 'Reminder set for 30 seconds from now. Leave the app to see it arrive.';
    }

    public function render(): View
    {
        return view('native.event-detail', [
            'event' => CalendarEvent::query()->find($this->eventId),
        ]);
    }
}
