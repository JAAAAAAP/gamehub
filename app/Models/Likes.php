<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Likes extends Model
{
    use HasFactory;
    protected $fillable = ['game_id', 'user_id'];

    public function games()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
