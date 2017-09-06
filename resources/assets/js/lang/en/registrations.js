/* global module */
module.exports = {
  attendance: {
    present: 'Present',
    excused: 'Excused',
    absent: 'Absent'
  },
  register: {
    heading: 'Register student for this lesson',
    registered: 'For this time the student is already registered at',
    submit: 'Register',
    change: 'Change registration',
    loadError: 'Error loading registrations for student.',
    saveError: 'Error registering the student for this lesson.'
  },
  student: {
    selectHeading: 'Register {student} for a lesson',
    submitHeading: 'Register {student} with lesson by {teacher} on {date}, Flex {number}',
    lessons: 'Available on {date}, Flex {number}:',
    none: 'No lessons available.',
    select: 'Select',
    back: 'Back',
    lessonsError: 'Error loading the available lessons.',
    warningsError: 'Error loading the lesson data.',
    saveError: 'Error saving the registration.'
  },
  warnings: {
    heading: 'Notice:',
    sameLesson: 'Student is already registered for this lesson.',
    maxstudents: 'There are already {students} students registered for this lesson (of at most {maxstudents}).',
    obligatory: 'The chosen lesson contains an obligatory course.',
    yearfrom: 'This course is intended for students of grade {yearfrom} and higher.',
    yearto: 'This course is intended for students of grade {yearto} and lower.',
    offday: 'The student has no lessons planned for the given time.',
    lesson: 'A registration already exists with {teacher}.',
    course: 'A registration for the course {course} with {teacher} already exists.',
    timetable: 'The student does not have lessons for this time.'
  },
  unregister: {
    submit: 'Unregister'
  },
  feedback: {
    heading: 'Add feedback for {student}',
    submit: 'Save feedback',
    label: 'Feedback',
    loadError: 'Error loading feedback for student.',
    saveError: 'Error saving the feedback.'
  },
  untis: {
    reload: 'Reload Untis data'
  }
};
