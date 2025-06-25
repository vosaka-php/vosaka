<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use DirectoryIterator;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use venndev\vosaka\utils\Result;
use venndev\vosaka\VOsaka;

final class Folder
{
    public static function copy(string $source, string $destination): Result
    {
        $fn = function () use ($source, $destination): Generator {
            if (!is_dir($source)) {
                throw new InvalidArgumentException("Source must be a directory: $source");
            }

            yield @mkdir($destination, 0755, true);
            if (!is_dir($destination)) {
                throw new RuntimeException("Failed to create destination directory: $destination");
            }

            $dir = new DirectoryIterator($source);
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }

                $sourcePath = $fileinfo->getPathname();
                $destinationPath = $destination . DIRECTORY_SEPARATOR . $fileinfo->getFilename();

                if ($fileinfo->isDir()) {
                    yield from self::copy($sourcePath, $destinationPath)->unwrap();
                } else {
                    yield @copy($sourcePath, $destinationPath);
                    if (!file_exists($destinationPath)) {
                        throw new RuntimeException("Failed to copy file: $sourcePath to $destinationPath");
                    }
                }
            }
        };

        return VOsaka::spawn($fn());
    }

    public static function delete(string $path): Result
    {
        $fn = function () use ($path): Generator {
            if (!is_dir($path)) {
                throw new InvalidArgumentException("Path must be a directory: $path");
            }

            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }

                $filePath = $fileinfo->getPathname();
                if ($fileinfo->isDir()) {
                    yield from self::delete($filePath)->unwrap();
                } else {
                    yield @unlink($filePath);
                }
            }

            yield @rmdir($path);
        };

        return VOsaka::spawn($fn());
    }
}
