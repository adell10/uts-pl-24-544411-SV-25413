<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cafes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('area', 100);
            $table->enum('status', ['visited', 'wishlist'])->default('wishlist');
            $table->unsignedTinyInteger('price_level')->default(2);   // 1 = Rp, 2 = Rp Rp, 3 = Rp Rp Rp
            $table->unsignedTinyInteger('rating')->nullable();         // 1–5, kosong kalau belum mampir
            $table->boolean('work_friendly')->default(false);          // cocok buat nugas
            $table->json('facilities')->nullable();                    // ["wifi","colokan","musala","parkir"]
            $table->string('favorite_menu', 100)->nullable();
            $table->text('notes')->nullable();
            $table->date('last_visited_at')->nullable();
            $table->unsignedSmallInteger('visit_count')->default(0);
            $table->string('color', 10)->default('pink');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafes');
    }
};