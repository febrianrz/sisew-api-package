<?php

namespace App\Exceptions;

use Exception;
use LaravelEnso\Helpers\app\Exceptions\EnsoException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\ExceptionOccured;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        EnsoException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        if ($this->shouldReport($exception)) {
            $this->sendEmail($exception); // sends an email
        }
    

        parent::report($exception);
    }

    /**
     * Sends an email to the developer about the exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function sendEmail(Exception $exception)
    {
        try {
            $e = FlattenException::create($exception);
    
            $handler = new SymfonyExceptionHandler();
    
            $html = $handler->getHtml($e);
            try{
                Mail::to('febrianrz@gmail.com')->send(new ExceptionOccured($html));
            } catch(\Exception $e){
                \DB::table('email_logs')->insert([
                    'email' => 'febrianrz@gmail.com',
                    'title' => 'Failed send error api',
                    'message'=> $e->getMessage()
                ]);
            }
            
        } catch (Exception $ex) {
            dd($ex);
        }
    }

    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
