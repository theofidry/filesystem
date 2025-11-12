## 2.0.0

- The class `FileSystem` was renamed `NativeFileSystem`. `FileSystem` is now an
  interface.
- Deprecated the `::getFileContents()` method in favour of `::readFile()`.
- Deprecated the `::isAbsolutePath()` method in favour of `Path::isAbsolute()`.
- Deprecated the `::isRelativePath()` method in favour of `Path::isRelative()`.
