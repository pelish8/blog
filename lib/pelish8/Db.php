<?php

namespace pelish8;

class Db extends \PDO
{
    const OK = '00000';
    const DUBLICATE = '23000';
    
    protected static $instance = null;

    public static function sharedDb()
    {
        if (static::$instance === null) {

            static::$instance = new static(Configuration::DB_TYPE . ':host=' . Configuration::DB_HOST . ';dbname=' . Configuration::DB_NAME, Configuration::DB_USER_NAME, Configuration::DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
        }

        return static::$instance;
    }
    
    public function uniqId()
    {
        return uniqid();
    }

    /**
     * Register new user
     *
     * @param string user Full name
     * @param string user email
     * @param password hash user password
     * @access public
     * @return mySql status code, '00000' = 'ok'
     */
    public function createUser($name, $email, $password)
    {
        $query = $this->prepare('INSERT INTO users (id, name, email, password, create_date) VALUES(:id, :name, :email, :password, :date)');

        $data = [
            ':id' => $this->uniqId(),
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':date' => gmdate('Y-m-d H:i:s')
        ];
        $query->execute($data);
        return $query->errorCode();
        
    }
    
    public function userLogIn($email, $password)
    {
        $query = $this->prepare('SELECT * FROM users WHERE email=:email AND password=:password');
        $data = [
            ':email' => $email,
            ':password' => $password
        ];
        
        $query->execute($data);
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        }
        return [];
    }
    
    public function createArticle($title, $article, $tags = '')
    {
        $query = $this->prepare('INSERT INTO articles (id, title, content, user_id, create_date) VALUES(:id, :title, :content, :user_id, :date)');
        $userInfo = Session::sharedSession()->userInfo();
        $data = [
            ':id' => $this->uniqId(),
            ':title' => $title,
            ':content' => $article,
            ':user_id' => $userInfo[0],
            ':date' => gmdate('Y-m-d H:i:s')
        ];
        
        $query->execute($data);
        $result = $query->errorCode();
        
        if ($result === self::OK) {
            return $this->addTags($tags);
        }
        
        return $result;
    }
    
    protected function addTags($tags)
    {   
        $tags = preg_replace('/[^A-Za-z0-9\_\-]/', '', $tags);
        
        $tagArray = explode(' ', $tags);
        
        if (count($tagsArray) > 0) {
            foreach ($tagArray as $tag) {
                
            }
        }
        
    }
}