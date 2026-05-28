<?php

namespace App\Services\Xray;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class XrayManager extends Manager
{
    /**
     * Get a connection instance.
     *
     * @param  string|null  $name
     * @return \App\Services\Xray\XrayService
     */
    public function connection($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('xray.default');
    }

    /**
     * Set the default driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->config->set('xray.default', $name);
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        $config = $this->config->get("xray.connections.{$driver}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Xray connection [{$driver}] not configured.");
        }

        return new XrayService($config);
    }
}
