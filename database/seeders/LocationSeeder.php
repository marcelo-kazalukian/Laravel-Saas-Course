<?php

namespace Database\Seeders;

use App\Enums\WeekDayEnum;
use App\Models\Calendar;
use App\Models\Location;
use App\Models\LocationHour;
use App\Models\Organization;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find organization
        $organization = Organization::firstOrCreate(
            ['name' => 'Demo Beauty Salon'],
            ['name' => 'Demo Beauty Salon']
        );

        // Create location
        $location = Location::firstOrCreate(
            ['slug' => 'downtown-salon'],
            [
                'organization_id' => $organization->id,
                'slug' => 'downtown-salon',
                'name' => 'Downtown Beauty Salon',
                'timezone' => 'America/New_York',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
                'phone' => '+1-555-123-4567',
                'email' => 'info@downtownsalon.com',
            ]
        );

        // Create calendar for location (if doesn't exist)
        $calendar = Calendar::firstOrCreate(
            ['location_id' => $location->id],
            [
                'organization_id' => $organization->id,
                'location_id' => $location->id,
                'slot_duration' => 30, // 30 minutes
                'show_providers' => true,
            ]
        );

        // Update location with calendar_id if not set
        if (! $location->calendar_id) {
            $location->update(['calendar_id' => $calendar->id]);
        }

        // Create location hours (only if none exist)
        if ($location->hours()->count() === 0) {
            $weekdayHours = [
                WeekDayEnum::MONDAY->value => ['09:00:00', '18:00:00'],
                WeekDayEnum::TUESDAY->value => ['09:00:00', '18:00:00'],
                WeekDayEnum::WEDNESDAY->value => ['09:00:00', '18:00:00'],
                WeekDayEnum::THURSDAY->value => ['09:00:00', '18:00:00'],
                WeekDayEnum::FRIDAY->value => ['09:00:00', '18:00:00'],
                WeekDayEnum::SATURDAY->value => ['09:00:00', '16:00:00'],
            ];

            foreach ($weekdayHours as $dayOfWeek => $times) {
                LocationHour::create([
                    'organization_id' => $organization->id,
                    'location_id' => $location->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $times[0],
                    'end_time' => $times[1],
                ]);
            }
        }

        // Create service category
        $serviceCategory = ServiceCategory::firstOrCreate(
            ['organization_id' => $organization->id, 'name' => 'Hair Services'],
            [
                'organization_id' => $organization->id,
                'name' => 'Hair Services',
                'description' => 'Professional hair care and styling services',
            ]
        );

        // Create services
        $services = [
            [
                'name' => 'Haircut & Style',
                'description' => 'Professional haircut with styling',
                'options' => [
                    ['name' => 'Basic Cut', 'price' => 4500, 'duration' => 45],
                    ['name' => 'Premium Cut & Style', 'price' => 6500, 'duration' => 60],
                ],
            ],
            [
                'name' => 'Hair Coloring',
                'description' => 'Professional hair coloring services',
                'options' => [
                    ['name' => 'Root Touch-up', 'price' => 7500, 'duration' => 90],
                    ['name' => 'Full Color', 'price' => 12000, 'duration' => 120],
                    ['name' => 'Highlights', 'price' => 15000, 'duration' => 150],
                ],
            ],
            [
                'name' => 'Blowout & Styling',
                'description' => 'Professional blowout and styling services',
                'options' => [
                    ['name' => 'Basic Blowout', 'price' => 3500, 'duration' => 30],
                    ['name' => 'Formal Styling', 'price' => 5500, 'duration' => 45],
                ],
            ],
        ];

        $createdServices = [];
        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'name' => $serviceData['name'],
                ],
                [
                    'organization_id' => $organization->id,
                    'calendar_id' => $calendar->id,
                    'service_category_id' => $serviceCategory->id,
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'active' => true,
                ]
            );

            // Create service options (only if none exist for this service)
            if ($service->serviceOptions()->count() === 0) {
                foreach ($serviceData['options'] as $optionData) {
                    ServiceOption::create([
                        'organization_id' => $organization->id,
                        'service_id' => $service->id,
                        'name' => $optionData['name'],
                        'price' => $optionData['price'], // price in cents
                        'duration' => $optionData['duration'], // duration in minutes
                        'active' => true,
                    ]);
                }
            }

            $createdServices[] = $service;
        }

        // Create providers
        $providers = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@downtownsalon.com',
                'phone' => '+1-555-123-4571',
            ],
            [
                'name' => 'Maria Rodriguez',
                'email' => 'maria@downtownsalon.com',
                'phone' => '+1-555-123-4572',
            ],
            [
                'name' => 'Jessica Chen',
                'email' => 'jessica@downtownsalon.com',
                'phone' => '+1-555-123-4573',
            ],
        ];

        $createdProviders = [];
        foreach ($providers as $providerData) {
            $provider = Provider::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'email' => $providerData['email'],
                ],
                [
                    'organization_id' => $organization->id,
                    'name' => $providerData['name'],
                    'email' => $providerData['email'],
                    'phone' => $providerData['phone'],
                ]
            );
            $createdProviders[] = $provider;
        }

        // Create service_provider relationships (assign providers to services with availability)
        // Only create if no relationships exist yet
        $existingRelationships = \DB::table('service_provider')
            ->where('organization_id', $organization->id)
            ->count();

        if ($existingRelationships === 0) {
            foreach ($createdServices as $service) {
                foreach ($createdProviders as $provider) {
                    // Each provider works different days and hours
                    $providerSchedules = [
                        0 => [ // Sarah - works Mon-Wed-Fri
                            [WeekDayEnum::MONDAY->value, '09:00:00', '17:00:00'],
                            [WeekDayEnum::WEDNESDAY->value, '09:00:00', '17:00:00'],
                            [WeekDayEnum::FRIDAY->value, '09:00:00', '17:00:00'],
                        ],
                        1 => [ // Maria - works Tue-Thu-Sat
                            [WeekDayEnum::TUESDAY->value, '09:00:00', '16:00:00'],
                            [WeekDayEnum::THURSDAY->value, '09:00:00', '17:00:00'],
                            [WeekDayEnum::SATURDAY->value, '09:00:00', '15:00:00'],
                        ],
                        2 => [ // Jessica - works Mon-Thu-Fri-Sat
                            [WeekDayEnum::MONDAY->value, '10:00:00', '18:00:00'],
                            [WeekDayEnum::THURSDAY->value, '10:00:00', '18:00:00'],
                            [WeekDayEnum::FRIDAY->value, '10:00:00', '18:00:00'],
                            [WeekDayEnum::SATURDAY->value, '09:00:00', '15:00:00'],
                        ],
                    ];

                    $providerIndex = array_search($provider, $createdProviders);
                    $schedules = $providerSchedules[$providerIndex] ?? [];

                    foreach ($schedules as $schedule) {
                        \DB::table('service_provider')->insert([
                            'organization_id' => $organization->id,
                            'service_id' => $service->id,
                            'provider_id' => $provider->id,
                            'day_of_week' => $schedule[0],
                            'start_time' => $schedule[1],
                            'end_time' => $schedule[2],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Location seeder completed successfully!');
        $this->command->info("Created organization: {$organization->name}");
        $this->command->info("Created location: {$location->name}");
        $this->command->info("Created calendar with {$calendar->slot_duration} minute slots");
        $this->command->info('Created '.$location->hours()->count().' location hours');
        $this->command->info("Created service category: {$serviceCategory->name}");
        $this->command->info('Created '.count($createdServices).' services');
        $this->command->info('Created '.count($createdProviders).' providers');
    }
}
