<div>
    <flux:card class="space-y-6 h-fit">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="lg">{{ __('Service Categories') }}</flux:heading>        
            <flux:button wire:click="create" size="sm">{{ __('Add Service Category') }}</flux:button>
        </div>
        <div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>                    
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
    
                <flux:table.rows>                
                    @forelse ($serviceCategories as $serviceCategory)
                        <flux:table.row>
                            <flux:table.cell>{{ $serviceCategory->name }}</flux:table.cell>
                            <flux:table.cell>{{ $serviceCategory->description }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="edit({{ $serviceCategory->id }})" size="sm" icon="pencil" />
                                    <flux:button wire:click="delete({{ $serviceCategory->id }})" wire:confirm="{{ __('Are you sure you want to delete this service category?') }}" size="sm" variant="danger" icon="trash" />                                    
                                </div>
                            </flux:table.cell>
                        </flux:table.row>                                   
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('No service categories found.') }}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse    
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    <flux:modal name="service-category-modal" class="md:w-96">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editing ? __('Edit Service Category') : __('Add Service Category') }}</flux:heading>                        
                </div>

                <flux:input wire:model="name" label="{{__('Service Category Name')}}" type="text" placeholder="{{__('Service Category Name')}}" />

                <flux:textarea wire:model="description" label="{{__('Description')}}" placeholder="{{__('Service Category Description')}}" />                
                
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

