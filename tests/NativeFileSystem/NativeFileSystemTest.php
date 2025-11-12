<?php

namespace Fidry\FileSystem\Tests;

use Fidry\FileSystem\FileSystem;
use Fidry\FileSystem\NativeFileSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use const PHP_OS_FAMILY;

#[CoversClass(NativeFileSystem::class)]
final class NativeFileSystemTest extends TestCase
{
    private FileSystem $fileSystem;

    protected function setUp(): void
    {
        $this->filesystem = new NativeFileSystem();
    }

    #[DataProvider('escapePathProvider')]
    public function test_it_escapes_path(string $path, string $expected): void
    {
        $actual = $this->fileSystem->escapePath($path);

        self::assertSame($expected, $actual);
    }

    public static function escapePathProvider(): iterable
    {
        yield 'Unix/Linux path' => [
            '/path/to/file',
            self::isWindows()
                ? '\path\to\file'
                : '/path/to/file',
        ];

        yield 'Windows path' => [
            'C:\path\to\file',
            self::isWindows()
                ? 'C:\path\to\file'
                : 'C:/path/to/file',
        ];
    }

    private static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
