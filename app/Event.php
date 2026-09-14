<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    
    protected $fillable = [
        'title',
        'event_date',
        'category',
        'description',
        'cover_image',
        'is_active',
        'likes_count'
    ];
    
    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
        'likes_count' => 'integer'
    ];
    
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
    
    public function likes()
    {
        return $this->hasMany(Like::class, 'event_id');
    }
    
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/events/' . $this->cover_image);
        }
        return asset('images/default-event-cover.jpg');
    }
}