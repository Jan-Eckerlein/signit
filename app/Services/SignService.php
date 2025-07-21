<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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
        // Load and process image
        $image = Image::read($uploadedFile)
            ->resizeDown(width: $maxWidth)
            ->toPng();

        // Save to a temporary file for transparency check
        $tmpPath = tempnam(sys_get_temp_dir(), 'sign_') . '.png';
        file_put_contents($tmpPath, (string) $image);

        // Fast check: can the PNG be transparent?
        if (!$this->isPngPotentiallyTransparent($tmpPath)) {
            @unlink($tmpPath);
            throw new \Exception('Signature image must have a transparent background. (no alpha channel)');
        }
        // If it can, check if it actually has any transparent pixels
        if (!$this->hasActualTransparency($tmpPath)) {
            @unlink($tmpPath);
            throw new \Exception('Signature image must have a transparent background. (no transparent pixels)');
        }

        // Generate a unique filename for storage
        $filename = uniqid('sign_') . '.png';
        $path = $directory . '/' . $filename;

        // Store the image
        if (!Storage::put($path, (string) $image)) {
            @unlink($tmpPath);
            throw new \Exception('Failed to store signature image');
        }

        @unlink($tmpPath); // Clean up temp file

        return $path;
    }

    public function isPngPotentiallyTransparent(string $filePath): bool
    {
        // 32-bit PNGs (alpha channel)
        if ((ord(file_get_contents($filePath, false, null, 25, 1)) & 4) > 0) {
            return true;
        }
        // 8-bit PNGs (palette-based)
        $fd = fopen($filePath, 'r');
        $continue = true;
        $plte = false;
        $trns = false;
        $idat = false;
        while ($continue === true) {
            $continue = false;
            $line = fread($fd, 1024);
            if ($plte === false) {
                $plte = (stripos($line, 'PLTE') !== false);
            }
            if ($trns === false) {
                $trns = (stripos($line, 'tRNS') !== false);
            }
            if ($idat === false) {
                $idat = (stripos($line, 'IDAT') !== false);
            }
            if ($idat === false && !($plte === true && $trns === true)) {
                $continue = true;
            }
        }
        fclose($fd);
        return ($plte === true && $trns === true);
    }

    public function hasActualTransparency(string $filePath, int $sampleStep = 9, int $resizeTo = 50): bool
    {
        $img = imagecreatefrompng($filePath);
        $width = imagesx($img);
        $height = imagesy($img);
        // Create a smaller image for sampling
        $smallImg = imagecreatetruecolor($resizeTo, $resizeTo);
        imagealphablending($smallImg, false);
        imagesavealpha($smallImg, true);
        imagecopyresampled($smallImg, $img, 0, 0, 0, 0, $resizeTo, $resizeTo, $width, $height);
        for ($x = 0; $x < $resizeTo; $x += $sampleStep) {
            for ($y = 0; $y < $resizeTo; $y += $sampleStep) {
                $rgba = imagecolorat($smallImg, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha > 0) {
                    imagedestroy($img);
                    imagedestroy($smallImg);
                    return true;
                }
            }
        }
        imagedestroy($img);
        imagedestroy($smallImg);
        return false;
    }
} 