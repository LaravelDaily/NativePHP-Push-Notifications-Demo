<?php

use App\NativeComponents\Calendar;
use App\NativeComponents\EventDetail;
use App\NativeComponents\Notifications;
use Illuminate\Support\Facades\Route;

Route::native('/', Calendar::class)->name('calendar');
Route::native('/events/{id}', EventDetail::class)->name('events.show');
Route::native('/notifications', Notifications::class)->name('notifications');
