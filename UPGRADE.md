## 2.0.0

- The class `FileSystem` was renamed `NativeFileSystem`. `FileSystem` is now an
  interface.
- Made explicit the dependency on `ext-mbstring`. If you do not have this dependency
  met use [symfony/polyfill-mbstring](https://packagist.org/packages/symfony/polyfill-mbstring).
- Deprecated the `::getFileContents()` method in favour of `::readFile()`.
- Deprecated the `::isAbsolutePath()` method in favour of `Path::isAbsolute()`.
- Deprecated the `::isRelativePath()` method in favour of `Path::isRelative()`.
- Deprecated the `::makeTmpDir()` method in favour of `::tmpDir()`.
- Deprecated the `::getNamespacedTmpDir()` method in favour of `::tmpDir()`.
- Deprecated the `FileSystemTestCase::getTmpDirNamespace()` has been removed. This was requiring too 
  much boilerplate. Instead, the temporary directory is now already thread safe although it no longer
  creates one unique directory.
