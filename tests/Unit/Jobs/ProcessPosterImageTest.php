<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\CustomConcertFactory;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    public function testItResizesThePosterImageTo_600pxWide()
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );
        $concert = CustomConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);
        ProcessPosterImage::dispatch($concert);
        $resizedImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width) = getimagesizefromstring($resizedImage);
        $this->assertEquals(600, $width);
    }

    public function testItOptimizesThePosterImage()
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'))
        );
        $concert = CustomConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);
        ProcessPosterImage::dispatch($concert);
        $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.png');
        $originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
        $this->assertLessThan($originalSize, $optimizedImageSize);
        $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.png');
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
        $this->assertEquals($controlImageContents, $optimizedImageContents);
    }
}
