<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublishProductImages extends Command
{
    protected $signature = 'products:publish-images {--dry-run : Show what would change without copying files}';

    protected $description = 'Copy product images from private storage to the public disk and update their paths';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $private = Storage::disk('local');
        $public = Storage::disk('public');

        $moved = 0;
        $alreadyPublic = 0;
        $missing = 0;
        $remote = 0;

        Product::query()
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->each(function (Product $product) use ($private, $public, $dryRun, &$moved, &$alreadyPublic, &$missing, &$remote): void {
                $image = trim($product->image);

                if (filter_var($image, FILTER_VALIDATE_URL)) {
                    $remote++;

                    return;
                }

                $path = str_replace('\\', '/', ltrim($image, '/'));
                $path = preg_replace('#^storage/#', '', $path);
                $path = preg_replace('#^public/#', '', $path);

                if ($public->exists($path)) {
                    $alreadyPublic++;

                    return;
                }

                if (! $private->exists($path)) {
                    $missing++;
                    $this->warn("Product #{$product->id} ({$product->name}): missing image [{$path}]");

                    return;
                }

                $target = str_starts_with($path, 'products/')
                    ? $path
                    : 'products/'.basename($path);

                $this->line("Product #{$product->id} ({$product->name}): {$path} -> public/{$target}");

                if (! $dryRun) {
                    $stream = $private->readStream($path);

                    if ($stream === false) {
                        $missing++;
                        $this->error("Could not read [{$path}]");

                        return;
                    }

                    $public->writeStream($target, $stream, ['visibility' => 'public']);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    $product->update(['image' => $target]);
                }

                $moved++;
            });

        $this->newLine();
        $this->info(sprintf(
            '%s %d image(s), %d already public, %d remote URL(s), %d missing.',
            $dryRun ? 'Would copy' : 'Copied',
            $moved,
            $alreadyPublic,
            $remote,
            $missing,
        ));

        return $missing > 0 && $moved === 0 ? self::FAILURE : self::SUCCESS;
    }
}
