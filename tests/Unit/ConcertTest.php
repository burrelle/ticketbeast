<?php

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanGetFormattedDate()
    {
        // Create a concert with a known date
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00 pm')
        ]);

        // Retrieve the formatted date
        $date = $concert->formatted_date;

        // Verify the date is formatted
        $this->assertEquals('December 1, 2016', $date);
    }
}
