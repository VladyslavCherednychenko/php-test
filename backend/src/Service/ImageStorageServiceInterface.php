<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageStorageServiceInterface
{
    public function saveImage(UploadedFile $file, string $folder = 'uncategorized', int $maxWidth = 1024, int $maxHeight = 1024): string;
}
