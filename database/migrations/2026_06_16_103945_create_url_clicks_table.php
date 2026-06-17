<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('url_clicks', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->timestamps();
            $table->foreignId('url_id')->bigInteger()->cascadeOnDelete();
            $table->string('ip_adress')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamp('clicked_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_clicks');
    }
};
