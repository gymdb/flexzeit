<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Update1 extends Migration {

  /**
   * Add a column for automatically generated lessons
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasColumn('lessons', 'generated')) {
      Schema::table('lessons', function(Blueprint $table) {
        $table->boolean('generated')->default(false);
      });
    }
  }

  /**
   * Drop the columns
   *
   * @return void
   */
  public function down() {
    if (Schema::hasColumn('lessons', 'generated')) {
      Schema::table('lessons', function(Blueprint $table) {
        $table->dropColumn('generated');
      });
    }
  }
}
