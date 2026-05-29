<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 100000),
            'name' => $name,
            'slug' => Str::slug($name),
            'country' => fake()->optional(0.6)->countryCode(),
        ];
    }
}
