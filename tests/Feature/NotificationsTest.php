<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Native\Mobile\Events\PushNotification\TokenGenerated;
use Native\Mobile\Testing\Native;
use NativePHP\LocalNotifications\Events\PermissionGranted;

uses(LazilyRefreshDatabase::class);

test('a permitted reminder schedules the event notification with a thirty second delay and destination', function () {
    $this->travelTo(now()->setDate(2026, 11, 20)->setTime(9, 0));
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'granted']);

    Native::visit('/events/studio-session')
        ->tap('remind')
        ->assertScheduled(fn (array $notification): bool => $notification['id'] === 'event-studio-session'
            && $notification['title'] === 'Record the NativePHP demo'
            && $notification['url'] === '/events/studio-session'
            && $notification['scheduledAt'] === 1795165230)
        ->assertSee('Reminder set for 30 seconds from now.');
});

test('a reminder waits for permission before it is scheduled', function () {
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'not_determined']);

    Native::visit('/events/studio-session')
        ->tap('remind')
        ->assertPermissionRequested()
        ->assertNothingSent()
        ->emitNative(PermissionGranted::class, ['granted' => true])
        ->assertSet('awaitingPermission', false)
        ->assertScheduled();
});

test('denying permission schedules nothing and offers app settings', function () {
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'not_determined']);

    Native::visit('/events/studio-session')
        ->tap('remind')
        ->emitNative(PermissionGranted::class, ['granted' => false])
        ->assertSee('Notifications are off.')
        ->assertNothingSent()
        ->tap('Open app settings')
        ->assertNativeCalled('System.OpenAppSettings');
});

test('an unsolicited permission event does not schedule a reminder', function () {
    Native::visit('/events/studio-session')
        ->emitNative(PermissionGranted::class, ['granted' => true])
        ->assertNothingSent();
});

test('announcements do not attempt enrollment before Firebase is configured', function () {
    config(['services.firebase.configured' => false]);

    Native::visit('/notifications')
        ->tap('Enable announcements')
        ->assertSee('Remote notifications are not connected for this build yet.')
        ->assertNativeNotCalled('PushNotification.RequestPermission')
        ->assertAccessible();
});

test('enabling announcements enrolls the device and displays its generated token', function () {
    config(['services.firebase.configured' => true]);
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'granted']);

    Native::visit('/notifications')
        ->tap('Enable announcements')
        ->assertNativeCalled('PushNotification.RequestPermission')
        ->emitNative(TokenGenerated::class, ['token' => 'test-device-token'])
        ->assertSet('token', 'test-device-token')
        ->assertSee('This device is connected');
});

test('denied announcement permission offers settings without enrolling', function () {
    config(['services.firebase.configured' => true]);
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'denied']);

    Native::visit('/notifications')
        ->tap('Enable announcements')
        ->assertSee('Notifications are off.')
        ->assertNativeNotCalled('PushNotification.RequestPermission');
});

test('a reminder detects permission granted when Android omits the plugin callback', function () {
    $bridge = Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'not_determined']);
    $screen = Native::visit('/events/studio-session')->tap('remind');
    $bridge->respondTo('PushNotification.CheckPermission', ['status' => 'granted']);

    $screen->firePoll('checkPendingPermission')
        ->assertSet('awaitingPermission', false)
        ->assertScheduled();
});

test('receiving a device token does not claim notification permission was granted', function () {
    config(['services.firebase.configured' => true]);
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'not_determined']);

    Native::visit('/notifications')
        ->tap('Enable announcements')
        ->emitNative(TokenGenerated::class, ['token' => 'test-device-token'])
        ->assertSet('permission', 'not_determined')
        ->assertDontSee('This device is connected');
});

test('an unanswered reminder permission request times out with settings guidance', function () {
    $this->freezeTime();
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'not_determined']);
    $screen = Native::visit('/events/studio-session')->tap('remind');
    $this->travel(21)->seconds();

    $screen->firePoll('checkPendingPermission')
        ->assertSet('awaitingPermission', false)
        ->assertSee('Open app settings')
        ->assertNothingSent();
});

test('an announcement connection timeout offers a retry instead of claiming success', function () {
    $this->freezeTime();
    config(['services.firebase.configured' => true]);
    Native::fakeBridge()->respondTo('PushNotification.CheckPermission', ['status' => 'granted']);
    $screen = Native::visit('/notifications')->tap('Enable announcements');
    $this->travel(31)->seconds();

    $screen->firePoll('checkConnection')
        ->assertSet('connecting', false)
        ->assertSee('Check your internet connection and try again.')
        ->assertDontSee('This device is connected');
});
