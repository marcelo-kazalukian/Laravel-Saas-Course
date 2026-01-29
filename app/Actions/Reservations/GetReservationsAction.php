<?php

namespace App\Actions\Reservations;

use App\Models\Calendar;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class GetReservationsAction
{
    public function __construct(
        private Calendar $calendar,
        private ?string $initialDate = null,
        private int $days = 1,
        private array $providerIds = [],
        private ?int $serviceId = null
    ) {}

    public function handle(): Collection
    {
        return Reservation::where('location_id', $this->calendar->location_id)
            ->when($this->providerIds, function ($query) {
                $query->whereIn('provider_id', $this->providerIds);
            })
            ->when($this->serviceId, function ($query) {
                $query->where('service_id', $this->serviceId);
            })
            ->whereBetween('reservation_date', [
                $this->initialDate ?? Carbon::today()->toDateString(),
                Carbon::today()->addDays($this->days)->toDateString(),
            ])
            ->get();
    }
}
