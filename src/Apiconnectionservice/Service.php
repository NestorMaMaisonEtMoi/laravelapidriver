<?php

namespace Nestor\LaravelApidriver\Apiconnectionservice;

trait Service
{
    use Helper;

    protected $model;

    public function setModel(string $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return class_exists($this->model) ? new $this->model : null;
    }

    public function setCurl(string $url)
    {
        return $this->initCurl($url);
    }
}
