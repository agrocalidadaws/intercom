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
        Schema::create('intercom_oauth_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->text('access_token');
            $table->timestamp('expires_in');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intercom_oauth_tokens');
    }
};
