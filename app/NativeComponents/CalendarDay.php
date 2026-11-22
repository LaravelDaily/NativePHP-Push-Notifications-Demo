<?php

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class CalendarDay extends NativeComponent
{
    public string $date = '';

    public int $number = 1;

    public bool $inMonth = true;

    public bool $selected = false;

    public bool $hasEvents = false;

    public function select(): void
    {
        $this->emit('selected', $this->date);
    }

    public function render(): View
    {
        return view('native.calendar-day');
    }
}
