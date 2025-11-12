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

use Fidry\FileSystem\Test\Finder\SplFileInfoBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
#[CoversClass(SplFileInfoBuilder::class)]
final class SplFileInfoBuilderTest extends TestCase
{
    public function test_it_can_create_an_instance_with_test_data(): void
    {
        $fileInfo = SplFileInfoBuilder::withTestData()->build();

        self::assertSame(
            [
                'path' => '/path/to/project/src',
                'pathname' => '/path/to/project/src/App.php',
                'relativePath' => 'src',
                'relativePathname' => 'src/App.php',
                'filename' => 'App.php',
                'filenameWithoutExtensions' => 'App',
                'contents' => <<<'PHP'
                    <?php

                    echo 'Hello world!';

                    PHP,
            ],
            self::getTestedSplFileInfoState($fileInfo),
        );
    }

    public function test_it_can_change_its_values(): void
    {
        $fileInfo = SplFileInfoBuilder::withTestData()
            // If not an absolute path, it counts as the relative path to this statement
            // (this is SplFileInfo behaviour).
            // If you do not which for that, use absolute paths.
            ->withFile('SplFileInfoTest.php')
            ->withContents('Hello world!')
            ->withRelativePath('tests/Test/Finder')
            ->withRelativePathname('tests/Test/Finder/SplFileInfoBuilderTest.php')
            ->build();

        self::assertSame(
            [
                'path' => '',
                'pathname' => 'SplFileInfoTest.php',
                'relativePath' => 'tests/Test/Finder',
                'relativePathname' => 'tests/Test/Finder/SplFileInfoBuilderTest.php',
                'filename' => 'SplFileInfoTest.php',
                'filenameWithoutExtensions' => 'SplFileInfoTest',
                'contents' => 'Hello world!',
            ],
            self::getTestedSplFileInfoState($fileInfo),
        );
    }

    public function test_it_is_immutable(): void
    {
        $builder = SplFileInfoBuilder::withTestData()
            ->withFile('SplFileInfoTest.php')
            ->withContents('Hello world!')
            ->withRelativePath('tests/Test/Finder')
            ->withRelativePathname('tests/Test/Finder/SplFileInfoBuilderTest.php');

        $fileInfo2 = $builder
            ->withFile('AnotherSplFileInfoTest.php')
            ->withContents('Something!')
            ->withRelativePath('tests/Test/AnotherFinder')
            ->withRelativePathname('tests/Test/AnotherFinder/AnotherSplFileInfoBuilderTest.php')
            ->build();

        // Build this file _after_ we updated the builder
        $fileInfo1 = $builder->build();

        self::assertSame(
            [
                'path' => '',
                'pathname' => 'SplFileInfoTest.php',
                'relativePath' => 'tests/Test/Finder',
                'relativePathname' => 'tests/Test/Finder/SplFileInfoBuilderTest.php',
                'filename' => 'SplFileInfoTest.php',
                'filenameWithoutExtensions' => 'SplFileInfoTest',
                'contents' => 'Hello world!',
            ],
            self::getTestedSplFileInfoState($fileInfo1),
        );
        self::assertSame(
            [
                'path' => '',
                'pathname' => 'AnotherSplFileInfoTest.php',
                'relativePath' => 'tests/Test/AnotherFinder',
                'relativePathname' => 'tests/Test/AnotherFinder/AnotherSplFileInfoBuilderTest.php',
                'filename' => 'AnotherSplFileInfoTest.php',
                'filenameWithoutExtensions' => 'AnotherSplFileInfoTest',
                'contents' => 'Something!',
            ],
            self::getTestedSplFileInfoState($fileInfo2),
        );
    }

    #[DataProvider('fileInfoProvider')]
    public function test_it_can_create_a_builder_from_an_existing_instance(SplFileInfo $fileInfo): void
    {
        $actual = SplFileInfoBuilder::from($fileInfo)->build();

        self::assertEquals($fileInfo, $actual);
    }

    public static function fileInfoProvider(): iterable
    {
        yield [
            SplFileInfoBuilder::withTestData()->build(),
        ];
    }

    private static function getTestedSplFileInfoState(SplFileInfo $fileInfo): array
    {
        return [
            'path' => $fileInfo->getPath(),
            'pathname' => $fileInfo->getPathname(),
            'relativePath' => $fileInfo->getRelativePath(),
            'relativePathname' => $fileInfo->getRelativePathname(),
            'filename' => $fileInfo->getFilename(),
            'filenameWithoutExtensions' => $fileInfo->getFilenameWithoutExtension(),
            'contents' => $fileInfo->getContents(),
        ];
    }
}
