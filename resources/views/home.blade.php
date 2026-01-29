<x-layouts.public :title="__('Home')">
    <flux:main>
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            @foreach ($locations as $location)
                <div class="flex flex-col">
                    <a href="{{ './b/'.$location->slug }}" target="_blank" rel="noopener noreferrer" class="mb-4 dark:text-white">
                        {{ $location->name }}
                    </a>
                </div>
            @endforeach
        </div>
    </flux:main>
</x-layouts.public>
