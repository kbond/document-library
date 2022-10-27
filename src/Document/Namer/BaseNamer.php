<?php

namespace Zenstruck\Document\Namer;

use Symfony\Component\String\ByteString;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Document;
use Zenstruck\Document\Namer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseNamer implements Namer
{
    private const ALPHABET = '123456789abcdefghijkmnopqrstuvwxyz';

    public function __construct(private ?SluggerInterface $slugger = null, private array $defaultContext = [])
    {
    }

    final public function generateName(Document $document, array $context = []): string
    {
        return $this->generate($document, \array_merge($this->defaultContext, $context));
    }

    abstract protected function generate(Document $document, array $context): string;

    final protected static function randomString(int $length = 6): string
    {
        if (!\class_exists(ByteString::class)) {
            /**
             * @source https://stackoverflow.com/a/13212994
             */
            return \mb_substr(\str_shuffle(\str_repeat(self::ALPHABET, (int) \ceil($length / \mb_strlen(self::ALPHABET)))), 1, $length);
        }

        return ByteString::fromRandom($length, self::ALPHABET)->toString();
    }

    final protected static function extensionWithDot(Document $document): string
    {
        return '' === ($ext = $document->extension()) ? '' : '.'.\mb_strtolower($ext);
    }

    final protected static function checksum(Document $document, ?string $algorithm, ?int $length): string
    {
        $checksum = $document->checksum($algorithm ?? []);

        return $length ? \mb_substr($checksum, 0, $length) : $checksum;
    }

    final protected function slugify(string $value): string
    {
        return $this->slugger ? $this->slugger->slug($value) : \mb_strtolower(\str_replace(' ', '-', $value));
    }
}
