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
use Symfony\Component\Finder\Finder;

interface FileSystem extends SymfonyFileSystem
{
    /**
     * @deprecated Deprecated since 2.0. Use `Path::isRelative()` instead. Will be removed in 3.0.
     * @see Path::isRelative
     */
    public function isRelativePath(string $path): bool;

    public function escapePath(string $path): string;

    /**
     * @deprecated Use the `::readFile()` method. Deprecated since 2.0 and it will be removed in 3.0.
     * @see readFile
     *
     * @throws IOException If the file cannot be read
     */
    public function getFileContents(string $file): string;

    /**
     * Creates a temporary directory.
     *
     * @param string $namespace the directory path in the system's temporary directory
     * @param string $className the name of the test class
     *
     * @return string the path to the created directory
     */
    public function makeTmpDir(string $namespace, string $className): string;

    /**
     * Gets a namespaced temporary directory.
     *
     * @param string $namespace the directory path in the system's temporary directory
     */
    public function getNamespacedTmpDir(string $namespace): string;

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
