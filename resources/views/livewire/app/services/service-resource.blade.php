<div>    
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="lg">{{ __('Your Services') }}</flux:heading>        
        <flux:button wire:click="create" size="sm">{{ __('Add Service') }}</flux:button>
    </div>
    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>                    
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>                
                @forelse ($services as $service)
                    <flux:table.row>
                        <flux:table.cell>{{ $service->name }}</flux:table.cell>
                        <flux:table.cell>{{ $service->category->name ?? '' }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $service->id }})" size="sm">{{__('Edit')}}</flux:button>
                                <livewire:app.service-options.service-option-resource :$service :key="'service-option-' . $service->id" />
                                <livewire:app.service-providers.service-provider-resource :$service :key="'service-provider-' . $service->id" />
                                <flux:button wire:click="delete({{ $service->id }})" wire:confirm="{{ __('Are you sure you want to delete this service?') }}" size="sm" variant="danger" icon="trash" />                                    
                            </div>
                        </flux:table.cell>
                    </flux:table.row>                                   
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                {{ __('No services found.') }}
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse    
            </flux:table.rows>
        </flux:table>
    </div>
    
    <flux:modal name="service-modal" class="md:w-96">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editing ? __('Edit Service') : __('Add Service') }}</flux:heading>                        
                </div>

                <flux:input wire:model="name" label="{{__('Service Name')}}" type="text" placeholder="{{__('Service Name')}}" />

                <flux:textarea wire:model="description" label="{{__('Description')}}" placeholder="{{__('Service Description')}}" />

                <flux:select wire:model="service_category_id" label="{{__('Category')}}">
                    <flux:select.option value=""></flux:select.option>
                    @foreach ($serviceCategories as $serviceCategory)
                        <flux:select.option value="{{ $serviceCategory->id }}">{{ $serviceCategory->name }}</flux:select.option>
                    @endforeach                    
                </flux:select>
                
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

