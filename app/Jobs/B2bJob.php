<?php

namespace App\Jobs;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class B2bJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $application;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //store orderid and change status
        $url = env('NBN_B2B_ENDPOINT');
        Http::fake();
        $response = Http::post($url,$this->application);

        if ($response->getStatusCode() != 200 || $response->json('status')==ApplicationStatus::OrderFailed)
            $this->application->status=ApplicationStatus::OrderFailed;
        else
        {
            $this->application->status=ApplicationStatus::Complete;
            $this->application->order_id = $response->json('order_id');
        }
        $this->application->save();
    }
}
