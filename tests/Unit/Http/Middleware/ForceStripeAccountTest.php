<?php

namespace Tests\Unit\Http\Middleware;

use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
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

    public function testUsersWithAStripeAccountCanContinue()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'test_stripe_account_1234',
        ]));
        $request = new Request;
        $next = new class
        {
            public $called = false;
            public function __invoke($request)
            {
                $this->called = true;
                return $request;
            }
        };
        $middleware = new ForceStripeAccount;
        $response = $middleware->handle($request, $next);
        $this->assertTrue($next->called);
        $this->assertSame($response, $request);
    }

    public function testMiddlewareIsAppliedToAllBackstageRoutes()
    {
        $routes = [
            'backstage.concerts.index',
            'backstage.concerts.new',
            'backstage.concerts.store',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store',
        ];
        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
