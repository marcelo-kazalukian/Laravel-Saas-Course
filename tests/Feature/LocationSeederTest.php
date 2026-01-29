<?php

use App\Enums\WeekDayEnum;
use App\Models\Calendar;
use App\Models\Location;
use App\Models\LocationHour;
use App\Models\Organization;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('location seeder creates complete location with all related data', function () {
    // Act - run the seeder
    $this->seed(LocationSeeder::class);

    // Assert organization was created
    $organization = Organization::where('name', 'Demo Beauty Salon')->first();
    expect($organization)->not->toBeNull();

    // Assert location was created
    $location = Location::where('slug', 'downtown-salon')->first();
    expect($location)->not->toBeNull();
    expect($location->organization_id)->toBe($organization->id);
    expect($location->name)->toBe('Downtown Beauty Salon');
    expect($location->timezone)->toBe('America/New_York');
    expect($location->city)->toBe('New York');

    // Assert calendar was created
    $calendar = Calendar::where('location_id', $location->id)->first();
    expect($calendar)->not->toBeNull();
    expect($calendar->slot_duration)->toBe(30);
    expect($calendar->show_providers)->toBeTrue();
    expect($location->calendar_id)->toBe($calendar->id);

    // Assert location hours were created
    expect($location->hours()->count())->toBe(6);

    // Check specific hours
    $mondayHours = $location->hours()->where('day_of_week', WeekDayEnum::MONDAY->value)->first();
    expect($mondayHours)->not->toBeNull();
    expect($mondayHours->start_time)->toBe('09:00:00');
    expect($mondayHours->end_time)->toBe('18:00:00');

    $saturdayHours = $location->hours()->where('day_of_week', WeekDayEnum::SATURDAY->value)->first();
    expect($saturdayHours)->not->toBeNull();
    expect($saturdayHours->start_time)->toBe('09:00:00');
    expect($saturdayHours->end_time)->toBe('16:00:00');

    // Assert service category was created
    $serviceCategory = ServiceCategory::where('name', 'Hair Services')->first();
    expect($serviceCategory)->not->toBeNull();
    expect($serviceCategory->organization_id)->toBe($organization->id);

    // Assert services were created
    expect($calendar->services()->count())->toBe(3);

    $haircutService = Service::where('name', 'Haircut & Style')->first();
    expect($haircutService)->not->toBeNull();
    expect($haircutService->organization_id)->toBe($organization->id);
    expect($haircutService->calendar_id)->toBe($calendar->id);
    expect($haircutService->service_category_id)->toBe($serviceCategory->id);
    expect($haircutService->active)->toBeTrue();

    // Assert service options were created
    expect($haircutService->serviceOptions()->count())->toBe(2);

    $basicCut = ServiceOption::where('name', 'Basic Cut')->first();
    expect($basicCut)->not->toBeNull();
    expect($basicCut->service_id)->toBe($haircutService->id);
    expect($basicCut->price)->toBe(4500);
    expect($basicCut->duration)->toBe(45);
    expect($basicCut->active)->toBeTrue();

    // Assert providers were created
    $providers = Provider::where('organization_id', $organization->id)->get();
    expect($providers->count())->toBe(3);

    $sarah = Provider::where('name', 'Sarah Johnson')->first();
    expect($sarah)->not->toBeNull();
    expect($sarah->email)->toBe('sarah@downtownsalon.com');

    // Assert service_provider relationships were created
    $serviceProviderCount = DB::table('service_provider')
        ->where('organization_id', $organization->id)
        ->count();
    expect($serviceProviderCount)->toBeGreaterThan(0);

    // Check specific provider schedule
    $sarahMonday = DB::table('service_provider')
        ->where('provider_id', $sarah->id)
        ->where('day_of_week', WeekDayEnum::MONDAY->value)
        ->first();
    expect($sarahMonday)->not->toBeNull();
    expect($sarahMonday->start_time)->toBe('09:00:00');
});

test('location seeder is idempotent - can run multiple times safely', function () {
    // Act - run seeder twice
    $this->seed(LocationSeeder::class);
    $this->seed(LocationSeeder::class);

    // Assert data wasn't duplicated
    expect(Organization::where('name', 'Demo Beauty Salon')->count())->toBe(1);
    expect(Location::where('slug', 'downtown-salon')->count())->toBe(1);
    expect(Calendar::count())->toBe(1);
    expect(LocationHour::count())->toBe(6);
    expect(ServiceCategory::where('name', 'Hair Services')->count())->toBe(1);
    expect(Service::count())->toBe(3);
    expect(Provider::count())->toBe(3);

    // Service options should not be duplicated either
    $haircutService = Service::where('name', 'Haircut & Style')->first();
    expect($haircutService->serviceOptions()->count())->toBe(2);
});

test('seeder creates all expected services and options', function () {
    // Act
    $this->seed(LocationSeeder::class);

    // Assert all services exist
    $expectedServices = ['Haircut & Style', 'Hair Coloring', 'Blowout & Styling'];
    foreach ($expectedServices as $serviceName) {
        $service = Service::where('name', $serviceName)->first();
        expect($service)->not->toBeNull();
        expect($service->serviceOptions()->count())->toBeGreaterThan(0);
    }

    // Assert specific options exist
    expect(ServiceOption::where('name', 'Basic Cut')->exists())->toBeTrue();
    expect(ServiceOption::where('name', 'Full Color')->exists())->toBeTrue();
    expect(ServiceOption::where('name', 'Highlights')->exists())->toBeTrue();
    expect(ServiceOption::where('name', 'Basic Blowout')->exists())->toBeTrue();
});

test('seeder creates providers with different schedules', function () {
    // Act
    $this->seed(LocationSeeder::class);

    // Assert all providers exist
    $expectedProviders = ['Sarah Johnson', 'Maria Rodriguez', 'Jessica Chen'];
    foreach ($expectedProviders as $providerName) {
        $provider = Provider::where('name', $providerName)->first();
        expect($provider)->not->toBeNull();

        // Each provider should have at least one schedule
        $scheduleCount = DB::table('service_provider')
            ->where('provider_id', $provider->id)
            ->count();
        expect($scheduleCount)->toBeGreaterThan(0);
    }

    // Check that different providers work different days
    $sarah = Provider::where('name', 'Sarah Johnson')->first();
    $maria = Provider::where('name', 'Maria Rodriguez')->first();

    $sarahDays = DB::table('service_provider')
        ->where('provider_id', $sarah->id)
        ->distinct()
        ->pluck('day_of_week')
        ->toArray();

    $mariaDays = DB::table('service_provider')
        ->where('provider_id', $maria->id)
        ->distinct()
        ->pluck('day_of_week')
        ->toArray();

    // Sarah and Maria should have different working days
    expect($sarahDays)->not->toEqual($mariaDays);
});
