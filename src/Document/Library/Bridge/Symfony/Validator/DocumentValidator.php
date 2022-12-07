<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Validator;

use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Zenstruck\Document;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentValidator extends ConstraintValidator
{
    public const KB_BYTES = 1000;
    public const MB_BYTES = 1000000;
    public const KIB_BYTES = 1024;
    public const MIB_BYTES = 1048576;

    private const SUFFICES = [
        1 => 'bytes',
        self::KB_BYTES => 'kB',
        self::MB_BYTES => 'MB',
        self::KIB_BYTES => 'KiB',
        self::MIB_BYTES => 'MiB',
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DocumentConstraint) {
            throw new UnexpectedTypeException($constraint, DocumentConstraint::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Document) {
            throw new UnexpectedValueException($value, Document::class);
        }

        if (!$value->exists()) {
            $this->context->buildViolation($constraint->notFoundMessage)
                ->setParameter('{{ file }}', $this->formatValue($value->path()))
                ->addViolation()
            ;

            return;
        }

        $sizeInBytes = $value->size();

        if (0 === $sizeInBytes) {
            $this->context->buildViolation($constraint->disallowEmptyMessage)
                ->setParameter('{{ file }}', $this->formatValue($value->path()))
                ->addViolation()
            ;

            return;
        }

        if ($constraint->maxSize) {
            $limitInBytes = $constraint->maxSize;

            if ($sizeInBytes > $limitInBytes) {
                [$sizeAsString, $limitAsString, $suffix] = $this->factorizeSizes($sizeInBytes, $limitInBytes, $constraint->binaryFormat);
                $this->context->buildViolation($constraint->maxSizeMessage)
                    ->setParameter('{{ file }}', $this->formatValue($value->path()))
                    ->setParameter('{{ size }}', $sizeAsString)
                    ->setParameter('{{ limit }}', $limitAsString)
                    ->setParameter('{{ suffix }}', $suffix)
                    ->addViolation()
                ;

                return;
            }
        }

        $mimeTypes = (array) $constraint->mimeTypes;

        if (\property_exists($constraint, 'extensions') && $constraint->extensions) {
            $fileExtension = $value->extension();

            $found = false;
            $normalizedExtensions = [];
            foreach ((array) $constraint->extensions as $k => $v) {
                if (!\is_string($k)) {
                    $k = $v;
                    $v = null;
                }

                $normalizedExtensions[] = $k;

                if ($fileExtension !== $k) {
                    continue;
                }

                $found = true;

                if (null === $v) {
                    if (!\class_exists(MimeTypes::class)) {
                        throw new LogicException('You cannot validate the mime-type of files as the Mime component is not installed. Try running "composer require symfony/mime".');
                    }

                    $mimeTypesHelper = MimeTypes::getDefault();
                    $v = $mimeTypesHelper->getMimeTypes($k);
                }

                $mimeTypes = $mimeTypes ? \array_intersect($v, $mimeTypes) : (array) $v;

                break;
            }

            if (!$found) {
                $this->context->buildViolation($constraint->extensionsMessage)
                    ->setParameter('{{ file }}', $this->formatValue($value->path()))
                    ->setParameter('{{ extension }}', $this->formatValue($fileExtension))
                    ->setParameter('{{ extensions }}', $this->formatValues($normalizedExtensions))
                    ->addViolation()
                ;
            }
        }

        if ($mimeTypes) {
            $mime = $value->mimeType();

            foreach ($mimeTypes as $mimeType) {
                if ($mimeType === $mime) {
                    return;
                }

                if ($discrete = \mb_strstr($mimeType, '/*', true)) {
                    if (\mb_strstr($mime, '/', true) === $discrete) {
                        return;
                    }
                }
            }

            $this->context->buildViolation($constraint->mimeTypesMessage)
                ->setParameter('{{ file }}', $this->formatValue($value->path()))
                ->setParameter('{{ type }}', $this->formatValue($mime))
                ->setParameter('{{ types }}', $this->formatValues($mimeTypes))
                ->addViolation()
            ;
        }
    }

    private static function moreDecimalsThan(string $double, int $numberOfDecimals): bool
    {
        return \mb_strlen($double) > \mb_strlen((string) \round((float) $double, $numberOfDecimals));
    }

    /**
     * Convert the limit to the smallest possible number
     * (i.e. try "MB", then "kB", then "bytes").
     */
    private function factorizeSizes(int $size, int|float $limit, bool $binaryFormat): array
    {
        if ($binaryFormat) {
            $coef = self::MIB_BYTES;
            $coefFactor = self::KIB_BYTES;
        } else {
            $coef = self::MB_BYTES;
            $coefFactor = self::KB_BYTES;
        }

        // If $limit < $coef, $limitAsString could be < 1 with less than 3 decimals.
        // In this case, we would end up displaying an allowed size < 1 (eg: 0.1 MB).
        // It looks better to keep on factorizing (to display 100 kB for example).
        while ($limit < $coef) {
            $coef /= $coefFactor;
        }

        $limitAsString = (string) ($limit / $coef);

        // Restrict the limit to 2 decimals (without rounding! we
        // need the precise value)
        while (self::moreDecimalsThan($limitAsString, 2)) {
            $coef /= $coefFactor;
            $limitAsString = (string) ($limit / $coef);
        }

        // Convert size to the same measure, but round to 2 decimals
        $sizeAsString = (string) \round($size / $coef, 2);

        // If the size and limit produce the same string output
        // (due to rounding), reduce the coefficient
        while ($sizeAsString === $limitAsString) {
            $coef /= $coefFactor;
            $limitAsString = (string) ($limit / $coef);
            $sizeAsString = (string) \round($size / $coef, 2);
        }

        return [$sizeAsString, $limitAsString, self::SUFFICES[$coef]];
    }
}
