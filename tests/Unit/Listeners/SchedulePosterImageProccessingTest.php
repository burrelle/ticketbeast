<?php

namespace Tests\Unit\Listeners;

use Tests\TestCase;
use App\Events\ConcertAdded;
use App\CustomConcertFactory;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePosterImageProccessingTest extends TestCase
{
    use RefreshDatabase;

    public function testItQueuesAJobToProcessAPosterImageIfAPosterImageIsPresent()
    {
        Queue::fake();
        $concert = CustomConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);
        ConcertAdded::dispatch($concert);
        Queue::assertPushed(ProcessPosterImage::class, function ($job) use ($concert) {
            return $job->concert->is($concert);
        });
    }

    public function testAJobIsNotQueuedIfAPosterIsNotPresent()
    {
        Queue::fake();
        $concert = CustomConcertFactory::createUnpublished([
            'poster_image_path' => null,
        ]);
        ConcertAdded::dispatch($concert);
        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}
