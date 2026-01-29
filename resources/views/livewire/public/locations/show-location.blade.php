<flux:main>
    <div class="max-w-5xl mx-auto p-4 pb-24 flex flex-col">

        <h1 class="text-2xl font-bold mb-6">{{ $stepTitle }}</h1>

        <div class="grid grid-cols-6 gap-6 ">
            <div class="col-span-6 md:col-span-4">

                @if ($currentStep === 'select-services')
                    @include('livewire.public.locations.partials.select-services')
                @endif

                @if ($currentStep === 'select-providers')
                    @include('livewire.public.locations.partials.select-providers')
                @endif

                @if ($currentStep === 'select-time')
                    @include('livewire.public.locations.partials.select-time')
                @endif

                @if ($currentStep === 'user-details')
                    @include('livewire.public.locations.partials.user-details-form')
                @endif
            </div>
            <div class="col-span-2 hidden md:block">
                @include('livewire.public.locations.partials.booking-details')
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer
        class="fixed bottom-0 inset-x-0 z-50 bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 pb-safe">
        <div class="max-w-3xl mx-auto p-4 flex justify-end">
            <div class="space-x-2">
                @if ($this->currentStep != 'select-services')
                <flux:button wire:click="$dispatch('previous-step')">{{ __('Back') }}</flux:button>
                @endif

                @if ($this->currentStep == 'user-details')
                    <flux:button wire:click="confirmBooking">{{ __('Confirm Booking') }}</flux:button>
                @else
                <flux:button wire:click="$dispatch('next-step')">{{ __('Next') }}</flux:button>
                @endif
            </div>
        </div>
    </footer>
</flux:main>