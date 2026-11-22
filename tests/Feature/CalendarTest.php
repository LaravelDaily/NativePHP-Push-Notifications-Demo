<?php

use App\Models\CalendarEvent;
use Database\Seeders\CalendarEventSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Native\Mobile\Testing\Native;

uses(LazilyRefreshDatabase::class);

test('the calendar shows today and opens an event from the agenda', function () {
    $this->travelTo(now()->setDate(2026, 11, 20)->setTime(9, 0));
    CalendarEvent::factory()->create(['id' => 'workshop', 'title' => 'NativePHP workshop', 'starts_at' => '2026-11-20 11:00:00']);

    Native::visit('/')
        ->assertSee('November')
        ->assertSee('Friday, 20 November')
        ->assertSee('NativePHP workshop')
        ->tap('NativePHP workshop')
        ->assertNavigatedTo('/events/workshop')
        ->follow()
        ->assertSee('NativePHP workshop')
        ->assertSee('Remind me in 30 seconds')
        ->assertAccessible();
});

test('selecting a day through its child component updates the agenda', function () {
    $this->travelTo(now()->setDate(2026, 11, 20)->setTime(9, 0));
    CalendarEvent::factory()->create(['title' => 'Saturday session', 'starts_at' => '2026-11-21 11:00:00']);

    Native::visit('/')
        ->tap('day-2026-11-21')
        ->assertSet('selectedDate', '2026-11-21')
        ->assertSee('Saturday session')
        ->assertDontSee('Record the NativePHP demo');
});

test('month navigation clamps the selected day to the destination month', function () {
    Native::visit('/')
        ->call('selectDate', '2027-01-31')
        ->tap('next-month')
        ->assertSet('selectedDate', '2027-02-28')
        ->assertSee('February')
        ->tap('previous-month')
        ->assertSee('January')
        ->call('selectDate', '2027-12-31')
        ->tap('next-month')
        ->assertSet('selectedDate', '2028-01-31')
        ->tap('next-month')
        ->assertSet('selectedDate', '2028-02-29');
});

test('empty days show an empty agenda and invalid selections leave it unchanged', function () {
    Native::visit('/')
        ->call('selectDate', '2030-04-10')
        ->assertSee('No events planned for this day.')
        ->call('selectDate', '2030-02-31')
        ->assertSet('selectedDate', '2030-04-10')
        ->assertAccessible();
});

test('an unknown notification destination shows an event not found screen', function () {
    Native::visit('/events/missing')
        ->assertSee('Event not found')
        ->tap('Back to calendar')
        ->assertReplacedWith('/');
});

test('seeding again keeps existing event data and avoids duplicates', function () {
    CalendarEvent::query()->findOrFail('studio-session')->update(['title' => 'My edited recording']);

    $this->seed(CalendarEventSeeder::class);

    expect(CalendarEvent::query()->count())->toBe(8);
    expect(CalendarEvent::query()->findOrFail('studio-session')->title)->toBe('My edited recording');
});
