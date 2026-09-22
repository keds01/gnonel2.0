<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'photos';
    
    protected $fillable = [
        'event_id',
        'image_path',
        'thumbnail_path',
        'caption',
        'is_featured',
        'is_hidden',
        'order',
        'likes_count'
    ];
    
    protected $casts = [
        'is_featured' => 'boolean',
        'is_hidden' => 'boolean',
        'order' => 'integer',
        'likes_count' => 'integer'
    ];
    
    protected $appends = ['image_url', 'thumbnail_url'];
    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    
    public function likes()
    {
        return $this->hasMany(Like::class, 'photo_id');
    }
    
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/events/' . $this->image_path);
        }
        return null;
    }
    
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/events/thumbnails/' . $this->thumbnail_path);
        }
        return $this->image_url;
    }
}