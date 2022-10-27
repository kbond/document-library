# zenstruck/document-library

## Basic Usage

Create a `Library` from any [Flysystem](https://flysystem.thephpleague.com/) filesystem
instance:

```php
use Zenstruck\Document\Library\FlysystemLibrary;

/** @var \League\Flysystem\FilesystemOperator $filesystem */

$library = new FlysystemLibrary($filesystem);
```

### Library API

```php
/** @var \Zenstruck\Document\Library $library */

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
$document->url(); // string (public url for document)
$document->exists(); // bool (whether the document exists or not)
$document->refresh(); // self (clears any cached metadata)
$document->tempFile(); // \SplFileInfo (real, local file that's deleted at the end of the script)
```

### `PendingDocument`

A `Zenstruck\Document` implementation that wraps a real, local file.

```php
use Zenstruck\Document\PendingDocument;

$document = new PendingDocument('/path/to/some/file.txt');
$document->path(); "/path/to/some/file.txt"
// ...
```

A `PendingDocument` can be created with a `Symfony\Component\HttpFoundation\File\UploadedFile`:

```php
use Zenstruck\Document\PendingDocument;

/** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */

$document = new PendingDocument($file);
$document->name(); // string - UploadedFile::getClientOriginalName()
$document->extension(); // string - UploadedFile::getClientOriginalExtension()
$document->mimeType(); // string - UploadedFile::getClientMimeType()
// ...
```

## Namers

Namer's can be used to generate the path for a document before saving. They are handy
when used in conjunction with [`PendingDocument`](#pendingdocument).

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
/** @var \Zenstruck\Document\Library $library */
/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new \Zenstruck\Document\Namer\ExpressionNamer();

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
- `{document.*}`: any raw document method (ie `{document.lastModified}`) - requires `symfony/property-access`.
- `{x}`: any passed `$context` key, the value must be _stringable_.
- `{x.y}`: if passed `$context` value for key `x` is an object, call method `y` on it, the return
  value must be _stringable_ - requires `symfony/property-access`.

#### Available Modifiers

- `{variable|lower}`: lowercase `{variable}`.
- `{variable|slug}`: slugify `{variable}`.

### `MultiNamer`

```php
use Zenstruck\Document\Namer\MultiNamer;

/** @var \Zenstruck\Document\PendingDocument $document */

$namer = new MultiNamer(); // defaults to containing above namers, with "expression" as the default

// defaults to ExpressionNamer (with its default expression)
$path = $namer->generateName($document); // "<slugified name>-<random 6 chars>.<extension>"

$path = $namer->generateName($document, ['namer' => 'checksum']); // use the checksum namer
$path = $namer->generateName($document, ['namer' => 'slugify']); // use the slugify namer
$path = $namer->generateName($document, ['namer' => 'expression', 'expression' => '{name}{ext}']); // use the expression namer
$path = $namer->generateName($document, ['expression' => '{name}{ext}']); // equivalent to above (because "expression" is the default)

// Customize the default namer
$namer = new MultiNamer(defaultNamer: 'checksum');

$path = $namer->generateName($document); // "<checksum>.<extension>"
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
    defaultNamer: 'custom1',
);

$path = $namer->generateName($document); // use the custom1 namer as it's the default
$path = $namer->generateName($document, ['namer' => 'custom2']); // use the custom2 namer
$path = $namer->generateName($document, ['namer' => 'checksum']); // default namers are still available
```

## Symfony

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

You can set [`PendingDocument`](#pendingdocument)'s to your entities `Document` properties.
These are automatically named (on persist and update) using the [Namer system](#namers)
and configured by your `Mapping`.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

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
     * using the "expression" namer with the configured "expression.
     *
     * Note the {this.username} syntax. "this" is the current instance of the entity.
     */
    #[Mapping(
        library: 'public',
        namer: 'expression', // use the expression namer
        expression: 'user/{this.username}-{checksum}{ext}' // saved to library as "user/<username><checksum>.<extension>"
    )]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;

    // equivalent to above (the default namer is "expression")
    #[Mapping(library: 'public', expression: 'user/{this.username}-{checksum}{ext}')]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;
}
```

> **Note**: If no `namer` or `expression` is configured, defaults to the `ExpressionNamer`
> with its configured default expression.

#### Store Additional Document Metadata

You can choose to store additional document metadata in the database column
(since it is a json type). This is useful to avoid retrieving this data
lazily from the filesystem.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    #[Mapping(library: 'public', metadata: true)] // will store path, lastModified, size, checksum, mimeType and url
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $image = null;

    // customize the saved metadata
    #[Mapping(library: 'public', metadata: ['path', 'url', 'lastModified'])] // will store just path, url, lastModified
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
$em->flush(); // json object with "path", "url" and "lastModified" saved to "user" db's "image" column

// autoload
$user = $em->find(User::class, 1);
$user->image->url(); // loads from json object (does not load from library)
$user->image->lastModified(); // loads from json object (does not load from library)
$user->image->read(); // will load document from filesystem

// update metadata stored in the database
$user->image = clone $user->image; // note the clone (this is required for doctrine to see the update)
$em->flush(); // metadata recalculated and saved
```

#### Post-Load Namer

When [storing additional metadata](#store-additional-document-metadata), if you don't configure
`path` in your `metadata` array, this triggers lazily generating the document's `path` after
loading the entity. This can be useful if your backend filesystem structure can change. Since
the path isn't stored in the database, you only have to update the mapping in your entity to
_mass-change_ all document's location.

```php
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Document;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

class User
{
    #[ORM\Column]
    public string $username;

    #[Mapping(
        library: 'public',
        metadata: ['checksum', 'size', 'extension'], // just store the checksum, size and file extension in the db
        namer: 'expression',
        expression: 'images/{this.username}-{checksum:7}{ext}'
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

class Part
{
    #[ORM\Column]
    public string $number;

    #[Mapping(
        library: 'public',
        namer: 'expression',
        expression: '/part/spec-sheets/{this.number}.pdf'
    )]
    public Document $specSheet;
}
```

Note, if it's possible the document may not exist for every entity, add a setter that
checks for existence before returning:

```php
public function getSpecSheet(): ?Document
{
    return $this->specSheet->exists() ? $this->specSheet : null;
}
```

### Serializer

Serialize/Deserialize document:

```php
use Zenstruck\Document;

/** @var \Symfony\Component\Serializer\Serializer $serializer */
/** @var Document $document */

$json = $serializer->serialize($document, 'json'); // "path/to/document"

$document = $serializer->deserialize($json, Document::class, 'json', [
    'library' => 'public', // library name IS REQUIRED when deserializing
]); // \Zenstruck\Document
```

When a document is a property on an object you want to serialize/deserialize, use the `Context`
attribute to specify the library name:

```php
use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document;

class User
{
    #[Context(['library' => 'public'])]
    public Document $profileImage;
}
```

#### Serialize Additional Metadata

You can optionally serialize with additional document metadata:

```php
use Zenstruck\Document;

/** @var \Symfony\Component\Serializer\Serializer $serializer */
/** @var Document $document */

$json = $serializer->serialize($document, 'json', [
    'metadata' => true,
]); // {"path": "...", "lastModified": ..., "size": ..., "checksum": "...", "mimeType": "...", "url": "..."}

// customize the metadata stored
$json = $serializer->serialize($document, 'json', [
    'metadata' => ['path', 'size', 'lastModified']
]); // {"path": "...", "size": ..., "lastModified": ...}
```

#### Post-Deserialize Namer

When [serializing with additional metadata](#serialize-additional-metadata), if you don't configure
`path` in your `metadata` array, this triggers lazily generating the document's `path` after
deserializing the document. This can be useful if your backend filesystem structure can change. Since
the path isn't stored in the database, you only have to update the context to _mass-change_
all serialized document's location.

```php
$json = $serializer->serialize($document, 'json', ['metadata' => ['checksum', 'extension']]); // no "path"

$document = $serializer->deserialize($json, Document::class, 'json', [
    'library' => 'public',
    'namer' => 'checksum',
]); // \Zenstruck\Document

$document->path(); // generated via the namer and the serialized data
```

#### Doctrine/Serializer Mapping

If you have an entity with a document that also can be serialized, you can configure
the doctrine mapping and serializer context with just the `Context` attribute to avoid
duplication.

```php
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document;

class User
{
    // ...

    #[Context(['library' => 'public'])]
    #[ORM\Column(type: Document::class, nullable: true)]
    public ?Document $profileImage = null;

    // ...
}
```
