@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Event details" back />

<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full px-6 pt-8 pb-10 gap-7">
        @if ($event)
            <native:column class="w-full gap-4">
                <native:text class="text-xs font-semibold tracking-widest text-theme-primary">{{ $event->category }}</native:text>
                <native:text class="text-4xl font-bold leading-tight text-theme-on-background">{{ $event->title }}</native:text>
                <native:text class="text-lg text-theme-on-surface-variant">{{ $event->starts_at->format('l, j F Y') }}</native:text>
            </native:column>

            <native:divider class="bg-theme-outline-variant" />

            <native:row class="w-full items-center gap-4">
                <native:icon :android="Android::Schedule" :ios="Ios::Clock" :size="24" class="text-theme-primary" />
                <native:column class="gap-1">
                    <native:text class="text-base font-semibold text-theme-on-background">{{ $event->starts_at->format('H:i') }} – {{ $event->starts_at->addMinutes($event->duration_minutes)->format('H:i') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $event->duration_minutes }} minutes</native:text>
                </native:column>
            </native:row>

            <native:row class="w-full items-center gap-4">
                <native:icon :android="Android::LocationOn" :ios="Ios::MappinAndEllipse" :size="24" class="text-theme-primary" />
                <native:text class="text-base text-theme-on-background">{{ $event->location }}</native:text>
            </native:row>

            <native:text class="text-base leading-relaxed text-theme-on-surface-variant">{{ $event->description }}</native:text>

            <native:column class="w-full p-5 gap-4 rounded-2xl bg-theme-primary/10">
                <native:row class="items-center gap-3">
                    <native:icon :android="Android::NotificationsActive" :ios="Ios::Bell" :size="24" class="text-theme-primary" />
                    <native:text class="text-lg font-semibold text-theme-on-background">Give yourself a nudge.</native:text>
                </native:row>
                <native:text class="text-sm leading-relaxed text-theme-on-surface-variant">Try a quick reminder for this event. It works even when you're offline.</native:text>
                <native:button variant="primary" class="w-full" @tap="remind" ref="remind" :disabled="$awaitingPermission">Remind me in 30 seconds</native:button>
                @if ($message !== '')
                    <native:text class="text-sm leading-relaxed text-theme-on-background">{{ $message }}</native:text>
                @endif
                @if ($permissionDenied)
                    <native:button variant="secondary" @tap="openSettings">Open app settings</native:button>
                @endif
            </native:column>
        @else
            <native:text class="text-3xl font-bold text-theme-on-background">Event not found</native:text>
            <native:text class="text-base text-theme-on-surface-variant">This event is no longer in your calendar.</native:text>
            <native:button @navigate.replace="'/'">Back to calendar</native:button>
        @endif
    </native:column>
</native:scroll-view>
