<?php

namespace App\Livewire\App\Locations;

use App\Models\Location;
use Livewire\Component;

class CurrentLocationSelector extends Component
{
    public ?int $current_location_id;

    public function mount(): void
    {
        $this->current_location_id = session('current_location_id', 0);
    }

    public function updatedCurrentLocationId($current_location_id): void
    {
        if (session('current_location_id') != $current_location_id) {

            $location = Location::where('id', $current_location_id)
                ->where('organization_id', auth()->user()->organization_id)
                ->first();

            if ($location) {
                session(['current_location_id' => $current_location_id]);
                session(['current_location_timezone' => $location->timezone]);

                $this->dispatch('current-location-changed');
            }
        }
    }

    public function render()
    {
        return view('livewire.app.locations.current-location-selector', [
            'locations' => Location::where('organization_id', auth()->user()->organization_id)->get(),
        ]);
    }
}
