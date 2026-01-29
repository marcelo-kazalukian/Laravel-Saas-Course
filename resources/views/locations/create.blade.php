<x-layouts.app :title="isset($location) ? __('Edit Location') : __('Create Location')">
    <div class="p-6">
        <div class="mb-6">
            <flux:heading size="xl">{{ isset($location) ? __('Edit Location') : __('Create New Location') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                {{ isset($location) ? __('Edit the location details.') : __('Add a new location to your organization.') }}
            </flux:text>
        </div>

        <div class="grid grid-cols-2 gap-4">
            
            @include('locations.partials.form')
            
            @isset($location)                            
                <livewire:location-hours.location-hour-resource :$location />
            @endisset
            
        </div>
    </div>
</x-layouts.app>
