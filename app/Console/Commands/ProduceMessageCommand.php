<?php

namespace App\Console\Commands;

use App\Jobs\TestJob;
use Illuminate\Console\Command;
use App\Models\Message;

class ProduceMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:message {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = $this->argument('limit') ?? 100;
        $count = intval(Message::max('count'));

        $rows = [];

        for ($i=0; $i < $limit; $i++){
            if ($i % 100 === 0){
                $count++;
            }
            $rows[] = [
                'service' => null,
                'success' => null,
                'count' => $count,
                'read' => 0,
            ];
        }
        Message::insert($rows);
        return 0;
    }
}
