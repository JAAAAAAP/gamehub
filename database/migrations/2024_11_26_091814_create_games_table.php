<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     *Todo : Create Er diagram in mermaid.js.org(not done yet)
     *
     * รัน Migration
     * เมธอดใช้ในการสร้างตารางในฐานข้อมูล
     *
     * Run the migrations.
     * This method is used to create the tables in the database.
     */
    public function up(): void
    {

        // สร้างตาราง 'categories' สำหรับจัดเก็บข้อมูลหมวดหมู่
        // Create the 'categories' table to store category data
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)
            $table->string('name');
            $table->timestamps();
        });

        // สร้างตาราง 'games' สำหรับจัดเก็บข้อมูลเกม
        // Create the 'games' table to store game data
        Schema::create('games', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)
            $table->string('title');
            $table->longText('content');
            $table->enum('play_type', ['web', 'download']);
            $table->json('canplay')->nullable();
            $table->json('file_path')->nullable();
            $table->integer('download')->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();


            $table->timestamps();
        });

        //Pivot table for many-to-many relationship between games and categories
        Schema::create('game_categories', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();


            $table->timestamps();
        });

        // สร้างตาราง 'game_galleries' สำหรับเก็บภาพที่เกี่ยวข้องกับเกม (gallery)
        // Create the 'game_galleries' table to store images related to the games (gallery)
        Schema::create('game_galleries', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)
            $table->json('images');
            $table->json('theme')->nullable();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();

            $table->timestamps();
        });

        // สร้างตาราง 'likes' สำหรับเก็บข้อมูลการกดไลค์เกม
        // Create the 'likes' table to store data for likes on games
        Schema::create('likes', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)

            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });

        // สร้างตาราง 'reviews' สำหรับเก็บข้อมูลการรีวิวเกม
        // Create the 'reviews' table to store game review data
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); //Primary key (auto-increment ID)
            $table->integer('rating')->nullable();
            $table->string('comment')->nullable();
            $table->string('parent_id')->nullable();

            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
        Schema::dropIfExists('game_category');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('game_img_gallery');
        Schema::dropIfExists('like');
        Schema::dropIfExists('reviews');
    }
};
