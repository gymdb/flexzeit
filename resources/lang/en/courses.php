<?php

return [
    'data'          => [
        'firstDate'     => 'First date',
        'lastDate'      => 'Last date',
        'lesson'        => 'Lesson',
        'chooseDay'     => 'Choose a date first.',
        'name'          => 'Course name',
        'room'          => 'Room',
        'selectRoom'    => 'Select room',
        'description'   => 'Description',
        'year'          => [
            'title'  => 'Grades',
            'from'   => 'Grade (from)',
            'to'     => 'Grade (to)',
            'one'    => 'Grade :year',
            'range'  => 'Grades :from to :to',
            'higher' => 'Grade :year and higher',
            'lower'  => 'Grade :year and lower'
        ],
        'maxStudents'   => 'Max. participants',
        'subject'       => 'Subject',
        'selectSubject' => 'Select subject',
        'groups'        => 'Groups',
        'selectGroups'  => 'Select groups'
    ],
    'index'         => [
        'heading' => 'All courses',
        'error'   => 'Error loading the courses.',
        'none'    => 'There are no courses to show.',
        'details' => 'Details for course'
    ],
    'obligatory'    => [
        'heading' => 'All obligatory courses',
        'error'   => 'Error loading the obligatory courses.',
        'none'    => 'There are no obligatory courses to show.'
    ],
    'show'          => [
        'heading' => 'Course: :name',
        'lessons' => 'Lessons'
    ],
    'registrations' => [
        'heading' => 'Registered students',
        'none'    => 'No students are registered for this course.'
    ],
    'create'        => [
        'heading'      => 'Create course',
        'impossible'   => 'Creating courses is currently not possible.',
        'submit'       => 'Create course',
        'withCourse'   => 'Courses already exist for some lessons within the chosen timeframe:',
        'forNewCourse' => 'The new course will be held at the following lessons:',
        'occupied'     => 'The room is already occupied:',
        'saveError'    => 'Error saving the course.',
        'loadError'    => 'Error loading the lesson information.',
        'obligatory'   => [
            'heading'        => 'Create obligatory course',
            'withObligatory' => 'Some groups already have obligatory courses within the chosen timeframe:',
            'timetable'      => 'Some students of the chosen groups do not have lessons in the chosen timeframe:',
            'offdays'        => 'Some students are absent with a group at some of the selected dates:'
        ]
    ],
    'destroy'       => [
        'submit'  => 'Delete course',
        'confirm' => 'Do you really want to delete this course? This cannot be undone!'
    ],
    'edit'          => [
        'link'           => 'Edit course',
        'heading'        => 'Edit course',
        'submit'         => 'Save changes',
        'withCourse'     => 'Courses already exist for some of the additional lessons:',
        'addedLessons'   => 'The course will additionally be held at the following lessons:',
        'removedLessons' => 'The course will no longer be held at the following lessons:',
        'occupied'       => 'The room is already occupied:',
        'saveError'      => 'Error saving the course.',
        'loadError'      => 'Error loading the lesson information.',
        'obligatory'     => [
            'heading'        => 'Edit obligatory course',
            'withObligatory' => 'Some groups already have obligatory courses within the chosen timeframe:',
            'timetable'      => 'Some students of the chosen groups do not have lessons in the chosen timeframe:',
            'offdays'        => 'Some students are absent with a group at some of the selected dates:'
        ]
    ]
];
