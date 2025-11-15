# Filesystem

This is a tiny wrapper around the [Symfony filesystem]. It provides a `FileSystem` interface and
a few more utilities.


## New methods

```php
interface FileSystem extends SymfonyFileSystem
{
    /**
     * Replaces the path directory separator with the system one.
     *
     * For example, on Windows:
     * 'C:/path/to/file' => 'C:\path\to\file',
     */
    public function escapePath(string $path): string;

    /**
     * Returns the absolute path, but the path will not be normalized.
     *
     * For example, `::realpath('C:\Users\Name\file.txt')` on Windows will
     * return "C:\Users\Name\file.txt" (backslashes).
     *
     * @see https://php.net/manual/en/function.realpath.php
     *
     * @throws IOException When the file or symlink target does not exist.
     */
    public function realPath(string $file): string;

    /**
     * Returns the absolute normalized path.
     *
     * For example, `::realpath('C:\Users\Name\file.txt')` on Windows will
     * return "C:/Users/Name/file.txt".
     *
     * @see https://php.net/manual/en/function.realpath.php
     *
     * @throws IOException When the file or symlink target does not exist.
     */
    public function normalizedRealPath(string $file): string;

    /**
     * Creates a temporary file with support for custom stream wrappers. Same as tempnam(),
     * but targets the system default temporary directory by default and has a more consistent
     * name with tmpDir.
     *
     * For example:
     *
     *  ```php
     *  tmpFile('build')
     *
     *  // on OSX
     *  => '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/build8d9e0f1a'
     *  // on Windows
     *  => C:\Windows\Temp\build8d9e0f1a.tmp
     *  ```
     *
     * @param string $prefix          The prefix of the generated temporary file name.
     * @param string $suffix          The suffix of the generated temporary file name.
     * @param string $targetDirectory The directory where to create the temporary directory.
     *                                Defaults to the system default temporary directory.
     *
     * @return string The new temporary file pathname.
     *
     * @throws IOException
     *
     * @see tempnam()
     * @see SymfonyFileSystem::tempnam()
     * @see self::tmpDir()
     */
    public function tmpFile(string $prefix, string $suffix = '', ?string $targetDirectory = null): string;

    /**
     * Creates a temporary directory with support for custom stream wrappers. Similar to tempnam()
     * but creates a directory instead of a file.
     *
     * For example:
     *
     * ```php
     * tmpDir('build')
     *
     * // on OSX
     * => '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/build8d9e0f1a'
     * // on Windows
     * => C:\Windows\Temp\build8d9e0f1a.tmp
     * ```
     *
     * @param string|null $prefix          The prefix of the generated temporary directory name.
     * @param string      $targetDirectory The directory where to create the temporary directory.
     *                                     Defaults to the system default temporary directory.
     *
     * @throws IOException
     *
     * @return string The new temporary directory pathname.
     *
     * @see tempnam()
     */
    public function tmpDir(string $prefix, ?string $targetDirectory = null): string;

    /**
     * Tells whether a file exists and is readable.
     *
     * @throws IOException When Window's path is longer than 258 characters
     */
    public function isReadable(string $filename): bool;

    public function isReadableFile(string $filename): bool;

    public function isReadableDirectory(string $filename): bool;

    public function createFinder(): Finder;
}
```


## FileSystemTestCase

An example of a PHPUnit test:

```php

<?php declare(strict_types=1);

namespace App;

use Fidry\FileSystem\FS;
use Fidry\FileSystem\Test\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\Finder;
use function getenv;
use function is_string;

final class MyAppFileSystemTest extends FileSystemTestCase
{
    // This method needs to be implemented by your test or base filesystem test class.
    public static function getTmpDirNamespace(): string
    {
        // This is to make it thread safe with Infection. If you are not using
        // infection or do not need thread safety, this can return a constant
        // string, e.g. your project/library name.
        $threadId = getenv('TEST_TOKEN');

        if (!is_string($threadId)) {
            $threadId = '';
        }

        return 'MyApp'.$threadId;
    }

    public function test_it_works(): void
    {
        // This file is dumped into a temporary directory. Here,
        // something like '/private/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/MyApp/MyAppFileSystemTest10000'
        // on OSX.
        FS::dumpFile('file1', '');
        
        $files = Finder::create()
            ->files()
            ->in($this->tmp);

        self::assertSame(['file1'], $this->normalizePaths($files));
    }
}

```

## SplFileInfo utilities

The Symfony Finder `SplFileInfo` distinguish itself from `\SplFileInfo` in two ways:

- Files found by a finder are relative to the root directory. Hence, the relative path and pathname.
- The `::getContents()` method.

Prior to Symfony 7.1, the `::getContents()` method was _very_ useful. But now
the method `Filesystem::readfile()` is available. Note that this method was
backported in this library.

However, a lot of applications may be using the Symfony Finder `SplFileInfo`
because of it still. Whilst for the source code it makes little difference, for
tests it is more annoying as it is more complicated to create a fake Symfony
Finder `SplFileInfo` object. You will have to create a mock, which may be more
or less verbose depending on how much of the `SplFileInfo` API is used.

To help to fill in the gap, this library provides two utilities.


### SplFileInfoFactory

`SplFileInfoFactory` allows to easily create a real Symfony Finder `SplFileInfo`
instance without using a `Finder`:

```php
use PHPUnit\Framework\TestCase;

class DemoTest extends TestCase {

    function test_it_allows_to_compare_finder_splfileinfo_files(): void
    {
        $actual = $myService->getFileInfo();
        
        $expected = SplFileInfoFactory::fromPath('/path/to/expected', __DIR__);

        self::assertEquals($expected, $actual);
    }
}
```


### SplFileInfoBuilder

`SplFileInfoBuilder` allows to easily create a fake `SplFileInfo` instance. It
is often simpler than using a mock:

```diff
- private function createSplFileInfoMock(string $file): SplFileInfo&MockObject
+ private function createSplFileInfo(string $file): SplFileInfo
{
-    $splFileInfoMock = $this->createMock(SplFileInfo::class);
-    $splFileInfoMock->method('__toString')->willReturn($file);
-    $splFileInfoMock->method('getFilename')->willReturn($file);
-    $splFileInfoMock->method('getRealPath')->willReturn($file);
-    $splFileInfoMock->method('getContents')->willReturn(
-        file_exists($file) ? file_get_contents($file) : 'content',
-    );
-
-    return $splFileInfoMock;
+    return SplFileInfoBuilder::withTestData()
+        ->withFile($file)
+        ->withContents(
+            file_exists($file) ? file_get_contents($file) : 'content',
+        )
+        ->build();
}
```


## ReadOnlyFileSystem

```php
// Write operations will throw a `DomainException` exception.
new ReadOnlyFileSystem(failOnWrite: true);

// Write operations will do nothing. Methods that return a path, e.g.
// `::tempnam()` will return an empty string.
new ReadOnlyFileSystem(failOnWrite: false);
```


## FS

A `FS` static class for when you are not interested of using dependency injection
for your filesystem layer or for usage within tests.


```php
FS::touch('file');

// instead of
(new NativeFileSystem)->touch('file');
```


## Contributing

[GNU Make] is your friend. Try `make` or `make help`!.


[Flysystem]: https://flysystem.thephpleague.com/docs/
[Symfony filesystem]: https://symfony.com/doc/current/components/filesystem.html
[GNU Make]: https://www.gnu.org/software/make/
