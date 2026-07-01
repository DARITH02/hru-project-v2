<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class PhotoService
{
    public function store(Model $owner, UploadedFile $file, ?int $uploadedBy, string $photoType = 'gallery', bool $isPrimary = false): Photo
    {
        $path = $file->store($this->directory($owner), 'public');

        if ($isPrimary || !$owner->photos()->where('is_primary', true)->exists()) {
            $owner->photos()->update(['is_primary' => false]);
            $isPrimary = true;
        }

        return $owner->photos()->create([
            'photo_type' => $photoType,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => basename($path),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize() ?: 0,
            'is_primary' => $isPrimary,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function storeMany(Model $owner, array $files, ?int $uploadedBy, string $photoType = 'gallery'): Collection
    {
        return collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->values()
            ->map(fn (UploadedFile $file, int $index) => $this->store(
                $owner,
                $file,
                $uploadedBy,
                $photoType,
                $index === 0 && !$owner->photos()->where('is_primary', true)->exists()
            ));
    }

    private function directory(Model $owner): string
    {
        $name = class_basename($owner);

        return 'photos/' . strtolower($name) . 's/' . $owner->getKey();
    }
}
