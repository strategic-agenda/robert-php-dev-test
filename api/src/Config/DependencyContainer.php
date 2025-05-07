<?php

namespace App\Config;

use PDO;
use App\Models\Language;
use App\Models\TranslationUnit;

/**
 * Simple dependency injection container
 */
class DependencyContainer
{
    /**
     * @var array
     */
    private $services = [];
    
    /**
     * @var array
     */
    private $singletons = [];
    
    /**
     * Register a service factory
     * 
     * @param string $id
     * @param callable $factory
     * @return self
     */
    public function register(string $id, callable $factory): self
    {
        $this->services[$id] = $factory;
        return $this;
    }
    
    /**
     * Register a singleton service
     * 
     * @param string $id
     * @param callable $factory
     * @return self
     */
    public function singleton(string $id, callable $factory): self
    {
        $this->register($id, function($container) use ($factory, $id) {
            if (!isset($this->singletons[$id])) {
                $this->singletons[$id] = $factory($container);
            }
            return $this->singletons[$id];
        });
        return $this;
    }
    
    /**
     * Get a service from the container
     * 
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get(string $id)
    {
        if (!isset($this->services[$id])) {
            throw new \Exception("Service not found: $id");
        }
        
        return $this->services[$id]($this);
    }
    
    /**
     * Create a database connection
     * 
     * @return PDO
     */
    private function createDatabaseConnection(): PDO
    {
        // Use Database class for connection
        $database = new Database();
        return $database;
    }
    
    /**
     * Build the container with default services
     * 
     * @return self
     */
    public static function build(): self
    {
        $container = new self();
        
        // Register database connection
        $container->singleton(PDO::class, function($c) {
            return $c->createDatabaseConnection();
        });
        
        // Register legacy models
        $container->singleton(TranslationUnit::class, function($c) {
            return new TranslationUnit($c->get(PDO::class));
        });
        
        $container->singleton(Language::class, function($c) {
            return new Language($c->get(PDO::class));
        });
        
        // Register repositories
        $container->singleton(\App\Interfaces\TranslationUnitRepositoryInterface::class, function($c) {
            return new \App\Repositories\TranslationUnitRepository($c->get(PDO::class));
        });
        
        $container->singleton(\App\Interfaces\LanguageRepositoryInterface::class, function($c) {
            return new \App\Repositories\LanguageRepository($c->get(PDO::class));
        });
        
        // Register services
        $container->singleton(\App\Interfaces\TranslationUnitServiceInterface::class, function($c) {
            return new \App\Services\TranslationUnitService(
                $c->get(\App\Interfaces\TranslationUnitRepositoryInterface::class)
            );
        });
        
        $container->singleton(\App\Interfaces\LanguageServiceInterface::class, function($c) {
            return new \App\Services\LanguageService(
                $c->get(\App\Interfaces\LanguageRepositoryInterface::class)
            );
        });
        
        // Register controllers
        $container->register(\App\Controllers\TranslationUnitController::class, function($c) {
            return new \App\Controllers\TranslationUnitController(
                $c->get(\App\Interfaces\TranslationUnitServiceInterface::class)
            );
        });
        
        $container->register(\App\Controllers\LanguageController::class, function($c) {
            return new \App\Controllers\LanguageController(
                $c->get(\App\Interfaces\LanguageServiceInterface::class)
            );
        });
        
        return $container;
    }
} 