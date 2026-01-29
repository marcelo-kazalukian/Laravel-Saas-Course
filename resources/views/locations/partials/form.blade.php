<flux:card class="space-y-6">
    <form method="POST" action="{{ isset($location) ? route('locations.update', $location->id) : route('locations.store') }}" class="space-y-6">
        @if (isset($location))                                       
            @method('PUT')
        @endif                
        @csrf
        {{-- Basic Information --}}
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('Basic Information') }}</flux:heading>
            
            <div class="space-y-4">
                <div>
                    <flux:input name="name" :label="__('Name')" :placeholder="__('Name')" value="{{old('name', $location->name ?? '')}}"/>                                            
                </div>

                <div>
                    <flux:input name="slug" :label="__('Slug')" :placeholder="__('Slug')" value="{{old('slug', $location->slug ?? '')}}"/>
                </div>

                <div>
                    <flux:select name="timezone" label="{{ __('Timezone') }}" placeholder="{{ __('Select a Timezone') }}">
                        @foreach ($timezones as $timezone)
                            <flux:select.option :selected="old('timezone', $location->timezone ?? '') == $timezone" value="{{ $timezone }}">{{ $timezone }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </div>

        {{-- Address Information --}}
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('Address') }}</flux:heading>
            
            <div class="space-y-4">
                <div>
                    <flux:input name="address" :label="__('Address')" :placeholder="__('Address')" value="{{old('address', $location->address ?? '')}}"/>                                
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:input name="city" :label="__('City')" :placeholder="__('City')" value="{{old('city', $location->city ?? '')}}"/>                                    
                    </div>

                    <div>
                        <flux:select name="state" label="{{ __('State') }}" placeholder="{{ __('Select a state') }}">
                            @foreach(App\Enums\AustralianStatesEnum::cases() as $state)
                                <flux:select.option :selected="old('state', $location->state ?? '') == $state->value" value="{{ $state->value }}">
                                    {{ $state->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:input name="postal_code" :label="__('Postal Code')" :placeholder="__('Postal Code')" value="{{old('postal_code', $location->postal_code ?? '')}}"/>                                    
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('Contact Information') }}</flux:heading>
            
            <div class="space-y-4">
                <div>
                    <flux:input name="phone" :label="__('Phone')" :placeholder="__('Phone')" value="{{old('phone', $location->phone ?? '')}}"/>                                
                </div>

                <div>
                    <flux:input name="email" :label="__('Email')" :placeholder="__('Email')" value="{{old('email', $location->email ?? '')}}"/>                                
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <flux:button variant="ghost" :href="route('locations.index')">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" type="submit" icon="plus">
                {{ isset($location) ? __('Update Location') : __('Create Location') }}
            </flux:button>
        </div>
    </form>
</flux:card>