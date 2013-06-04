<?php

namespace pelish8;

class Api extends ApiAbstract
{
    public function __construct($action)
    {
        $this->mapAppend([
            'register' => 'register',
            'login' => 'login',
            'create-article' => 'createArticle',
            'articles' => 'articles',
            'create-comment' => 'createComment',
            'comments' => 'comments'
        ]);

        parent::__construct($action);
    }
    /**
     * Register new user
     * @access public
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
            return $this->successResponse();
        } else if ($status === DB::DUBLICATE) {
            return $this->errorResponse(self::USER_EXISTS);
        }

        return $this->errorResponse(self::ERROR_SENDING_DATA);
    }

    /**
     * Log in user
     * @access public
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

            return $this->successResponse();
        }

        return $this->errorResponse(self::INVALID_CREDENTIALS);
    }

    /**
     * Create new article
     * @access public
     */
    public function createArticle()
    {
        if (!Session::sharedSession()->isLogIn()) {
            return $this->errorResponse(self::ACCESS_DENIED);
        }
        $post = $this->getPost('title', 'article', 'tags');
        $status = Db::sharedDb()->createArticle($post['title'], $post['article'], $post['tags']);

        if ($status === DB::OK) {
            return $this->successResponse();
        }

        return $this->errorResponse(self::ERROR_SENDING_DATA);
    }

    /**
     * Return list of articles
     * @access public
     */
    public function articles()
    {
        $get = $this->getGet('pageSize', 'pageNumber');
        $result = Db::sharedDb()->articles($get['pageNumber'], $get['pageSize']);

        return $this->successResponse($result);
    }

    /**
     * Create new comment
     * @access public
     */
    public function createComment()
    {
        $post = $this->getPost('name', 'comment', 'articleId');

        $status = Db::sharedDb()->createComment($post['articleId'], $post['comment'], $post['name']);

        if ($status === DB::OK) {
            return $this->successResponse();
        }

        return $this->errorResponse(self::ERROR_SENDING_DATA);

    }

    /**
     * Return list of comment for the article
     * @access public
     */
    public function comments()
    {
        $get = $this->getGet('articleId');
        $result = Db::sharedDb()->comments($get['articleId']);

        return $this->successResponse($result);

    }
}