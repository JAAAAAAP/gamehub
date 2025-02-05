<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameGalleries extends Model
{
    use HasFactory;

    protected $fillable = ['images', 'theme' , 'game_id'];
    protected $casts = [
        'images' => 'array',
    ];
    public function games()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
