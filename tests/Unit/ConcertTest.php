<?php

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    public function testCanGetFormattedDate()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00 pm')
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    public function testCanGetFormattedStartTime()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00')
        ]);

        $this->assertEquals('5:00 pm', $concert->formatted_start_time);
    }

    public function testCanGetTicketPriceInDollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => '6750'
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }
}
