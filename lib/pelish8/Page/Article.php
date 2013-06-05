<?php

namespace pelish8\Page;

/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
class Article extends \pelish8\AbstractPage
{
    private $pageNotFound = false;
    public function template()
    {
        if ($this->pageNotFound) {
            return '404.html';
        }

        return 'article.html';
    }

    public function __construct($date, $time, $title)
    {
        parent::__construct();
        
        $name = \pelish8\Session::sharedSession()->get(\pelish8\Configuration::SESSIN_USER_NAME);
        $this->setData('name', $name);
        
        $fullDate = $date . ' ' . str_replace('-', ':', $time);
        $article = \pelish8\Db::sharedDb()->article($fullDate, $title);

        if (empty($article['id'])) {
            $this->pageNotFound = true;
            return;
        }
        $this->setData('article', $article);
    }
}