<?php

/*
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2022, Théo FIDRY <theo.fidry@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Fidry\FileSystem\Tests\NativeFileSystem;

use Fidry\FileSystem\FileSystem;
use Fidry\FileSystem\NativeFileSystem;
use Fidry\FileSystem\Test\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Filesystem\Exception\IOException;
use const PHP_OS_FAMILY;

/**
 * @internal
 */
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
    ): void {
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
    ): void {
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
        $parentDir = realpath(__DIR__.'/..');
        $validSymlink = __DIR__.'/valid_symlink.txt';
        $brokenSymlink = __DIR__.'/broken_symlink.txt';
        $nonExistentFile = __DIR__.'/this_file_does_not_exist.txt';

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
            __DIR__.'/.',
            str_replace('/', '\\', $testDir),
            $testDir,
        ];

        yield 'relative path to parent directory' => [
            __DIR__.'/..',
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
    ): void {
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
        $parentDir = realpath(__DIR__.'/..');
        $validSymlink = __DIR__.'/valid_symlink.txt';
        $brokenSymlink = __DIR__.'/broken_symlink.txt';
        $nonExistentFile = __DIR__.'/this_file_does_not_exist.txt';

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
            __DIR__.'/.',
            $testDir,  // Windows: resolved and canonicalized
            $testDir,  // Unix/Linux: resolved
        ];

        yield 'relative path to parent directory' => [
            __DIR__.'/..',
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
