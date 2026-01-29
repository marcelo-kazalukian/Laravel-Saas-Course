<div>
    @foreach ($reservations as $providerId => $providerReservations)
    
        <div id="provider-{{ $providerId }}" class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Provider {{ $providerId }}</h3>
            
            @if($locationHours->isNotEmpty())
                @php
                    // Get the earliest start time and latest end time for the day
                    $dayStart = $locationHours->min('start_time');
                    $dayEnd = $locationHours->max('end_time');
                    
                    // Convert to Carbon instances for easier manipulation
                    $currentSlot = \Carbon\Carbon::createFromFormat('H:i:s', $dayStart);
                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $dayEnd);
                    
                    // Create array of reservation times for quick lookup
                    $reservationTimes = $providerReservations->pluck('reservation_date')->map(function($time) {
                        return \Carbon\Carbon::parse($time)->format('H:i:s');
                    })->toArray();
                @endphp
                
                <div class="grid gap-2">
                    @while($currentSlot->lte($endTime))
                        @php
                            $slotTime = $currentSlot->format('H:i:s');
                            $hasReservation = in_array($slotTime, $reservationTimes);
                        @endphp
                        
                        <div class="p-3 border rounded {{ $hasReservation ? 'bg-blue-100 border-blue-300' : 'bg-gray-50 border-gray-200' }}">
                            <span class="font-mono">{{ $currentSlot->format('H:i') }}</span>
                            @if($hasReservation)
                                @php
                                    $reservation = $providerReservations->first(function($res) use ($slotTime) {
                                        return \Carbon\Carbon::parse($res->reservation_date)->format('H:i:s') === $slotTime;
                                    });
                                @endphp
                                @if($reservation)
                                    <span class="ml-2 text-blue-700 font-medium">
                                        {{ $reservation->customer->name ?? 'Customer' }}
                                    </span>
                                @endif
                            @else
                                <span class="ml-2 text-gray-500">Available</span>
                            @endif
                        </div>
                        
                        @php
                            $currentSlot->addMinutes($slot_duration ?? 30);
                        @endphp
                    @endwhile
                </div>
            @else
                <p class="text-gray-500">No operating hours defined for today.</p>
            @endif
        </div>        
    @endforeach
</div>
