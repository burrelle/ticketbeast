<?php
namespace Tests\Unit\Http\Middleware;

use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Middleware\ForceStripeAccount;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testUsersWithoutAStripeAccountAreForcedToConnectWithStripe()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => null,
        ]));
        $middleware = new ForceStripeAccount;
        $response = $middleware->handle(new Request, function ($request) {
            $this->fail("Next middleware was called when it should not have been.");
        });
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }
}
