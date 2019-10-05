<?php

/** @noinspection SpellCheckingInspection */
return [
    'dashboard'     => [
        'heading'    => 'Heute: :date',
        'none'       => 'Heute gibt es keine Einheiten.',
        'cancelled'  => 'Abgesagt',
        'attendance' => 'Anwesenheit kontrollieren'
    ],
    'index'         => [
        'heading' => 'Alle Einheiten',
        'error'   => 'Fehler beim Laden der Einheiten.',
        'none'    => 'Im ausgewählten Zeitraum gibt es keine Einheiten.',
        'details' => 'Details zur Einheit'
    ],
    'show'          => [
        'heading'   => 'Flex :number am <em>:date</em>', //'Einheit am <em>:date</em> von <em>:start</em> bis <em>:end</em>',
        'cancelled' => 'Diese Einheit wurde abgesagt.'
    ],
    'registrations' => [
        'heading' => 'Angemeldete SchülerInnen',
        'none'    => 'Für diese Einheit haben sich keine SchülerInnen angemeldet.'
    ],
    'attendance'    => [
        'heading' => 'Anwesenheit',
        'error'   => 'Fehler beim Speichern der Anwesenheit.',
        'checked' => 'Die Anwesenheiten wurden bereits kontrolliert.',
        'button'  => 'Anwesenheiten kontrolliert'
    ],
    'register'      => [
        'button'     => 'SchülerIn anmelden',
        'buttonPast' => 'SchülerIn nachmelden',
        'change'     => 'Ummelden'
    ],
    'unregister'    => [
        'heading' => 'SchülerIn abmelden',
        'error'   => 'Fehler beim Abmelden der Schülerin/des Schülers.',
        'confirm' => 'Soll :student wirklich abgemeldet werden?'
    ],
    'feedback'      => [
        'button' => 'Rückmeldung an KV'
    ],
    'cancel'        => [
        'submit'  => 'Einheit absagen',
        'confirm' => 'Soll diese Einheit wirklich abgesagt werden?'
    ],
    'reinstate'     => [
        'submit'  => 'Absage zurücknehmen',
        'confirm' => 'Soll diese Einheit doch gehalten werden? SchülerInnen ohne Anmeldungen zu parallelen Einheiten werden wieder angemeldet.'
    ],
    'substitute'    => [
        'button' => 'Einheit supplieren'
    ]
];
