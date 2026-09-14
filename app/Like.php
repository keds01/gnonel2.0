<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'likes';
    
    protected $fillable = [
        'event_id',
        'photo_id',
        'session_id',
        'ip_address',
        'user_agent'
    ];
    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    
    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
    
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }
    
    public function scopeForPhoto($query, $photoId)
    {
        return $query->where('photo_id', $photoId);
    }
    
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
    
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }
}