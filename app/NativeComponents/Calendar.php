<?php

namespace App\NativeComponents;

use App\Models\CalendarEvent;
use Carbon\CarbonImmutable;
use Carbon\WeekDay;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Device;

class Calendar extends NativeComponent
{
    public string $month = '';

    public string $selectedDate = '';

    public function mount(): void
    {
        $today = CarbonImmutable::now(Device::getTimezone() ?? config('app.timezone'));
        $this->month = $today->startOfMonth()->toDateString();
        $this->selectedDate = $today->toDateString();
    }

    public function previousMonth(): void
    {
        $this->changeMonth(-1);
    }

    public function nextMonth(): void
    {
        $this->changeMonth(1);
    }

    public function today(): void
    {
        $this->mount();
    }

    public function selectDate(string $date): void
    {
        if (Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d'])->fails()) {
            return;
        }

        $this->selectedDate = $date;
        $this->month = CarbonImmutable::parse($date)->startOfMonth()->toDateString();
    }

    private function changeMonth(int $offset): void
    {
        $month = CarbonImmutable::parse($this->month)->addMonths($offset);
        $selectedDay = min(CarbonImmutable::parse($this->selectedDate)->day, $month->daysInMonth);
        $this->month = $month->toDateString();
        $this->selectedDate = $month->day($selectedDay)->toDateString();
    }

    public function render(): View
    {
        $month = CarbonImmutable::parse($this->month);
        $firstCell = $month->startOfWeek(WeekDay::Monday);
        $lastCell = $month->endOfMonth()->endOfWeek(WeekDay::Sunday);
        $events = CalendarEvent::query()
            ->whereBetween('starts_at', [$firstCell->startOfDay(), $lastCell->endOfDay()])
            ->orderBy('starts_at')->orderBy('id')->get();
        $eventDates = $events->groupBy(fn (CalendarEvent $event): string => $event->starts_at->toDateString());
        $days = [];

        for ($day = $firstCell; $day->lte($lastCell); $day = $day->addDay()) {
            $date = $day->toDateString();
            $days[] = [
                'date' => $date,
                'number' => $day->day,
                'inMonth' => $day->month === $month->month,
                'selected' => $date === $this->selectedDate,
                'hasEvents' => $eventDates->has($date),
            ];
        }

        return view('native.calendar', [
            'monthLabel' => $month->format('F'),
            'yearLabel' => $month->format('Y'),
            'days' => $days,
            'selectedLabel' => CarbonImmutable::parse($this->selectedDate)->format('l, j F'),
            'events' => $eventDates->get($this->selectedDate, collect()),
        ]);
    }
}
