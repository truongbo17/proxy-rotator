<?php

namespace TruongBo\ProxyRotation;

use Illuminate\Support\ServiceProvider;

class ProxyRotationServiceProvider extends ServiceProvider
{
    /**
     * @var string $configPath
     */
    private string $configPath;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->configPath = dirname(__DIR__) . '/config/proxy.php';
    }


    public function boot()
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath)),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath,
            basename($this->configPath, '.php')
        );
    }
}
