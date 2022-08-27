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
//        $adapter = new RedisAdapter($redis, 'my-product');

        $circuit = \Ackintosh\Ganesha\Builder::withRateStrategy()
            ->adapter(new \Ackintosh\Ganesha\Storage\Adapter\Redis($redis))
            ->failureRateThreshold(50)
            ->intervalToHalfOpen(10)
            ->minimumRequests(20)
            ->timeWindow(30)
            ->build();

        $messages = Message::where(['read' => 0])->limit(100)->get();

        $services = ['Kavenegar', 'Ghasedak', 'SmsIr'];
        $success = [0=>0,1=>0,2=>0];
        $failure = [0=>0,1=>0,2=>0];

        foreach($messages as $message) {
            foreach($services as $index => $service) {

                $successValue = $this->getRandomZeroOrOne();

                // Set redis adapter for CB
//                $circuit = new CircuitBreaker($adapter, $service);

//                echo("id: $message->id , service: $service , value: $successValue \n");


                // Check circuit status for service
//                echo("service: $service , isAvailable: ", $circuit->isAvailable());
                if (! $circuit->isAvailable($service)) {
                    echo('Circuit is not available! '.$service.' id:'.$message->id)."\n";
                    continue;
                }

                try {
                    $this->myService($message, $service, $successValue);
                    $circuit->success($service);
                    $success[$index] += 1;
                    echo "success, id: $message->id, service: $service \n";
                } catch (\RuntimeException $e) {
                    // If an error occurred, it must be recorded as failure.
//                    echo 'service: '.$service.' '.$services[$service]."\n";
                    $circuit->failure($service);
                    $failure[$index] += 1;
                    echo "failed, id: $message->id, service: $service \n";
                }
                break;
            }

        }

        $this->info("\n".json_encode(array_combine($services, $success)).' count:'.$messages->count());
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

        if ( $successValue ===0 ) {
            throw new \RuntimeException('Something got wrong!');
        }
    }

    private function getRandomZeroOrOne(): int
    {
        $arr = [0, 0, 0, 1];
        return $arr[array_rand($arr)];
    }
}
