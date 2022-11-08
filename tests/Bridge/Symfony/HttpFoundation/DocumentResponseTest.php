<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Symfony\HttpFoundation\DocumentResponse;
use Zenstruck\Document\Library\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentResponseTest extends TestCase
{
    /**
     * @test
     */
    public function can_create(): void
    {
        $document = $this->document();

        \ob_start();
        $response = (new DocumentResponse($document))->prepare(Request::create(''))->send();
        $output = \ob_get_clean();

        $this->assertTrue($response->headers->has('last-modified'));
        $this->assertSame(\DateTime::createFromFormat('U', $document->lastModified())->format('Y-m-d O'), (new \DateTime($response->headers->get('last-modified')))->format('Y-m-d O'));
        $this->assertTrue($response->headers->has('content-type'));
        $this->assertStringContainsString($document->mimeType(), $response->headers->get('content-type'));
        $this->assertFalse($response->headers->has('content-disposition'));
        $this->assertSame($document->contents(), $output);
    }

    /**
     * @test
     */
    public function can_create_as_inline(): void
    {
        $document = $this->document();

        \ob_start();
        $response = DocumentResponse::inline($document)->prepare(Request::create(''))->send();
        $output = \ob_get_clean();

        $this->assertTrue($response->headers->has('last-modified'));
        $this->assertSame(\DateTime::createFromFormat('U', $document->lastModified())->format('Y-m-d O'), (new \DateTime($response->headers->get('last-modified')))->format('Y-m-d O'));
        $this->assertTrue($response->headers->has('content-type'));
        $this->assertStringContainsString($document->mimeType(), $response->headers->get('content-type'));
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame("inline; filename={$document->name()}", $response->headers->get('content-disposition'));
        $this->assertSame($document->contents(), $output);
    }

    /**
     * @test
     */
    public function can_create_as_attachment(): void
    {
        $document = $this->document();

        \ob_start();
        $response = DocumentResponse::attachment($document)->prepare(Request::create(''))->send();
        $output = \ob_get_clean();

        $this->assertTrue($response->headers->has('last-modified'));
        $this->assertSame(\DateTime::createFromFormat('U', $document->lastModified())->format('Y-m-d O'), (new \DateTime($response->headers->get('last-modified')))->format('Y-m-d O'));
        $this->assertTrue($response->headers->has('content-type'));
        $this->assertStringContainsString($document->mimeType(), $response->headers->get('content-type'));
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame("attachment; filename={$document->name()}", $response->headers->get('content-disposition'));
        $this->assertSame($document->contents(), $output);
    }

    private function document(): Document
    {
        return self::$library->store('some/file.txt', 'content');
    }
}
