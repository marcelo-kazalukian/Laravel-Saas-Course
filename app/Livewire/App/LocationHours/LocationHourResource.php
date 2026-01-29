<?php

namespace App\Livewire\App\LocationHours;

use App\Enums\WeekDayEnum;
use App\Models\Location;
use App\Models\LocationHour;
use Flux\Flux;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LocationHourResource extends Component
{
    #[Validate(['required', new Enum(WeekDayEnum::class)])]
    public WeekDayEnum|string $day_of_week = '';

    #[Validate(['required', 'date_format:H:i'])]
    public $start_time;

    #[Validate(['required', 'date_format:H:i', 'after:start_time'])]
    public $end_time;

    #[Locked]
    public Location $location;

    public ?LocationHour $locationHour = null;

    public bool $editing = false;

    public function mount(Location $location): void
    {
        $this->location = $location;
    }

    public function create(): void
    {
        $this->reset(['day_of_week', 'start_time', 'end_time']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('location-hour')->show();
    }

    public function edit(LocationHour $locationHour): void
    {
        $this->locationHour = $locationHour;

        $this->day_of_week = $locationHour->day_of_week;
        $this->start_time = $locationHour->start_time;
        $this->end_time = $locationHour->end_time;

        $this->editing = true;

        $this->resetValidation();

        Flux::modal('location-hour')->show();
    }

    public function save()
    {
        if ($this->locationHour) {
            $this->authorize('update', $this->location);
        } else {
            $this->authorize('create', $this->location);
        }

        $validated = $this->validate();

        $validated['start_time'] .= ':00';
        $validated['end_time'] .= ':00';

        if ($this->locationHour) {
            $this->locationHour->update($validated);
        } else {
            LocationHour::create($validated + [
                'location_id' => session('current_location_id'),
                'organization_id' => auth()->user()->organization_id,
            ]);
        }

        Flux::modal('location-hour')->close();

        $this->reset(['day_of_week', 'start_time', 'end_time']);
    }

    public function delete(LocationHour $locationHour): void
    {
        $this->authorize('delete', $this->location);

        $locationHour->delete();
    }

    public function render()
    {
        return view('livewire.app.location-hours.location-hour-resource', [
            'locationHours' => $this->location->hours()->orderBy('day_of_week')->get(),
        ]);
    }
}
