<?php

namespace pelish8;

class Configuration
{
    protected static $instance = null;

    protected $bdType = 'mysql';

    protected $databaseName = 'blog';

    protected $dbPassword = 'root';

    protected $dbPassword = 'root';

    protected function __construct() {}

    public static function sharedConfirugation()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}