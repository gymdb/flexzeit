<?php

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    /** @noinspection SpellCheckingInspection */
    $subjects = [
        'Bildn. Erziehung',
        'Biologie',
        'Chemie',
        'Deutsch',
        'Englisch',
        'Ev. Rel.',
        'Französisch',
        'Geografie',
        'Geschichte',
        'Informatik',
        'Italienisch',
        'Kommunikation',
        'Latein',
        'Mathematik',
        'Musik',
        'Physik',
        'Pol. Bildung',
        'Psych./Phil.',
        'Religion',
        'Spanisch',
        'Sport-K.',
        'Sport-M.',
        'Techn. Werken',
        'Text. Werken'
    ];

    foreach ($subjects as $subject) {
      Subject::create(['name' => $subject]);
    }
  }
}
