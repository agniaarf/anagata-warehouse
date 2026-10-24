<?php

namespace App\Providers;

use App\Models\Content;
use App\Observers\ContentObserver;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Content::observe(ContentObserver::class);

        // Same construction as FilesystemManager::createLocalDriver(), except the
        // adapter gets an explicit ExtensionMimeTypeDetector — Laravel's built-in
        // 'local' driver leaves this null, which makes Flysystem default to a
        // FinfoMimeTypeDetector that needs the fileinfo PHP extension. See
        // config/filesystems.php's 'public' disk for why this exists.
        Storage::extend('public_local', function ($app, array $config) {
            $visibility = PortableVisibilityConverter::fromArray(
                $config['permissions'] ?? [],
                $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE
            );

            $adapter = new LocalFilesystemAdapter(
                $config['root'],
                $visibility,
                $config['lock'] ?? LOCK_EX,
                LocalFilesystemAdapter::DISALLOW_LINKS,
                new ExtensionMimeTypeDetector(),
            );

            $flysystem = new Flysystem($adapter, [
                'directory_visibility' => $config['directory_visibility'] ?? null,
                'disable_asserts' => $config['disable_asserts'] ?? null,
                'temporary_url' => $config['temporary_url'] ?? null,
                'url' => $config['url'] ?? null,
                'visibility' => $config['visibility'] ?? null,
            ]);

            return new FilesystemAdapter($flysystem, $adapter, $config);
        });

        Password::defaults(function () {
            $rule = Password::min(10)->mixedCase()->numbers();

            // uncompromised() calls the haveibeenpwned API — restrict that network
            // call to production so local dev/tests stay fast and offline-friendly.
            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
