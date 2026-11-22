<native:pressable class="h-12 w-full items-center justify-center" @tap="select"
    a11y-label="{{ $date }}{{ $selected ? ', selected' : '' }}{{ $hasEvents ? ', has events' : '' }}" ref="day-{{ $date }}">
    <native:column class="w-10 h-11 items-center justify-center gap-1 rounded-xl {{ $selected ? 'bg-theme-primary' : '' }}">
        <native:text class="text-base {{ $selected ? 'font-bold text-theme-on-primary' : ($inMonth ? 'font-medium text-theme-on-surface' : 'text-theme-muted') }}">{{ $number }}</native:text>
        <native:column class="w-1 h-1 rounded-full {{ $hasEvents ? ($selected ? 'bg-theme-on-primary' : 'bg-theme-primary') : 'bg-transparent' }}" />
    </native:column>
</native:pressable>
