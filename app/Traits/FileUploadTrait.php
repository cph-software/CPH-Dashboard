<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    /**
     * Upload a file to a specific disk and directory.
     *
     * @param object $file
     * @param string $path
     * @param string|null $oldFile
     * @param string $disk
     * @return string|null
     */
    public function uploadFile($file, $path = 'uploads', $oldFile = null, $disk = 'public')
    {
        if ($file) {
            // Delete old file if exists
            if ($oldFile) {
                $this->deleteFile($oldFile, $disk);
            }

            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            return $file->storeAs($path, $fileName, $disk);
        }

        return $oldFile;
    }

    /**
     * Delete a file from disk.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function deleteFile($path, $disk = 'public')
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
