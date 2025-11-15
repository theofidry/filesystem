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

namespace Fidry\FileSystem\Tests\Test;

use Fidry\FileSystem\FS;
use Fidry\FileSystem\Test\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use Symfony\Component\Finder\Finder;
use function getcwd;

/**
 * @internal
 */
#[CoversClass(FileSystemTestCase::class)]
#[Small]
final class FilesystemTestCaseTest extends FileSystemTestCase
{
    private string $cwdBeforeSetUp = '';

    protected function setUp(): void
    {
        $this->cwdBeforeSetUp = getcwd();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $tmpBeforeCleanup = $this->tmp;

        parent::tearDown();

        $cwdAfterTearDown = getcwd();

        // It is not very elegant to test in a teardown, but it's easier that
        // way...

        self::assertSame('', $this->cwd, 'Expected the current working directory to have been reset.');
        self::assertSame('', $this->tmp, 'Expected the current temporary directory to have been reset.');
        self::assertSame($this->cwdBeforeSetUp, $cwdAfterTearDown, 'Expected the current working directory to have been restored.');
        self::assertDirectoryDoesNotExist($tmpBeforeCleanup, 'Expected the temporary directory to have been removed.');
    }

    public function test_it_creates_a_temporary_directory_to_which_we_switch_to(): void
    {
        $cwd = getcwd();

        self::assertNotSame('', $this->cwd, 'Expected the current working directory to be set.');
        self::assertNotSame('', $this->tmp, 'Expected the current temporary directory to be set.');
        self::assertSame($this->cwdBeforeSetUp, $this->cwd, 'Expected the current working directory before setup to have been stored.');
        self::assertSame($this->tmp, $cwd, 'Expected the current working directory to be the temporary directory.');
    }

    public function test_it_can_provide_the_relative_paths(): void
    {
        FS::touch(['file1', 'file2']);
        FS::dumpFile('dir1/file3', '');

        $files = Finder::create()
            ->files()
            ->in($this->tmp);

        $expected = [
            FS::escapePath('dir1/file3'),
            'file1',
            'file2',
        ];
        $actual = $this->normalizePaths($files);

        self::assertEqualsCanonicalizing(
            $expected,
            $actual,
        );
        self::assertIsList($actual);
    }

    public function test_it_can_normalize_paths(): void
    {
        FS::touch(['file1', 'file2']);

        $expected = [
            'file1',
            'file2',
        ];
        $actual = $this->normalizePaths(
            (static function () {
                yield 'a' => 'file1';
                yield 'a' => 'file2';
            })(),
        );

        self::assertEqualsCanonicalizing(
            $expected,
            $actual,
        );
        self::assertIsList($actual);
    }
}
