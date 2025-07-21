<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;


class SignService
{
    /**
     * Process, compress, and store the signature image as PNG.
     *
     * @param UploadedFile $uploadedFile
     * @param string $directory
     * @param int $maxWidth
     * @return string Storage path
     */
    public function processAndStoreSignature(UploadedFile $uploadedFile, string $directory = 'signatures', int $maxWidth = 200): string
    {
        // Load image
        $image = Image::read($uploadedFile)
			->resizeDown(width: $maxWidth)
			->toPng();
			

        // Generate a unique filename
        $filename = uniqid('sign_') . '.png';
        $path = $directory . '/' . $filename;

        // Store the image
        if (!Storage::put($path, (string) $image)) {
			throw new \Exception('Failed to store signature image');
        }

        return $path;
    }
} 