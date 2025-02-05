<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GameGalleries;
use App\Models\Game;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameGalleries>
 */
class GameGalleriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = GameGalleries::class;
    public function definition(): array
    {

        return [
            'images' => json_encode([
                'logo' => "https://placehold.co/200x200?text=" . $this->faker->word(),
                'background' => $this->faker->boolean() ? "https://placehold.co/1920x1080?text=" . $this->faker->word() : null, // Nullable background image
                'gallery' => [
                    "https://placehold.co/800x600?text=" . $this->faker->word(),
                    "https://placehold.co/800x600?text=" . $this->faker->word(),
                    "https://placehold.co/800x600?text=" . $this->faker->word(),
                    "https://placehold.co/800x600?text=" . $this->faker->word(),
                ],
            ]),
            'theme' => json_encode([
                'Bg_Color' => $this->faker->boolean() ? $this->faker->hexColor() : null,
                'Bg_2_Color' => $this->faker->boolean() ? $this->faker->hexColor() : null,
                'Text_Color' => $this->faker->boolean() ? $this->faker->hexColor() : null,
                'Link_Color' => $this->faker->boolean() ? $this->faker->hexColor() : null,
                'Button_Color' => $this->faker->boolean() ? $this->faker->hexColor() : null,
            ]),
            'game_id' => Game::factory(),
        ];
    }
}
