<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategories extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'game_id'];

    /**
     * Get the category that owns the game category.
     */
    public function categories()
    {
        return $this->belongsTo(Categories::class);
    }

    /**
     * Get the game that owns the game category.
     */
    public function games()
    {
        return $this->belongsTo(Game::class);
    }
}

