<?php

declare(strict_types=1);

namespace App\Livewire\Public\Locations;

use App\Actions\Providers\GetAvailableHoursAction;
use App\Actions\Reservations\CreateReservationAction;
use App\Actions\Services\GetProvidersAction;
use App\Models\Calendar;
use App\Models\Location;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ShowLocation extends Component
{
    #[Locked]
    public $company;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|email|max:255')]
    public $email;

    #[Validate('nullable|string|max:20')]
    public $phone;

    #[Locked]
    public $locations;

    #[Locked]
    public $currentStep = 'select-services';

    #[Locked]
    public $stepTitle = 'Select Services';

    public $providers = [];

    public $availableTimes = [];

    public $selectedLocation = null;

    #[Validate('required|array')]
    public $selectedServices = [];

    #[Validate('nullable|array')]
    public $selectedProviders = [];

    #[Validate('required')]
    public $selectedTime = null;

    #[Validate('required|date')]
    public $reservationDate;

    #[Locked]
    public ?Location $location = null;

    #[Locked]
    public Calendar $calendar;

    public function mount(string $slug)
    {
        // Fetch the location by its slug
        $this->location = Location::where('slug', $slug)
            ->with('calendar.services.serviceOptions')
            ->first();

        if (! $this->location) {
            return $this->redirectRoute('home');
        }

        $this->calendar = $this->location->calendar;

    }

    public function showProviders()
    {
        $flatServiceOptionIds = collect($this->selectedServices)->flatten()->all();

        // Filter from already loaded calendar items (no DB query)
        $this->providers = (new GetProvidersAction)
            ->handle($flatServiceOptionIds);

        $this->currentStep = 'select-providers';

        $this->stepTitle = 'Select Providers';
    }

    public function showSlotsTimeAvailable()
    {
        $flatServiceOptionIds = collect($this->selectedServices)->flatten()->all();

        $this->availableTimes = (new GetAvailableHoursAction(
            $this->calendar,
            $flatServiceOptionIds,
            $this->selectedProviders,
            Carbon::today()->toDateString(),
            7
        ))
            ->handle();

        $this->currentStep = 'select-time';

        $this->stepTitle = 'Select Date & Time';
    }

    public function setUserDetails()
    {
        $this->currentStep = 'user-details';

        $this->stepTitle = 'Your details';
    }

    public function confirmBooking()
    {
        $this->validate();

        $flatServiceOptionIds = collect($this->selectedServices)->flatten()->all();

        (new CreateReservationAction)->handle([
            'organization_id' => $this->location->organization_id,
            'location_id' => $this->location->id,
            'service_option_ids' => $flatServiceOptionIds,
            'provider_ids' => $this->selectedProviders,
            'reservation_date' => $this->reservationDate.' '.$this->selectedTime.':00',
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
        ]);

        $this->reset(['currentStep', 'selectedLocation', 'selectedServices', 'selectedProviders', 'selectedTime']);

    }

    #[On('next-step')]
    public function nextStep()
    {
        if ($this->currentStep == 'select-services') {
            // Filter out empty arrays (unchecked services)
            $validServices = array_filter($this->selectedServices);

            if (! empty($validServices)) {
                // Update selectedServices to only contain valid selections
                $this->selectedServices = $validServices;

                if ($this->calendar->show_providers) {
                    $this->showProviders();
                } else {
                    $this->showSlotsTimeAvailable();
                }
            } else {
                Flux::toast(variant: 'warning', text: 'Please select at least one service.', position: 'top center');
            }
        } elseif ($this->currentStep == 'select-providers') {
            if (! empty($this->selectedProviders)) {
                $this->showSlotsTimeAvailable();
            } else {
                Flux::toast(variant: 'warning', text: 'Please select at least one provider.', position: 'top center');
            }
        } elseif ($this->currentStep == 'select-time') {
            if (! empty($this->selectedTime)) {
                $this->setUserDetails();
            } else {
                Flux::toast(variant: 'warning', text: 'Please select at least one time slot.', position: 'top center');
            }
        }
    }

    #[On('previous-step')]
    public function previousStep()
    {
        if ($this->currentStep == 'select-providers') {
            $this->currentStep = 'select-services';
            $this->stepTitle = 'Select Services';
            $this->selectedProviders = [];
            $this->providers = [];
        } elseif ($this->currentStep == 'select-time') {

            $this->selectedTime = null;
            $this->reservationDate = null;

            if ($this->calendar->show_providers) {
                $this->currentStep = 'select-providers';
                $this->stepTitle = 'Select Providers';
            } else {
                $this->currentStep = 'select-services';
                $this->stepTitle = 'Select Services';
            }
        } elseif ($this->currentStep == 'user-details') {
            $this->currentStep = 'select-time';
            $this->stepTitle = 'Select Date & Time';
        }
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.public.locations.show-location');
    }
}
