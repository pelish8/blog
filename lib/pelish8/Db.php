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
            $query = $this->prepare('INSERT INTO articles (id, title, content, user_id, url_path, create_date) VALUES(:id, :title, :content, :user_id, :url_path, :date)');
            $userInfo = Session::sharedSession()->userInfo();
            $urlPath = str_replace(' ', '-', strtolower($title));
            $data = [
                ':id' => $this->uniqId(),
                ':title' => $title,
                ':content' => $article,
                ':user_id' => $userInfo[0],
                ':url_path' => $urlPath,
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
        $tags = preg_replace('!\s+!', ' ', $tags);
        $tags = preg_replace('/[^A-Za-z0-9\_\- ]/', '', $tags);

        $tagArray = explode(' ', $tags);
        $count = count($tagArray);

        $insertPlaceHolder = implode(',', array_fill(0, $count, '(?,?)'));

        // inser new tags
        $query = $this->prepare('INSERT IGNORE INTO tags (id, tag) VALUES' . $insertPlaceHolder);

        $i = 1;
        foreach ($tagArray as $val) {
            // $insert[] = ['id' => $this->uniqId(), 'tag' => $val];
            $query->bindValue($i++, $this->uniqId(), \PDO::PARAM_INT);
            $query->bindValue($i++, $val, \PDO::PARAM_STR);
        }
        $query->execute();

        // find all tags id
        $queryTag = $this->prepare('SELECT id FROM tags WHERE tag IN (' . implode(',', array_fill(0, $count, '?')) . ') GROUP BY id');
        $i = 1;
        foreach ($tagArray as $val) {
            $queryTag->bindValue($i++, $val);
        }
        $queryTag->execute();

        // save relationship
        $queryRelationship = $this->prepare('INSERT IGNORE INTO article_tag (article_id, tag_id) VALUES' . $insertPlaceHolder);
        $i = 1;
        while ($result = $queryTag->fetch(\PDO::FETCH_ASSOC)) {
            $queryRelationship->bindValue($i++, $articleId);
            $queryRelationship->bindValue($i++, $result['id']);
        }
        $queryRelationship->execute();
    }

    public function articles($pageNumber, $pageSize)
    {
        $sql = 'SELECT articles.*, GROUP_CONCAT(tags.tag ORDER BY tags.tag) AS tags, users.name AS author
                FROM articles
                LEFT JOIN article_tag ON article_tag.article_id = articles.id
                LEFT JOIN tags ON tags.id = article_tag.tag_id
                LEFT JOIN users ON users.id = articles.user_id
                GROUP BY articles.id
                ORDER BY articles.create_date DESC
                LIMIT :start, :end';

        $query = $this->prepare($sql);
        $pageNumber -= 1;
        $d = $pageNumber * $pageSize;
        $query->bindParam(':start', $d, \PDO::PARAM_INT);
        $query->bindParam(':end', $pageSize, \PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            // $totalRowCount = $this->query('SELECT FOUND_ROWS()')->fetchColumn();
            $totalRowCount = $this->query('SELECT COUNT(*) FROM articles')->fetchColumn(); // workaround mysql bug
            $result['totalRowCount'] = $totalRowCount;
            return $result;
        }

        return [];
    }

    public function article($date, $urlPath)
    {
        $sql = 'SELECT articles.*, GROUP_CONCAT(tags.tag ORDER BY tags.tag) AS tags, users.name AS author
                FROM articles
                LEFT JOIN article_tag ON article_tag.article_id = articles.id
                LEFT JOIN tags ON tags.id = article_tag.tag_id
                LEFT JOIN users ON users.id = articles.user_id
                WHERE articles.create_date = :date AND articles.url_path = :url_path';

        $query = $this->prepare($sql);

        $query->bindParam(':date', $date);
        $query->bindParam(':url_path', $urlPath);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
