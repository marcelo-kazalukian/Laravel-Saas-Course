<?php

namespace App\Livewire\App\Services;

use App\Models\Calendar;
use App\Models\Service;
use App\Models\ServiceCategory;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServiceResource extends Component
{
    #[Locked]
    public Calendar $calendar;

    public ?Service $service = null;

    #[Locked]
    public bool $editing = false;

    #[Validate(['nullable'])]
    public ?int $service_category_id;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name;

    #[Validate(['required', 'string'])]
    public string $description;

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    public function create(): void
    {
        $this->reset(['service_category_id', 'name', 'description']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('service-modal')->show();
    }

    public function edit(Service $service): void
    {
        $this->service = $service;

        $this->service_category_id = $service->service_category_id;
        $this->name = $service->name;
        $this->description = $service->description;

        $this->editing = true;

        $this->resetValidation();

        Flux::modal('service-modal')->show();
    }

    public function save()
    {
        $this->authorize('update', $this->calendar);

        $validated = $this->validate();

        if ($this->service) {
            $this->service->update($validated);
        } else {
            Service::create($validated + [
                'calendar_id' => $this->calendar->id,
                'organization_id' => auth()->user()->organization_id,
            ]);
        }

        Flux::modal('service-modal')->close();

        $this->reset(['service_category_id', 'name', 'description']);

        Flux::toast(variant: 'success', text: __('Service saved successfully.'), position: 'top center');
    }

    public function delete(Service $service): void
    {
        $this->authorize('delete', $this->calendar);

        $service->delete();

        Flux::toast(variant: 'success', text: __('Service deleted successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.services.service-resource', [
            'services' => $this->calendar->services,
            'serviceCategories' => ServiceCategory::where('organization_id', auth()->user()->organization_id)->get(),
        ]);
    }
}
