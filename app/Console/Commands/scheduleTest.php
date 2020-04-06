<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class scheduleTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testsch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Je fais un test de cronjob';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $random =   Arr::random([1,2,3,4,5,6,7,8,9,10]);
        $data = DB::table('schedule_test')->where('id',$random)->delete();
    }
}
