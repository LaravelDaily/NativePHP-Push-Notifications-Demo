<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:push
    {token : The FCM token shown on the phone}
    {--event=community-live : The event ID to open on tap}
    {--title=Livestream update : Notification title}
    {--body=We start in 15 minutes. See you there! : Notification body}')]
#[Description('Send a Daymark announcement with a link to an event')]
class SendDemoPush extends Command
{
    public function handle(): int
    {
        $eventId = (string) $this->option('event');

        if (! preg_match('/\A[a-zA-Z0-9_-]{1,100}\z/', $eventId)) {
            $this->error('Use an event ID containing only letters, numbers, hyphens, or underscores.');

            return self::FAILURE;
        }

        return $this->call('fcm:send', [
            'token' => $this->argument('token'),
            '--title' => $this->option('title'),
            '--body' => $this->option('body'),
            '--url' => '/events/'.$eventId,
        ]);
    }
}
