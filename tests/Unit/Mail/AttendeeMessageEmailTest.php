<?php
namespace Tests\Unit\Mail;

use App\Order;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;

class AttendeeMessageEmailTest extends TestCase
{
    public function testEmailHasTheCorrectSubjectAndMessage()
    {
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $email = new AttendeeMessageEmail($message);
        $this->assertEquals("My subject", $email->build()->subject);
        $this->assertEquals("My message", trim($email->render($email)));
    }
}
