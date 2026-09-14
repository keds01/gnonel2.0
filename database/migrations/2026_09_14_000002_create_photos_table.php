<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotosTable extends Migration
{
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('caption')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->integer('order')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
            
            $table->index('event_id');
            $table->index('is_featured');
            $table->index('is_hidden');
        });
    }

    public function down()
    {
        Schema::dropIfExists('photos');
    }
}