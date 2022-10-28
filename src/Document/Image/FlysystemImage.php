<?php

namespace Zenstruck\Document\Image;

use League\Flysystem\UnableToRetrieveMetadata;
use Zenstruck\Document\FlysystemDocument;
use Zenstruck\Document\Image;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class FlysystemImage extends FlysystemDocument implements Image
{
    /** @var array{0:int,1:int,2:mixed[]} */
    private array $imageMetadata;
    private \SplFileInfo $file;

    /** @var array<string,string> */
    private array $iptc;

    /** @var array<string,string> */
    private array $exif;

    public function height(): int
    {
        return $this->imageMetadata()[1];
    }

    public function width(): int
    {
        return $this->imageMetadata()[0];
    }

    public function aspectRatio(): float
    {
        return $this->width() / $this->height();
    }

    public function pixels(): int
    {
        return $this->width() * $this->height();
    }

    public function isSquare(): bool
    {
        return $this->width() === $this->height();
    }

    public function isPortrait(): bool
    {
        return $this->height() > $this->width();
    }

    public function isLandscape(): bool
    {
        return $this->width() > $this->height();
    }

    /**
     * @copyright Bulat Shakirzyanov <mallluhuct@gmail.com>
     * @source https://github.com/php-imagine/Imagine/blob/9b9aacbffadce8f19abeb992b8d8d3a90cc2a52a/src/Image/Metadata/ExifMetadataReader.php
     */
    public function exif(): array
    {
        if (!\function_exists('exif_read_data')) {
            throw new \LogicException('exif extension is not available.');
        }

        if (isset($this->exif)) {
            return $this->exif;
        }

        if (false === $data = @\exif_read_data($this->file(), as_arrays: true)) {
            throw new \RuntimeException(\sprintf('Unable to parse EXIF data for "%s".', $this->path()));
        }

        $ret = [];

        foreach ($data as $section => $values) {
            if (!\is_array($values)) {
                continue;
            }

            if (array_is_list($values)) {
                $ret[\mb_strtolower($section)] = \implode("\n", $values);

                continue;
            }

            foreach ($values as $key => $value) {
                $ret[\sprintf('%s.%s', \mb_strtolower($section), $key)] = $value;
            }
        }

        return $this->exif = $ret;
    }

    /**
     * @copyright Oliver Vogel
     * @source https://github.com/Intervention/image/blob/54934ae8ea3661fd189437df90fb09ec3b679c74/src/Intervention/Image/Commands/IptcCommand.php
     */
    public function iptc(): array
    {
        if (isset($this->iptc)) {
            return $this->iptc;
        }

        if (!\array_key_exists('APP13', $info = $this->imageMetadata()[2])) {
            return $this->iptc = [];
        }

        if (false === $iptc = \iptcparse($info['APP13'])) {
            throw new \RuntimeException(\sprintf('Unable to parse IPTC data for "%s".', $this->path()));
        }

        return $this->iptc = \array_filter([
            'DocumentTitle' => $iptc['2#005'][0] ?? null,
            'Urgency' => $iptc['2#010'][0] ?? null,
            'Category' => $iptc['2#015'][0] ?? null,
            'Subcategories' => $iptc['2#020'][0] ?? null,
            'Keywords' => $iptc['2#025'][0] ?? null,
            'ReleaseDate' => $iptc['2#030'][0] ?? null,
            'ReleaseTime' => $iptc['2#035'][0] ?? null,
            'SpecialInstructions' => $iptc['2#040'][0] ?? null,
            'CreationDate' => $iptc['2#055'][0] ?? null,
            'CreationTime' => $iptc['2#060'][0] ?? null,
            'AuthorByline' => $iptc['2#080'][0] ?? null,
            'AuthorTitle' => $iptc['2#085'][0] ?? null,
            'City' => $iptc['2#090'][0] ?? null,
            'SubLocation' => $iptc['2#092'][0] ?? null,
            'State' => $iptc['2#095'][0] ?? null,
            'Country' => $iptc['2#101'][0] ?? null,
            'OTR' => $iptc['2#103'][0] ?? null,
            'Headline' => $iptc['2#105'][0] ?? null,
            'Source' => $iptc['2#110'][0] ?? null,
            'PhotoSource' => $iptc['2#115'][0] ?? null,
            'Copyright' => $iptc['2#116'][0] ?? null,
            'Caption' => $iptc['2#120'][0] ?? null,
            'CaptionWriter' => $iptc['2#122'][0] ?? null,
        ]);
    }

    public function refresh(): static
    {
        parent::refresh();

        unset($this->file, $this->imageMetadata, $this->iptc, $this->exif);

        return $this;
    }

    public function asImage(): Image
    {
        return $this;
    }

    /**
     * @return array{0:int,1:int,2:mixed[]}
     */
    private function imageMetadata(): array
    {
        if (isset($this->imageMetadata)) {
            return $this->imageMetadata;
        }

        $file = $this->file();

        if ('svg' === $this->extension()) {
            return $this->imageMetadata = self::parseSvg($file) ?? throw UnableToRetrieveMetadata::create($this->path(), 'image_metadata', 'Unable to load svg.');
        }

        $info = [];

        if (false === $imageMetadata = @\getimagesize($file, $info)) {
            throw UnableToRetrieveMetadata::create($this->path(), 'image_metadata');
        }

        return $this->imageMetadata = [$imageMetadata[0], $imageMetadata[1], $info];
    }

    /**
     * @return null|array{0:int,1:int,2:mixed[]}
     */
    private static function parseSvg(\SplFileInfo $file): ?array
    {
        if (false === $xml = \file_get_contents($file)) {
            return null;
        }

        if (false === $xml = \simplexml_load_string($xml)) {
            return null;
        }

        if (!$xml = $xml->attributes()) {
            return null;
        }

        return [
            (int) \round((float) $xml->width),
            (int) \round((float) $xml->height),
            [],
        ];
    }

    private function file(): \SplFileInfo
    {
        return $this->file ??= $this->tempFile();
    }
}
