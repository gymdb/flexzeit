<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Frequency extends Migration {

  /**
   * Add column for course frequency
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasColumn('courses', 'frequency')) {
      Schema::table('courses', function(Blueprint $table) {
        $table->unsignedInteger('frequency')->nullable();
      });
    }
  }

  /**
   * Drop the column
   *
   * @return void
   */
  public function down() {
    if (Schema::hasColumn('courses', 'frequency')) {
      Schema::table('courses', function(Blueprint $table) {
        $table->dropColumn('frequency');
      });
    }
  }
}
