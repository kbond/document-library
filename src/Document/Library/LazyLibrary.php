<?php

namespace Zenstruck\Document\Library;

use Zenstruck\Document;
use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyLibrary implements Library
{
    /** @var Library|callable():Library */
    private $library;

    /**
     * @param callable():Library $library
     */
    public function __construct(callable $library)
    {
        $this->library = $library;
    }

    public function open(string $path): Document
    {
        return $this->library()->open($path);
    }

    public function has(string $path): bool
    {
        return $this->library()->has($path);
    }

    public function store(string $path, \SplFileInfo|Document|string $document, array $config = []): Document
    {
        return $this->library()->store($path, $document, $config);
    }

    public function delete(string $path): static
    {
        $this->library()->delete($path);

        return $this;
    }

    private function library(): Library
    {
        if ($this->library instanceof Library) {
            return $this->library;
        }

        if (\is_callable($this->library)) {
            return $this->library = ($this->library)();
        }

        throw new \LogicException('A library has not been properly configured');
    }
}
