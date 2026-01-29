<div class="space-y-6">
        <flux:tab.group>
        <flux:tabs wire:model="tab">
            <flux:tab name="settings">{{__('Settings')}}</flux:tab>
            <flux:tab name="services">{{__('Services')}}</flux:tab>
            <flux:tab name="service-categories">{{__('Service Categories')}}</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="settings"><form wire:submit="update">
            <flux:card class="space-y-6">
                <div>
                    <flux:heading size="lg">{{__('Calendar')}}</flux:heading>            
                </div>

                <div class="space-y-6">
                    
                    <flux:input wire:model="slot_duration" label="{{__('Slot Duration')}}" type="number" placeholder="{{__('Slot Duration')}}" />

                    <flux:field variant="inline">
                        <flux:label>{{__('Show Providers')}}</flux:label>

                        <flux:switch wire:model="show_providers" />

                        <flux:error name="show_providers" />
                    </flux:field>
                </div>

                <div class="space-y-2">
                    <flux:button type="submit" variant="primary" class="w-full">{{__('Update')}}</flux:button>            
                </div>
            </flux:card>
        </form></flux:tab.panel>
        <flux:tab.panel name="services">
            <livewire:app.services.service-resource :$calendar/>
        </flux:tab.panel>
        <flux:tab.panel name="service-categories">
            <livewire:app.service-categories.service-category-resource />
        </flux:tab.panel>
    </flux:tab.group>
</div>

