<div>
    <flux:modal.trigger name="service-provider-modal-{{ $service->id }}">
        <flux:button size="sm">{{__('Provider member')}}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="service-provider-modal-{{ $service->id }}" class="w-3/4" >
        <div class="space-y-6">      
            
            <div>
                <flux:heading size="lg"></flux:heading>                        
                
            </div>
                
            <div class="space-y-6">
               <div class="flex flex-wrap items-center justify-between gap-4">
                    <flux:heading size="lg">{{ __('Provider member') }}</flux:heading>        
                    <flux:button wire:click="create" size="sm">{{ __('Add Provider member') }}</flux:button>
                </div>
                <div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Day of Week') }}</flux:table.column>                    
                            <flux:table.column>{{ __('Start time') }}</flux:table.column>                    
                            <flux:table.column>{{ __('End time') }}</flux:table.column>                    
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>                
                            @forelse ($providers as $provider)
                                <flux:table.row>
                                    <flux:table.cell>{{ $provider->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $provider->pivot->day_of_week_name }}</flux:table.cell>
                                    <flux:table.cell>{{ $provider->pivot->start_time }}</flux:table.cell>
                                    <flux:table.cell>{{ $provider->pivot->end_time }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <flux:button wire:click="edit({{ $provider->id }})" size="sm">{{__('Edit')}}</flux:button>
                                            <flux:button wire:click="delete({{ $provider->id }})" wire:confirm="{{ __('Are you sure you want to delete this provider member?') }}" size="sm" variant="danger" icon="trash" />                                    
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>                                   
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5">
                                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                                            {{ __('No service provider members found.') }}
                                        </flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse    
                        </flux:table.rows>
                    </flux:table>
                </div>


                <flux:modal name="service-provider-modal-form-{{ $service->id }}" class="md:w-96">
                    <form wire:submit="save">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ $editing ? __('Edit Provider member') : __('Add Provider member') }}</flux:heading>                        
                            </div>

                            <flux:select wire:model="provider_id" label="{{__('Provider Member')}}">
                                <flux:select.option value=""></flux:select.option>
                                @foreach (\App\Models\Provider::where('organization_id', auth()->user()->organization_id)->get() as $providerMember)
                                    <flux:select.option value="{{ $providerMember->id }}">{{ $providerMember->name }}</flux:select.option>
                                @endforeach                    
                            </flux:select>
                            
                            <flux:select wire:model="day_of_week" label="{{__('Day of Week')}}">
                                <flux:select.option value=""></flux:select.option>
                                <flux:select.option value="1">{{ __('Monday') }}</flux:select.option>
                                <flux:select.option value="2">{{ __('Tuesday') }}</flux:select.option>
                                <flux:select.option value="3">{{ __('Wednesday') }}</flux:select.option>
                                <flux:select.option value="4">{{ __('Thursday') }}</flux:select.option>
                                <flux:select.option value="5">{{ __('Friday') }}</flux:select.option>
                                <flux:select.option value="6">{{ __('Saturday') }}</flux:select.option>
                                <flux:select.option value="7">{{ __('Sunday') }}</flux:select.option>
                            </flux:select>
                            <flux:input wire:model="start_time" label="{{__('Start Time')}}" type="time" placeholder="{{__('Start Time')}}" />
                            <flux:input wire:model="end_time" label="{{__('End Time')}}" type="time" placeholder="{{__('End Time')}}" />
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
