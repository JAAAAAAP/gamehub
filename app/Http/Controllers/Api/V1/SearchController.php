<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Game;
use App\Models\User;
use App\ResponeTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\UserResource;

class SearchController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $formattedQuery = str_replace(' ', '-', $query);

        $games = Game::search($formattedQuery)
        ->query(function ($builder) {
            $builder->with(['galleries']); // เพิ่มความสัมพันธ์ที่ต้องการ
        })
        ->take(10)
        ->get();

        $users = User::search($query)->take(10)->get();

        $results = [
            'games' => GameResource::collection($games),
            'users' => UserResource::collection($users),
        ];

        return $this->Success(
            data: $results,
            meta: [
                'games_result' => $games->count(),
                'users_result' => $users->count(),
            ]
        );
    }
}
