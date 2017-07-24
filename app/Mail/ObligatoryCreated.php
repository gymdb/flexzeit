<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class ObligatoryCreated extends Mailable implements ShouldQueue {

  use Queueable, SerializesModels;

  public $teacher;
  public $course;
  public $lessons;

  /**
   * Create a new message instance.
   *
   * @param Teacher $teacher
   * @param Course $course
   * @param Collection $lessons
   */
  public function __construct(Teacher $teacher, Course $course, Collection $lessons) {
    $this->teacher = $teacher;
    $this->course = $course;
    $this->lessons = $lessons;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this->subject(Lang::get('mail.obligatory.created.subject'))
        ->markdown('emails.obligatory.created');
  }
}
