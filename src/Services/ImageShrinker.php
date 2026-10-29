<?php

namespace TamirRental\DocumentExtraction\Services;

use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;

/**
 * Re-encodes an oversized JPEG or PNG as a JPEG that fits a byte limit, keeping as much
 * quality and resolution as possible. Anything else is returned unchanged.
 */
class ImageShrinker
{
    /**
     * Image types that are shrunk; anything else is returned unchanged.
     */
    protected const array SHRINKABLE_MIME_TYPES = ['image/jpeg', 'image/png'];

    /**
     * JPEG qualities tried in order; the first one that fits is used.
     */
    protected const array QUALITIES = [95, 90, 85, 80];

    /**
     * Width factor applied each time the lowest quality still does not fit.
     */
    protected const float SCALE_STEP = 0.9;

    public function __construct(
        private int $maxBytes,
    ) {}

    /**
     * Return the contents unchanged when they fit or are not a JPEG or PNG. Otherwise
     * return them upright as a JPEG at the highest listed quality that fits at full
     * size, scaling the image down in steps only when the lowest quality does not fit.
     *
     * @throws ImageException When the image does not fit even at one pixel wide.
     */
    public function shrink(string $contents): string
    {
        if (strlen($contents) <= $this->maxBytes) {
            return $contents;
        }

        $image = Image::fromBytes($contents);

        if (! collect(self::SHRINKABLE_MIME_TYPES)->contains($image->mimeType())) {
            return $contents;
        }

        $image = $image->orient();
        $width = null;
        $targetWidth = null;
        $scale = 1.0;

        while (true) {
            $scaled = $targetWidth === null ? $image : $image->scale(width: $targetWidth);

            foreach (self::QUALITIES as $quality) {
                $encoded = $scaled->toJpg()->quality($quality)->toBytes();

                if (strlen($encoded) <= $this->maxBytes) {
                    return $encoded;
                }
            }

            if ($targetWidth === 1) {
                throw new ImageException("The image does not fit in {$this->maxBytes} bytes even at one pixel wide.");
            }

            $width ??= Image::fromBytes($encoded)->width();
            $scale *= self::SCALE_STEP;
            $targetWidth = max(1, (int) round($width * $scale));
        }
    }
}
