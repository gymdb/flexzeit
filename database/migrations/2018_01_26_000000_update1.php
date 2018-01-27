<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Update1 extends Migration {

  /**
   * Add a column for automatically generated lessons
   * Add a column for room shortname
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasColumn('lessons', 'generated')) {
      Schema::table('lessons', function(Blueprint $table) {
        $table->boolean('generated')->default(false);
      });
    }

    if (!Schema::hasColumn('rooms', 'shortname')) {
      Schema::table('rooms', function(Blueprint $table) {
        $table->string('shortname', 10)->nullable()->unique();
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

    if (Schema::hasColumn('rooms', 'shortname')) {
      Schema::table('rooms', function(Blueprint $table) {
        $table->dropColumn('shortname');
      });
    }
  }
}
