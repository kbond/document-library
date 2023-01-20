# zenstruck/document-library

## Basic Usage

Create a `Library` from any [Flysystem](https://flysystem.thephpleague.com/) filesystem
instance:

```php
use Zenstruck\Document\Library\FlysystemLibrary;

/** @var \League\Flysystem\FilesystemOperator $filesystem */

$library = new FlysystemLibrary('public', $filesystem);
```

### Library API

```php
/** @var \Zenstruck\Document\Library $library */

// Library ID
$library->id(); // string identifier passed to the constructor

// "Open" Documents
$document = $library->open('path/to/file.txt'); // \Zenstruck\Document

// Check if Document exists
$library->has('some/file.txt'); // bool (whether the document exists or not)

// Store Documents
$library->store('some/file.txt', 'file contents'); // \Zenstruck\Document

/** @var \SplFileInfo $file */
$library->store('some/file.txt', $file); // \Zenstruck\Document

/** @var \Zenstruck\Document $document */
$library->store('some/file.txt', $document); // \Zenstruck\Document

// Delete Documents
$library->delete('some/file.txt'); // self (fluent)
```

### Document API

```php
/** @var \Zenstruck\Document $document */

$document->dsn(); // "library:path/to/file.txt"
$document->path(); // "path/to/file.txt"
$document->name(); // "file.txt"
$document->extension(); // "txt"
$document->nameWithoutExtension(); // "file"
$document->lastModified(); // int (timestamp)
$document->size(); // int (bytes)
$document->mimeType(); // "text/plain"
$document->checksum(); // string (uses default checksum algorithm for flysystem provider)
$document->checksum('sha1'); // "string" (specify checksum algorithm)
$document->read(); // resource (file contents as stream)
$document->contents(); // string (file contents)
$document->publicUrl(); // string (public url for document)
$document->temporaryUrl(new \DateTime('+30 minutes')); // string (expiring url for document)
$document->temporaryUrl('+30 minutes'); // equivalent to above
$document->exists(); // bool (whether the document exists or not)
$document->refresh(); // self (clears any cached metadata)
```

## Namers

Namer's can be used to generate the path for a document before saving.

### `ChecksumNamer`

```php
/** @var \Zenstruck\Document\Library $library */
/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new \Zenstruck\Document\Namer\ChecksumNamer();

$library->store($namer->generateName($document), $document); // stored as "<document checksum>.<extension>"

// customize the checksum algorithm
$library->store($namer->generateName($document, ['alg' => 'sha1']), $document); // stored as "<document sha1 checksum>.<extension>"

// customize the checksum length (first x characters)
$library->store($namer->generateName($document, ['length' => 7]), $document); // stored as "<first 7 chars of document checksum>.<extension>"
```

### `SlugifyNamer`

```php
/** @var \Zenstruck\Document\Library $library */
/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new \Zenstruck\Document\Namer\ChecksumNamer();

$library->store($namer->generateName($document), $document); // stored as "<slugified filename>"
```

### `ExpressionNamer`

```php
use Zenstruck\Document\Namer\ExpressionNamer;

/** @var \Zenstruck\Document\Library $library */
/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new ExpressionNamer();

// Default expression
$path = $namer->generateName($document); // "<slugified name>-<random 6 chars>.<extension>"

// Customize expression
$path = $namer->generateName($document, [
    'expression' => 'some/prefix/{name}-{checksum:7}{ext}',
]); // "some/prefix/<slugified name>-<first 7 chars of checksum>.<extension>"

// Complex expression
$path = $namer->generateName($document, [
    'expression' => 'profile-images/{user.username|lower}{ext}',
    'user' => $userObject,
]); // "profile-images/<username (lowercased)>.<extension>"

$library->store($path, $document); // stored as "<slugified filename>"
```

#### Available Variables

- `{name}`: slugified document filename without extension.
- `{ext}`: document extension _with dot_ (ie `.txt` or _empty string_ if no extension).
- `{checksum}`: document checksum (uses default algorithm for flysystem provider).
- `{checksum:alg}`: document checksum using `alg` as the algorithm (ie `{checksum:sha1}`).
- `{checksum:n}`: first `n` characters of document checksum (ie `{checksum:7}`).
- `{checksum:alg:n}`: first `n` characters of document checksum using `alg` as the algorithm (ie `{checksum:sha1:7}`).
- `{rand}`: random `6` character string.
- `{rand:n}`: random `n` character string.
- `{document.*}`: any raw document method (ie `{document.lastModified}`).
- `{x}`: any passed `$context` key, the value must be _stringable_.
- `{x.y}`: if passed `$context` value for key `x` is an object, call method `y` on it, the return
  value must be _stringable_.

#### Available Modifiers

- `{variable|lower}`: lowercase `{variable}`.
- `{variable|slug}`: slugify `{variable}`.

### `MultiNamer`

```php
use Zenstruck\Document\Namer\MultiNamer;
use Zenstruck\Document\Namer\Expression;

/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new MultiNamer(); // defaults to containing above namers, with "expression" as the default

// defaults to ExpressionNamer (with its default expression)
$path = $namer->generateName($document); // "<slugified name>-<random 6 chars>.<extension>"

$path = $namer->generateName($document, ['namer' => 'checksum']); // use the checksum namer
$path = $namer->generateName($document, ['namer' => 'slugify']); // use the slugify namer
$path = $namer->generateName($document, ['namer' => 'expression', 'expression' => '{name}{ext}']); // use the expression namer
$path = $namer->generateName($document, ['namer' => new Expression('{name}{ext}')]); // equivalent to above
$path = $namer->generateName($document, ['namer' => 'expression:{name}{ext}']); // equivalent to above

// Customize the default namer
$namer = new MultiNamer(defaultContext: ['namer' => 'checksum']);

$path = $namer->generateName($document); // "<checksum>.<extension>"
```

The `MultiNamer` can also use a `callable` for the `namer`:

```php
use Zenstruck\Document;

/** @var \Zenstruck\Document\Namer\MultiNamer $namer */
/** @var Document $document */

$path = $namer->generateName($document, ['namer' => function(Document $document, array $context):string {
    // return string
}]);
```

### Custom Namer

You can create your own namer by having an object implement the `Zenstruck\Document\Namer`
interface and register it with the [`MultiNamer`](#multinamer):

```php
use Zenstruck\Document\Namer\MultiNamer;

/** @var \Zenstruck\Document\Namer $customNamer1 */
/** @var \Zenstruck\Document\Namer $customNamer2 */

$namer = new MultiNamer(
    namers: ['custom1' => $customNamer1, 'custom2' => $customNamer2],
    defaultContext: ['namer' => 'custom1'],
);

$path = $namer->generateName($document); // use the custom1 namer as it's the default
$path = $namer->generateName($document, ['namer' => 'custom2']); // use the custom2 namer
$path = $namer->generateName($document, ['namer' => 'checksum']); // default namers are still available
```

## Symfony

### Bundle

A Symfony bundle is available to configure different libraries for your application.
Enable in your `config/bundles.php`:

```php
// config/bundles.php

return [
    // ...
    Zenstruck\Document\Library\Bridge\Symfony\ZenstruckDocumentLibraryBundle::class => ['all' => true]
];
```

> **Note**: the remaining Symfony docs will assume you have the bundle enabled.

#### Configuration

Configure your application's libraries:

```yaml
# config/packages/zenstruck_filesystem.yaml

zenstruck_document_library:
    libraries:
        public: 'service.id.for.flysystem.operator'
        private: 'service.id.for.flysystem.operator'
```

#### Services

Your defined libraries can be autowired:

```php
use Zenstruck\Document\Library;

class SomeController
{
    public function someAction(
        Library $public, // the public library as defined in your config
        Library $private, // the private library as defined in your config
    ) {
        $document = $public->open('some/path');

        // ...
    }
}
```

### Form

A `DocumentType` form type is provided - it extends Symfony's native
[`FileType`](https://symfony.com/doc/current/reference/forms/types/file.html).
This type takes one or more uploaded files and stores them in the configured
`library` and names them using the configured [`namer`](#namers).

```php
use Zenstruck\Document\Library\Bridge\Symfony\Form\DocumentType;
use Zenstruck\Document\Namer\Expression;

/** @var \Symfony\Component\Form\FormBuilderInterface $builder */

$builder->add('attachment', DocumentType::class, [
    'library' => 'public',
    'namer' => new Expression('users/profile-images/{checksum}{ext}'),
]);

// multiple
$builder->add('attachments', DocumentType::class, [
    'multiple' => true,
    'library' => 'public',
    'namer' => new Expression('users/profile-images/{checksum}{ext}'),
]);
```

> **Note**: If no `namer` is configured, defaults to the `ExpressionNamer` with its configured
> default expression.

### Validator

A `DocumentConstraint` and `DocumentValidator` is provided. The `DocumentConstraint` has the
same API as Symfony's native [`File`](https://symfony.com/doc/current/reference/constraints/File.html)
constraint.

```php
use Zenstruck\Document\Library\Bridge\Symfony\Validator\DocumentConstraint;

/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
/** @var \Zenstruck\Document $document */

$validator->validate($document, new DocumentConstraint(maxSize: '1M'));
```

### Argument injection

An argument value resolver is provided that enables injecting `PendingDocument`'s
(from `$request->files`) directly into your controllers:

```php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Zenstruck\Document\Attribute\UploadedFile;
use Zenstruck\Document\PendingDocument;

class MyController extends AbstractController
{
    public function __invoke(
        // Inject directly from $request->files->get('file')
        ?PendingDocument $file,

        // Inject by path, which is the same format as `name` on HTML `<input>`
        #[UploadedFile('data[file]')]
        ?PendingDocument $anotherFile,

        // Inject array of pending documents from $request->files->get('files')
        #[UploadedFile]
        array $files,

        // Inject array of pending documents by path
        #[UploadedFile('data[files]')]
        array $anotherFiles
    ): Response {
        // Handle files and prepare response
    }
}
```

### Response

A `DocumentResponse` object is provided to easily create a Symfony response
from a `Document`:

```php
use Zenstruck\Document\Library\Bridge\Symfony\HttpFoundation\DocumentResponse;

/** @var \Zenstruck\Document $document */

$response = new DocumentResponse($document); // auto-adds content-type/last-modified headers

// create inline/attachment responses
$response = DocumentResponse::attachment($document); // auto names by the filename
$response = DocumentResponse::inline($document); // auto names by the filename

// customize the filename used for the content-disposition header
$response = DocumentResponse::attachment($document, 'different-name.txt');
$response = DocumentResponse::inline($document, 'different-name.txt');
```

### Doctrine ORM Integration

A custom DBAL type is provided to map `Document` instances to a json column and back. Add
a document property to your entity (using `Zenstruck\Document` as the _column type_ and
property typehint) and map the filesystem using the `Mapping` attribute:

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    // ...

    #[Mapping(library: 'public')]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;

    /**
     * Alternatively, map with column options:
     */
    #[ORM\Column(type: Document::class, nullable: true, options: ['library' => 'public'])]
    public ?Document $image = null;
    // ...
}
```

> **Warning**: It's important that the typehint is the `Zenstruck\Document` interface and
> not a concrete document object. Behind the scenes, it is populated with different
> implementations of this interface.

Usage:

```php
/** @var \Zenstruck\Document\Library $library */
/** @var \Doctrine\ORM\EntityManagerInterface $em */

// persist
$user = new User();
$user->image = $library->open('first/image.png');
$em->persist($user);
$em->flush(); // "first/image.png" is saved to the "user" db's "image" column

// autoload
$user = $em->find(User::class, 1);
$user->image->contents(); // call any Document method (lazily loads from library)

// update
$user->image = $library->open('second/image.png');
$em->flush(); // "second/image.png" is saved and "first/image.png" is deleted from the library

// delete
$em->remove($user);
$em->flush(); // "second/image.png" is deleted from the library
```

#### Persist/Update with `PendingDocument`

`Zenstruck\Document\PendingDocument` is a `Zenstruck\Document` implementation that wraps
a real, local file.

```php
use Zenstruck\Document\PendingDocument;

$document = new PendingDocument('/path/to/some/file.txt');
$document->path(); "/path/to/some/file.txt"
// ...
```

A `PendingDocument` can be also be created with a `Symfony\Component\HttpFoundation\File\UploadedFile`:

```php
use Zenstruck\Document\PendingDocument;

/** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */

$document = new PendingDocument($file);
$document->name(); // string - UploadedFile::getClientOriginalName()
$document->extension(); // string - UploadedFile::getClientOriginalExtension()
$document->mimeType(); // string - UploadedFile::getClientMimeType()
// ...
```

You can set `PendingDocument`'s to your entity's `Document` properties. These are automatically
named (on persist and update) using the [Namer system](#namers) and configured by your `Mapping`.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\Namer\Expression;

class User
{
    #[ORM\Column]
    public string $username;

    /**
     * PendingDocument's set on this property will be automatically named
     * using the "checksum" namer.
     */
    #[Mapping(library: 'public', namer: 'checksum')]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;

    /**
     * PendingDocument's set on this property will be automatically named
     * using the "expression" namer with the configured "expression".
     *
     * Note the {this.username} syntax. "this" is the current instance of the entity.
     */
    #[Mapping(
        library: 'public',
        namer: new Expression('user/{this.username}-{checksum}{ext}'), // saved to library as "user/<username><checksum>.<extension>"
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;
}
```

> **Note**: If not on PHP 8.1+, the `namer: new Expression()` syntax above is invalid. Use
> `namer: 'expression:user/{this.username}-{checksum}{ext}'` instead.

> **Note**: If no `namer` is configured, defaults to the `ExpressionNamer` with its configured
> default expression.

#### PendingDocument Form Type

This form type is similar to [`DocumentType`](#form) but instead of storing
the uploaded document to a library, it just converts to a `PendingDocument`.
This allows your entity mapping to be used to name/store.

```php
use Zenstruck\Document\Library\Bridge\Symfony\Form\PendingDocumentType;
use Zenstruck\Document\Namer\Expression;

/** @var \Symfony\Component\Form\FormBuilderInterface $builder */

$builder->add('attachment', PendingDocumentType::class);
```

#### Store Additional Document Metadata

You can choose to store additional document metadata in the database column
(since it is a json type). This is useful to avoid retrieving this data
lazily from the filesystem. Note that library identifier is always stored
in the metadata.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    #[Mapping(library: 'public', metadata: true)] // will store library, path, lastModified, size, checksum, mimeType and publicUrl
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;

    // customize the saved metadata
    #[Mapping(library: 'public', metadata: ['library', 'path', 'publicUrl', 'lastModified'])] // will store just library, path, publicUrl and lastModified
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;
}
```

Usage:

```php
/** @var \Zenstruck\Document\Library $library */
/** @var \Doctrine\ORM\EntityManagerInterface $em */

// persist
$user = new User();
$user->image = $library->open('first/image.png');
$em->persist($user);
$em->flush(); // json object with "library", "path", "publicUrl" and "lastModified" saved to "user" db's "image" column

// autoload
$user = $em->find(User::class, 1);
$user->image->publicUrl(); // loads from json object (does not load from library)
$user->image->lastModified(); // loads from json object (does not load from library)
$user->image->read(); // will load document from filesystem
```

#### Update Metadata

The following method is required to update the metadata stored in the database:

```php
$user->image = clone $user->image; // note the clone (this is required for doctrine to see the update)
$em->flush(); // metadata recalculated and saved
```

#### Name on Load

When [storing additional metadata](#store-additional-document-metadata), if you don't configure
`path` in your `metadata` array, this triggers lazily generating the document's `path` after
loading the entity. This can be useful if your backend filesystem structure can change. Since
the path isn't stored in the database, you only have to update the mapping in your entity to
_mass-change_ all document's location.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\Namer\Expression;

class User
{
    #[ORM\Column]
    public string $username;

    #[Mapping(
        library: 'public',
        metadata: ['checksum', 'size', 'extension'], // just store the library, checksum, size and file extension in the db
        namer: new Expression('images/{this.username}-{checksum:7}{ext}'), // use "namer: 'expression:images/{this.username}-{checksum:7}{ext}'" on PHP 8.0
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;
}
```

Now, when you load the entity, the `path` will be calculated (with the namer) when
first accessing a document method that requires it (ie `Document::contents()`).

Note in the above example, the expression is `images/{this.username}-{checksum:7}{ext}`.
Say you've renamed the `images` directory on your filesystem to `profile-images`. You
need only update the mapping's expression to `profile-images/{this.username}-{checksum:7}{ext}`.
The next time the document is loaded, it will point to the new directory.

You can force this behaviour even if the `path` is stored in the database:

```php
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    #[Mapping(
        library: 'public',
        metadata: ['checksum', 'size', 'extension'],
        namer: new Expression('images/{this.username}-{checksum:7}{ext}'),
        nameOnLoad: true, // force naming on load
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;
}
```

> **Note**: this doesn't update the path in the database automatically, see
> [Updating Metadata](#update-metadata) to see how to do this.

#### Virtual Document Properties

You can also create `Document` properties on your entities that aren't mapped to the
database. In this case, when you access the property, the `namer` will be called to
generate the path, which will then be loaded from the library. This can be useful
if you have documents that an entity has access to, but are managed elsewhere
(_readonly_ in the context of the entity). As long as they are named in a consistent
manner, you can map to them:

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\Namer\Expression;

class Part
{
    #[ORM\Column]
    public string $number;

    #[Mapping(
        library: 'public',
        namer: new Expression('/part/spec-sheets/{this.number}.pdf'), // use "namer: 'expression:/part/spec-sheets/{this.number}.pdf'" on PHP 8.0
    )]
    public Document $specSheet;
}
```

> **Note**: the namer for virtual documents will not have [document-related variables](#available-variables)
> available (as it's not yet available). You can only access the object (via `this`).

If it's possible the document may not exist for every entity, add a setter that
checks for existence before returning:

```php
public function getSpecSheet(): ?Document
{
    return $this->specSheet->exists() ? $this->specSheet : null;
}
```

### Document Column as String

The `Zenstruck\Document` DBAL type stores the document as JSON - even if just storing the
path. This is the most flexible as it allows storing additional metadata later without
needing to migrate the column. If you know you will only ever store the path, you can
use the `document_string` DBAL type. This uses a string column instead of a JSON column.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    // ...

    // Store DSN document string (default behavior)
    #[Mapping(library: 'public')]
    #[ORM\Column(type: 'document_string', nullable: true)]
    public ?Document $image = null;

    // Store only document path (you need to ensure the library is correctly set by yourself)
    #[Mapping(library: 'public', onlyPath: true)]
    #[ORM\Column(type: 'document_string', nullable: true)]
    public ?Document $image2 = null;

    // ...
}
```

> **Warning**: The default string that is stored in the database contains both library name
> and the document path (for example "public:foo/bar.txt").

> **Warning**: If you ever want to store additional metadata, you will need to run a database
> migration to convert the column from string to JSON.

### Serializer

Serialize/Deserialize document:

```php
use Zenstruck\Document;

/** @var \Symfony\Component\Serializer\Serializer $serializer */
/** @var Document $document */

$json = $serializer->serialize($document, 'json'); // "path/to/document"

$document = $serializer->deserialize($json, Document::class); // \Zenstruck\Document
```

#### Serialize as path string

You can optionally serialize document using only its path.
In such a case you need to provide document library as a deserialization context:

```php
use Zenstruck\Document;

/** @var \Symfony\Component\Serializer\Serializer $serializer */
/** @var Document $document */

$json = $serializer->serialize($document, 'json', ['only_path' => true]); // "path/to/document"

$document = $serializer->deserialize($json, Document::class, ['library' => 'public']); // \Zenstruck\Document
```

#### Serialize Additional Metadata

You can optionally serialize with additional document metadata:

```php
use Zenstruck\Document;

/** @var \Symfony\Component\Serializer\Serializer $serializer */
/** @var Document $document */

$json = $serializer->serialize($document, 'json', [
    'metadata' => true,
]); // {"library": "...", "path": "...", "lastModified": ..., "size": ..., "checksum": "...", "mimeType": "...", "publicUrl": "..."}

// customize the metadata stored
$json = $serializer->serialize($document, 'json', [
    'metadata' => ['path', 'size', 'lastModified']
]); // {"library": "...", "path": "...", "size": ..., "lastModified": ...}
```

#### Name on Deserialize

When [serializing with additional metadata](#serialize-additional-metadata), if you don't configure
`path` in your `metadata` array, this triggers lazily generating the document's `path` after
deserializing the document. This can be useful if your backend filesystem structure can change. Since
the path isn't stored in the database, you only have to update the context to _mass-change_
all serialized document's location.

```php
$json = $serializer->serialize($document, 'json', ['metadata' => ['checksum', 'extension']]); // no "path"

$document = $serializer->deserialize($json, Document::class, 'json', [
    'namer' => 'checksum',
]); // \Zenstruck\Document

$document->path(); // generated via the namer and the serialized data
```

You can force this behaviour even if the `path` is serialized:

```php
$json = $serializer->serialize($document, 'json', ['metadata' => ['path', 'checksum', 'extension']]); // includes path

$document = $serializer->deserialize($json, Document::class, 'json', [
    'namer' => 'checksum',
    'rename' => true, // trigger the rename
]); // \Zenstruck\Document

$document->path(); // always generated via the namer
```
