<?php

namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

use App\Models\Plan;
use App\Models\Application;
use App\Enums\ApplicationStatus;
class B2BTest extends TestCase
{
    use RefreshDatabase;
    public function test_b2b_job_success()
    {
        $user = User::factory()->create();
        $application = Application::factory()
            ->state(['status'=>ApplicationStatus::Order])
            ->create();
        $this->assertNotEmpty($application);
        $response = $this->actingAs($user)->getJson(uri:'/api/listNbnApplications');
        $this->expectsJobs(App\Jobs\B2bJob::class);
        $response->assertStatus(200);
    }

    //retrieve nbn plan type
    public function test_unsuccessful_nbn_applications()
    {
        $user = User::factory()->create();
        $application = Application::factory()
            ->state(['status'=>ApplicationStatus::OrderFailed])
            ->create();
        $this->assertNotEmpty($application);
        $response = $this->actingAs($user)->getJson(uri:'/api/listNbnApplications');
        $response->assertStatus(200);
    }

}
