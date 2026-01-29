<flux:card size="sm" class="space-y-6">
    <div>
        <flux:heading size="lg">Your Booking</flux:heading>
    </div>

    <div class="space-y-6">
        <div>
            <flux:heading>Services</flux:heading>


            @foreach ($selectedServices as $serviceId => $serviceOptionIds)
                @if (!empty($serviceOptionIds))
                    @php
                        $service = $location->calendar->services->find($serviceId);
                    @endphp
                    
                    @foreach ($serviceOptionIds as $serviceOptionId)                
                        @php
                            $serviceOption = $service->serviceOptions->find($serviceOptionId);
                        @endphp
                        <flux:text class="mt-2">
                            {{ $service->name }} - {{ $serviceOption->name }}
                        </flux:text>
                    @endforeach
                @endif
            @endforeach
        </div>
        @if (count($selectedProviders))
            <div>
                <flux:heading>Providers</flux:heading>

                <flux:text class="mt-2">
                    @foreach ($selectedProviders as $providerId)
                    {{ $providers[$providerId] }}
                    @endforeach
                </flux:text>
            </div>
        @endif
        @if ($selectedTime && $reservationDate)
            <div>
                <flux:heading>Time Slot</flux:heading>

                <flux:text class="mt-2">
                    {{ \Carbon\Carbon::parse($reservationDate . ' ' . $selectedTime)->format('d M g:i A') }}
                </flux:text>
            </div>
        @endif
        <flux:error name="selectedServices" />
        <flux:error name="selectedProviders" />
        <flux:error name="selectedTime" />
        <flux:error name="reservationDate" />
    </div>
</flux:card>