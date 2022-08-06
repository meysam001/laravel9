<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use LeoCarmo\CircuitBreaker\CircuitBreaker;
use LeoCarmo\CircuitBreaker\Adapters\RedisAdapter;


class CheckProviderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull:message';

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
        // Connect to redis
        $redis = new \Redis();
        $redis->connect('redis', 6379);

        $adapter = new RedisAdapter($redis, 'my-product');

        $messages = Message::where(['read' => 0])->limit(100)->get();

        $services = ['Kavenegar'];
        $success = [0=>0];
        $failure = [0=>0,1=>0,2=>0];

        foreach($services as $index => $service) {
            foreach($messages as $message) {

                $successValue = $this->getRandomZeroOrOne();

                // Set redis adapter for CB
                $circuit = new CircuitBreaker($adapter, $service);

                dump("id: $message->id , service: $service , value: $successValue ,failureCount: ".$circuit->getFailuresCounter());

                // Configure settings for CB
                $circuit->setSettings([
                    'timeWindow' => 60, // Time for an open circuit (seconds)
                    'failureRateThreshold' => 10, // Fail rate for open the circuit
                    'intervalToHalfOpen' => 30,  // Half open time (seconds)
                ]);
                // Check circuit status for service
//                dump("service: $service , isAvailable: ", $circuit->isAvailable());
                if (! $circuit->isAvailable()) {
                    dump('Circuit is not available! '.$circuit->getService().' '.
                        $circuit->getFailuresCounter().' id:'.$message->id);
                    continue 2;
                }

                try {
                    $this->myService($message, $service, $successValue);
                    $circuit->success();
                    $success[$index] += 1;
//                echo 'success!' . PHP_EOL;
                } catch (\RuntimeException $e) {
                    // If an error occurred, it must be recorded as failure.
//                    echo 'service: '.$service.' '.$services[$service]."\n";
                    $circuit->failure();
                    $failure[$index] += 1;
//                echo 'fail!' . PHP_EOL;
                }
            }
        }

        $this->info("\n".json_encode(array_combine($services, $success)).' count:'.$message->count);
        foreach($services as $service) {
            $circuit = new CircuitBreaker($adapter, $service);
           dump('service: '.$service. 'failure: '.$circuit->getFailuresCounter());
        }
//        $this->info(json_encode(array_combine($services, $failure)).' count:'.$message->count);

//        Log::info(json_encode($services));
        return 0;
    }

    private function myService(Message &$message, string $service, int $successValue)
    {

        $message->read = 1;
        $message->success = $successValue;
        $message->service = $service;
        $message->save();
        if ( $message->success ===0 ) {
            throw new \RuntimeException('Something got wrong!');
        }
    }

    private function getRandomZeroOrOne(): int
    {
        $arr = [0, 0, 0, 1];
        return $arr[array_rand($arr)];
    }
}
