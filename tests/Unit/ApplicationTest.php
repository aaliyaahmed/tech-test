<?php

namespace Tests\Unit;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use PHPUnit\Framework\TestCase;
class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all_applications()
    {
        //$this->withoutExceptionHandling();
        $user = User::factory()->create();
        $application = Application::factory()
            ->state(['status'=>ApplicationStatus::Prelim])
            ->create();
        $application = Application::factory()
            ->state(['status'=>ApplicationStatus::Complete])
            ->create();
        $this->assertNotEmpty($application);
        $response = $this->actingAs($user)->getJson('/api/listAllApplications/planType');
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [   'status',
                'message',
                'data'=>[
                    'Application id',
                    'Customer Full Name',
                    'Address',
                    'Plan type',
                    'Plan name',
                    'State',
                    'Plan monthly cost',
                    'Order Id'
                ]
            ]
        );
    }

    public function test_list_applications_without_orderId()
    {
        $user = User::factory()->create();
        $application = Application::factory()
            ->state(['status'=>ApplicationStatus::Prelim])
            ->create();
        $this->assertNotEmpty($application);
        $planType = Plan::find($application->plan_id)->type;
        $response = $this->actingAs($user)->getJson('/api/listAllApplications/planType/'.$planType);
        $response->assertStatus(200);
        //$data = $response->getOriginalContent();
        $response->assertJsonMissing(['Order Id'],true);
    }

    public function test_list_applications_with_orderId()
    {
        $user = User::factory()->create();
        $application = Application::factory()
                    ->state(['status'=>ApplicationStatus::Complete])
                    ->state(['order_id'=>1])
                    ->create();
        $this->assertNotEmpty($application);
        $planType = Plan::find($application->plan_id)->type;
        $response = $this->actingAs($user)->getJson('/api/listAllApplications/planType/'.$planType);
        $response->assertStatus(200);
        $orderId = $response->getOriginalContent()['data']["Order Id"];
        $this->assertEquals($orderId,1);
    }
}
