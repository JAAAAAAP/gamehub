<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameGalleryResource extends JsonResource
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
            $this->base_url . '/search',
        ];

        if (in_array(request()->path(), $sharedRoutes)) {
            return [
                'id' => $this->id,
                'images' => json_decode($this->images)->logo,
            ];
        }

        return [
            'id' => $this->id,
            'images' => json_decode($this->images),
            'theme' => json_decode($this->theme)
        ];
    }
}
