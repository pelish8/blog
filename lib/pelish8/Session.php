<?php

namespace pelish8;


/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
class Session
{
    /**
     *
     * @var /pelish8/Session
     * @access protected
     */
    protected static $instance = null;

    /**
     * __construct function
     *
     * @access protected
     * @return void
     */
    protected function __construct()
    {
        ini_set('session.gc-maxlifetime', Configuration::SESSION_TIMEOUT);
        session_cache_limiter('nocache');
        session_start();

        // set session timeout
        if ($this->exist('sessionLastActivity') && (time() - $this->get('sessionLastActivity') > Configuration::SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
        }

        $this->set('sessionLastActivity', time());

        if (!$this->exist('sessionCreated')) {
            $this->set('sessionCreated', time());
        } else if (time() - $this->get('sessionCreated') > Configuration::SESSION_TIMEOUT) {
            session_regenerate_id(true);
            $this->set('sessionCreated', time());
        }
    }

    /**
     * return instance of /pelish8/Session
     *
     * @access public
     * @return /pelish8/Session
     */
    public static function sharedSession()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * set session
     *
     * @param string [$name] name of session variable
     * @param string [$value] session value
     * @access public
     * @return void
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * get session value
     *
     * @param string [$name] name of session
     * @access public
     * @return mixed
     */
    public function get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return null;
    }

    /**
     * check if session exist
     *
     * @param string [$name] session name
     * @access public
     * @return boolean
     */
    public function exist($name)
    {
        if (isset($_SESSION[$name])) {
            return true;
        }

        return false;
    }

    /**
     * destroy session
     *
     * @param string [$name] name of session that should be destroyed
     * @access public
     * @return void
     */
    public function destroy($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Check if user is log in.
     *
     * @access public
     * @return boolean
     */
    public function isLogIn()
    {
        if ($this->exist(Configuration::SESSIN_USER_LOG_IN_ID)) {
            return true;
        }

        return false;
    }

    /**
     * setLogInSession function
     *
     * @param string [$id] user id
     * @param striong [$name] user name
     * @access public
     * @return void
     */
    public function setLogInSession($id, $name)
    {
        $this->set(Configuration::SESSIN_USER_LOG_IN_ID, $id);
        $this->set(Configuration::SESSIN_USER_NAME, $name);
    }

    /**
     * userLogOut function
     *
     * @access public
     * @return void
     */
    public function userLogOut()
    {
        session_unset();
        session_destroy();
    }

    /**
     * return user data saved in session
     *
     * @access public
     * @return array
     */
    public function userInfo()
    {
        return [
            'id' => $this->get(Configuration::SESSIN_USER_LOG_IN_ID),
            'name' => $this->get(Configuration::SESSIN_USER_NAME)
        ];
    }
}
