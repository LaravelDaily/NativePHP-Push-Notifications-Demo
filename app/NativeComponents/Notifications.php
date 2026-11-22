<?php

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Attributes\On;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\PushNotification\TokenGenerated;
use Native\Mobile\Facades\PushNotifications;
use Native\Mobile\Facades\System;

class Notifications extends NativeComponent
{
    public string $token = '';

    public string $message = '';

    public string $permission = '';

    public bool $connecting = false;

    public int $connectionDeadline = 0;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function onResume(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        $this->permission = PushNotifications::checkPermission() ?? 'not_determined';
        $this->token = PushNotifications::getToken() ?? '';

        if ($this->token !== '' && $this->permission === 'granted') {
            $this->connecting = false;
            $this->message = 'Connected. This device can receive event announcements.';
        }
    }

    public function enable(): void
    {
        if (! config('services.firebase.configured')) {
            $this->message = 'Remote notifications are not connected for this build yet.';

            return;
        }

        if ($this->permission === 'denied') {
            $this->message = 'Notifications are off. Enable them in app settings, then come back.';

            return;
        }

        $this->message = 'Connecting this device…';
        $this->connecting = true;
        $this->connectionDeadline = now()->timestamp + 30;
        PushNotifications::enroll();
    }

    #[Poll(1000)]
    public function checkConnection(): void
    {
        if (! $this->connecting) {
            return;
        }

        $this->refreshStatus();

        if ($this->connecting && now()->timestamp >= $this->connectionDeadline) {
            $this->connecting = false;
            $this->message = $this->permission === 'denied'
                ? 'Notifications are off. Enable them in app settings, then come back.'
                : 'Not connected yet. Check your internet connection and try again.';
        }
    }

    #[On(TokenGenerated::class)]
    public function tokenGenerated(string $token): void
    {
        $this->token = $token;
        $this->permission = PushNotifications::checkPermission() ?? 'not_determined';

        if ($this->permission === 'granted') {
            $this->connecting = false;
            $this->message = 'Connected. This device can receive event announcements.';
        }
    }

    public function openSettings(): void
    {
        System::appSettings();
    }

    public function render(): View
    {
        return view('native.notifications');
    }
}
