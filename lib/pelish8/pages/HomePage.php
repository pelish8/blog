<?php
    
namespace pelish8\pages;

class HomePage extends \pelish8\Pages
{
    public function template()
    {
        return 'home.html';
    }
    
    public function __construct()
    {
        parent::__construct();
        $name = \pelish8\Session::sharedSession()->get(\pelish8\Configuration::SESSIN_USER_NAME);
        $this->setData('name', $name);
    }
}