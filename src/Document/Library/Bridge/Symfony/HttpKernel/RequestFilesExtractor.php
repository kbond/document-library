<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\HttpKernel;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 *
 * @internal
 */
class RequestFilesExtractor
{
    public function __construct(private PropertyAccessor $propertyAccessor)
    {
    }

    public function extractFilesFromRequest(
        Request $request,
        string  $path,
        bool    $returnArray = false
    ): PendingDocument|array|null {
        $path = $this->canonizePath($path);

        $files = $this->propertyAccessor->getValue($request->files->all(), $path);

        if ($returnArray) {
            if (!$files) {
                return [];
            }

            if (!is_array($files)) {
                $files = [$files];
            }

            return array_map(
                static fn (UploadedFile $file) => new PendingDocument($file),
                $files
            );
        }

        if (is_array($files)) {
            throw new \LogicException(sprintf('Could not extract files from request for "%s" path: expecting a single file, got %d files.', $path, count($files)));
        }

        if (!$files) {
            return null;
        }

        return new PendingDocument($files);
    }

    /**
     * Convert HTML paths, like "data[file]", to
     * PropertyAccessor compatible ("[data][file]")
     */
    private function canonizePath(string $path): string
    {
        if ($path[0] !== '[') {
            return preg_replace(
                '/^([^[]+)/',
                '[$1]',
                $path
            );
        }

        return $path;
    }
}
