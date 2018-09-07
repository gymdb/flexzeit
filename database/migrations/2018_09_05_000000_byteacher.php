<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ByTeacher extends Migration {

  /**
   * Add columns for registrations made by teachers and the timestamp
   *
   * @return void
   */
  public function up() {
    if (!Schema::hasColumn('registrations', 'byteacher')) {
      Schema::table('registrations', function(Blueprint $table) {
        $table->boolean('byteacher')->default(false);
      });
    }
    if (!Schema::hasColumn('registrations', 'registered_at')) {
      Schema::table('registrations', function(Blueprint $table) {
        $table->timestamp('registered_at')->nullable();
      });
    }
  }

  /**
   * Drop the column
   *
   * @return void
   */
  public function down() {
    if (Schema::hasColumn('registrations', 'byteacher')) {
      Schema::table('registrations', function(Blueprint $table) {
        $table->dropColumn('byteacher');
      });
    }
    if (Schema::hasColumn('registrations', 'registered_at')) {
      Schema::table('registrations', function(Blueprint $table) {
        $table->dropColumn('registered_at');
      });
    }
  }
}
