<?php

namespace pelish8;

class Session
{
    /**
     *
     * @var /pelish8/Session
     * @access protected 
     */
    protected static $instance = null;
    
    protected function __construct()
    {
        session_cache_limiter(false);
        session_start();
        
        // set timeout session
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
     * @param string $name name of session variable
     * @param string $value session value
     * @access public
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }
    
    /**
     * get session value
     *
     * @param string $name name of session 
     * @access public
     * @return session value or null
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
     * @param string $name name of session that should be destroyed
     * @access public
     */
    public function destroy($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }
    
    /**
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
     *
     * @access public
     * @return void
     */
    public function setLogInSession($id, $name)
    {
        $this->set(Configuration::SESSIN_USER_LOG_IN_ID, $id);
        $this->set(Configuration::SESSIN_USER_NAME, $name);
    }
    
    /**
     *
     * @access public
     * @return void
     */
    public function userLogOut()
    {
        $this->destroy(Configuration::SESSIN_USER_LOG_IN_ID);
        $this->destroy(Configuration::SESSIN_USER_NAME);
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
