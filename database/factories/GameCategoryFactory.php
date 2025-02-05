<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GameCategories;
use App\Models\Game;
use App\Models\Categories;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameCategories>
 */
class GameCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = GameCategory::class;

    public function definition(): array
    {
        return [
            'game_id' => Game::factory(), // สร้างเกมใหม่
            'category_id' => Categories::factory(), // สร้างหมวดหมู่ใหม่
        ];
    }
}
