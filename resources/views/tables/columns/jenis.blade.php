<x-filament::badge :color="
    $getState() === false ? 'success' : ($getState() === true ? 'gray' : 'gray')
">
    @if ($getState() === false)
    Pengajuan
    @elseif ($getState() === true)
    Draft
    @else
    Pending
    @endif
</x-filament::badge>