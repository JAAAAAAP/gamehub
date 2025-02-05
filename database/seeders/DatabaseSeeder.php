<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\User;
use App\Models\Game;
use App\Models\GameGalleries;
use App\Models\Likes;
use App\Models\Reviews;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $categories = Categories::factory(10)->create();

        $users = User::factory(10)->create();

        // Game::factory(100)
        //     ->state(function () use ($users) {
        //         return [
        //             'user_id' => $users->random()->id,
        //         ];
        //     })
        //     ->afterCreating(function ($game) use ($categories) {
        //         // เชื่อมโยงเกมกับหมวดหมู่แบบสุ่ม 1-3 หมวดหมู่
        //         $game->categories()->attach(
        //             $categories->random(rand(1, 3))->pluck('id')->toArray()
        //         );
        //     })
        //     ->has(GameGalleries::factory(1), 'galleries')
        //     ->has(
        //         Likes::factory(rand(1, 20))->state(function () use ($users) {
        //             // กำหนดค่า user_id ใน Review
        //             return [
        //                 'user_id' => $users->random()->id,
        //             ];
        //         }),
        //         'likes'
        //     )
        //     ->has(
        //         Reviews::factory(3)->state(function () use ($users) {
        //             // กำหนดค่า user_id ใน Review
        //             return [
        //                 'user_id' => $users->random()->id,
        //             ];
        //         }),
        //         'reviews'
        //     )
        //     ->create();
    }
}
