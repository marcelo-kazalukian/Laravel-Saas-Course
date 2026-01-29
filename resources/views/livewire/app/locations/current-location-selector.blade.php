<flux:select wire:model.live="current_location_id" class="w-80">    
    @foreach ($locations as $location)
        <flux:select.option value="{{ $location->id }}">{{ $location->name}}</flux:select.option>
    @endforeach                
</flux:select>
@script
    <script>    
        // refresh page when current location is changed
        $wire.on('current-location-changed', () => {
            window.location.reload();
        });
    </script>
@endscript
