<div class="flex flex-col gap-4">

    <flux:tab.group>

        <flux:tabs wire:model.live="reservationDate" scrollable>

            @foreach ($availableTimes as $date => $slotTimes)
            <flux:tab name="{{ $date }}" wire:key="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d
                M')}}</flux:tab>
            @endforeach

        </flux:tabs>

        @foreach ($availableTimes as $date => $slotTimes)

        <flux:tab.panel name="{{ $date }}" wire:key="{{ 'panel-' . $date }}">

            <flux:radio.group wire:model="selectedTime" label="Select time slot" variant="cards"
                class="grid grid-cols-2 gap-4">
                @forelse ($slotTimes as $slotTime)
                    <div class="col-span-1" wire:key="{{ $date . '#' . $slotTime['start_time'] }}">
                        <flux:radio value="{{ $slotTime['start_time'] }}" class="w-full"
                            label="{{ Carbon\Carbon::createFromFormat('H:i', $slotTime['start_time'])->format('g:i A')}}" />
                    </div>
                @empty
                    <p class="text-gray-600">No time slots available for {{ $date }}.</p>
                @endforelse
            </flux:radio.group>

        </flux:tab.panel>
        @endforeach

    </flux:tab.group>

</div>