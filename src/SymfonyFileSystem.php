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

namespace Fidry\FileSystem;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Traversable;

/**
 * Is an interface that captures all the methods of the Symfony Filesystem. Purely internal, to facilitate
 * moving this to Symfony eventually.
 *
 * @internal
 */
interface SymfonyFileSystem
{
    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     * @throws FileNotFoundException When originFile doesn't exist
     * @throws IOException           When copy fails
     *
     * @return void
     */
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false);

    /**
     * Creates a directory recursively.
     *
     * @throws IOException On any directory creation failure
     *
     * @return void
     */
    public function mkdir(string|iterable $dirs, int $mode = 0o777);

    /**
     * Checks the existence of files or directories.
     */
    public function exists(string|iterable $files): bool;

    /**
     * Sets access and modification time of file.
     *
     * @param string|iterable<string> $files
     * @param int|null                $time  The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param int|null                $atime The access time as a Unix timestamp, if not supplied the current system time is used
     *
     * @throws IOException When touch fails
     *
     * @return void
     */
    public function touch(string|iterable $files, ?int $time = null, ?int $atime = null);

    /**
     * Removes files or directories.
     *
     * @throws IOException When removal fails
     *
     * @return void
     */
    public function remove(string|iterable $files);

    /**
     * Change mode for an array of files or directories.
     *
     * @param int  $mode      The new mode (octal)
     * @param int  $umask     The mode mask (octal)
     * @param bool $recursive Whether change the mod recursively or not
     *
     * @throws IOException When the change fails
     *
     * @return void
     */
    public function chmod(string|iterable $files, int $mode, int $umask = 0o000, bool $recursive = false);

    /**
     * Change the owner of an array of files or directories.
     *
     * This method always throws on Windows, as the underlying PHP function is not supported.
     *
     * @see https://www.php.net/chown
     *
     * @param string|int $user      A user name or number
     * @param bool       $recursive Whether change the owner recursively or not
     *
     * @throws IOException When the change fails
     *
     * @return void
     */
    public function chown(string|iterable $files, string|int $user, bool $recursive = false);

    /**
     * Change the group of an array of files or directories.
     *
     * This method always throws on Windows, as the underlying PHP function is not supported.
     *
     * @see https://www.php.net/chgrp
     *
     * @param string|iterable<string> $files
     * @param string|int              $group     A group name or number
     * @param bool                    $recursive Whether change the group recursively or not
     *
     * @throws IOException When the change fails
     *
     * @return void
     */
    public function chgrp(string|iterable $files, string|int $group, bool $recursive = false);

    /**
     * Renames a file or a directory.
     *
     * @throws IOException When target file or directory already exists
     * @throws IOException When origin cannot be renamed
     *
     * @return void
     */
    public function rename(string $origin, string $target, bool $overwrite = false);

    /**
     * Creates a symbolic link or copy a directory.
     *
     * @throws IOException When symlink fails
     *
     * @return void
     */
    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false);

    /**
     * Creates a hard link, or several hard links to a file.
     *
     * @param string|string[] $targetFiles The target file(s)
     *
     * @throws FileNotFoundException When original file is missing or not a file
     * @throws IOException           When link fails, including if link already exists
     *
     * @return void
     */
    public function hardlink(string $originFile, string|iterable $targetFiles);

    /**
     * Resolves links in paths.
     *
     * With $canonicalize = false (default)
     *      - if $path does not exist or is not a link, returns null
     *      - if $path is a link, returns the next direct target of the link without considering the existence of the target
     *
     * With $canonicalize = true
     *      - if $path does not exist, returns null
     *      - if $path exists, returns its absolute fully resolved final version
     */
    public function readlink(string $path, bool $canonicalize = false): ?string;

    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     */
    public function makePathRelative(string $endPath, string $startPath): string;

    /**
     * Mirrors a directory to another.
     *
     * Copies files and directories from the origin directory into the target directory. By default:
     *
     *  - existing files in the target directory will be overwritten, except if they are newer (see the `override` option)
     *  - files in the target directory that do not exist in the source directory will not be deleted (see the `delete` option)
     *
     * @param Traversable|null $iterator Iterator that filters which files and directories to copy, if null a recursive iterator is created
     * @param array            $options  An array of boolean options
     *                                   Valid options are:
     *                                   - $options['override'] If true, target files newer than origin files are overwritten (see copy(), defaults to false)
     *                                   - $options['copy_on_windows'] Whether to copy files instead of links on Windows (see symlink(), defaults to false)
     *                                   - $options['delete'] Whether to delete files that are not in the source directory (defaults to false)
     *
     * @throws IOException When a file type is unknown
     *
     * @return void
     */
    public function mirror(string $originDir, string $targetDir, ?Traversable $iterator = null, array $options = []);

    /**
     * Returns whether the file path is an absolute path.
     */
    public function isAbsolutePath(string $file): bool;

    /**
     * Creates a temporary file with support for custom stream wrappers.
     *
     * @param string $prefix The prefix of the generated temporary filename
     *                       Note: Windows uses only the first three characters of prefix
     * @param string $suffix The suffix of the generated temporary filename
     *
     * @return string The new temporary filename (with path), or throw an exception on failure
     */
    public function tempnam(string $dir, string $prefix, string $suffix = ''): string;

    /**
     * Atomically dumps content into a file.
     *
     * @param string|resource $content The data to write into the file
     *
     * @throws IOException if the file cannot be written to
     */
    public function dumpFile(string $filename, $content): void;

    /**
     * Appends content to an existing file.
     *
     * @param string|resource $content The content to append
     * @param bool            $lock    Whether the file should be locked when writing to it
     *
     * @throws IOException If the file is not writable
     *
     * @return void
     */
    public function appendToFile(string $filename, $content /* bool $lock = false */);

    /**
     * Returns the content of a file as a string.
     *
     * @throws IOException If the file cannot be read
     */
    public function readFile(string $filename): string;
}
