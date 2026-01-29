<?php

namespace App\Actions\Reservations;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\ServiceOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class CreateReservationAction
{
    public function handle(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {

            $customerEmail = $data['customer_email'];

            $customer = Customer::firstOrCreate(
                ['organization_id' => $data['organization_id'], 'email' => $customerEmail],
                ['name' => $data['customer_name'], 'phone' => $data['customer_phone'] ?? null]
            );

            $serviceOptions = ServiceOption::whereIn('id', $data['service_option_ids'])
                ->select('id', 'name', 'price', 'duration', 'service_id')
                ->get();

            $providerId = $this->getProviderId($data);

            $location = Location::find($data['location_id']);

            $reservation = [
                'organization_id' => $data['organization_id'],
                'location_id' => $data['location_id'],
                'provider_id' => $providerId,
                'reservation_date' => Carbon::parse($data['reservation_date'], $location->timezone)->setTimezone('UTC'),
                'day_of_week' => Carbon::parse($data['reservation_date'], $location->timezone)->format('N'),
                'duration' => $serviceOptions->sum('duration'),
                'status' => $data['status'] ?? 'pending',
                'customer_id' => $customer->id,
            ];

            $reservation = Reservation::create($reservation);

            $serviceOptions->each(function ($serviceOption) use ($reservation) {
                ReservationItem::create([
                    'organization_id' => $reservation->organization_id,
                    'reservation_id' => $reservation->id,
                    'service_id' => $serviceOption->service_id,
                    'service_option_id' => $serviceOption->id,
                    'duration' => $serviceOption->duration,
                    'service_name' => $serviceOption->name,
                    'service_option_name' => $serviceOption->name,
                    'price' => $serviceOption->price,
                ]);
            });

            return $reservation;
        });
    }

    private function getProviderId(array $data): ?int
    {
        $providerIds = collect($data['provider_ids']);

        return $providerIds->isEmpty() ? null : $providerIds->first();
    }
}
