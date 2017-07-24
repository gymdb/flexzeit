<?php

return [
    'dashboard'     => [
        'heading'    => 'Today: :date',
        'none'       => 'You don\'t have any lessons today.',
        'cancelled'  => 'Cancelled',
        'attendance' => 'Check attendance'
    ],
    'index'         => [
        'heading' => 'All lessons',
        'error'   => 'Error loading the lessons.',
        'none'    => 'There are no lessons to show.',
        'details' => 'Details for lesson'
    ],
    'show'          => [
        'heading'   => 'Flex :number on <em>:date</em>', //'Lesson on <em>:date</em> at <em>:start &ndash; :end</em>',
        'cancelled' => 'This lesson has been cancelled.'
    ],
    'registrations' => [
        'heading' => 'Registered students',
        'none'    => 'No students are registered for this lesson.'
    ],
    'attendance'    => [
        'heading' => 'Attendance',
        'error'   => 'Error saving attendance.',
        'checked' => 'Attendance has already been checked',
        'button'  => 'Attendance checked'
    ],
    'register'      => [
        'button' => 'Register student',
        'change' => 'Change registration'
    ],
    'unregister'    => [
        'heading' => 'Unregister student',
        'error'   => 'Error unregistering student.',
        'confirm' => 'Really unregister :student?'
    ],
    'feedback'      => [
        'button' => 'Feedback'
    ],
    'cancel'       => [
        'submit'  => 'Cancel lesson',
        'confirm' => 'Do you really want to cancel this lesson?'
    ]
];
