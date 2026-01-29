<?php

namespace App\Livewire\App\ServiceOptions;

use App\Models\Service;
use App\Models\ServiceOption;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServiceOptionResource extends Component
{
    #[Locked]
    public Service $service;

    public ?ServiceOption $serviceOption = null;

    #[Locked]
    public bool $editing = false;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name;

    #[Validate(['required', 'string'])]
    public string $price;

    #[Validate(['required', 'integer', 'min:0'])]
    public string $duration;

    public function mount(Service $service)
    {
        $this->service = $service;
    }

    public function create(): void
    {
        $this->reset(['name', 'price', 'duration']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('service-option-modal-form-'.$this->service->id)->show();
    }

    public function edit(ServiceOption $serviceOption): void
    {
        $this->serviceOption = $serviceOption;

        $this->name = $serviceOption->name;
        $this->price = $serviceOption->price;
        $this->duration = $serviceOption->duration;

        $this->editing = true;

        $this->resetValidation();

        Flux::modal('service-option-modal-form-'.$this->service->id)->show();
    }

    public function save()
    {
        if ($this->editing) {

            $this->authorize('update', $this->service);

            $validated = $this->validate();

            $this->serviceOption->update($validated);
        } else {
            $this->authorize('create', $this->service);

            $validated = $this->validate();

            ServiceOption::create($validated + [
                'service_id' => $this->service->id,
                'organization_id' => auth()->user()->organization_id,
                'active' => true,
            ]);
        }

        Flux::modal('service-option-modal-form-'.$this->service->id)->close();

        $this->reset(['name', 'price', 'duration']);

        Flux::toast(variant: 'success', text: __('Service option saved successfully.'), position: 'top center');
    }

    public function delete(ServiceOption $serviceOption): void
    {
        $this->authorize('delete', $this->service);

        $serviceOption->delete();

        Flux::toast(variant: 'success', text: __('Service option deleted successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.service-options.service-option-resource', [
            'serviceOptions' => $this->service->serviceOptions,
        ]);
    }
}
