<style>
    .custom-badge {
        display: inline-block;
        padding: 0.25em 0.75em;
        /* Adjust the padding as needed */
        /* Adjust the font size as needed */
        white-space: nowrap;
        /* Prevent text wrapping */
    }
</style>
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <x-filament::badge :color="match ($getState()) {
        0 => 'warning',
        1 => 'success',
        2 => 'danger',
        default => 'gray',
    }" class="truncate custom-badge">
        @if ($getState() === 0)
        Proccessing
        @elseif ($getState() === 1)
        Approved
        @elseif ($getState() === 2)
        Rejected
        @else
        Pending
        @endif
    </x-filament::badge>
</x-dynamic-component>