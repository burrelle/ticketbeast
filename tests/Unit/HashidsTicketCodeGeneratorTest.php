<?php

use App\Ticket;
use Tests\TestCase;
use App\HashidsTicketCodeGenerator;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    public function testTicketsCodesAreAtLeast6CharactersLong()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $this->assertTrue(strlen($code) >= 6);
    }

    public function testTicketsCodesOnlyContainUppercaseLetters()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    public function testTicketsCodesForTheSameTicketIdAreTheSame()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $this->assertEquals($code1, $code2);
    }

    public function testTicketsCodesForTheDifferentTicketIdAreDifferent()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));
        $this->assertNotEquals($code1, $code2);
    }

    public function testTicketCodesGeneratedWithDifferentSaltsAreDifferent()
    {
        $ticketCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
        $ticketCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');
        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));
        $this->assertNotEquals($code1, $code2);
    }
}
