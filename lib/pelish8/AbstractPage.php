<?php

namespace pelish8;

/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
abstract class AbstractPage
{

    /**
     * Data that will be use in tempalte.
     * @var array
     * @access protected
     */
    protected $templateData = [];

    /**
     * template name
     *
     * @abstract
     * @access public
     */
    abstract public function template();

    /**
     * __construct function
     *
     * @access public
     */
    public function __construct()
    {
        $this->templateData = [
            'host' => $_SERVER['HTTP_HOST'],
            'resources' => Configuration::PAGES_RESOURCES_PATH,
            'api' => Configuration::PAGES_API_URL,
            'blogTitle' => Configuration::BLOG_TITLE,
            'isLogIn' => Session::sharedSession()->isLogIn(),
            'config' => [
                'dateFormat' => Configuration::DATE_FORMAT
            ]
        ];
    }

    /**
     * Return data that will be used in template
     *
     * @access public
     * @return array
     */
    public function userData()
    {
        return $this->templateData;
    }

    /**
     * Set data that will be use in tempate
     *
     * @access public
     * @return void
     */
    public function setData($name, $value)
    {
        $this->templateData[$name] = $value;
    }

    /**
     * Append array to template data
     * @access public
     * @return void
     */
    public function appendData(array $data)
    {
        $this->templateData = array_merge($this->templateData, $data);
    }
}