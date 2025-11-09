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

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Fidry\FileSystem;

use Symfony\Component\Filesystem\Exception\IOException;
use Traversable;
use function sprintf;

class ReadOnlyFileSystem extends NativeFileSystem
{
    public function __construct(private readonly bool $failOnWrite)
    {
    }

    public function copy(
        string $originFile,
        string $targetFile,
        bool $overwriteNewerFiles = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function mkdir(
        iterable|string $dirs,
        int $mode = 0o777
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function touch(
        iterable|string $files,
        ?int $time = null,
        ?int $atime = null
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function remove(iterable|string $files): void
    {
        $this->handleWrite(__METHOD__);
    }

    public function chmod(
        iterable|string $files,
        int $mode,
        int $umask = 0o000,
        bool $recursive = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function chown(
        iterable|string $files,
        int|string $user,
        bool $recursive = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function chgrp(
        iterable|string $files,
        int|string $group,
        bool $recursive = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function rename(
        string $origin,
        string $target,
        bool $overwrite = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function symlink(
        string $originDir,
        string $targetDir,
        bool $copyOnWindows = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function hardlink(
        string $originFile,
        iterable|string $targetFiles
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function mirror(
        string $originDir,
        string $targetDir,
        ?Traversable $iterator = null,
        array $options = []
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function tempnam(
        string $dir,
        string $prefix,
        string $suffix = ''
    ): string {
        $this->handleWrite(__METHOD__);

        return '';
    }

    public function appendToFile(
        string $filename,
        $content,
        bool $lock = false
    ): void {
        $this->handleWrite(__METHOD__);
    }

    public function dumpFile(string $filename, $content = ''): void
    {
        $this->handleWrite(__METHOD__);
    }

    public function makeTmpDir(string $namespace, string $className): string
    {
        $this->handleWrite(__METHOD__);

        return '';
    }

    public function getNamespacedTmpDir(string $namespace): string
    {
        $this->handleWrite(__METHOD__);

        return '';
    }

    private function handleWrite(string $methodName): void
    {
        if ($this->failOnWrite) {
            throw new IOException(
                sprintf(
                    'The operation "%s" is not allowed on a read-only file system.',
                    $methodName,
                ),
            );
        }
    }
}
