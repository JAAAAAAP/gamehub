<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory, Searchable;
    protected $fillable = [
        'title',
        'content',
        'play_type',
        'canplay',
        'file_path',
        'user_id'
    ];
    protected $casts = [
        'canplay' => 'array',
    ];
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'game_categories', 'game_id', 'category_id')->withTimestamps();
    }
    public function galleries()
    {
        return $this->hasMany(GameGalleries::class);
    }
    public function likes()
    {
        return $this->hasMany(Likes::class);
    }
    public function reviews()
    {
        return $this->hasMany(Reviews::class);
    }
}
