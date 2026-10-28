<?php

namespace TamirRental\DocumentExtraction\Services;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * Re-encodes an oversized JPEG or PNG as a JPEG that fits a byte limit, keeping as much
 * quality and resolution as possible. Anything else is returned unchanged.
 */
class ImageShrinker
{
    /**
     * Leading bytes of the JPEG and PNG formats, the only images that are shrunk.
     */
    protected const array IMAGE_SIGNATURES = ["\xFF\xD8\xFF", "\x89PNG\r\n\x1A\n"];

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
     */
    public function shrink(string $contents): string
    {
        if (strlen($contents) <= $this->maxBytes || ! Str::startsWith($contents, self::IMAGE_SIGNATURES)) {
            return $contents;
        }

        $manager = ImageManager::gd();
        $scale = 1.0;

        while (true) {
            $image = $manager->read($contents);

            if ($scale < 1.0) {
                $image->scale(width: (int) round($image->width() * $scale));
            }

            foreach (self::QUALITIES as $quality) {
                $encoded = $image->toJpeg(quality: $quality);

                if ($encoded->size() <= $this->maxBytes) {
                    return $encoded->toString();
                }
            }

            $scale *= self::SCALE_STEP;
        }
    }
}
