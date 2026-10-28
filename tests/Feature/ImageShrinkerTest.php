<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use TamirRental\DocumentExtraction\Services\ImageShrinker;

/**
 * A 120x80 image of deterministic noise, which compresses like a photo rather than a graphic.
 */
function busyImage(): ImageInterface
{
    $image = ImageManager::gd()->create(120, 80);

    for ($x = 0; $x < 120; $x++) {
        for ($y = 0; $y < 80; $y++) {
            $image->drawPixel($x, $y, sprintf('%06x', crc32("{$x}:{$y}") & 0xFFFFFF));
        }
    }

    return $image;
}

/**
 * Insert an EXIF segment holding only the given orientation tag right after the JPEG start marker.
 */
function withExifOrientation(string $jpeg, int $orientation): string
{
    $exif = "Exif\x00\x00MM\x00\x2A\x00\x00\x00\x08\x00\x01\x01\x12\x00\x03\x00\x00\x00\x01".pack('n', $orientation)."\x00\x00\x00\x00\x00\x00";

    return substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
}

it('keeps full size at the highest quality that fits', function (int $quality) {
    $original = busyImage()->toJpeg(quality: 100)->toString();
    $atQuality = ImageManager::gd()->read($original)->toJpeg(quality: $quality)->toString();

    $shrunk = (new ImageShrinker(strlen($atQuality)))->shrink($original);

    expect($shrunk)->toBe($atQuality);
})->with([95, 90, 85, 80]);

it('scales down only when the lowest quality does not fit', function () {
    $original = busyImage()->toJpeg(quality: 100)->toString();
    $maxBytes = ImageManager::gd()->read($original)->toJpeg(quality: 80)->size() - 1;

    $shrunk = (new ImageShrinker($maxBytes))->shrink($original);
    [$width, $height] = getimagesizefromstring($shrunk);

    expect(strlen($shrunk))->toBeLessThanOrEqual($maxBytes)
        ->and($width)->toBe(108)
        ->and($height)->toBe(72);
});

it('converts an oversized png to a jpeg', function () {
    $png = busyImage()->toPng()->toString();

    $shrunk = (new ImageShrinker(strlen($png) - 1))->shrink($png);
    [0 => $width, 1 => $height, 'mime' => $mime] = getimagesizefromstring($shrunk);

    expect($mime)->toBe('image/jpeg')
        ->and($width)->toBe(120)
        ->and($height)->toBe(80);
});

it('turns a photo rotated only by its exif data upright', function () {
    $rotated = withExifOrientation(busyImage()->toJpeg(quality: 100)->toString(), 6);

    $shrunk = (new ImageShrinker(strlen($rotated) - 1))->shrink($rotated);
    [$width, $height] = getimagesizefromstring($shrunk);

    expect($width)->toBe(80)
        ->and($height)->toBe(120);
});
