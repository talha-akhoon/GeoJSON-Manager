<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $validFrom = $this->faker->dateTimeBetween('-1 year');
        $validTo = $this->faker->optional(0.7)->dateTimeBetween($validFrom, '+1 year');

        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'display_in_breaches' => $this->faker->boolean(),
            'geojson_data' => $this->generateGeoJsonData(),
        ];
    }

    /**
     * @return array
     */
    protected function generateGeoJsonData(): array
    {
        $centerLat = $this->faker->latitude();
        $centerLon = $this->faker->longitude();

        $polygon = [
            [$centerLon - 0.1, $centerLat - 0.1],
            [$centerLon + 0.1, $centerLat - 0.1],
            [$centerLon + 0.1, $centerLat + 0.1],
            [$centerLon - 0.1, $centerLat + 0.1],
            [$centerLon - 0.1, $centerLat - 0.1],
        ];

        return [
            'type' => 'Feature',
            'properties' => [],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$polygon],
            ],
        ];
    }
}
