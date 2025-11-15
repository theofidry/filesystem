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
use function tempnam;

interface FileSystem extends SymfonyFileSystem
{
    /**
     * @deprecated Deprecated since 2.0. Use `Path::isRelative()` instead. Will be removed in 3.0.
     * @see Path::isRelative())
     */
    public function isRelativePath(string $path): bool;

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
     * @deprecated Use the `::readFile()` method. Deprecated since 2.0 and it will be removed in 3.0.
     * @see SymfonyFileSystem::readFile()
     *
     * @throws IOException If the file cannot be read
     */
    public function getFileContents(string $file): string;

    /**
     * @deprecated Use the `::tmpDir()` method. Deprecated since 2.0 and it will be removed in 3.0.
     * @see self::tmpDir()
     *
     * Creates a temporary directory.
     *
     * @param string $namespace the directory path in the system's temporary directory
     * @param string $className the name of the test class
     *
     * @return string the path to the created directory
     */
    public function makeTmpDir(string $namespace, string $className): string;

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
     * @deprecated Deprecated since 2.0. Use `Path::isRelative()` instead. Will be removed in 3.0.
     *             Using a namespaced dir is an antipattern with parallel testing.
     *
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
