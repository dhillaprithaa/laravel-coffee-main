<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImage
{
    /**
     * Image columns and their storage config.
     */
    public function images(): array
    {
        return [
            'image' => [
                'disk' => 'public',
                'folder' => 'images'
            ],
        ];
    }

    /**
     * Get the storage config for the given image column.
     */
    protected function imageConfig(string $column): ?object
    {
        $images = $this->images();
        if (!array_key_exists($column, $images)) return null;
        return (object) $images[$column];
    }

    /**
     * Delete all image files when the model is deleted.
     */
    public static function bootHasImage(): void
    {
        static::deleting(function ($model) {
            $images = $model->images();
            $columns = array_keys($images);
            foreach ($columns as $column) {
                $model->deleteImageFile($column);
            }
        });
    }

    /**
     * Store an uploaded file and return the storage path.
     */
    public function uploadImage(UploadedFile $file, string $column): string
    {
        $config = $this->imageConfig($column);
        return $file->store($config->folder, $config->disk);
    }

    /**
     * Delete old file, upload new one, set the attribute.
     */
    public function updateImage(UploadedFile $file, string $column): string
    {
        $this->deleteImageFile($column);
        $path = $this->uploadImage($file, $column);
        $this->setAttribute($column, $path);
        return $path;
    }

    /**
     * Delete the image file for the given column.
     */
    public function deleteImageFile(string $column): void
    {
        $config = $this->imageConfig($column);
        $value = $this->getAttribute($column);

        if ($config && $value) {
            Storage::disk($config->disk)->delete($value);
        }
    }

    /**
     * Get the full URL for the image column, or null if empty.
     */
    public function getImageUrl(string $column): ?string
    {
        $config = $this->imageConfig($column);
        $value = $this->getAttribute($column);

        if (!$config || !$value) return null;
        return Storage::disk($config->disk)->url($value);
    }
}
