<div class="flex flex-col gap-2">
    @foreach($calendar->services as $service)
        <flux:modal.trigger name="service-{{ $service->id }}">
            
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
            
                <flux:heading class="flex items-center gap-2">{{ $service->name }}
                    <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" />
                </flux:heading>
                
                <flux:text class="mt-2">{{$service->description}}</flux:text>

            </flux:card>

        </flux:modal.trigger>

        <flux:modal name="service-{{ $service->id }}">
            
            <div class="space-y-4">
            
                <div>
                    <flux:heading size="lg">{{ $service->name }}</flux:heading>
                    <flux:text class="mt-2">{{$service->description}}</flux:text>
                </div>
            
                <div class="grid gap-3">
                    <flux:checkbox.group wire:model.live="selectedServices.{{ $service->id }}" label="Select an option">
                        @foreach ($service->serviceOptions as $serviceOption)
                            <flux:checkbox key="{{ $serviceOption->id }}" label="{{ $service->name }} - {{ $serviceOption->name }} - {{ $serviceOption->price . ' AUD' }}" value="{{ $serviceOption->id }}"/>                        
                        @endforeach
                    </flux:checkbox.group>
                </div>
            
                <div class="flex justify-end">
                    <flux:button x-on:click="$flux.modals().close()">Close</flux:button>
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>