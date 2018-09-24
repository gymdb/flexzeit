<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UntisLesson extends Migration {

  /**
   * Add column for the last untis lesson id a lesson was bound to
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasColumn('lessons', 'untis_id')) {
      Schema::table('lessons', function(Blueprint $table) {
        $table->unsignedInteger('untis_id')->nullable();
      });
    }
  }

  /**
   * Drop the column
   *
   * @return void
   */
  public function down() {
    if (Schema::hasColumn('lessons', 'untis_id')) {
      Schema::table('lessons', function(Blueprint $table) {
        $table->dropColumn('untis_id');
      });
    }
  }
}
