
    <div>

    
    <div class="mb-4">
    <flux:date-picker wire:model.live="selectedDate" with-today />
</div>

<flux:kanban>
        @if($locationHours->isEmpty())
            <flux:text>No operating hours defined for today.</flux:text>
        @else
            @foreach ($reservations as $providerId => $providerReservations)
                <flux:kanban.column>
                    <flux:kanban.column.header :heading="$providers->firstWhere('id', $providerId)->name" :count="count($providerReservations)" />

                    @php
                        // Get the earliest start time and latest end time for the day
                        $dayStart = $locationHours->min('start_time');
                        $dayEnd = $locationHours->max('end_time');
                        
                        // Convert to Carbon instances for easier manipulation
                        $currentSlot = \Carbon\Carbon::parse($dayStart);
                        $endTime = \Carbon\Carbon::parse($dayEnd);
                        
                        // Create array of reservation times for quick lookup
                        $reservationTimes = $providerReservations->pluck('reservation_date')->map(function($time) {
                            return \Carbon\Carbon::parse($time)->format('H:i:s');
                        })->toArray();
                    @endphp

                    <flux:kanban.column.cards>
                        @while($currentSlot->lte($endTime))
                            @php
                                $slotTime = $currentSlot->format('H:i:s');
                                $hasReservation = in_array($slotTime, $reservationTimes);
                            @endphp
                            
                            @if($hasReservation)
                                @php
                                    $reservation = $providerReservations->first(function($res) use ($slotTime) {
                                        return \Carbon\Carbon::parse($res->reservation_date)->format('H:i:s') === $slotTime;
                                    });
                                @endphp
                                <flux:kanban.card :heading="$currentSlot->format('H:i')">                                
                                    <flux:text class="text-xs">{{ 'Customer: ' . $reservation->customer->name }}</flux:text>
                                    <flux:text class="text-xs">{{ 'Provider: ' . $providers->firstWhere('id', $reservation->provider_id)->name }}</flux:text>
                                    @php
                                        $serviceNames = $reservation->reservationItems->map(function($item) {
                                            return $item->service->name;
                                        })->join(', ');
                                    @endphp
                                    <flux:text class="text-xs">{{ $serviceNames }}</flux:text>
                                </flux:kanban.card>
                            @else
                                <flux:kanban.card :heading="$currentSlot->format('H:i')"/>
                            @endif

                            @php
                                $currentSlot->addMinutes($slot_duration ?? 30);
                            @endphp
                        @endwhile
                    </flux:kanban.column.cards>
                </flux:kanban.column>
            @endforeach
        @endif
    </flux:kanban>

</div>