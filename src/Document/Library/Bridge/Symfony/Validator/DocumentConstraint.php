<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Validator;

use Symfony\Component\Validator\Constraints\File;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class DocumentConstraint extends File
{
}
