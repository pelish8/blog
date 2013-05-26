<?php

namespace pelish8;

class Api extends \pelish8\ApiController
{   
    /**
     *
     *
     */
    public function register()
    {
        $post = $this->getPost('name', 'email', 'password', 'confirmPassword');
        // hash
        if ($post['password'] !== $post['confirmPassword']) {
            return $this->errorResponse(self::PASSWORD_DO_NOT_MATCH);
        }

        $password = $this->hash($post['password']);;
        $status = Db::sharedDb()->createUser($post['name'], $post['email'], $password);

        if ($status === DB::OK) {
            return $this->succesResponse();
        } else if ($status === DB::DUBLICATE) {
            return $this->errorResponse(self::USER_EXISTS);
        }

        return $this->errorResponse(self::ERROR_SENDIND_DATA);
    }

    /**
     *
     *
     */
    public function login()
    {
        $post = $this->getPost('email', 'password');
        
        $password = $this->hash($post['password']);
        $result = Db::sharedDb()->userLogIn($post['email'], $password);

        if (isset($result['id'])) {
            // set sessions
            $session = Session::sharedSession();
            $session->setLogInSession($result['id'], $result['name']);
        
            return $this->succesResponse();
        }
        
        return $this->errorResponse(self::INVALID_CREDENTIALS);        
    }
    
    /**
     *
     *
     */
    public function create()
    {
        $post = $this->getPost('title', 'article', 'tags');
        $status = Db::sharedDb()->createArticle($post['title'], $post['article'], $post['tags']);
        
        if ($status === DB::OK) {
            return $this->succesResponse();
        }
        
        return $this->errorResponse(self::ERROR_SENDIND_DATA);
    }
}