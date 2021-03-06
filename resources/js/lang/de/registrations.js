/* global module */
//noinspection SpellCheckingInspection
module.exports = {
  attendance: {
    present: 'Anwesend',
    excused: 'Entschuldigt',
    absent: 'Abwesend'
  },
  register: {
    heading: 'SchülerIn für diese Einheit anmelden',
    headingCourse: 'SchülerIn für diesen Kurs anmelden',
    registered: 'SchülerIn ist für diese Zeit bereits woanders angemeldet',
    submit: 'Anmelden',
    change: 'Ummelden',
    confirm: 'SchülerIn für die Einheit anmelden?',
    confirmCourse: 'SchülerIn für den Kurs anmelden?',
    loadError: 'Fehler beim Laden der Anmeldungen für SchülerIn.',
    saveError: 'Fehler beim Anmelden der Schülerin/des Schülers.'
  },
  student: {
    selectHeading: '{student} für eine Einheit anmelden',
    submitHeading: '{student} bei {teacher} am {date}, Flex {number} anmelden',
    lessons: 'Verfügbar am {date}, Flex {number}:',
    none: 'Keine Einheiten verfügbar.',
    select: 'Auswählen',
    back: 'Zurück',
    lessonsError: 'Fehler beim Laden der verfügbaren Einheiten.',
    warningsError: 'Fehler beim Laden der Daten für die Einheit.',
    saveError: 'Fehler beim Speichern der Anmeldung.'
  },
  warnings: {
    heading: 'Hinweise:',
    sameLesson: 'SchülerIn ist für die gewählte Einheit bereits angemeldet.',
    maxstudents: 'Für die gewählte Einheit gibt es bereits {students} Anmeldungen (maximal {maxstudents}).',
    obligatory: 'In der gewählten Einheit findet eine Klassen-/Gruppenbindung statt.',
    yearfrom: 'Der Kurs ist erst ab der {yearfrom}. Klasse vorgesehen.',
    yearto: 'Der Kurs ist nur bis zur {yearto}. Klasse vorgesehen.',
    offday: 'SchülerIn ist zur gewählten Zeit mit einer Klasse/Gruppe abwesend.',
    offdays: 'SchülerIn ist an manchen Terminen mit einer Klasse/Gruppe abwesend:',
    lesson: 'Zur gewählten Zeit gibt es bereits eine Anmeldung bei {teacher}.',
    course: 'Zur gewählten Zeit gibt es bereits eine Anmeldung zum Kurs {course} bei {teacher}.',
    lessons: 'Es gibt zu Kurszeiten bereits Anmeldungen:',
    timetable: 'SchülerIn hat für die gewählte Einheit keine Flexzeit.'
  },
  unregister: {
    submit: 'Abmelden'
  },
  feedback: {
    heading: 'Rückmeldung zu {student} hinzufügen',
    submit: 'Rückmeldung speichern',
    label: 'Rückmeldung',
    loadError: 'Fehler beim Laden der Rückmeldung.',
    saveError: 'Fehler beim Speichern der Rückmeldung.'
  },
  untis: {
    reload: 'Daten von Untis neu laden'
  }
};
