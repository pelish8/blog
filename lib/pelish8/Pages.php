<?php

namespace pelish8;

abstract class Pages
{

    /**
     *
     *
     */
    protected $templateData = [];

    /**
     *
     *
     */
    protected $page = null;

    abstract public function template();

    /**
     *
     *
     */
    public function __construct()
    {
        $this->templateData = [
            'host' => $_SERVER['HTTP_HOST'],
            'resurse' => Configuration::PAGES_RESURSE_PATH,
            'api' => Configuration::PAGES_API_URL
        ];
    }

    /**
     * return data that will be used in template
     *
     */
    public function userData()
    {
        return $this->templateData;
    }

    /**
     * set date
     *
     */
    public function setData($name, $value)
    {
        $this->templateData[$name] = $value;
    }

    /**
     * append array to template data
     *
     */
    public function appendData(array $data)
    {
        $this->templateData = array_merge($this->templateData, $data);
    }
}