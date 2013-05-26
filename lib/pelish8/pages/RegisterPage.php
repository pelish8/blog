<?php
    
namespace pelish8\pages;

class RegisterPage extends \pelish8\Pages
{
    public function template()
    {
        return 'register.html';
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->setData('nickName', 'Sale');
    }
}