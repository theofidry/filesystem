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

namespace Fidry\FileSystem\Tests\Test\Finder;

use Fidry\FileSystem\Test\Finder\DummySplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function realpath;

/**
 * @internal
 */
#[CoversClass(DummySplFileInfo::class)]
final class DummySplFileInfoTest extends TestCase
{
    public function test_it_uses_the_file_system_real_path_by_default(): void
    {
        $fileInfo = new DummySplFileInfo(
            file: __FILE__,
            relativePath: '',
            relativePathname: '',
            contents: '',
        );

        $expected = realpath(__FILE__);
        $actual = $fileInfo->getRealPath();

        self::assertSame($expected, $actual);
    }

    public function test_it_can_fake_the_real_path_of_a_non_existent_file(): void
    {
        $expected = '/path/to/project/src/File1.php';

        $fileInfo = new DummySplFileInfo(
            file: 'src/File1.php',
            relativePath: 'src',
            relativePathname: 'src/File1.php',
            contents: '',
            realPath: $expected,
        );

        $actual = $fileInfo->getRealPath();

        self::assertSame($expected, $actual);
    }

    public function test_it_can_force_no_real_path_for_an_existing_file(): void
    {
        $fileInfo = new DummySplFileInfo(
            file: __FILE__,
            relativePath: '',
            relativePathname: '',
            contents: '',
            realPath: false,
        );

        self::assertFalse($fileInfo->getRealPath());
    }
}
