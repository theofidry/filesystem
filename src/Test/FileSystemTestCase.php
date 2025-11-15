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

namespace Fidry\FileSystem\Test;

use Fidry\FileSystem\FS;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stringable;
use function array_map;
use function array_values;
use function chdir;
use function getcwd;
use function is_array;
use function iterator_to_array;
use function natcasesort;
use function natsort;
use function sprintf;
use function str_replace;
use const DIRECTORY_SEPARATOR;

abstract class FileSystemTestCase extends TestCase
{
    protected string $cwd = '';
    protected string $tmp = '';

    protected function setUp(): void
    {
        $tmpDirPrefix = $this->getTmpDirPrefix();

        $this->cwd = self::safeGetCurrentWorkingDirectory();
        // We use the real path here as, for example, in OSX, the path returned
        // is otherwise a link.
        $this->tmp = FS::realPath(FS::tmpDir($tmpDirPrefix));

        self::safeChdir($this->tmp);
    }

    protected function tearDown(): void
    {
        $wasSetupSkipped = '' === $this->cwd && '' === $this->tmp;

        if (!$wasSetupSkipped) {
            self::safeChdir($this->cwd);
            FS::remove($this->tmp);
        }

        $this->cwd = '';
        $this->tmp = '';
    }

    /**
     * @param iterable<string|Stringable> $paths
     *
     * @return list<string> File real paths relative to the current temporary directory
     */
    final protected function normalizePaths(iterable $paths): array
    {
        $root = $this->tmp;

        $normalizedPaths = array_map(
            static fn (string $file): string => str_replace($root.DIRECTORY_SEPARATOR, '', $file),
            is_array($paths)
                ? $paths
                : iterator_to_array($paths, false),
        );

        natsort($normalizedPaths);
        natcasesort($normalizedPaths);

        return array_values($normalizedPaths);
    }

    final protected static function safeChdir(string $directory): void
    {
        $chdirResult = chdir($directory);

        if (!$chdirResult) {
            throw new RuntimeException(
                sprintf(
                    'Could not change the current working directory to "%s".',
                    $directory,
                ),
            );
        }
    }

    final protected static function safeGetCurrentWorkingDirectory(): string
    {
        $result = getcwd();

        if (false === $result) {
            throw new RuntimeException('Could not get the current working directory.');
        }

        return $result;
    }

    /**
     * If the test case is `App\Tests\MyFilesystemServiceTestCase`, the default prefix will be "App\Tests\MyFilesystemServiceTestCase".
     *
     * @return string
     */
    protected function getTmpDirPrefix(): string
    {
        return str_replace('\\', '', static::class);
    }
}
