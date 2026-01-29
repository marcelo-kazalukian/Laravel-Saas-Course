<x-layouts.app :title="__('Locations')">
    <div class="p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">{{ __('Locations') }}</flux:heading>

            <flux:button variant="primary" :href="route('locations.create')" icon="plus">
                {{ __('Add Location') }}
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div>
            <flux:table class="min-w-full">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Slug') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>                    
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($locations as $location)
                        <flux:table.row>
                            <flux:table.cell>{{ $location->name }}</flux:table.cell>
                            <flux:table.cell>{{ $location->slug }}</flux:table.cell>
                            <flux:table.cell>{{ $location->email }}</flux:table.cell>
                            <flux:table.cell>
                                @can('update', $location)
                                    <flux:button
                                        :href="route('locations.edit', $location)"                                        
                                        size="sm"
                                        icon="pencil"
                                    >
                                        {{ __('Edit') }}
                                    </flux:button>
                                @endcan 
                            </flux:table.cell>
                            
                        </flux:table.row>  
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center">
                                <flux:text>
                                    {{ __('You have not added any locations yet.') }}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>      
                    @endforelse            
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</x-layouts.app>
