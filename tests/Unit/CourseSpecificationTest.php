<?php

namespace Tests\Unit;

use App\Http\Requests\Course\CourseRequest;
use App\Http\Requests\Course\CreateNormalCourseRequest;
use App\Http\Requests\Course\CreateObligatoryCourseRequest;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use VladaHejda\AssertException;

/**
 * Test the CourseSpecification classes
 *
 * @package Tests\Unit
 */
class CourseSpecificationTest extends TestCase {

  use AssertException;

  /**
   * @param array $attributes
   * @param string $class
   * @return FormRequest|\Mockery\MockInterface
   */
  private function createRequest(array $attributes, $class) {
    /** @var FormRequest|\Mockery\MockInterface $request */
    $request = \Mockery::mock($class . '[authorize,response]');
    $request->setContainer($this->app);
    $request->replace($attributes);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $request->shouldReceive(['authorize' => true]);
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $request->shouldReceive('response')->andReturnUsing(function($errors) {
      return $errors;
    });

    return $request;
  }

  /**
   * @param FormRequest $request
   * @param array $expected
   * @return array|null
   */
  private function getErrors(FormRequest $request, array $expected = []) {
    $errors = [];
    try {
      $request->validate();
    } catch (ValidationException $e) {
      $errors = $e->getResponse();
    }

    foreach ($expected as $error) {
      $this->assertArrayHasKey($error, $errors);
    }
    return $errors;
  }

  private function checkCourseValues(Course $course, array $values) {
    foreach ($values as $key => $value) {
      $this->{is_object($value) ? 'assertEquals' : 'assertSame'}($value, $course->{$key});
    }
  }

  private function checkSpecValues(CourseRequest $request, array $values) {
    foreach ($values as $key => $value) {
      $this->{is_object($value) ? 'assertEquals' : 'assertSame'}($value, $request->{$key}());
    }
  }

  public function testCreateNormalDefault() {
    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest([], CreateNormalCourseRequest::class);

    $this->getErrors($request, ['name', 'room', 'maxstudents', 'firstDate']);

    $this->checkSpecValues($request, [
        'getFirstDate'   => null,
        'getLastDate'    => null,
        'getFirstLesson' => 1,
        'getLastLesson'  => null
    ]);

    $this->checkCourseValues($request->populateCourse(), [
        'id'          => null,
        'name'        => null,
        'description' => null,
        'subject'     => null,
        'maxstudents' => null,
        'room'        => null,
        'yearfrom'    => null,
        'yearto'      => null
    ]);
  }

  public function testCreateNormalInvalid() {
    $attributes = [
        'name'        => 1,
        'description' => 1,
        'room'        => 1,
        'maxstudents' => 0,
        'yearfrom'    => 0,
        'yearto'      => 0,
        'firstDate'   => 'foo',
        'lastDate'    => 'bar',
        'firstLesson' => 0,
        'lastLesson'  => 0
    ];

    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest($attributes, CreateNormalCourseRequest::class);

    $this->getErrors($request, array_keys($attributes));
  }

  public function testCreateNormal() {
    $course = [
        'id'          => 27,
        'name'        => "Foobar",
        'description' => "Lorem ipsum",
        'room'        => "107",
        'maxstudents' => 15,
        'yearfrom'    => 2,
        'yearto'      => 4,
        'subject'     => 3
    ];
    $dates = [
        'firstDate'   => Carbon::now()->addWeek()->toDateString(),
        'lastDate'    => Carbon::now()->addMonth()->toDateString(),
        'firstLesson' => 1,
        'lastLesson'  => 3
    ];

    /** @var CreateNormalCourseRequest $request */
    $request = $this->createRequest(array_merge($course, $dates), CreateNormalCourseRequest::class);

    $this->assertEmpty($this->getErrors($request));

    $this->checkSpecValues($request, [
        'getFirstDate'   => Carbon::now()->addWeek()->startOfDay(),
        'getLastDate'    => Carbon::now()->addMonth()->startOfDay(),
        'getFirstLesson' => 1,
        'getLastLesson'  => 3
    ]);

    $this->checkCourseValues($request->populateCourse(), array_merge($course, [
        'id'      => null,
        'subject' => null
    ]));
  }

  public function testCreateObligatoryDefault() {
    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest([], CreateObligatoryCourseRequest::class);

    $this->getErrors($request, ['name', 'room', 'groups', 'subject', 'firstDate']);

    $this->checkSpecValues($request, [
        'getFirstDate'   => null,
        'getLastDate'    => null,
        'getFirstLesson' => 1,
        'getLastLesson'  => null,
        'getGroups'      => null,
        'getSubject'     => null
    ]);

    $this->checkCourseValues($request->populateCourse(), [
        'id'          => null,
        'name'        => null,
        'description' => null,
        'subject'     => null,
        'maxstudents' => null,
        'room'        => null,
        'yearfrom'    => null,
        'yearto'      => null
    ]);
  }

  public function testCreateObligatoryInvalid() {
    $attributes = [
        'name'        => 1,
        'description' => 1,
        'room'        => 1,
        'groups'      => 2,
        'subject'     => 'a',
        'firstDate'   => 'foo',
        'lastDate'    => 'bar',
        'firstLesson' => 0,
        'lastLesson'  => 0
    ];

    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest($attributes, CreateObligatoryCourseRequest::class);

    $this->getErrors($request, array_keys($attributes));
  }

  public function testCreateObligatory() {
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
        'firstDate'   => Carbon::now()->addWeek()->toDateString(),
        'lastDate'    => Carbon::now()->addMonth()->toDateString(),
        'firstLesson' => 1,
        'lastLesson'  => 3,
        'subject'     => 3,
        'groups'      => [2]
    ];

    /** @var CreateObligatoryCourseRequest $request */
    $request = $this->createRequest(array_merge($course, $dates), CreateObligatoryCourseRequest::class);

    // TODO Insert test data such that no errors occur
    $this->assertCount(2, $this->getErrors($request));

    $this->checkSpecValues($request, [
        'getFirstDate'   => Carbon::now()->addWeek()->startOfDay(),
        'getLastDate'    => Carbon::now()->addMonth()->startOfDay(),
        'getFirstLesson' => 1,
        'getLastLesson'  => 3,
        'getSubject'     => 3
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
