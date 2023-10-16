<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof UnauthorizedHttpException) {
            return $this->unauthorized($request, $e);
        }
 
        return parent::render($request, $e);
    }

    /**
     * Convert an authorization exception into a response.
     */
    protected function unauthorized($request, UnauthorizedHttpException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
                    ? response()->json(['status' => 401, 'message' => $exception->getMessage()], 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * {@inheritDoc}
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
                    ? response()->json(['status' => 401, 'message' => $exception->getMessage()], 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * {@inheritDoc}
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => $exception->status,
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * {@inheritDoc}
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug') ? [
            'status' => $e->getCode(),
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'status' => $e->getCode(),
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
}
