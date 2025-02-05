<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{

    public $base_url = 'api/V1';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sharedRoutes = [
            $this->base_url . '/games',
            $this->base_url . '/newestgames',
            $this->base_url . '/mostdownloadgames',
            $this->base_url . '/mostratinggames',
        ];

        if (in_array(request()->path(), $sharedRoutes)) {
            return [
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'play_type' => $this->play_type,
                'canplay' => $this->canplay,
                'download' => $this->download,
                'user' => [
                    "name" => $this->user->name,
                ],


                'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                'galleries' => GameGalleryResource::collection($this->whenLoaded('galleries')),
                'likes' => $this->whenLoaded('likes', $this->likes_count, 0),
                'rating' =>  $this->whenLoaded('reviews', round($this->reviews_avg_rating, 1), 0),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }

        if (request()->is($this->base_url . '/search')) {
            return [
                'title' => $this->title,
                'galleries' => GameGalleryResource::collection($this->whenLoaded('galleries')),
                'user' => $this->user->name,
                'type' => 'game'
            ];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'play_type' => $this->play_type,
            'canplay' => json_decode($this->canplay),
            'file_path' => json_decode($this->file_path),
            'download' => $this->download,
            'user' => [
                "id" => Crypt::encrypt($this->user->id),
                "name" => $this->user->name,
                "email" => $this->user->email,
            ],

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'galleries' => GameGalleryResource::collection($this->whenLoaded('galleries')),
            'likes' => $this->whenLoaded('likes', $this->likes_count, 0),
            'rating' =>  $this->whenLoaded('reviews', round($this->reviews_avg_rating, 1), 0),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'total_games' => $this->total(), // ตัวอย่างการใช้ total จาก pagination
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
