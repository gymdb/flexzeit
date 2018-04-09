<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoomOccupations extends Migration {

  /**
   * Add a table for occupied rooms
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasTable('room_occupations')) {
      Schema::create('room_occupations', function(Blueprint $table) {
        $table->increments('id');
        $table->date('date');
        $table->unsignedTinyInteger('number')->nullable();
        $table->unsignedInteger('room_id')->nullable();
        $table->unsignedInteger('teacher_id')->nullable();

        $table->foreign('room_id')->references('id')->on('rooms');
        $table->foreign('teacher_id')->references('id')->on('teachers');
        $table->unique(['room_id', 'teacher_id', 'date', 'number']);
        $table->index(['date', 'number']);
      });
    }
  }

  /**
   * Drop the table
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('room_occupations');
  }
}
