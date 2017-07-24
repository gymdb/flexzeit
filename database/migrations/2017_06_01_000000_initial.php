<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Initial extends Migration {

  /**
   * Create the initial tables
   *
   * @return void
   */
  public function up() {
    // First create all tables that don't have foreign key relations
    Schema::create('config', function(Blueprint $table) {
      $table->string('key', 32);
      $table->json('value');

      $table->primary('key');
    });

    Schema::create('groups', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name', 32)->unique();
    });

    Schema::create('students', function(Blueprint $table) {
      $table->increments('id');
      $table->string('firstname', 32);
      $table->string('lastname', 32);
      $table->string('username', 32)->unique();
      $table->string('password');
      $table->string('image')->nullable();
      $table->unsignedBigInteger('untis_id')->unique();
    });

    Schema::create('subjects', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name', 32)->unique();
    });

    Schema::create('teachers', function(Blueprint $table) {
      $table->increments('id');
      $table->string('firstname', 32);
      $table->string('lastname', 32);
      $table->string('username', 32)->unique();
      $table->string('password');
      $table->boolean('admin')->default(false);
      $table->string('info', 32)->nullable();
      $table->string('image')->nullable();
    });

    // Now create tables with foreign key relations to the previously created ones
    Schema::create('absences', function(Blueprint $table) {
      $table->unsignedInteger('student_id');
      $table->date('date');
      $table->unsignedTinyInteger('number');

      $table->primary(['student_id', 'date', 'number']);
      $table->foreign('student_id')->references('id')->on('students');
      $table->index(['date', 'number']);
      $table->index(['number']);
    });

    Schema::create('courses', function(Blueprint $table) {
      $table->increments('id');
      $table->string('name', 64);
      $table->text('description');
      $table->string('room', 32);
      $table->unsignedInteger('subject_id')->nullable();
      $table->unsignedSmallInteger('maxstudents')->nullable();
      $table->unsignedTinyInteger('yearfrom')->nullable();
      $table->unsignedTinyInteger('yearto')->nullable();

      $table->foreign('subject_id')->references('id')->on('subjects');
    });

    Schema::create('forms', function(Blueprint $table) {
      $table->unsignedInteger('group_id');
      $table->unsignedTinyInteger('year');
      $table->unsignedInteger('kv_id');

      $table->primary('group_id');
      $table->foreign('group_id')->references('id')->on('groups');
      $table->foreign('kv_id')->references('id')->on('teachers');
      $table->index('kv_id');
    });

    Schema::create('lessons', function(Blueprint $table) {
      $table->increments('id');
      $table->date('date');
      $table->unsignedTinyInteger('number');
      $table->string('room', 32);
      $table->boolean('cancelled')->default(false);
      $table->unsignedInteger('teacher_id');
      $table->unsignedInteger('course_id')->nullable();

      $table->foreign('teacher_id')->references('id')->on('teachers');
      $table->foreign('course_id')->references('id')->on('courses');
      $table->unique(['teacher_id', 'date', 'number']);
      $table->index(['teacher_id', 'date', 'number', 'cancelled']);
      $table->index(['date', 'number', 'cancelled']);
      $table->index(['number', 'cancelled']);
      $table->index(['cancelled']);
    });

    Schema::create('offdays', function(Blueprint $table) {
      $table->increments('id');
      $table->date('date');
      $table->unsignedTinyInteger('number')->nullable();
      $table->unsignedInteger('group_id')->nullable();

      $table->foreign('group_id')->references('id')->on('groups');
      $table->unique(['group_id', 'date', 'number']);
      $table->index(['date', 'number']);
      $table->index(['number']);
    });

    Schema::create('registrations', function(Blueprint $table) {
      $table->increments('id');
      $table->unsignedInteger('lesson_id');
      $table->unsignedInteger('student_id');
      $table->boolean('obligatory');
      $table->boolean('attendance')->nullable();
      $table->text('documentation')->nullable();
      $table->text('feedback')->nullable();

      $table->foreign('lesson_id')->references('id')->on('lessons');
      $table->foreign('student_id')->references('id')->on('students');
      $table->unique(['lesson_id', 'student_id']);
      $table->index('student_id');
    });

    Schema::create('bugreports', function(Blueprint $table) {
      $table->increments('id');
      $table->unsignedInteger('teacher_id')->nullable();
      $table->unsignedInteger('student_id')->nullable();
      $table->text('text');
      $table->timestamp('date');

      $table->foreign('teacher_id')->references('id')->on('teachers');
      $table->foreign('student_id')->references('id')->on('students');
      $table->index('date');
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

    // Table for laravel job queue
    Schema::create('jobs', function(Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('queue');
      $table->longText('payload');
      $table->tinyInteger('attempts')->unsigned();
      $table->unsignedInteger('reserved_at')->nullable();
      $table->unsignedInteger('available_at');
      $table->unsignedInteger('created_at');

      $table->index(['queue', 'reserved_at']);
    });
  }

  /**
   * Drop the tables
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
    Schema::dropIfExists('bugreports');

    // drop tables without foreign keys
    Schema::dropIfExists('config');
    Schema::dropIfExists('groups');
    Schema::dropIfExists('students');
    Schema::dropIfExists('subjects');
    Schema::dropIfExists('teachers');

    // Drop queue table
    Schema::dropIfExists('jobs');
  }
}
