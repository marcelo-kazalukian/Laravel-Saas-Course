<div>
    <flux:modal.trigger name="service-option-modal-{{ $service->id }}">
        <flux:button size="sm">{{__('Service Options')}}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="service-option-modal-{{ $service->id }}" class="w-3/4" >
        <div class="space-y-6">      
            
            <div>
                <flux:heading size="lg"></flux:heading>                        
                
            </div>
                
            <div class="space-y-6">
               <div class="flex flex-wrap items-center justify-between gap-4">
                    <flux:heading size="lg">{{ __('Service Options') }}</flux:heading>        
                    <flux:button wire:click="create" size="sm">{{ __('Add Service Option') }}</flux:button>
                </div>
                <div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Price') }}</flux:table.column>                    
                            <flux:table.column>{{ __('Duration') }}</flux:table.column>                    
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>                
                            @forelse ($serviceOptions as $serviceOption)
                                <flux:table.row>
                                    <flux:table.cell>{{ $serviceOption->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $serviceOption->price }}</flux:table.cell>
                                    <flux:table.cell>{{ $serviceOption->duration }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <flux:button wire:click="edit({{ $serviceOption->id }})" size="sm">{{__('Edit')}}</flux:button>
                                            <flux:button wire:click="delete({{ $serviceOption->id }})" wire:confirm="{{ __('Are you sure you want to delete this service option?') }}" size="sm" variant="danger" icon="trash" />                                    
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>                                   
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ __('No service options found.') }}
                                        </flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse    
                        </flux:table.rows>
                    </flux:table>
                </div>


                <flux:modal name="service-option-modal-form-{{ $service->id }}" class="md:w-96">
                    <form wire:submit="save">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ $editing ? __('Edit Service Option') : __('Add Service Option') }}</flux:heading>                        
                            </div>

                            <flux:input wire:model="name" label="{{__('Name')}}" type="text" placeholder="{{__('Name')}}" />
                        
                            <flux:input wire:model="price" label="{{__('Price')}}" type="number" step="0.01" placeholder="{{__('Price')}}" />

                            <flux:input wire:model="duration" label="{{__('Duration (minutes)')}}" type="number" step="1" placeholder="{{__('Duration in minutes')}}" />
                            
                            <div class="flex">
                                <flux:spacer />

                                <flux:button type="submit" variant="primary">
                                    {{ $editing ? __('Update') : __('Save') }}
                                </flux:button>
                            </div>
                        </div>
                    </form>
                </flux:modal>
            </div>
        </div>
    </flux:modal>   
</div>
