<?php

namespace Zenstruck\Document;

use Psr\Container\ContainerInterface;
use Zenstruck\Document;
use Zenstruck\Document\Library\LazyLibrary;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LibraryRegistry
{
    /** @var array<string,LazyLibrary> */
    private array $lazyLibraries = [];

    /**
     * @param array<string,Library> $libraries
     */
    public function __construct(private ContainerInterface|array $libraries)
    {
    }

    public function get(string $name): Library
    {
        return $this->lazyLibraries[$name] ??= new LazyLibrary(function() use ($name) {
            if ($this->libraries instanceof ContainerInterface) {
                return $this->libraries->get($name);
            }

            return $this->libraries[$name] ?? throw new \InvalidArgumentException();
        });
    }

    public function getForDocument(Document $document): Library
    {
        $dsn = \parse_url($document->dsn());
        if (!isset($dsn['scheme'])) {
            throw new \InvalidArgumentException();
        }

        return $this->get($dsn['scheme']);
    }
}
