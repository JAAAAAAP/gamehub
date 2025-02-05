<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Reviews extends Model
{
    use HasFactory;
    protected $fillable = ['rating', 'comment', 'parent_id', 'game_id', 'user_id'];
    public function game()
    {
        return $this->belongsTo(Game::class,'game_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
