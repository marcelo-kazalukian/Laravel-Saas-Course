<?php

namespace App\Livewire\App\ServiceProviders;

use App\Models\Service;
use App\Models\ServiceProvider;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServiceProviderResource extends Component
{
    #[Locked]
    public Service $service;

    public ?ServiceProvider $serviceProvider = null;

    #[Locked]
    public bool $editing = false;

    #[Validate(['required', 'integer', 'min:1', 'max:7'])]
    public int $day_of_week;

    #[Validate(['required', 'date_format:H:i'])]
    public string $start_time;

    #[Validate(['required', 'date_format:H:i'])]
    public string $end_time;

    #[Validate(['required', 'integer'])]
    public int $provider_id;

    public function mount(Service $service)
    {
        $this->service = $service;
    }

    public function create(): void
    {
        $this->reset(['day_of_week', 'start_time', 'end_time', 'provider_id']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('service-provider-modal-form-'.$this->service->id)->show();
    }

    public function edit(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;

        $this->day_of_week = $serviceProvider->day_of_week;
        $this->start_time = $serviceProvider->start_time;
        $this->end_time = $serviceProvider->end_time;
        $this->provider_id = $serviceProvider->provider_id;

        $this->editing = true;

        $this->resetValidation();

        Flux::modal('service-provider-modal-form-'.$this->service->id)->show();
    }

    public function save()
    {
        if ($this->editing) {

            $this->authorize('update', $this->service);

            $validated = $this->validate();

            $this->serviceProvider->update($validated);
        } else {
            $this->authorize('create', Service::class);

            $validated = $this->validate();

            ServiceProvider::create($validated + [
                'service_id' => $this->service->id,
                'organization_id' => auth()->user()->organization_id,
            ]);
        }

        Flux::modal('service-provider-modal-form-'.$this->service->id)->close();

        $this->reset(['day_of_week', 'start_time', 'end_time', 'provider_id']);

        Flux::toast(variant: 'success', text: __('Service provider saved successfully.'), position: 'top center');
    }

    public function delete(ServiceProvider $serviceProvider): void
    {
        $this->authorize('delete', $this->service);

        $serviceProvider->delete();

        Flux::toast(variant: 'success', text: __('Service provider deleted successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.service-providers.service-provider-resource', [
            'providers' => $this->service->providers,
        ]);
    }
}
