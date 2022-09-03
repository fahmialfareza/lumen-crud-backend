<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        Log::error($exception);

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpResponseException) {
          $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
          $status = Response::HTTP_METHOD_NOT_ALLOWED;
          $exception = new MethodNotAllowedHttpException([], 'Method Not Allowed', $exception);
        } elseif ($exception instanceof NotFoundHttpException) {
          $status = Response::HTTP_NOT_FOUND;
          $exception = new NotFoundHttpException('Not Found', $exception);
        } elseif ($exception instanceof AuthorizationException) {
          $status = Response::HTTP_FORBIDDEN;
          $exception = new AuthorizationException('Forbidden', $status);
        } elseif ($exception instanceof \Dotenv\Exception\ValidationException && $exception->getResponse()) {
          $status = Response::HTTP_BAD_REQUEST;
          $exception = new \Dotenv\Exception\ValidationException('Bad Request', $status, $exception);
        } elseif ($exception) {
          $status = $exception->getStatusCode();
          $exception = new HttpException($status, $exception->getMessage());
        }

        return response()->json([
          'status' => "Not OK",
          'data' => null,
          'message' => $exception->getMessage()
        ], $status);
    }
}
