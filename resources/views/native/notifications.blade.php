@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Notifications" />
<native:bottom-nav :active-color="theme('primary')">
    <native:bottom-nav-item id="calendar" label="Calendar" url="/" :android-icon="Android::CalendarMonth" :ios-icon="Ios::Calendar" />
    <native:bottom-nav-item id="notifications" label="Notifications" url="/notifications" :android-icon="Android::NotificationsNone" :ios-icon="Ios::Bell" />
</native:bottom-nav>

<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full px-6 pt-8 pb-10 gap-7">
        <native:column class="w-16 h-16 items-center justify-center rounded-2xl bg-theme-primary/10">
            <native:icon :android="Android::NotificationsActive" :ios="Ios::Bell" :size="30" class="text-theme-primary" />
        </native:column>
        <native:column class="w-full gap-3">
            <native:text class="text-3xl font-bold text-theme-on-background">Stay in the loop.</native:text>
            <native:text class="text-base leading-relaxed text-theme-on-surface-variant">A nudge for your plans. An announcement when something changes.</native:text>
        </native:column>

        <native:column class="w-full gap-3">
            <native:text class="text-xs font-semibold tracking-widest text-theme-primary">ON YOUR DEVICE</native:text>
            <native:text class="text-xl font-semibold text-theme-on-background">Personal reminders</native:text>
            <native:text class="text-base leading-relaxed text-theme-on-surface-variant">Open any event and set a 30-second reminder. Your phone takes care of the rest, even offline.</native:text>
            <native:button variant="secondary" @navigate.replace="'/'">Choose an event</native:button>
        </native:column>

        <native:divider class="bg-theme-outline-variant" />

        <native:column class="w-full gap-3">
            <native:text class="text-xs font-semibold tracking-widest text-theme-primary">FROM THE COMMUNITY</native:text>
            <native:text class="text-xl font-semibold text-theme-on-background">Event announcements</native:text>
            <native:text class="text-base leading-relaxed text-theme-on-surface-variant">Receive updates from the organiser. Tap an announcement to open the event.</native:text>
            @if ($token === '' || $permission !== 'granted')
                <native:button variant="primary" @tap="enable" :disabled="$connecting">Enable announcements</native:button>
            @else
                <native:text class="text-base font-semibold text-theme-success">This device is connected</native:text>
            @endif
            @if ($message !== '')
                <native:text class="text-sm leading-relaxed text-theme-on-surface-variant">{{ $message }}</native:text>
            @endif
            @if ($permission === 'denied')
                <native:button variant="secondary" @tap="openSettings">Open app settings</native:button>
            @endif
            <native:button variant="ghost" size="sm" @tap="refreshStatus">Refresh connection</native:button>
        </native:column>

        @if ($token !== '')
            <native:divider class="bg-theme-outline-variant" />
            <native:column class="w-full gap-3">
                <native:text class="text-xs font-semibold tracking-widest text-theme-on-surface-variant">DEVICE TOKEN</native:text>
                <native:outlined-text-input label="FCM token" :value="$token" multiline read-only />
                <native:text class="text-sm leading-relaxed text-theme-on-surface-variant">Use this token when sending a test announcement from your computer.</native:text>
            </native:column>
        @endif
    </native:column>
</native:scroll-view>
