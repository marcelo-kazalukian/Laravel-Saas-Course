<div class="flex flex-col gap-4">
    <flux:checkbox.group label="Providers" variant="cards" class="flex-col">
        @foreach($providers as $providerId => $providerName)
            <flux:checkbox checked value="{{ $providerId }}" wire:model.live="selectedProviders">
                <flux:checkbox.indicator />

                <div class="flex-1">
                    <flux:heading class="leading-4">{{ $providerName }}</flux:heading>
                    <flux:text size="sm" class="mt-2">Something about the provider.</flux:text>
                </div>
            </flux:checkbox>
        @endforeach
    </flux:checkbox.group>

</div>