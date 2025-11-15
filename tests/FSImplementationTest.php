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

namespace Fidry\FileSystem\Tests;

use Fidry\FileSystem\FileSystem;
use Fidry\FileSystem\FS;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use function array_diff;
use function array_filter;
use function array_map;
use function ksort;

/**
 * @internal
 */
#[CoversNothing]
final class FSImplementationTest extends TestCase
{
    private const NON_SYNCHRONIZED_FILESYSTEM_METHOD_NAMES = [
        'createFinder',
    ];

    private const NON_SYNCHRONIZED_FS_METHOD_NAMES = [
        'getInstance',
        'setInstance',
    ];

    public function test_fs_implements_the_filesystem_interface_statically(): void
    {
        $expected = self::getFileSystemMethodNames();
        $actual = self::getFSStaticMethodNames();

        self::assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @return list<string>
     */
    private static function getFileSystemMethodNames(): array
    {
        $fileSystemReflection = new ReflectionClass(FileSystem::class);
        $fileSystemMethods = $fileSystemReflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $methodNames = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            $fileSystemMethods,
        );

        $synchronizedMethodNames = array_diff($methodNames, self::NON_SYNCHRONIZED_FILESYSTEM_METHOD_NAMES);

        ksort($synchronizedMethodNames);

        return array_values($synchronizedMethodNames);
    }

    /**
     * @return list<string>
     */
    private static function getFSStaticMethodNames(): array
    {
        $fileSystemReflection = new ReflectionClass(FS::class);
        $fileSystemStaticMethods = array_filter(
            $fileSystemReflection->getMethods(ReflectionMethod::IS_PUBLIC),
            static fn (ReflectionMethod $method): bool => $method->isStatic(),
        );

        $methodNames = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            $fileSystemStaticMethods,
        );

        $synchronizedMethodNames = array_diff($methodNames, self::NON_SYNCHRONIZED_FS_METHOD_NAMES);

        ksort($synchronizedMethodNames);

        return array_values($synchronizedMethodNames);
    }
}
