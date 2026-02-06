<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageStorageService implements ImageStorageServiceInterface
{
    public function __construct(private string $storageRoot) {}

    public function saveImage(UploadedFile $file, string $folder = 'uncategorized', int $maxWidth = 1024, int $maxHeight = 1024): string
    {
        $hash = hash_file('sha256', $file->getPathname());
        $relativePath = $this->getWebFileRelativePath($hash, $folder);

        $dir = $this->getWebFileAbsolutePath($relativePath);
        $this->createDirectory($dir);

        $target = "$dir/$hash.webp";

        if (!file_exists($target)) {
            $this->resizeImage($file->getPathname(), $target, $maxWidth, $maxHeight);
        }

        return "/images/$relativePath/$hash.webp";
    }

    private function getWebFileRelativePath(string $hash, ?string $folder): string
    {
        $a = substr($hash, 0, 2);
        $b = substr($hash, 2, 2);
        return "$folder/$a/$b";
    }

    private function getWebFileAbsolutePath(string $relativePath): string
    {
        return sprintf(
            '%s/%s',
            $this->storageRoot,
            $relativePath
        );
    }

    private function createDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private function resizeImage(string $source, string $target, int $maxWidth, int $maxHeight): void
    {
        $imagine = new Imagine();
        $image = $imagine->open($source);
        $size  = $image->getSize();

        $ratio = min($maxWidth / $size->getWidth(), $maxHeight / $size->getHeight(), 1);

        $new = new Box(
            (int)($size->getWidth() * $ratio),
            (int)($size->getHeight() * $ratio)
        );

        $image->resize($new)->save($target, ['webp_quality' => 85]);
    }
}
