<?php

namespace App\Livewire\App\Providers;

use App\Models\Provider;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProviderResource extends Component
{
    #[Locked]
    public ?Provider $provider = null;

    #[Locked]
    public bool $editing = false;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name;

    #[Validate(['nullable'])]
    public string $email;

    #[Validate(['required', 'string'])]
    public string $phone;

    public function create(): void
    {
        $this->reset(['email', 'name', 'phone']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('provider-modal')->show();
    }

    public function edit(Provider $provider): void
    {
        $this->provider = $provider;

        $this->email = $provider->email;
        $this->name = $provider->name;
        $this->phone = $provider->phone;
        $this->editing = true;

        $this->resetValidation();

        Flux::modal('provider-modal')->show();
    }

    public function save()
    {
        if ($this->editing) {

            $this->authorize('update', $this->provider);

            $validated = $this->validate();

            $this->provider->update($validated);
        } else {
            $this->authorize('create', Provider::class);

            $validated = $this->validate();

            Provider::create($validated + [
                'organization_id' => auth()->user()->organization_id,
            ]);
        }

        Flux::modal('provider-modal')->close();

        $this->reset(['email', 'name', 'phone']);

        Flux::toast(variant: 'success', text: __('Provider Member saved successfully.'), position: 'top center');
    }

    public function delete(Provider $provider): void
    {
        $this->authorize('delete', $provider);

        $provider->delete();

        Flux::toast(variant: 'success', text: __('Provider Member deleted successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.providers.provider-resource', [
            'providers' => auth()->user()->organization->providers()->orderBy('name')->get(),
        ]);
    }
}
