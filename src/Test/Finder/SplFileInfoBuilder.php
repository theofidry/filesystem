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

namespace Fidry\FileSystem\Test\Finder;

use Symfony\Component\Finder\SplFileInfo;

/**
 * This class is an immutable builder to build a SplFileInfo in a desired state. To use
 * for testing only.
 */
final class SplFileInfoBuilder
{
    public function __construct(
        private string $file,
        private string $relativePath,
        private string $relativePathname,
        private string $contents,
    ) {
    }

    public static function from(SplFileInfo $file): self
    {
        return new self(
            $file->getPath(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
            $file->getContents(),
        );
    }

    public static function withTestData(): self
    {
        return new self(
            '/path/to/project/src/App.php',
            // Example of path relative to `/path/to/project`.
            'src',
            'src/App.php',
            <<<'PHP'
                <?php

                echo 'Hello world!';

                PHP,
        );
    }

    public function withFile(string $file): self
    {
        $clone = clone $this;
        $clone->file = $file;

        return $clone;
    }

    public function withRelativePath(string $relativePath): self
    {
        $clone = clone $this;
        $clone->relativePath = $relativePath;

        return $clone;
    }

    public function withRelativePathname(string $relativePathname): self
    {
        $clone = clone $this;
        $clone->relativePathname = $relativePathname;

        return $clone;
    }

    public function withContents(string $contents): self
    {
        $clone = clone $this;
        $clone->contents = $contents;

        return $clone;
    }

    public function build(): SplFileInfo
    {
        return new DummySplFileInfo(
            $this->file,
            $this->relativePath,
            $this->relativePathname,
            $this->contents,
        );
    }
}
