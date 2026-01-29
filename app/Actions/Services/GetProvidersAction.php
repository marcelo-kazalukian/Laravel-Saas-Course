<?php

namespace App\Actions\Services;

use App\Models\Provider;

final class GetProvidersAction
{
    public function handle(array $serviceOptionIds): array
    {
        if (empty($serviceOptionIds)) {
            return [];
        }

        $requiredCount = count($serviceOptionIds);

        // Query providers who offer all specified service options
        return Provider::query()
            ->select('providers.id', 'providers.name')
            ->join('service_provider', 'service_provider.provider_id', '=', 'providers.id')
            ->join('services', 'services.id', '=', 'service_provider.service_id')
            ->join('service_options', 'service_options.service_id', '=', 'services.id')
            ->whereIn('service_options.id', $serviceOptionIds)
            ->groupBy('providers.id', 'providers.name')
            ->havingRaw('COUNT(DISTINCT service_options.id) = ?', [$requiredCount])
            ->orderBy('providers.name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }
}
