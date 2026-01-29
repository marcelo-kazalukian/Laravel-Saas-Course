<?php

namespace App\Livewire\App\ServiceCategories;

use App\Models\ServiceCategory;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServiceCategoryResource extends Component
{
    public ?ServiceCategory $serviceCategory = null;

    #[Locked]
    public bool $editing = false;

    #[Validate(['nullable'])]
    public ?int $service_category_id;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name;

    #[Validate(['required', 'string'])]
    public string $description;

    public function create(): void
    {
        $this->reset(['service_category_id', 'name', 'description']);

        $this->editing = false;

        $this->resetValidation();

        Flux::modal('service-category-modal')->show();
    }

    public function edit(ServiceCategory $serviceCategory): void
    {
        $this->serviceCategory = $serviceCategory;

        $this->service_category_id = $serviceCategory->service_category_id;
        $this->name = $serviceCategory->name;
        $this->description = $serviceCategory->description;

        $this->editing = true;

        $this->resetValidation();

        Flux::modal('service-category-modal')->show();
    }

    public function save()
    {
        if ($this->editing) {

            $this->authorize('update', $this->serviceCategory);

            $validated = $this->validate();

            $this->serviceCategory->update($validated);
        } else {

            $this->authorize('create', ServiceCategory::class);

            $validated = $this->validate();

            ServiceCategory::create($validated + [
                'organization_id' => auth()->user()->organization_id,
            ]);
        }

        Flux::modal('service-category-modal')->close();

        $this->reset(['service_category_id', 'name', 'description']);

        Flux::toast(variant: 'success', text: __('Service category saved successfully.'), position: 'top center');
    }

    public function delete(ServiceCategory $serviceCategory): void
    {
        $this->authorize('delete', $this->serviceCategory);

        $serviceCategory->delete();

        Flux::toast(variant: 'success', text: __('Service category deleted successfully.'), position: 'top center');
    }

    public function render()
    {
        return view('livewire.app.service-categories.service-category-resource', [
            'serviceCategories' => ServiceCategory::where('organization_id', auth()->user()->organization_id)->get(),
        ]);
    }
}
