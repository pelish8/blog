<?php

namespace pelish8;

abstract class Pages
{

    /**
     * Data that will be use in tempalte.
     * @access protected
     */
    protected $templateData = [];

    /**
     *
     *
     */
    abstract public function template();

    /**
     * Prepare default date for template
     * @access public
     */
    public function __construct()
    {
        $this->templateData = [
            'host' => $_SERVER['HTTP_HOST'],
            'resurse' => Configuration::PAGES_RESURSE_PATH,
            'api' => Configuration::PAGES_API_URL,
            'blogTitle' => Configuration::BLOG_TITLE,
            'isLogIn' => Session::sharedSession()->isLogIn(),
            'config' => [
                'dateFormat' => Configuration::dateFormat
            ]
        ];
    }

    /**
     * Return data that will be used in template
     * @access public
     * @return array
     */
    public function userData()
    {
        return $this->templateData;
    }

    /**
     * Set data that will be use in tempate
     * @access public
     */
    public function setData($name, $value)
    {
        $this->templateData[$name] = $value;
    }

    /**
     * Append array to template data
     * @access public
     */
    public function appendData(array $data)
    {
        $this->templateData = array_merge($this->templateData, $data);
    }
}