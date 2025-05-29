<?php

declare(strict_types=1);

namespace Api\Providers;

use Api\Repositories\TranslationUnitRepository;
use Api\Repositories\TranslationUnitRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TranslationUnitRepositoryInterface::class,
            TranslationUnitRepository::class
        );
    }
} 