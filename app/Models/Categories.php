<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categories extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function games()
    {
        return $this->belongsToMany(Game::class,'game_categories','category_id','game_id')->withTimestamps();
    }

}
