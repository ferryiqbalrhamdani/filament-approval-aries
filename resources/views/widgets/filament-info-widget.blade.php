<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <div class="flex justify-between">
                    <h2><b>Approval App</b></h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('d M Y') }}</span>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ \Composer\InstalledVersions::getPrettyVersion('filament/filament') }}
                </p>
            </div>


        </div>
    </x-filament::section>
</x-filament-widgets::widget>