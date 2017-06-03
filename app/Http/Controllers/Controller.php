<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\RouteDependencyResolverTrait;
use Illuminate\Support\Facades\Auth;
use ReflectionFunctionAbstract;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Base class for all other controllers
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController {

  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
  use RouteDependencyResolverTrait;

  /**
   * Get the currently logged in teacher
   *
   * @return Teacher
   * @throws AccessDeniedException The currently authenticated user is not a teacher
   */
  protected function getTeacher() {
    $teacher = Auth::user();
    if (!($teacher instanceof Teacher)) {
      throw new AccessDeniedException('Authenticated user is not a teacher!');
    }
    return $teacher;
  }

  /**
   * Get the currently logged in student
   *
   * @return Student
   * @throws AccessDeniedException The currently authenticated user is not a student
   */
  protected function getStudent() {
    $student = Auth::user();
    if (!($student instanceof Student)) {
      throw new AccessDeniedException('Authenticated user is not a student!');
    }
    return $student;
  }

  /**
   * Execute an action on the controller.
   *
   * This is an extension of the default parameter resolving strategy of laravel, since it's crappy at best
   *
   * @param  string $method
   * @param  array $parameters
   * @return Response
   */
  public function callAction($method, $parameters) {
    $parameters = $this->resolveClassMethodDependencies($parameters, $this, $method);
    return parent::callAction($method, $parameters);
  }

  /**
   * Resolve the given method's type-hinted dependencies.
   *
   * @param  array $parameters
   * @param  \ReflectionFunctionAbstract $reflector
   * @return array
   */
  public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector) {
    $values = [];
    foreach ($reflector->getParameters() as $key => $parameter) {
      $class = $parameter->getClass();

      $value = null;
      if (isset($parameters[$parameter->name])) {
        $value = $parameters[$parameter->name];
      } else if (!$parameter->isOptional() && $class) {
        $value = $this->getParameterForClass($class->name, $parameters);
      } else if ($parameter->isOptional()) {
        $value = $parameter->getDefaultValue();
      }

      if (is_null($value) && !$parameter->allowsNull()) {
        throw new NotFoundHttpException('Parameter ' . $parameter->name . ' must not be empty!');
      }
      if ($class && !is_null($value) && !($value instanceof $class->name)) {
        throw new NotFoundHttpException('Parameter ' . $parameter->name . ' is invalid!');
      }

      $values[] = $value;
    }

    return $values;
  }

  private function getParameterForClass($class, array $parameters) {
    foreach ($parameters as $key => $value) {
      if (is_int($key) && $value instanceof $class) {
        return $value;
      }
    }
    return null;
  }

}
