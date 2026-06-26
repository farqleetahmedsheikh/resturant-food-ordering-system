<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageUpload
{
    public static function validationRules(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            'mimetypes:image/jpeg,image/png',
            'max:2048',
            'dimensions:max_width=3000,max_height=3000',
        ];
    }

    public static function store(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($directory, $filename, 'public');

        if (! $path) {
            throw new RuntimeException('Unable to store uploaded image.');
        }

        if ($oldPath && $oldPath !== $path) {
            self::delete($oldPath);
        }

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
