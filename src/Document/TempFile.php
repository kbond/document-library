<?php

namespace Zenstruck\Document;

use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TempFile extends \SplFileInfo
{
    public function __construct(?string $filename = null)
    {
        $filename ??= self::tempFile();

        if (\is_dir($filename)) {
            throw new \LogicException("\"{$filename}\" is a directory.");
        }

        parent::__construct($filename);

        // delete on script end
        \register_shutdown_function([$this, 'delete']);
    }

    public static function new(?string $filename = null): self
    {
        return new self($filename);
    }

    /**
     * @param resource|Document|string|\SplFileInfo $contents
     */
    public static function for(mixed $contents): self
    {
        $file = new self();

        if (\is_string($contents)) {
            if (false === \file_put_contents($file, $contents)) {
                throw new \RuntimeException('Unable to write to file.');
            }

            return $file;
        }

        if ($contents instanceof \SplFileInfo) {
            if (false === \copy($contents, $file)) {
                throw new \RuntimeException('Unable to copy file.');
            }

            return $file;
        }

        $close = false;

        if ($contents instanceof Document) {
            $contents = $contents->read();
            $close = true;
        }

        if (!\is_resource($contents)) {
            throw new \InvalidArgumentException('Not a resource.');
        }

        if (false === $fp = \fopen($file, 'w')) {
            throw new \RuntimeException('Unable to open file stream.');
        }

        if (0 !== \ftell($contents) && \stream_get_meta_data($contents)['seekable']) {
            \rewind($contents);
        }

        if (false === \stream_copy_to_stream($contents, $fp)) {
            throw new \RuntimeException('Unable to write stream.');
        }

        \fclose($fp);

        if ($close) {
            \fclose($contents);
        }

        return $file;
    }

    public static function withExtension(string $extension): self
    {
        $original = self::tempFile();

        if (!\rename($original, $new = "{$original}.{$extension}")) {
            throw new \RuntimeException('Unable to create temp file with extension.');
        }

        return new self($new);
    }

    public function refresh(): self
    {
        \clearstatcache(false, $this);

        return $this;
    }

    public function delete(): self
    {
        if (\file_exists($this)) {
            \unlink($this);
        }

        return $this;
    }

    private static function tempFile(): string
    {
        if (false === $filename = \tempnam(\sys_get_temp_dir(), 'zsdl_')) {
            throw new \RuntimeException('Failed to create temporary file.');
        }

        return $filename;
    }
}
