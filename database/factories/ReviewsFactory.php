<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Game;
use App\Models\User;
use App\Models\Reviews;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reviews>
 */
class ReviewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Reviews::class;

    public function definition(): array
    {
        return [
            'rating' => $this->faker->randomElement(['1', '2', '3', '4', '5']),
            'comment' => $this->faker->text(255),
            'parent_id' => null,
            'game_id' => null, // สร้างเกมใหม่และเชื่อมโยงกับรีวิว
            'user_id' => null,
        ];
    }


}
