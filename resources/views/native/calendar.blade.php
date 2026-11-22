@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Daymark" display-mode="inline">
    <native:top-bar-action id="today" label="Today" @tap="today"
        :android-icon="Android::CalendarToday" :ios-icon="Ios::Calendar" />
</native:top-bar>

<native:bottom-nav :active-color="theme('primary')">
    <native:bottom-nav-item id="calendar" label="Calendar" url="/" :android-icon="Android::CalendarMonth" :ios-icon="Ios::Calendar" />
    <native:bottom-nav-item id="notifications" label="Notifications" url="/notifications" :android-icon="Android::NotificationsNone" :ios-icon="Ios::Bell" />
</native:bottom-nav>

<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full px-5 pt-5 pb-8 gap-5">
        <native:row class="w-full items-center justify-between">
            <native:column class="gap-1">
                <native:text class="text-xs font-semibold tracking-widest text-theme-primary">MAKE ROOM FOR YOUR DAY</native:text>
                <native:row class="items-center gap-2">
                    <native:text class="text-4xl font-bold text-theme-on-background">{{ $monthLabel }}</native:text>
                    <native:text class="text-lg text-theme-on-surface-variant">{{ $yearLabel }}</native:text>
                </native:row>
            </native:column>
            <native:row class="items-center">
                <native:button variant="ghost" size="sm" :android-icon="Android::ChevronLeft" :ios-icon="Ios::ChevronLeft"
                    a11y-label="Previous month" ref="previous-month" @tap="previousMonth" />
                <native:button variant="ghost" size="sm" :android-icon="Android::ChevronRight" :ios-icon="Ios::ChevronRight"
                    a11y-label="Next month" ref="next-month" @tap="nextMonth" />
            </native:row>
        </native:row>

        <native:column class="w-full rounded-3xl bg-theme-surface p-2 gap-2">
            <native:row class="w-full pt-2">
                @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $weekday)
                    <native:text class="flex-1 text-center text-xs font-semibold text-theme-on-surface-variant">{{ $weekday }}</native:text>
                @endforeach
            </native:row>
            <native:lazy-grid :columns="7" :gap="0" class="w-full">
                @foreach ($days as $day)
                    <native:calendar-day :date="$day['date']" :number="$day['number']" :in-month="$day['inMonth']"
                        :selected="$day['selected']" :has-events="$day['hasEvents']"
                        key="day-{{ $day['date'] }}" @selected="selectDate" />
                @endforeach
            </native:lazy-grid>
        </native:column>

        <native:row class="w-full items-center justify-between">
            <native:column class="gap-1">
                <native:text class="text-xs font-semibold tracking-widest text-theme-on-surface-variant">YOUR AGENDA</native:text>
                <native:text class="text-xl font-semibold text-theme-on-background">{{ $selectedLabel }}</native:text>
            </native:column>
            <native:text class="text-sm text-theme-on-surface-variant">{{ $events->count() }} {{ $events->count() === 1 ? 'event' : 'events' }}</native:text>
        </native:row>

        <native:column class="w-full gap-1">
            @forelse ($events as $event)
                <native:pressable class="w-full py-4" @navigate="$this->route('events.show', ['id' => $event->id])"
                    a11y-label="{{ $event->title }}, {{ $event->starts_at->format('H:i') }}">
                    <native:row class="w-full items-center gap-4">
                        <native:column class="w-12 gap-1">
                            <native:text class="text-sm font-semibold text-theme-on-background">{{ $event->starts_at->format('H:i') }}</native:text>
                            <native:text class="text-xs text-theme-on-surface-variant">{{ $event->duration_minutes }} min</native:text>
                        </native:column>
                        <native:column class="w-1 h-12 rounded-full bg-theme-primary/40" />
                        <native:column class="flex-1 gap-1">
                            <native:text class="text-xs font-semibold tracking-wider text-theme-primary">{{ $event->category }}</native:text>
                            <native:text class="text-base font-semibold text-theme-on-background">{{ $event->title }}</native:text>
                            <native:text class="text-sm text-theme-on-surface-variant">{{ $event->location }}</native:text>
                        </native:column>
                        <native:icon :android="Android::ChevronRight" :ios="Ios::ChevronRight" :size="20" class="text-theme-on-surface-variant" />
                    </native:row>
                </native:pressable>
                <native:divider class="bg-theme-outline-variant" />
            @empty
                <native:column class="w-full items-center py-10 gap-3">
                    <native:icon :android="Android::CalendarToday" :ios="Ios::Calendar" :size="32" class="text-theme-primary" />
                    <native:text class="text-lg font-semibold text-theme-on-background">A little breathing room.</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">No events planned for this day.</native:text>
                </native:column>
            @endforelse
        </native:column>
    </native:column>
</native:scroll-view>
