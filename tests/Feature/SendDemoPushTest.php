<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('the push command reports missing credentials without contacting Firebase', function () {
    config(['services.firebase.credentials' => null]);
    Http::preventStrayRequests();

    $this->artisan('demo:push', ['token' => 'test-device-token'])
        ->expectsOutputToContain('Set FIREBASE_CREDENTIALS')
        ->assertFailed();

    Http::assertNothingSent();
});

test('the push command sends the announcement and event destination through Firebase', function () {
    Storage::fake('local');
    $privateKey = openssl_pkey_new(['private_key_bits' => 2048]);
    openssl_pkey_export($privateKey, $pem);
    Storage::disk('local')->put('firebase-test.json', json_encode([
        'project_id' => 'daymark-demo',
        'client_email' => 'sender@daymark-demo.iam.gserviceaccount.com',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'private_key' => $pem,
    ]));
    config(['services.firebase.credentials' => Storage::disk('local')->path('firebase-test.json')]);
    Http::preventStrayRequests();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
        'https://fcm.googleapis.com/v1/projects/daymark-demo/messages:send' => Http::response(['name' => 'projects/daymark-demo/messages/123']),
    ]);

    $this->artisan('demo:push', ['token' => 'test-device-token'])
        ->expectsOutputToContain('Sent: projects/daymark-demo/messages/123')
        ->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://fcm.googleapis.com/v1/projects/daymark-demo/messages:send'
        && $request->hasHeader('Authorization', 'Bearer test-access-token')
        && $request['message']['token'] === 'test-device-token'
        && $request['message']['data']['url'] === '/events/community-live'
        && $request['message']['notification']['title'] === 'Livestream update');
});

test('the push command rejects destinations outside the event route', function () {
    Http::preventStrayRequests();

    $this->artisan('demo:push', ['token' => 'test-device-token', '--event' => '../notifications'])
        ->expectsOutputToContain('Use an event ID')
        ->assertFailed();

    Http::assertNothingSent();
});
