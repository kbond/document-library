<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Image extends Document
{
    public function height(): int;

    public function width(): int;

    public function aspectRatio(): float;

    public function pixels(): int;

    public function isSquare(): bool;

    public function isPortrait(): bool;

    public function isLandscape(): bool;

    /**
     * Returns a flattened array of exif data in the format of
     * ["<lowercase-top-level-key>.<key>" => "<value>"].
     *
     * @example ["file.MimeType" => "image/jpeg"]
     *
     * @return array<string,string>
     */
    public function exif(): array;

    /**
     * @return array<string,string>
     */
    public function iptc(): array;
}
