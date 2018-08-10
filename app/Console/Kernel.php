<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //     ->hourly();

        $schedule->call(function(){
            $cronService = \App\CronService::where('status',1)
                ->where('minutes',1)->get();
            foreach($cronService as $cron){
                $log = new \App\CronServiceLog;
                $log->id_cron_service = $cron->id;
                
                try{
                    $class = $cron->command;
                    $obj   = new $class;
                    $obj->run();
                    $log->status = 1;
                    $log->message = 'Cron Berhasil Dieksekusi';
                } catch(Exception $e){
                    $log->status = 0;
                    $log->message = $e->getMessage();
                    $log->save();
                }
                
            }
        })->everyMinute();

        $schedule->call(function(){
            $cronService = \App\CronService::where('status',1)
                ->where('minutes',5)->get();
            foreach($cronService as $cron){
                $log = new \App\CronServiceLog;
                $log->id_cron_service = $cron->id;
                
                try{
                    $class = $cron->command;
                    $obj   = new $class;
                    $obj->run();
                    $log->status = 1;
                    $log->message = 'Cron Berhasil Dieksekusi';
                } catch(Exception $e){
                    $log->status = 0;
                    $log->message = $e->getMessage();
                    $log->save();
                }
                
            }
        })->everyFiveMinutes();

        $schedule->call(function(){
            $cronService = \App\CronService::where('status',1)
                ->where('minutes',5)->get();
            foreach($cronService as $cron){
                $log = new \App\CronServiceLog;
                $log->id_cron_service = $cron->id;
                
                try{
                    $class = $cron->command;
                    $obj   = new $class;
                    $obj->run();
                    $log->status = 1;
                    $log->message = 'Cron Berhasil Dieksekusi';
                } catch(Exception $e){
                    $log->status = 0;
                    $log->message = $e->getMessage();
                    $log->save();
                }
                
            }
        })->everyTenMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
