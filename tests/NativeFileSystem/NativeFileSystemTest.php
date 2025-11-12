<?php

namespace Fidry\FileSystem\Tests\NativeFileSystem;

use Fidry\FileSystem\FileSystem;
use Fidry\FileSystem\NativeFileSystem;
use Fidry\FileSystem\Test\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Filesystem\Exception\IOException;
use const PHP_OS_FAMILY;

#[CoversClass(NativeFileSystem::class)]
final class NativeFileSystemTest extends FileSystemTestCase
{
    private FileSystem $fileSystem;

    public static function getTmpDirNamespace(): string
    {
        return 'native-filesystem-test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystem = new NativeFileSystem();
    }

    #[DataProvider('escapePathProvider')]
    public function test_it_escapes_path(
        string $path,
        string $expectedWindows,
        string $expectedUnixLinux,
    ): void
    {
        $expected = self::isWindows() ? $expectedWindows : $expectedUnixLinux;

        $actual = $this->fileSystem->escapePath($path);

        self::assertSame($expected, $actual);
    }

    public static function escapePathProvider(): iterable
    {
        yield 'Unix/Linux path' => [
            '/path/to/file',
            '\path\to\file',
            '/path/to/file',
        ];

        yield 'Windows path' => [
            'C:\path\to\file',
            'C:\path\to\file',
            'C:/path/to/file',
        ];
    }

    #[DataProvider('realPathProvider')]
    public function test_it_can_get_the_realpath_of_a_file(
        string $path,
        string|IOException $expectedWindows,
        string|IOException $expectedUnixLinux,
    ): void
    {
        $expected = self::isWindows() ? $expectedWindows : $expectedUnixLinux;

        if ($expected instanceof IOException) {
            $this->expectException(IOException::class);
            $this->expectExceptionMessage($expected->getMessage());
        }

        $actual = $this->fileSystem->realPath($path);

        if (is_string($expected)) {
            self::assertSame($expected, $actual);
        }
    }

    public static function realPathProvider(): iterable
    {
        $testFile = __FILE__;
        $testDir = __DIR__;
        $parentDir = realpath(__DIR__ . '/..');
        $validSymlink = __DIR__ . '/valid_symlink.txt';
        $brokenSymlink = __DIR__ . '/broken_symlink.txt';
        $nonExistentFile = __DIR__ . '/this_file_does_not_exist.txt';

        yield 'test file path' => [
            $testFile,
            str_replace('/', '\\', $testFile),
            $testFile,
        ];

        yield 'test directory path' => [
            $testDir,
            str_replace('/', '\\', $testDir),
            $testDir,
        ];

        yield 'relative path to current directory' => [
            __DIR__ . '/.',
            str_replace('/', '\\', $testDir),
            $testDir,
        ];

        yield 'relative path to parent directory' => [
            __DIR__ . '/..',
            str_replace('/', '\\', $parentDir),
            $parentDir,
        ];

        // Symlink resolves to its target
        yield 'valid symlink' => [
            $validSymlink,
            str_replace('/', '\\', realpath($validSymlink)),
            realpath($validSymlink),
        ];

        // Broken symlink throws exception
        yield 'broken symlink' => [
            $brokenSymlink,
            new IOException(sprintf('The file or directory "%s" does not exist.', $brokenSymlink)),
            new IOException(sprintf('The file or directory "%s" does not exist.', $brokenSymlink)),
        ];

        // Non-existent file throws exception
        yield 'non-existent file' => [
            $nonExistentFile,
            new IOException(sprintf('The file or directory "%s" does not exist.', $nonExistentFile)),
            new IOException(sprintf('The file or directory "%s" does not exist.', $nonExistentFile)),
        ];
    }

    #[DataProvider('normalizedRealPathProvider')]
    public function test_it_can_get_the_normalized_realpath_of_a_file(
        string $path,
        string|IOException $expectedWindows,
        string|IOException $expectedUnixLinux,
    ): void
    {
        $expected = self::isWindows() ? $expectedWindows : $expectedUnixLinux;

        if ($expected instanceof IOException) {
            $this->expectException(IOException::class);
            $this->expectExceptionMessage($expected->getMessage());
        }

        $actual = $this->fileSystem->normalizedRealPath($path);

        if (is_string($expected)) {
            self::assertSame($expected, $actual);
        }
    }

    public static function normalizedRealPathProvider(): iterable
    {
        $testFile = __FILE__;
        $testDir = __DIR__;
        $parentDir = realpath(__DIR__ . '/..');
        $validSymlink = __DIR__ . '/valid_symlink.txt';
        $brokenSymlink = __DIR__ . '/broken_symlink.txt';
        $nonExistentFile = __DIR__ . '/this_file_does_not_exist.txt';

        // Path::canonicalize always returns forward slashes regardless of OS
        yield 'test file path' => [
            $testFile,
            $testFile,  // Windows: canonicalized to forward slashes
            $testFile,  // Unix/Linux: already has forward slashes
        ];

        yield 'test directory path' => [
            $testDir,
            $testDir,  // Windows: canonicalized to forward slashes
            $testDir,  // Unix/Linux: already has forward slashes
        ];

        yield 'relative path to current directory' => [
            __DIR__ . '/.',
            $testDir,  // Windows: resolved and canonicalized
            $testDir,  // Unix/Linux: resolved
        ];

        yield 'relative path to parent directory' => [
            __DIR__ . '/..',
            $parentDir,  // Windows: resolved and canonicalized
            $parentDir,  // Unix/Linux: resolved
        ];

        // Symlink resolves to its target with forward slashes
        $resolvedSymlink = realpath($validSymlink);
        yield 'valid symlink' => [
            $validSymlink,
            $resolvedSymlink,  // Windows: canonicalized to forward slashes
            $resolvedSymlink,  // Unix/Linux: already has forward slashes
        ];

        // Broken symlink throws exception
        yield 'broken symlink' => [
            $brokenSymlink,
            new IOException(sprintf('The file or directory "%s" does not exist.', $brokenSymlink)),
            new IOException(sprintf('The file or directory "%s" does not exist.', $brokenSymlink)),
        ];

        // Non-existent file throws exception
        yield 'non-existent file' => [
            $nonExistentFile,
            new IOException(sprintf('The file or directory "%s" does not exist.', $nonExistentFile)),
            new IOException(sprintf('The file or directory "%s" does not exist.', $nonExistentFile)),
        ];
    }

    private static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
