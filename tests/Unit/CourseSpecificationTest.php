<?php

namespace Tests\Unit;

use App\Helpers\Date;
use App\Http\Requests\Course\CourseRequest;
use App\Models\Course;
use App\Validators\DateValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Base Test for the CourseSpecification classes
 *
 * @package Tests\Unit
 */
abstract class CourseSpecificationTest extends TestCase {

  protected function mockValidator(Date $firstDate = null, Date $lastDate = null) {
    $this->mock(['validator' => DateValidator::class]);

    $this->shouldReceive('validator', 'validateInYear')
        ->andReturnUsing(function($attribute, $value) use ($firstDate, $lastDate) {
          $this->assertTrue(($firstDate && $attribute == 'firstDate' && $firstDate == $value)
              || ($lastDate && $attribute == 'lastDate' && $lastDate == $value));
          return true;
        });

    $this->shouldReceive('validator', 'validateCreateAllowed')
        ->andReturnUsing(function($attribute, $value) use ($firstDate, $lastDate) {
          $this->assertTrue($firstDate && $attribute == 'firstDate' && $firstDate == $value);
          return true;
        });

    $this->shouldReceive('validator', 'validateSchoolDay')
        ->andReturnUsing(function($attribute, $value) use ($firstDate, $lastDate) {
          $this->assertTrue($firstDate && $attribute == 'firstDate' && $firstDate == $value);
          return true;
        });
  }

  /**
   * @param array $attributes
   * @param string $class
   * @return FormRequest|MockInterface
   */
  protected function createRequest(array $attributes, $class) {
    /** @var FormRequest|MockInterface $request */
    $request = Mockery::mock($class . '[authorize,response]');
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

  protected function checkRules(array $expected, $class) {
    $rules = $this->createRequest([], $class)->rules();

    foreach ($expected as $field => $validations) {
      $this->assertArrayHasKey($field, $rules);
      foreach ($validations as $rule) {
        $this->assertRegExp('/^(.*\|)?' . $rule . '(\|.*)?$/', $rules[$field]);
      }
    }
  }

  /**
   * @param FormRequest $request
   * @param array $expected
   * @return array|null
   */
  protected function getErrors(FormRequest $request, array $expected = []) {
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

  protected function checkCourseValues(Course $course, array $values) {
    foreach ($values as $key => $value) {
      $this->{is_object($value) ? 'assertEquals' : 'assertSame'}($value, $course->{strtolower($key)}, $key);
    }
  }

  protected function checkSpecValues(CourseRequest $request, array $values) {
    foreach ($values as $key => $value) {
      $this->{is_object($value) ? 'assertEquals' : 'assertSame'}($value, $request->{$key}(), $key);
    }
  }

}
