<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'image', 'description', 'slug'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            Log::warning('Product image is empty.', [
                'product_id' => $this->getKey(),
                'product_name' => $this->name,
            ]);

            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $path = $this->normalizeImagePath($this->image);
        $publicDisk = Storage::disk('public');

        $candidates = array_values(array_unique(array_filter([
            $path,
            'products/'.basename($path),
            basename($path),
        ])));

        foreach ($candidates as $candidate) {
            if (! $publicDisk->exists($candidate)) {
                continue;
            }

            if ($candidate !== $path) {
                Log::info('Product image resolved using a fallback path.', [
                    'product_id' => $this->getKey(),
                    'image' => $this->image,
                    'resolved_path' => $candidate,
                    'url' => $publicDisk->url($candidate),
                ]);
            }

            return $publicDisk->url($candidate);
        }

        $privateDisk = Storage::disk('local');

        $existingOnPrivateDisk = array_values(array_filter(
            $candidates,
            fn (string $candidate): bool => $privateDisk->exists($candidate),
        ));

        if ($existingOnPrivateDisk !== []) {
            $source = $existingOnPrivateDisk[0];
            $target = str_starts_with($source, 'products/')
                ? $source
                : 'products/'.basename($source);

            try {
                $stream = $privateDisk->readStream($source);

                if ($stream !== false) {
                    $publicDisk->writeStream($target, $stream, ['visibility' => 'public']);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if ($this->exists) {
                        $this->forceFill(['image' => $target])->saveQuietly();
                    }

                    Log::info('Product image copied from the private disk to the public disk.', [
                        'product_id' => $this->getKey(),
                        'source' => $source,
                        'target' => $target,
                        'url' => $publicDisk->url($target),
                    ]);

                    return $publicDisk->url($target);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to copy product image to the public disk.', [
                    'product_id' => $this->getKey(),
                    'source' => $source,
                    'target' => $target,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Product image could not be found on the public disk.', [
            'product_id' => $this->getKey(),
            'product_name' => $this->name,
            'image_column' => $this->image,
            'looks_like_an_id' => ctype_digit((string) $this->image),
            'normalized_path' => $path,
            'checked_public_paths' => $candidates,
            'existing_private_paths' => $existingOnPrivateDisk,
            'public_disk_root' => config('filesystems.disks.public.root'),
            'public_disk_url' => config('filesystems.disks.public.url'),
            'public_storage_link_exists' => file_exists(public_path('storage')),
            'hint' => 'If the image exists on the private disk, run: php artisan products:publish-images',
        ]);

        return null;
    }

    private function normalizeImagePath(string $image): string
    {
        $path = str_replace('\\', '/', ltrim(trim($image), '/'));

        $prefixes = [
            'storage/app/public/',
            'app/public/',
            'storage/public/',
            'storage/',
            'public/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));

                break;
            }
        }

        return $path;
    }
}
