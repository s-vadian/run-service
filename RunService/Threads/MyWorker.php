<?php

namespace RunService\Threads;

class MyWorker extends \Worker
{
    /**
     * @var DataProvider
     */
    private $provider;

    /**
     * @param DataProvider $provider
     */
    public function __construct(DataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Вызывается при отправке в Pool.
     */
    public function run() {}

    /**
     * Возвращает провайдера
     *
     * @return DataProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}