<div>
    <flux:card class="space-y-6 h-fit">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="lg">{{ __('Time Table') }}</flux:heading>        
            <flux:button wire:click="create" size="sm">{{ __('Add Hour') }}</flux:button>
        </div>
        <div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Day') }}</flux:table.column>
                    <flux:table.column>{{ __('Start') }}</flux:table.column>
                    <flux:table.column>{{ __('Finish') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
    
                <flux:table.rows>                
                    @forelse ($locationHours as $item)
                        <flux:table.row>
                            <flux:table.cell>{{ $item->day_of_week->label() }}</flux:table.cell>
                            <flux:table.cell>{{ $item->start_time }}</flux:table.cell>
                            <flux:table.cell>{{ $item->end_time }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="edit({{ $item->id }})" size="sm" icon="pencil" />
                                    <flux:button wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Are you sure you want to delete this hour?') }}" size="sm" variant="danger" icon="trash" />                                    
                                </div>
                            </flux:table.cell>
                        </flux:table.row>                                   
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('No hours found for this location.') }}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse    
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
    
    
    <flux:modal name="location-hour" class="md:w-96">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editing ? __('Edit Hour') : __('Add Hour') }}</flux:heading>                        
                </div>
    
                <flux:select label="{{ __('Day') }}" wire:model="day_of_week" placeholder="Choose day...">
                    @foreach (\App\Enums\WeekdayEnum::cases() as $day)
                        <flux:select.option value="{{ $day->value }}">{{ $day->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
    
                <flux:time-picker label="{{ __('Start Time') }}" wire:model="start_time" interval="15"/>         
                <flux:error name="start_time" />                                   
    
                <flux:time-picker label="{{ __('End Time') }}" wire:model="end_time" interval="15"/>                                            
                <flux:error name="end_time" />
    
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