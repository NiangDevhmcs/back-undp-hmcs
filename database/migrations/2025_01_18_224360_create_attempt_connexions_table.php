<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttemptConnexionsTable extends Migration
{
    public function up()
    {
        Schema::create('attempt_connexions', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('email')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->integer('block_time')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attempt_connexions');
    }
}

