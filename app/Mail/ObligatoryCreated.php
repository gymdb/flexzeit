<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class ObligatoryCreated extends Mailable implements ShouldQueue {

  use Queueable, SerializesModels;

  public $teacher;
  public $course;

  /**
   * Create a new message instance.
   *
   * @param Teacher $teacher
   * @param Course $course
   */
  public function __construct(Teacher $teacher, Course $course) {
    $this->teacher = $teacher;
    $this->course = $course;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this
        ->subject(config('app.name') . ' - ' . Lang::get('mail.obligatory.created.subject'))
        ->markdown('emails.obligatory.created')
        ->with(['lessons' => $this->course->lessons()->orderBy('date')->get()]);
  }
}
