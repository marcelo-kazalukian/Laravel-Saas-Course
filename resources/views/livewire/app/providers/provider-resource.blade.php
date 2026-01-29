<div>    
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="lg">{{ __('Providers') }}</flux:heading>        
        <flux:button wire:click="create" size="sm">{{ __('Add Provider') }}</flux:button>
    </div>
    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>                    
                <flux:table.column>{{ __('Phone') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>                
                @forelse ($providers as $provider)
                    <flux:table.row>
                        <flux:table.cell>{{ $provider->name }}</flux:table.cell>
                        <flux:table.cell>{{ $provider->email }}</flux:table.cell>
                        <flux:table.cell>{{ $provider->phone }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $provider->id }})" size="sm">{{__('Edit')}}</flux:button>                                    
                                <flux:button wire:click="delete({{ $provider->id }})" wire:confirm="{{ __('Are you sure you want to delete this provider?') }}" size="sm" variant="danger" icon="trash" />                                    
                            </div>
                        </flux:table.cell>
                    </flux:table.row>                                   
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <flux:text class="text-zinc-600 dark:text-zinc-400">
                                {{ __('No providers found.') }}
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse    
            </flux:table.rows>
        </flux:table>
    </div>
    
    <flux:modal name="provider-modal" class="md:w-96">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editing ? __('Edit Provider') : __('Add Provider') }}</flux:heading>                        
                </div>

                <flux:input wire:model="name" label="{{__('Name')}}" type="text" placeholder="{{__('Name')}}" />

                <flux:input wire:model="email" label="{{__('Email')}}" type="email" placeholder="{{__('Email')}}" />

                <flux:input wire:model="phone" label="{{__('Phone')}}" type="text" placeholder="{{__('Phone')}}" />
                
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

