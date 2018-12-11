<?php

use Tests\TestCase;
use App\RandomOrderConfirmationNumberGenerator;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    public function testMustBe24CharactersLong()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;
        $confirmationNumber = $generator->generate();
        $this->assertEquals(24, strlen($confirmationNumber));
    }

    public function testCanOnlyContainUppercaseLettersAndNumbers()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;
        $confirmationNumber = $generator->generate();
        $this->assertRegExp('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    public function testCannotContainAmbiguousCharacters()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;
        $confirmationNumber = $generator->generate();
        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
    }
}
