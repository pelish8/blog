<?php

namespace pelish8;

/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
class Db extends \PDO
{
    /**
     *
     * @const string [OK] status ok
     */
    const OK = '00000';

    /**
     *
     * @const string [DUPLICATE] status duplicate
     */
    const DUPLICATE = '23000';

    /**
     *
     * @const string [TRANSACTION_ERROR] status transaction error
     */
    const TRANSACTION_ERROR = '-1';

    /**
     *
     * @var $instance
     * @static
     * @access protected
     */
    protected static $instance = null;

    /**
     * sharedDb function
     *
     * @static
     * @access public
     * @return \pelish8\Db
     */
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

    /**
     * Uniq ID
     *
     * @access public
     * @return string
     */
    public function uniqId()
    {
        return uniqid();
    }

    /**
     * Register new user
     *
     * @param string [$name] user name
     * @param string [$email] user email
     * @param string [$password] hash user password
     * @access public
     * @return string
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

    /**
     * Log in user
     *
     * @param string [$email] user imail
     * @param string [$password] user password
     * @access public
     * @return array
     */
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

    /**
     * Create new article
     *
     * @param string [$title] article title
     * @param string [$article] article content
     * @param string [$tags] article tags
     * @access public
     * @return string
     */
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
                ':user_id' => $userInfo['id'],
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
            return self::TRANSACTION_ERROR;
        }

    }

    /**
     * Register new user
     *
     * @param string [$tags] tags to sava
     * @param string [$articleId] id of article related with tags
     * @access protected
     * @return void
     */
    protected function addTags($tags, $articleId)
    {
        // clean string
        $tags = strtolower(trim($tags));
        $tags = preg_replace('!\s+!', ' ', $tags);
        $tags = preg_replace('/[^A-Za-z0-9\_\- ]/', '', $tags);

        $tagArray = array_unique(explode(' ', $tags));
        $count = count($tagArray);

        $insertPlaceHolder = implode(',', array_fill(0, $count, '(?,?)'));

        // inser new tags
        $query = $this->prepare('INSERT IGNORE INTO tags (id, tag) VALUES' . $insertPlaceHolder);

        $i = 1;
        foreach ($tagArray as $val) {
            $query->bindValue($i++, $this->uniqId(), \PDO::PARAM_INT);
            $query->bindValue($i++, $val, \PDO::PARAM_STR);
        }
        $query->execute();

        // find all tags id
        $queryTag = $this->prepare('SELECT * FROM tags WHERE tag IN (' . implode(',', array_fill(0, $count, '?')) . ') GROUP BY id');
        $i = 1;
        foreach ($tagArray as $val) {
            $queryTag->bindValue($i++, $val);
        }
        $queryTag->execute();
        // save relationship
        $queryRelationship = $this->prepare('INSERT IGNORE INTO article_tag (article_id, tag_id) VALUES' . $insertPlaceHolder);
        $i = 1;
        while ($result = $queryTag->fetch(\PDO::FETCH_ASSOC)) {
            $queryRelationship->bindParam($i, $articleId);
            $i++;
            $queryRelationship->bindParam($i, $result['id']);
            $i++;
        }
        $queryRelationship->execute();
        $queryRelationship->errorCode();
    }

    /**
     * List of articles
     *
     * @param int [$pageNumber] number of current page
     * @param int [$pageSize] size of page
     * @access public
     * @return  array
     */
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

    /**
     * Single article
     *
     * @param string [$date] date when article was created
     * @param string [$urlPath] url path
     * @access public
     * @return array
     */
    public function article($date, $urlPath)
    {
        $sql = 'SELECT articles.*, GROUP_CONCAT(tags.tag ORDER BY tags.tag SEPARATOR \' \') AS tags, users.name AS author
                FROM articles
                LEFT JOIN article_tag ON article_tag.article_id = articles.id
                LEFT JOIN tags ON tags.id = article_tag.tag_id
                LEFT JOIN users ON users.id = articles.user_id
                WHERE articles.create_date = :date AND articles.url_path = :url_path';

        $query = $this->prepare($sql);

        $query->bindParam(':date', $date);
        $query->bindParam(':url_path', $urlPath);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create comment
     *
     * @param string [$articleId]
     * @param string [$comment]
     * @param string $name name of user that created article
     * @access public
     * @return string
     */
    public function createComment($articleId, $comment, $name, $parentId)
    {
            $session = Session::sharedSession();
            if ($session->isLogIn()) {
                $userInfo = $session->userInfo();
                $userId = $userInfo['id'];
            } else {
                $userId = null;
            }

            if (empty($parentId)) {
                $parentId = null;
            }

            $query = $this->prepare('INSERT INTO comments (id, comment, article_id, parent_id, user_id, user_name, create_date)
                                    VALUES (:id, :comment, :article_id, :parent_id, :user_id, :user_name, :create_date)');
            $data = [
                ':id' => $this->uniqId(),
                ':comment' => $comment,
                ':article_id' => $articleId,
                ':parent_id' => $parentId,
                ':user_id' => $userId,
                ':user_name' => $name,
                ':create_date' => gmdate('Y-m-d H:i:s')
            ];

            $query->execute($data);
            return $query->errorCode();
    }

    /**
     * List of all comments associated with article
     *
     * @param string [$articleId]
     * @access public
     * @return array
     */
    public function comments($articleId)
    {
        $sql = 'SELECT comments.id AS id, comments.comment AS comment, COALESCE(users.name, comments.user_name) AS author,
                comments.create_date AS createDate, comments.parent_id AS parent_id
                FROM comments
                LEFT JOIN users ON users.id = comments.user_id
                WHERE comments.article_id = :article_id
                ORDER BY comments.create_date ASC';

        $query = $this->prepare($sql);

        $query->bindParam(':article_id', $articleId);
        $query->execute();

        $start = microtime(true);
        $comments = [];
        $keyToRemove = [];

        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $comments[$row['id']] = $row;
        }
        // comment hierarchy
        foreach ($comments as &$row) {
            if ($row['parent_id'] != 0) {
                $comments[$row['parent_id']]['children'][] = &$row;
                $keyToRemove[] = $row['id'];
            } else {
                $comments[$row['id']] = $row;
            }
        }
        // remove children comments from array root
        foreach ($keyToRemove as $key) {
            unset($comments[$key]);
        }

        if (!empty($comments)) {
            return array_values($comments);
        }

        return [];
    }
}
