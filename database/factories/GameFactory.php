<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Game::class;
    public function definition(): array
    {
        $playType = $this->faker->randomElement(['web', 'download']);
        $canplay = $playType === 'download' ? $this->faker->randomElements(
            ['Windows', 'Mac', 'Linux', 'Android', 'Ios'],
            $this->faker->numberBetween(1, 3)
        ) : null;
        $filePaths = [];
        if ($playType === "download") {
            foreach ($canplay as $platform) {
                $filePaths[$platform] = $this->faker->filePath();
            }
        } else {
            $filePaths['web'] = 'storage/Game/Snake-Game-master';
        }

        return [
            'title' => $this->faker->slug(2, true),

            'content' => '<h1>' . $this->faker->words(4, true) . '</h1>' .
                '<p>' . $this->faker->paragraph() . '</p>' .
                '<img src="https://via.placeholder.com/200x200?text=Image1" alt="Image 1" />' .
                '<p>' . $this->faker->paragraph() . '</p>' .
                '<ul>' .
                '<li>' . $this->faker->word() . '</li>' .
                '<li>' . $this->faker->word() . '</li>' .
                '<li>' . $this->faker->word() . '</li>' .
                '</ul>' .
                '<img src="https://via.placeholder.com/200x200?text=Image2" alt="Image 2" />' .
                '<p>' . $this->faker->paragraph() . '</p>' .
                '<img src="https://via.placeholder.com/200x200?text=Image3" alt="Image 3" />' .
                '<p>' . $this->faker->paragraph() . '</p>',
            'play_type' => $playType,
            'canplay' => json_encode($canplay)  ,
            'file_path' => json_encode($filePaths),
            'download' => $playType === 'web' ? null : $this->faker->numberBetween(0, 500),
            'created_at' => $this->faker->dateTimeBetween('2023-12-01', '2023-12-31'),
            'user_id' => null,
        ];
    }
}
