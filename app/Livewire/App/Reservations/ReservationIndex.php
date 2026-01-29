<?php

namespace App\Livewire\App\Reservations;

use App\Models\Calendar;
use App\Models\LocationHour;
use App\Models\Provider;
use App\Models\Reservation;
use Carbon\Carbon;
use Livewire\Component;

class ReservationIndex extends Component
{
    public string $selectedDate;

    public function mount(): void
    {
        $this->selectedDate = Carbon::now(session('current_location_timezone'))->format('Y-m-d');
    }

    public function render()
    {
        $today = Carbon::parse($this->selectedDate, session('current_location_timezone'));

        $startOfDayUtc = $today->copy()->startOfDay()->utc();

        $endOfDayUtc = $today->copy()->endOfDay()->utc();

        $reservations = Reservation::where('location_id', session('current_location_id'))
            ->whereBetween('reservation_date', [$startOfDayUtc, $endOfDayUtc])
            ->with(['customer', 'reservationItems.service'])
            ->get();

        return view('livewire.app.reservations.index', [
            'reservations' => $reservations->groupBy('provider_id'),
            'providers' => Provider::whereIn('id', $reservations->pluck('provider_id'))->get(),
            'locationHours' => LocationHour::where('location_id', session('current_location_id'))
                ->where('day_of_week', $today->dayOfWeekIso)
                ->orderBy('start_time')
                ->get(),
            'slot_duration' => Calendar::where('location_id', session('current_location_id'))
                ->value('slot_duration'),
        ]);
    }
}
