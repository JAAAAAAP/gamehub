<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Categories;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categories>
 */
class CategoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Categories::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Action',
                'Adventure',
                'RPG',
                'Shooter',
                'Puzzle',
                'Sports',
                'Strategy',
                'Simulation',
                'Horror',
                'Casual',
            ]),
        ];
    }
}
