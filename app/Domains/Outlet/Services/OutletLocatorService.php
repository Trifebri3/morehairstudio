<?php

namespace App\Domains\Outlet\Services;

use App\Domains\Outlet\Models\Outlet;

class OutletLocatorService
{
    /**
     * Get outlets sorted by distance from the given coordinates.
     */
    public function findNearest(float $latitude, float $longitude, int $limit = 5): array
    {
        $outlets = Outlet::where('status', 'active')->get();
        $results = [];

        foreach ($outlets as $outlet) {
            if ($outlet->latitude && $outlet->longitude) {
                $distance = $this->calculateDistance($latitude, $longitude, (float)$outlet->latitude, (float)$outlet->longitude);
                $results[] = [
                    'outlet' => $outlet,
                    'distance' => round($distance, 2) // in km
                ];
            } else {
                $results[] = [
                    'outlet' => $outlet,
                    'distance' => 99999.0
                ];
            }
        }

        // Sort by distance ascending
        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return array_slice($results, 0, $limit);
    }

    /**
     * Calculate geodesic distance in kilometers using the Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0; // Earth radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
