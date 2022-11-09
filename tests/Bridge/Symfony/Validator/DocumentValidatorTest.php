<?php

namespace Zenstruck\Document\Library\Tests\Bridge\Symfony\Validator;

use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Symfony\Validator\DocumentConstraint;
use Zenstruck\Document\Library\Bridge\Symfony\Validator\DocumentValidator;
use Zenstruck\Document\PendingDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentValidatorTest extends ConstraintValidatorTestCase
{
    protected $path;

    protected $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.'FileValidatorTest';
        $this->file = \fopen($this->path, 'w');
        \fwrite($this->file, ' ', 1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (\is_resource($this->file)) {
            \fclose($this->file);
        }

        if (\file_exists($this->path)) {
            @\unlink($this->path);
        }

        $this->path = null;
        $this->file = null;
    }

    /**
     * @test
     */
    public function null_is_valid(): void
    {
        $this->validator->validate(null, new DocumentConstraint());

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function empty_string_is_invalid(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate('', new DocumentConstraint());
    }

    public function provideMaxSizeExceededTests(): array
    {
        // We have various interesting limit - size combinations to test.
        // Assume a limit of 1000 bytes (1 kB). Then the following table
        // lists the violation messages for different file sizes:
        // -----------+--------------------------------------------------------
        // Size       | Violation Message
        // -----------+--------------------------------------------------------
        // 1000 bytes | No violation
        // 1001 bytes | "Size of 1001 bytes exceeded limit of 1000 bytes"
        // 1004 bytes | "Size of 1004 bytes exceeded limit of 1000 bytes"
        //            | NOT: "Size of 1 kB exceeded limit of 1 kB"
        // 1005 bytes | "Size of 1.01 kB exceeded limit of 1 kB"
        // -----------+--------------------------------------------------------

        // As you see, we have two interesting borders:

        // 1000/1001 - The border as of which a violation occurs
        // 1004/1005 - The border as of which the message can be rounded to kB

        // Analogous for kB/MB.

        // Prior to Symfony 2.5, violation messages are always displayed in the
        // same unit used to specify the limit.

        // As of Symfony 2.5, the above logic is implemented.
        return [
            // limit in bytes
            [1001, 1000, '1001', '1000', 'bytes'],
            [1004, 1000, '1004', '1000', 'bytes'],
            [1005, 1000, '1.01', '1', 'kB'],

            [1000001, 1000000, '1000001', '1000000', 'bytes'],
            [1004999, 1000000, '1005', '1000', 'kB'],
            [1005000, 1000000, '1.01', '1', 'MB'],

            // limit in kB
            [1001, '1k', '1001', '1000', 'bytes'],
            [1004, '1k', '1004', '1000', 'bytes'],
            [1005, '1k', '1.01', '1', 'kB'],

            [1000001, '1000k', '1000001', '1000000', 'bytes'],
            [1004999, '1000k', '1005', '1000', 'kB'],
            [1005000, '1000k', '1.01', '1', 'MB'],

            // limit in MB
            [1000001, '1M', '1000001', '1000000', 'bytes'],
            [1004999, '1M', '1005', '1000', 'kB'],
            [1005000, '1M', '1.01', '1', 'MB'],

            // limit in KiB
            [1025, '1Ki', '1025', '1024', 'bytes'],
            [1029, '1Ki', '1029', '1024', 'bytes'],
            [1030, '1Ki', '1.01', '1', 'KiB'],

            [1048577, '1024Ki', '1048577', '1048576', 'bytes'],
            [1053818, '1024Ki', '1029.12', '1024', 'KiB'],
            [1053819, '1024Ki', '1.01', '1', 'MiB'],

            // limit in MiB
            [1048577, '1Mi', '1048577', '1048576', 'bytes'],
            [1053818, '1Mi', '1029.12', '1024', 'KiB'],
            [1053819, '1Mi', '1.01', '1', 'MiB'],

            // $limit < $coef, @see FileValidator::factorizeSizes()
            [169632, '100k', '169.63', '100', 'kB'],
            [1000001, '990k', '1000', '990', 'kB'],
            [123, '80', '123', '80', 'bytes'],
        ];
    }

    /**
     * @test
     * @dataProvider provideMaxSizeExceededTests
     */
    public function max_size_exceeded($bytesWritten, $limit, $sizeAsString, $limitAsString, $suffix): void
    {
        \fseek($this->file, $bytesWritten - 1);
        \fwrite($this->file, '0');
        \fclose($this->file);

        $constraint = new DocumentConstraint([
            'maxSize' => $limit,
            'maxSizeMessage' => 'myMessage',
        ]);

        $this->validator->validate($this->getDocument(), $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ limit }}', $limitAsString)
            ->setParameter('{{ size }}', $sizeAsString)
            ->setParameter('{{ suffix }}', $suffix)
            ->setParameter('{{ file }}', '"'.$this->path.'"')
            ->assertRaised()
        ;
    }

    public function provideMaxSizeNotExceededTests(): array
    {
        return [
            // 0 has no effect
            [100, 0],

            // limit in bytes
            [1000, 1000],
            [1000000, 1000000],

            // limit in kB
            [1000, '1k'],
            [1000000, '1000k'],

            // limit in MB
            [1000000, '1M'],

            // limit in KiB
            [1024, '1Ki'],
            [1048576, '1024Ki'],

            // limit in MiB
            [1048576, '1Mi'],
        ];
    }

    /**
     * @test
     * @dataProvider provideMaxSizeNotExceededTests
     */
    public function max_size_not_exceeded($bytesWritten, $limit): void
    {
        \fseek($this->file, $bytesWritten - 1);
        \fwrite($this->file, '0');
        \fclose($this->file);

        $constraint = new DocumentConstraint([
            'maxSize' => $limit,
            'maxSizeMessage' => 'myMessage',
        ]);

        $this->validator->validate($this->getDocument(), $constraint);

        $this->assertNoViolation();
    }

    public function provideBinaryFormatTests(): array
    {
        return [
            [11, 10, null, '11', '10', 'bytes'],
            [11, 10, true, '11', '10', 'bytes'],
            [11, 10, false, '11', '10', 'bytes'],

            // round(size) == 1.01kB, limit == 1kB
            [\ceil(1000 * 1.01), 1000, null, '1.01', '1', 'kB'],
            [\ceil(1000 * 1.01), '1k', null, '1.01', '1', 'kB'],
            [\ceil(1024 * 1.01), '1Ki', null, '1.01', '1', 'KiB'],

            [\ceil(1024 * 1.01), 1024, true, '1.01', '1', 'KiB'],
            [\ceil(1024 * 1.01 * 1000), '1024k', true, '1010', '1000', 'KiB'],
            [\ceil(1024 * 1.01), '1Ki', true, '1.01', '1', 'KiB'],

            [\ceil(1000 * 1.01), 1000, false, '1.01', '1', 'kB'],
            [\ceil(1000 * 1.01), '1k', false, '1.01', '1', 'kB'],
            [\ceil(1024 * 1.01 * 10), '10Ki', false, '10.34', '10.24', 'kB'],
        ];
    }

    /**
     * @test
     * @dataProvider provideBinaryFormatTests
     */
    public function binary_format($bytesWritten, $limit, $binaryFormat, $sizeAsString, $limitAsString, $suffix): void
    {
        \fseek($this->file, $bytesWritten - 1, \SEEK_SET);
        \fwrite($this->file, '0');
        \fclose($this->file);

        $constraint = new DocumentConstraint([
            'maxSize' => $limit,
            'binaryFormat' => $binaryFormat,
            'maxSizeMessage' => 'myMessage',
        ]);

        $this->validator->validate($this->getDocument(), $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ limit }}', $limitAsString)
            ->setParameter('{{ size }}', $sizeAsString)
            ->setParameter('{{ suffix }}', $suffix)
            ->setParameter('{{ file }}', '"'.$this->path.'"')
            ->assertRaised()
        ;
    }

    /**
     * @test
     */
    public function binary_format_named(): void
    {
        \fseek($this->file, 10, \SEEK_SET);
        \fwrite($this->file, '0');
        \fclose($this->file);

        $constraint = new DocumentConstraint(maxSize: 10, binaryFormat: true, maxSizeMessage: 'myMessage');

        $this->validator->validate($this->getDocument(), $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ limit }}', '10')
            ->setParameter('{{ size }}', '11')
            ->setParameter('{{ suffix }}', 'bytes')
            ->setParameter('{{ file }}', '"'.$this->path.'"')
            ->assertRaised()
        ;
    }

    /**
     * @test
     */
    public function valid_mime_type(): void
    {
        $document = $this->createMock(Document::class);
        $document
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true)
        ;
        $document
            ->expects($this->once())
            ->method('size')
            ->willReturn(10)
        ;
        $document
            ->expects($this->once())
            ->method('mimeType')
            ->willReturn('image/jpg')
        ;

        $constraint = new DocumentConstraint([
            'mimeTypes' => ['image/png', 'image/jpg'],
        ]);

        $this->validator->validate($document, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function valid_wildcard_mime_type(): void
    {
        $document = $this->createMock(Document::class);
        $document
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true)
        ;
        $document
            ->expects($this->once())
            ->method('size')
            ->willReturn(10)
        ;
        $document
            ->expects($this->once())
            ->method('mimeType')
            ->willReturn('image/jpg')
        ;

        $constraint = new DocumentConstraint([
            'mimeTypes' => ['image/*'],
        ]);

        $this->validator->validate($document, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @test
     * @dataProvider provideMimeTypeConstraints
     */
    public function invalid_mime_type(DocumentConstraint $constraint): void
    {
        $document = $this->createMock(Document::class);
        $document
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true)
        ;
        $document
            ->expects($this->once())
            ->method('size')
            ->willReturn(10)
        ;
        $document
            ->expects($this->once())
            ->method('mimeType')
            ->willReturn('application/pdf')
        ;
        $document
            ->expects($this->once())
            ->method('path')
            ->willReturn('some/path')
        ;

        $this->validator->validate($document, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ type }}', '"application/pdf"')
            ->setParameter('{{ types }}', '"image/png", "image/jpg"')
            ->setParameter('{{ file }}', '"some/path"')
            ->assertRaised()
        ;
    }

    public function provideMimeTypeConstraints(): iterable
    {
        yield 'Doctrine style' => [new DocumentConstraint([
            'mimeTypes' => ['image/png', 'image/jpg'],
            'mimeTypesMessage' => 'myMessage',
        ])];
        yield 'named arguments' => [
            new DocumentConstraint(mimeTypes: ['image/png', 'image/jpg'], mimeTypesMessage: 'myMessage'),
        ];
    }

    /**
     * @test
     */
    public function invalid_wildcard_mime_type(): void
    {
        $document = $this->createMock(Document::class);
        $document
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true)
        ;
        $document
            ->expects($this->once())
            ->method('size')
            ->willReturn(10)
        ;
        $document
            ->expects($this->once())
            ->method('mimeType')
            ->willReturn('application/pdf')
        ;
        $document
            ->expects($this->once())
            ->method('path')
            ->willReturn('some/path')
        ;

        $constraint = new DocumentConstraint([
            'mimeTypes' => ['image/*', 'image/jpg'],
            'mimeTypesMessage' => 'myMessage',
        ]);

        $this->validator->validate($document, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ type }}', '"application/pdf"')
            ->setParameter('{{ types }}', '"image/*", "image/jpg"')
            ->setParameter('{{ file }}', '"some/path"')
            ->assertRaised()
        ;
    }

    /**
     * @test
     * @dataProvider provideDisallowEmptyConstraints
     */
    public function disallow_empty(DocumentConstraint $constraint): void
    {
        \ftruncate($this->file, 0);

        $this->validator->validate($this->getDocument(), $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ file }}', '"'.$this->path.'"')
            ->assertRaised()
        ;
    }

    public function provideDisallowEmptyConstraints(): iterable
    {
        yield 'Doctrine style' => [new DocumentConstraint([
            'disallowEmptyMessage' => 'myMessage',
        ])];
        yield 'named arguments' => [
            new DocumentConstraint(disallowEmptyMessage: 'myMessage'),
        ];
    }

    protected function createValidator(): DocumentValidator
    {
        return new DocumentValidator();
    }

    private function getDocument(): Document
    {
        return new PendingDocument($this->path);
    }
}
