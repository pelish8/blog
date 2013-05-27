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

            static::$instance = new static(Configuration::DB_TYPE . ':host=' . Configuration::DB_HOST . ';dbname=' . Configuration::DB_NAME,
                                            Configuration::DB_USER_NAME,
                                            Configuration::DB_PASSWORD,
                                            [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);

            static::$instance->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
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

    public function createArticle($title, $article, $tags)
    {
        try {
            $this->beginTransaction();
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

            $this->addTags($tags, $data[':id']);

            $result = $query->errorCode();

            $this->commit();
            return $result;
        } catch (\PDOException $e) {
            $db->rollBack();
            return $query->errorCode();
        }

    }

    protected function addTags($tags, $articleId)
    {
        // clean string
        $tags = trim($tags);
        $tags = preg_replace('!\s+!', ' ', $input);
        $tags = preg_replace('/[^A-Za-z0-9\_\- ]/', '', $tags);

        $query = $this->prepare('INSERT IGNORE INTO tags (id, tag) VALUES(:id, :tag)');

        $queryRelationship = $this->prepare('INSERT IGNORE INTO article_tag (article_id, tag_id) VALUES(:article_id, :tag_id)');
        $queryRelationship->bindValue(':article_id', $articleId, \PDO::PARAM_INT);
        
        $queryTag = $this->prepare('SELECT id FROM tags WHERE tag=:tag');

        $tagArray = explode(' ', $tags);
        $count = count($tagArray);

        if ($count > 0) { // @TO_DO find better solution
            foreach ($tagArray as $tag) {

                $queryTag->bindValue(':tag', $tag);
                $queryTag->execute();
                $result = $queryTag->fetch(\PDO::FETCH_ASSOC);

                if (!isset($result['id'])) {
                    $id = $this->uniqId();
                    $query->bindValue(':id', $id, \PDO::PARAM_INT);
                    $query->bindValue(':tag', $tag, \PDO::PARAM_INT);
                    $query->execute();
                } else {
                    $id =$result['id'];
                }
                
                $queryRelationship->bindValue(':tag_id', $id, \PDO::PARAM_STR);
                $queryRelationship->execute();
            }
        }
    }
    
    public function articles($pageNumber, $pageSize)
    {
        $sql = 'SELECT articles.*, GROUP_CONCAT(tags.tag ORDER BY tags.tag) AS tags, users.name AS name
                FROM articles 
                LEFT JOIN article_tag ON article_tag.article_id = articles.id 
                LEFT JOIN tags ON tags.id = article_tag.tag_id
                LEFT JOIN users ON users.id = articles.user_id
                GROUP BY articles.id LIMIT :start, :end';

        $query = $this->prepare($sql);
        $pageNumber -= 1;
        $d = $pageNumber * $pageSize;
        $query->bindParam(':start', $d, \PDO::PARAM_INT);
        $query->bindParam(':end', $pageSize, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        }
        
        return [];
    }
}
