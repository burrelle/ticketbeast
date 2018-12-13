<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\AttendeeMessage;
use App\CustomOrderFactory;
use App\CustomConcertFactory;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SendAttendeeMessageTest extends TestCase
{
    use DatabaseMigrations;

    public function testItSendsToAllConcertAttendees()
    {
        Mail::fake();
        $concert = CustomConcertFactory::createPublished();
        $otherConcert = CustomConcertFactory::createPublished();

        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject' => 'My subject',
            'message' => 'My message'
        ]);
        $orderA = CustomOrderFactory::createForConcert($concert, ['email' => 'alex@example.com']);
        $orderB = CustomOrderFactory::createForConcert($concert, ['email' => 'sam@example.com']);
        $orderC = CustomOrderFactory::createForConcert($concert, ['email' => 'taylor@example.com']);
        $otherOrder = CustomOrderFactory::createForConcert($otherConcert, ['email' => 'jane@example.com']);
        SendAttendeeMessage::dispatch($message);
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('alex@example.com')
            && $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('sam@example.com')
            && $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('taylor@example.com')
            && $mail->attendeeMessage->is($message);
        });
        Mail::assertNotSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('jane@example.com');
        });
    }
}
