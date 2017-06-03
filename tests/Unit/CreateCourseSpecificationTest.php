<?php

namespace Tests\Unit;

use App\Helpers\Date;
use App\Http\Requests\Course\CreateNormalCourseRequest;
use App\Http\Requests\Course\CreateObligatoryCourseRequest;
use Tests\Traits\MockConfigTrait;
use Tests\Traits\MockDbTrait;
use Tests\Traits\MockOffdaysTrait;

/**
 * Test the CreateCourseSpecification classes
 *
 * @package Tests\Unit
 */
class CreateCourseSpecificationTest extends CourseSpecificationTest {

  use MockConfigTrait;
  use MockDbTrait;
  use MockOffdaysTrait;

  public function testNormalRules() {
    $this->checkRules([
        'name'         => ['required', 'string', 'max:50'],
        'description'  => ['nullable', 'string'],
        'room'         => ['required', 'string', 'max:50'],
        'maxStudents'  => ['nullable', 'integer', 'min:1'],
        'firstDate'    => ['required', 'date', 'after:today', 'in_year', 'create_allowed', 'school_day'],
        'lastDate'     => ['nullable', 'date', 'after_or_equal:firstDate', 'in_year'],
        'lessonNumber' => ['required', 'integer', 'min:1']
    ], CreateNormalCourseRequest::class);
  }

  public function testObligatoryRules() {
    $this->checkRules([
        'name'         => ['required', 'string', 'max:50'],
        'description'  => ['nullable', 'string'],
        'room'         => ['required', 'string', 'max:50'],
        'firstDate'    => ['required', 'date', 'after:today', 'in_year', 'create_allowed', 'school_day'],
        'lastDate'     => ['nullable', 'date', 'after_or_equal:firstDate', 'in_year'],
        'lessonNumber' => ['required', 'integer', 'min:1'],
        'subject'      => ['required', 'integer', 'exists:subjects,id'],
        'groups'       => ['required', 'array'],
        'groups.*'     => ['required', 'integer', 'exists:groups,id']
    ], CreateObligatoryCourseRequest::class);
  }

  public function testInvalidEndDate() {
    $this->mockOffdaysInRange([]);
    $date = Date::today()->addMonth();
    $request = $this->createRequest([
        'firstDate' => $date->toDateString(),
        'lastDate'  => $date->copy()->addDay(-1)->toDateString()
    ], CreateNormalCourseRequest::class);
    $this->getErrors($request, ['lastDate']);
  }

  public function testDateNotInYear() {
    $this->mockOffdaysInRange([]);
    $this->mockConfig(['year.start' => Date::createFromDate(2017, 4, 1), 'year.end' => Date::createFromDate(2017, 5, 31)]);
    $dates = [
        [Date::createFromDate(2017, 3, 31), null],
        [Date::createFromDate(2017, 6, 1), null],
        [Date::createFromDate(2017, 5, 20), Date::createFromDate(2017, 6, 1)]
    ];

    foreach ($dates as $date) {
      $request = $this->createRequest([
          'firstDate' => $date[0]->toDateString(),
          'lastDate'  => $date[1] ? $date[1]->toDateString() : null
      ], CreateObligatoryCourseRequest::class);
      $this->getErrors($request, [$date[1] ? 'lastDate' : 'firstDate']);
    }
  }

  public function testStartNotAllowed() {
    $this->mockOffdaysInRange([]);
    $this->mockConfig(['year.start' => Date::createFromDate(2017, 2, 1), 'year.end' => Date::today()->addMonths(3)]);

    // Try creating courses if creation is always allowed up to $i days before the course
    for ($i = -2; $i < 10; $i++) {
      $this->mockConfig(['course.create.week' => 0, 'course.create.day' => max(1, $i + 1)]);
      $date = Date::today()->addDay($i);
      $request = $this->createRequest(['firstDate' => $date->toDateString()], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['firstDate']);
    }

    // Try creating courses if creation is always allowed until today's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Date::today()->dayOfWeek]);
      $date = Date::today()->addWeek($i - 1);
      $request = $this->createRequest(['firstDate' => $date->toDateString()], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['firstDate']);
    }

    // Try creating courses if creation is always allowed until yesterday's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Date::today()->addDays(-1)->dayOfWeek]);
      $date = Date::today()->addWeek($i);
      $request = $this->createRequest(['firstDate' => $date->toDateString()], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['firstDate']);
    }
  }

  public function testStartOffday() {
    $this->mockConfig(['year.start' => Date::createFromDate(2017, 2, 1), 'year.end' => Date::today()->addMonths(3)]);

    // Mock a few offdays for the whole school
    $dates = [Date::today()->next(Date::MONDAY), Date::today()->next(Date::WEDNESDAY)];
    $this->mockOffdaysInRange($dates);

    foreach ($dates as $date) {
      $request = $this->createRequest(['firstDate' => $date->toDateString()], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['firstDate']);
    }
  }

  public function testInvalidStartDate() {
    // Mock config with 2 lessons on monday, 1 on tuesday and 3 on wednesday
    $this->mockConfig(['lesson.count.1' => 2, 'lesson.count.2' => 1, 'lesson.count.3' => 3, 'lesson.count.4' => 0]);

    // Create courses on days without flextime
    foreach ([0, 4, 5, 6] as $i) {
      $request = $this->createRequest([
          'firstDate'    => Date::today()->setToDayOfWeek($i)->toDateString(),
          'lessonNumber' => 1
      ], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['firstDate']);
    }
  }

  public function testInvalidLessons() {
    // Mock config with 2 lessons on monday, 1 on tuesday and 3 on wednesday
    $this->mockConfig(['lesson.count.1' => 2, 'lesson.count.2' => 1, 'lesson.count.3' => 3, 'lesson.count.4' => 0]);

    // dayOfWeek, lessonNumber
    $values = [
        [1, 0], [1, 3],
        [2, 2], [2, 3],
        [3, 0], [3, 4]
    ];

    foreach ($values as $value) {
      $request = $this->createRequest([
          'firstDate'    => Date::today()->setToDayOfWeek($value[0])->toDateString(),
          'lessonNumber' => $value[1]
      ], CreateNormalCourseRequest::class);
      $this->getErrors($request, ['lessonNumber']);
    }
  }

  public function testCreateNormalDefault() {
    $this->mockValidator();

    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest([], CreateNormalCourseRequest::class);

    $this->getErrors($request, ['name', 'firstDate']);

    $this->checkSpecValues($request, [
        'getFirstDate'    => null,
        'getLastDate'     => null,
        'getLessonNumber' => null
    ]);

    $this->checkCourseValues($request->populateCourse(), [
        'id'          => null,
        'name'        => null,
        'description' => '',
        'subject'     => null,
        'maxStudents' => null,
        'room'        => null,
        'yearFrom'    => null,
        'yearTo'      => null
    ]);
  }

  public function testCreateNormalInvalid() {
    $this->mockValidator();

    $attributes = [
        'name'         => 1,
        'description'  => 1,
        'room'         => 1,
        'maxStudents'  => 0,
        'yearFrom'     => 0,
        'yearTo'       => 0,
        'firstDate'    => 'foo',
        'lastDate'     => 'bar',
        'lessonNumber' => 0
    ];

    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest($attributes, CreateNormalCourseRequest::class);

    $this->getErrors($request, array_keys($attributes));
  }

  public function testCreateNormal() {
    $this->mockConfig(['lesson.count.' . Date::today()->dayOfWeek => 3, 'year.min' => 1, 'year.max' => 8]);
    $this->mockValidator(Date::today()->addWeek(), Date::today()->addMonth());

    $course = [
        'id'          => 27,
        'name'        => "Foobar",
        'description' => "Lorem ipsum",
        'room'        => "107",
        'maxStudents' => 15,
        'yearFrom'    => 2,
        'yearTo'      => 4,
        'subject'     => 3
    ];
    $dates = [
        'firstDate'    => Date::today()->addWeek()->toDateString(),
        'lastDate'     => Date::today()->addMonth()->toDateString(),
        'lessonNumber' => 2
    ];

    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest(array_merge($course, $dates), CreateNormalCourseRequest::class);

    $this->assertEmpty($this->getErrors($request));

    $this->checkSpecValues($request, [
        'getFirstDate'    => Date::today()->addWeek(),
        'getLastDate'     => Date::today()->addMonth(),
        'getLessonNumber' => 2
    ]);

    $this->checkCourseValues($request->populateCourse(), array_merge($course, [
        'id'      => null,
        'subject' => null
    ]));
  }

  public function testCreateObligatoryDefault() {
    $this->mockValidator();

    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest([], CreateObligatoryCourseRequest::class);

    $this->getErrors($request, ['name', 'groups', 'subject', 'firstDate']);

    $this->checkSpecValues($request, [
        'getFirstDate'    => null,
        'getLastDate'     => null,
        'getLessonNumber' => null,
        'getGroups'       => null,
        'getSubject'      => null
    ]);

    $this->checkCourseValues($request->populateCourse(), [
        'id'          => null,
        'name'        => null,
        'description' => '',
        'subject'     => null,
        'maxstudents' => null,
        'room'        => null,
        'yearfrom'    => null,
        'yearto'      => null
    ]);
  }

  public function testCreateObligatoryInvalid() {
    $this->mockValidator();

    $attributes = [
        'name'         => 1,
        'description'  => 1,
        'room'         => 1,
        'groups'       => 2,
        'subject'      => 'a',
        'firstDate'    => 'foo',
        'lastDate'     => 'bar',
        'lessonNumber' => 0
    ];

    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest($attributes, CreateObligatoryCourseRequest::class);

    $this->getErrors($request, array_keys($attributes));
  }

  public function testCreateObligatory() {
    $this->mockConfig(['lesson.count.' . Date::today()->dayOfWeek => 3]);
    $this->mockValidator(Date::today()->addWeek(), Date::today()->addMonth());

    $course = [
        'id'          => 27,
        'name'        => "Foobar",
        'description' => "Lorem ipsum",
        'room'        => "107",
        'maxstudents' => 15,
        'yearfrom'    => 2,
        'yearto'      => 4,
    ];
    $dates = [
        'firstDate'    => Date::today()->addWeek()->toDateString(),
        'lastDate'     => Date::today()->addMonth()->toDateString(),
        'lessonNumber' => 3,
        'subject'      => 3,
        'groups'       => [2]
    ];

    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest(array_merge($course, $dates), CreateObligatoryCourseRequest::class);
    $request->validate();

    $this->checkSpecValues($request, [
        'getFirstDate'    => Date::today()->addWeek(),
        'getLastDate'     => Date::today()->addMonth(),
        'getLessonNumber' => 3,
        'getSubject'      => 3
    ]);

    $this->assertCount(1, $request->getGroups());

    $this->checkCourseValues($request->populateCourse(), array_merge($course, [
        'id'          => null,
        'maxstudents' => null,
        'yearfrom'    => null,
        'yearto'      => null,
        'subject'     => null
    ]));
  }

}
