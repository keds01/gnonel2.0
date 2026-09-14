<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('photo_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('event_id');
            $table->index('photo_id');
            $table->index('session_id');
            $table->index('ip_address');
            
            // Ensure a session can only like once per event or photo
            $table->unique(['session_id', 'event_id'], 'unique_session_event_like');
            $table->unique(['session_id', 'photo_id'], 'unique_session_photo_like');
        });
    }

    public function down()
    {
        Schema::dropIfExists('likes');
    }
}