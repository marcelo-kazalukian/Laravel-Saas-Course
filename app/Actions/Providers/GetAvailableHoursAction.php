<?php

declare(strict_types=1);

namespace App\Actions\Providers;

use App\Actions\Reservations\GetReservationsAction;
use App\Models\Calendar;
use App\Models\Location;
use App\Models\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class GetAvailableHoursAction
{
    private Collection $providerHours;

    private Collection $locationHours;

    private array $providerTimeSlotsGroupByDay = [];

    private array $reservations = [];

    public function __construct(
        private Calendar $calendar,
        private array $serviceOptionIds,
        private array $providerIds,
        private ?string $initialDate,
        private int $days
    ) {}

    /**
     * Generate available hours for the calendar and providers
     */
    public function handle(): array
    {
        $this->locationHours = Location::find($this->calendar->location_id)->hours;

        $this->providerHours = collect();

        if (! empty($this->providerIds)) {
            $this->providerHours = ServiceProvider::query()
                ->select('service_provider.day_of_week', 'service_provider.start_time', 'service_provider.end_time')
                ->join('services', 'services.id', '=', 'service_provider.service_id')
                ->join('service_options', 'service_options.service_id', '=', 'services.id')
                ->when(! in_array('any', $this->providerIds), function ($query) {
                    $query->whereIn('service_provider.provider_id', $this->providerIds);
                })
                ->whereIn('service_options.id', $this->serviceOptionIds)
                ->get();
        }

        $this->providerTimeSlotsGroupByDay = $this->createTimeSlotsGroupByDay();

        $this->reservations = (new GetReservationsAction($this->calendar, $this->initialDate, $this->days, $this->providerIds))
            ->handle()
            ->toArray();

        return $this->removeReservedTimeSlots();
    }

    private function createTimeSlotsGroupByDay(): array
    {
        $timeSlotsGroupByDay = [];

        // Generate dates for the range
        $startDate = Carbon::parse($this->initialDate);

        for ($i = 0; $i < $this->days; $i++) {

            $currentDate = $startDate->copy()->addDays($i);

            $dateKey = $currentDate->format('Y-m-d');

            $dayOfWeekName = $currentDate->format('N'); // 1 for Monday, 2 for Tuesday, etc.

            // Initialize the date key
            $timeSlotsGroupByDay[$dateKey] = [];

            if ($this->providerHours->isEmpty()) {
                // No specific provider hours, use location hours
                $locationHour = $this->locationHours->firstWhere('day_of_week', $dayOfWeekName);

                if ($locationHour) {
                    $slots = $this->generateTimeSlots($locationHour->start_time, $locationHour->end_time, $this->calendar->slot_duration);

                    $timeSlotsGroupByDay[$dateKey] = array_merge($timeSlotsGroupByDay[$dateKey], $slots);
                }
            }

            // Find provider hours for this day of week
            foreach ($this->providerHours as $providerHour) {

                if ($providerHour->day_of_week == $dayOfWeekName) {

                    $locationHour = $this->locationHours->firstWhere('day_of_week', $dayOfWeekName);

                    if ($locationHour) {
                        $slots = $this->generateTimeSlots($providerHour->start_time, $providerHour->end_time, $this->calendar->slot_duration);

                        $timeSlotsGroupByDay[$dateKey] = array_merge($timeSlotsGroupByDay[$dateKey], $slots);
                    }
                }
            }
        }

        return $timeSlotsGroupByDay;
    }

    private function removeReservedTimeSlots(): array
    {
        // operate on $this->providerTimeSlotsGroupByDay
        foreach ($this->reservations as $reservation) {

            $reservationDate = Carbon::parse($reservation['reservation_date'])->format('Y-m-d');

            $reservationStartTime = Carbon::parse($reservation['reservation_date'])->format('H:i:s');

            $reservationEndTime = Carbon::parse($reservation['reservation_date'])->addMinutes($reservation['duration'])->format('H:i:s');

            if (isset($this->providerTimeSlotsGroupByDay[$reservationDate])) {

                foreach ($this->providerTimeSlotsGroupByDay[$reservationDate] as $key => $timeSlot) {

                    $slotStartTime = strtotime($timeSlot['start_time']);

                    $slotEndTime = strtotime($timeSlot['end_time']);

                    // Convert reservation times to timestamps for comparison
                    $reservationStartTimestamp = strtotime($reservationStartTime);
                    $reservationEndTimestamp = strtotime($reservationEndTime);

                    // Check if the time slot overlaps with the reservation
                    if (($slotStartTime < $reservationEndTimestamp) && ($slotEndTime > $reservationStartTimestamp)) {
                        unset($this->providerTimeSlotsGroupByDay[$reservationDate][$key]);
                    }
                }
                // Reindex the array to maintain numeric keys
                $this->providerTimeSlotsGroupByDay[$reservationDate] = array_values($this->providerTimeSlotsGroupByDay[$reservationDate]);
            }
        }

        return $this->providerTimeSlotsGroupByDay;
    }

    private function generateTimeSlots($start_time, $end_time, $slot_duration = 15)
    {
        $time_slots = [];
        $current_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $slot_duration_seconds = $slot_duration * 60;

        while ($current_time < $end_time) {
            $next_time = $current_time + $slot_duration_seconds;
            if ($next_time > $end_time) {
                break;
            }
            $time_slots[] = [
                'start_time' => date('H:i', $current_time),
                'end_time' => date('H:i', $next_time),
            ];
            $current_time = $next_time;
        }

        return $time_slots;
    }
}
