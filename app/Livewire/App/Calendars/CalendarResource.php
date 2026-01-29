<?php

namespace App\Livewire\App\Calendars;

use App\Models\Calendar as CalendarModel;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalendarResource extends Component
{
    #[Locked]
    public CalendarModel $calendar;

    public int $slot_duration;

    public bool $show_providers;

    public function mount()
    {
        $this->calendar = CalendarModel::where('location_id', session('current_location_id'))->first();
        $this->slot_duration = $this->calendar->slot_duration;
        $this->show_providers = $this->calendar->show_providers;
    }

    public function update()
    {
        $this->authorize('update', $this->calendar);

        $this->calendar->update([
            'slot_duration' => $this->slot_duration,
            'show_providers' => $this->show_providers,
        ]);

        Flux::toast(variant: 'success', text: __('Calendar settings updated successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.calendars.calendar-resource');
    }
}
