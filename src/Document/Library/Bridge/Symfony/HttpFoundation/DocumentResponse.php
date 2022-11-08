<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\HttpFoundation;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentResponse extends StreamedResponse
{
    /**
     * @param array<string,string|string[]> $headers
     */
    public function __construct(Document $document, int $status = 200, array $headers = [])
    {
        parent::__construct(
            static function() use ($document) {
                \stream_copy_to_stream($document->read(), \fopen('php://output', 'w') ?: throw new \RuntimeException('Unable to open output stream.'));
            },
            $status,
            $headers
        );

        if (!$this->headers->has('Last-Modified')) {
            $this->setLastModified(\DateTimeImmutable::createFromFormat('U', (string) $document->lastModified()) ?: null);
        }

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $document->mimeType());
        }
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    public static function inline(Document $document, ?string $filename = null, int $status = 200, array $headers = []): self
    {
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename ?? $document->name());

        return new self($document, $status, \array_merge($headers, ['Content-Disposition' => $disposition]));
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    public static function attachment(Document $document, ?string $filename = null, int $status = 200, array $headers = []): self
    {
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename ?? $document->name());

        return new self($document, $status, \array_merge($headers, ['Content-Disposition' => $disposition]));
    }
}
