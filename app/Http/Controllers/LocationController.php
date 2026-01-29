<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LocationController extends Controller
{
    public function index(): View
    {
        $currentUser = auth()->user();

        $locations = Location::query()
            ->where('organization_id', $currentUser->organization_id)
            ->orderBy('name')
            ->get();

        return view('locations.index', [
            'locations' => $locations,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Location::class);

        return view('locations.create', [
            'timezones' => $this->getTimezones(),
        ]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        if (! $request->user()->organization->canCreateLocation()) {

            $limit = $request->user()->organization->getLocationLimit();

            return redirect()
                ->route('billing.index')
                ->with('error', "You've reached your limit of {$limit} locations. Upgrade to create more.");
        }

        $validated = $request->validated();

        $validated['slug'] = $this->getSlug($validated['name']);

        $location = Location::create($validated + [
            'organization_id' => $request->user()->organization_id,
        ]);

        if (auth()->user()->default_location_id === null) {

            User::where('id', auth()->user()->id)->update(['default_location_id' => $location->id]);

            session(['current_location_id' => $location->id]);

            session(['current_location_timezone' => $location->timezone]);
        }

        // Create a default calendar for the new location
        $location->calendar()->create([
            'organization_id' => $request->user()->organization_id,
            'slot_duration' => 30,
            'show_providers' => true,
        ]);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit(Location $location): View
    {
        Gate::authorize('update', $location);

        return view('locations.create', [
            'location' => $location,
            'timezones' => $this->getTimezones(),
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        Gate::authorize('update', $location);

        $validated = $request->validated();

        $validated['slug'] = $this->getSlug($validated['name']);

        $location->update($validated);

        session(['current_location_id' => $location->id]);
        session(['current_location_timezone' => $location->timezone]);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location updated successfully.');
    }

    private function getTimezones(): array
    {
        $timezones = array_filter(
            timezone_identifiers_list(),
            fn ($tz) => str_starts_with($tz, 'Australia/')
        );

        // Optional: sort alphabetically
        sort($timezones);

        return $timezones;
    }

    private function getSlug($slug)
    {
        $slug = str()->of($slug)->slug('-');

        // Ensure slug is unique by appending -2, -3, etc. if needed
        $originalSlug = $slug;
        $counter = 1;
        while (Location::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $originalSlug.'-'.$counter;
        }

        return $slug;
    }
}
