<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    // First create all tables without foreign key relations
    Schema::create('config', function(Blueprint $table) {
      $table->string('key', 30);
      $table->json('value');

      $table->primary('key');
    });

    Schema::create('groups', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name')->unique();
    });

    Schema::create('students', function(Blueprint $table) {
      $table->increments('id');
      $table->string('firstname', 50);
      $table->string('lastname', 50);
      $table->string('username', 50)->unique();
      $table->string('password');
      $table->string('image');
    });

    Schema::create('subjects', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name', 20)->unique();
    });

    Schema::create('teachers', function(Blueprint $table) {
      $table->increments('id');
      $table->string('firstname', 50);
      $table->string('lastname', 50);
      $table->string('username', 50)->unique();
      $table->string('password');
      $table->boolean('admin');
    });

    // Now create tables with foreign key relations to the previously created ones
    Schema::create('absences', function(Blueprint $table) {
      $table->unsignedInteger('student_id');
      $table->date('date');

      $table->primary(['student_id', 'date']);
      $table->foreign('student_id')->references('id')->on('students');
    });

    Schema::create('forms', function(Blueprint $table) {
      $table->unsignedInteger('group_id');
      $table->unsignedTinyInteger('year');
      $table->unsignedInteger('kv_id');

      $table->primary('group_id');
      $table->foreign('group_id')->references('id')->on('groups');
      $table->foreign('kv_id')->references('id')->on('teachers');
    });

    Schema::create('courses', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name');
      $table->text('description');
      $table->unsignedInteger('subject_id')->nullable();
      $table->unsignedSmallInteger('maxstudents')->nullable();
      $table->string('room')->nullable();
      $table->unsignedTinyInteger('yearfrom')->nullable();
      $table->unsignedTinyInteger('yearto')->nullable();

      $table->foreign('subject_id')->references('id')->on('subjects');
    });

    Schema::create('lessons', function(Blueprint $table) {
      $table->increments('id');
      $table->unsignedInteger('teacher_id');
      $table->date('date');
      $table->unsignedTinyInteger('number');
      $table->unsignedInteger('course_id')->nullable();
      $table->string('room');
      $table->boolean('cancelled')->default(false);

      $table->foreign('teacher_id')->references('id')->on('teachers');
      $table->foreign('course_id')->references('id')->on('courses');
      $table->unique(['teacher_id', 'date', 'number']);
    });

    Schema::create('offdays', function(Blueprint $table) {
      $table->increments('id');
      $table->date('date');
      $table->unsignedInteger('group_id')->nullable();

      $table->foreign('group_id')->references('id')->on('groups');
      $table->unique(['date', 'group_id']);
    });

    Schema::create('registrations', function(Blueprint $table) {
      $table->increments('id');
      $table->unsignedInteger('lesson_id');
      $table->unsignedInteger('student_id');
      $table->boolean('obligatory');
      $table->boolean('present')->nullable();
      $table->text('documentation');

      $table->foreign('lesson_id')->references('id')->on('lessons');
      $table->foreign('student_id')->references('id')->on('students');
      $table->unique(['lesson_id', 'student_id']);
    });

    // Last create association tables
    Schema::create('course_group', function(Blueprint $table) {
      $table->unsignedInteger('course_id');
      $table->unsignedInteger('group_id');

      $table->primary(['course_id', 'group_id']);
      $table->foreign('course_id')->references('id')->on('courses');
      $table->foreign('group_id')->references('id')->on('groups');
    });

    Schema::create('group_student', function(Blueprint $table) {
      $table->unsignedInteger('group_id');
      $table->unsignedInteger('student_id');

      $table->primary(['group_id', 'student_id']);
      $table->foreign('group_id')->references('id')->on('groups');
      $table->foreign('student_id')->references('id')->on('students');
    });

    Schema::create('group_teacher', function(Blueprint $table) {
      $table->unsignedInteger('group_id');
      $table->unsignedInteger('teacher_id');

      $table->primary(['group_id', 'teacher_id']);
      $table->foreign('group_id')->references('id')->on('groups');
      $table->foreign('teacher_id')->references('id')->on('teachers');
    });

    Schema::create('subject_teacher', function(Blueprint $table) {
      $table->unsignedInteger('subject_id');
      $table->unsignedInteger('teacher_id');

      $table->primary(['subject_id', 'teacher_id']);
      $table->foreign('subject_id')->references('id')->on('subjects');
      $table->foreign('teacher_id')->references('id')->on('teachers');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    // drop association tables
    Schema::dropIfExists('course_group');
    Schema::dropIfExists('group_student');
    Schema::dropIfExists('group_teacher');
    Schema::dropIfExists('subject_teacher');

    // drop tables with foreign keys (order is relevant)
    Schema::dropIfExists('absences');
    Schema::dropIfExists('forms');
    Schema::dropIfExists('offdays');
    Schema::dropIfExists('registrations');
    Schema::dropIfExists('lessons');
    Schema::dropIfExists('courses');

    // drop tables without foreign keys
    Schema::dropIfExists('config');
    Schema::dropIfExists('groups');
    Schema::dropIfExists('students');
    Schema::dropIfExists('subjects');
    Schema::dropIfExists('teachers');
  }
}
