<?php

/** @noinspection SpellCheckingInspection */
return [
    'data'          => [
        'firstDate'     => 'Kursbeginn',
        'lastDate'      => 'Kursende',
        'frequency'     => 'Häufigkeit',
        'lesson'        => 'Einheit',
        'chooseDay'     => 'Zuerst Kursbeginn auswählen.',
        'name'          => 'Kursname',
        'room'          => 'Raum',
        'selectRoom'    => 'Raum auswählen',
        'description'   => 'Beschreibung',
        'year'          => [
            'title'  => 'Jahrgänge',
            'from'   => 'Jahrgang (von)',
            'to'     => 'Jahrgang (bis)',
            'one'    => ':year. Klasse',
            'range'  => ':from. bis :to. Klasse',
            'higher' => ':year. Klasse und höher',
            'lower'  => ':year. Klasse und niedriger'
        ],
        'category'      => 'Kategorie',
        'selectCategory' => 'Kategorie auswählen',
        'maxStudents'   => 'Max. Teilnehmer',
        'subject'       => 'Fach',
        'selectSubject' => 'Fach auswählen',
        'groups'        => 'Klassen/Gruppen',
        'selectGroups'  => 'Klassen auswählen'
    ],
    'index'         => [
        'heading' => 'Alle Kurse',
        'error'   => 'Fehler beim Laden der Kurse.',
        'none'    => 'Im ausgewählten Zeitraum gibt es keine Kurse.',
        'details' => 'Details zum Kurs'
    ],
    'obligatory'    => [
        'heading' => 'Alle Klassen-/Gruppenbindungen',
        'error'   => 'Fehler beim Laden der Klassen-/Gruppenbindungen.',
        'none'    => 'Zu den ausgewählten Filtern gibt es keine Klassen-/Gruppenbindungen.'
    ],
    'show'          => [
        'heading' => 'Kurs: :name',
        'lessons' => 'Einheiten'
    ],
    'registrations' => [
        'heading' => 'Angemeldete SchülerInnen',
        'none'    => 'Für diesen Kurs haben sich keine SchülerInnen angemeldet.'
    ],
    'unregister'    => [
        'heading' => 'SchülerIn abmelden',
        'error'   => 'Fehler beim Abmelden der Schülerin/des Schülers.',
        'confirm' => 'Soll :student wirklich vom gesamten Kurs abgemeldet werden?'
    ],
    'create'        => [
        'heading'      => 'Kurs erstellen',
        'impossible'   => 'Derzeit können keine Kurse erstellt werden.',
        'submit'       => 'Kurs erstellen',
        'withCourse'   => 'Für Einheiten innerhalb des gewählten Zeitraums existieren bereits Kurse:',
        'forNewCourse' => 'Der neue Kurs wird in folgenden Einheiten abgehalten werden:',
        'additional'   => 'neu angelegte Einheit!',
        'occupied'     => 'Der Raum ist bereits belegt:',
        'saveError'    => 'Fehler beim Speichern des Kurses.',
        'loadError'    => 'Informationen zu den gewählten Einheiten konnten nicht geladen werden.',
        'cancelled'    => [
            'warning' => 'Der Kurs wird in den folgenden Einheiten nicht gehalten, weil sie abgesagt wurden:',
            'error'   => 'Der Kurs kann nicht erstellt werden, weil alle ausgewählten Einheiten abgesagt wurden:'
        ],
        'obligatory'   => [
            'heading'        => 'Klassen-/Gruppenbindung erstellen',
            'withObligatory' => 'Für manche Klassen/Gruppen existieren im gewählten Zeitraum bereits Bindungen:',
            'timetable'      => 'Für manche Klassen/Gruppen haben nicht alle SchülerInnen im gewählten Zeitraum Flexzeit:',
            'offdays'        => 'Manche SchülerInnen sind an manchen Terminen mit einer Klasse/Gruppe abwesend:'
        ]
    ],
    'destroy'       => [
        'submit'  => 'Kurs löschen',
        'confirm' => 'Soll dieser Kurs wirklich gelöscht werden? Das kann nicht rückgängig gemacht werden!'
    ],
    'edit'          => [
        'link'           => 'Kurs bearbeiten',
        'heading'        => 'Kurs bearbeiten',
        'submit'         => 'Änderungen speichern',
        'withCourse'     => 'Für zusätzlich gewählte Einheiten existieren bereits Kurse:',
        'addedLessons'   => 'Der Kurs wird auf folgenden Einheiten verlängert:',
        'additional'     => 'neu angelegte Einheit!',
        'cancelled'      => 'Der Kurs wird in den folgenden Einheiten nicht gehalten, weil sie abgesagt wurden:',
        'removedLessons' => 'Der Kurs findet in den folgenden Einheiten nicht mehr statt:',
        'occupied'       => 'Der Raum ist bereits belegt:',
        'saveError'      => 'Fehler beim Speichern des Kurses.',
        'loadError'      => 'Informationen zu den Einheiten konnten nicht geladen werden.',
        'obligatory'     => [
            'heading'        => 'Klassen-/Gruppenbindung bearbeiten',
            'withObligatory' => 'Für manche Klassen/Gruppen existieren im gewählten Zeitraum bereits Bindungen:',
            'timetable'      => 'Für manche Klassen/Gruppen haben nicht alle SchülerInnen im gewählten Zeitraum Flexzeit:',
            'offdays'        => 'Manche SchülerInnen sind an manchen Terminen mit einer Klasse/Gruppe abwesend:'
        ]

    ]
];
