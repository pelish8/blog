<?php

namespace pelish8;

/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
class Configuration
{
    const BLOG_TITLE = 'Title';

    const DB_TYPE = 'mysql';
    const DB_HOST = 'localhost';
    const DB_NAME = 'blog';
    const DB_USER_NAME = 'root';
    const DB_PASSWORD = 'root';

    const SESSIN_USER_LOG_IN_ID = 'userIsLogInId';
    const SESSIN_USER_NAME = 'userName';

    const SLIM_DEBUG = true;
    const SLIM_TEMPLATE_PATH = './resurse/templates';
    const SLIM_VIEW = '\Slim\Extras\Views\Twig';
    const SLIN_CACHE = './cache/templates';

    const PAGES_RESURSE_PATH = '/resurse';
    const PAGES_API_URL = '/api';

    const SECURITY_SALT = 'le50OYVCY&Qfc5e4e3ab0.2f99cd0a3jd30f8778502b5801ad7I303f6f4b4P4d5394b98ydedd55d09Vac6eab78c,04cb30124Dca1f51d72|2656934f9|2aba41';

    const DATE_FORMAT = 'd/m/Y G:i';
}