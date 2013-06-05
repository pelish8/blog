<?php

namespace pelish8;

/**
 * @package prelovac
 * @author  Aleksandar Stevic
 */
abstract class AbstractApi
{
    /**
     *
     * @const string [ACCESS_DENIED]
     */
    const ACCESS_DENIED = 'access-denied';

    /**
     *
     * @const string [NOT_FOUND]
     */
    const NOT_FOUND = 'not-found';

    /**
     *
     * @const string [PASSWORD_DO_NOT_MATCH]
     */
    const PASSWORD_DO_NOT_MATCH = 'password-do-hot-match';

    /**
     *
     * @const string [USER_EXISTS]
     */
    const USER_EXISTS = 'user-exists';

    /**
     *
     * @const string [ERROR_SENDING_DATA]
     */
    const ERROR_SENDING_DATA = 'error-sending-data';

    /**
     *
     * @const string [INVALID_PARAMS]
     */
    const INVALID_PARAMS = 'invalid-params';

    /**
     *
     * @const string [INVALID_CREDENTIALS]
     */
    const INVALID_CREDENTIALS = 'invalid-credentials';

    /**
     *
     * @const string [DATABASE_ERROR]
     */
    const DATABASE_ERROR = 'database-error';

    /**
     * Map http request action API mthod
     *
     * @var array
     * @access protected
     */
    protected $apiMap = [];

    /**
     * __construct function
     *
     * @param string [$action]
     * @return void
     */
    public function __construct($action)
    {
        try {
            $response = "";

            if (isset($this->apiMap[$action])) {
                $method = $this->apiMap[$action];
                $response = $this->$method();
            } else {
                $response = $this->errorResponse(self::NOT_FOUND);
            }

        } catch (Exception\InvalidParam $e) { // @TO_DO implement
            $response = $this->errorResponse(self::INVALID_PARAMS);
        } catch (\PDOException $e) {
            // log  'message: ' .$e->getMessage() . ' code: ' . $e->getCode() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            $response = $this->errorResponse(self::DATABASE_ERROR);
        } catch (\Exception $e) {
            // log 'message: ' . $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine();
            $response = $this->errorResponse(self::ERROR_SENDING_DATA);
        }

        $this->processResponse($response);
    }

    /**
     * Map request action to api method
     *
     * @param string [$action] request action
     * @param string  [$method]
     * @access public
     * @return void
     */
    public function map($action, $method)
    {
        $this->apiMap[$action] = $method;
    }

    /**
    * Append array of request action to api methods
    *
    * @param array [$mapArray]
    * @access public
    * @return void
    */
    public function mapAppend(array $mapArray)
    {
        $this->apiMap = array_merge($this->apiMap, $mapArray);
    }

    /**
     * Prepare success response
     *
     * @param mixed [$result]
     * @access public
     * @return array
     */
    public function successResponse($result = null)
    {
        $response = [
            'status' => 'ok'
        ];

        if ($result !== null) {
            $response['result'] = $result;
        }

        return $response;
    }
    /**
     * Prepare error response
     *
     * @param string [$reason]
     * @access public
     * @return array
     */
    public function errorResponse($reason)
    {
        return [
            'status' => 'error',
            'reason' => $reason,
        ];
    }

    /**
     * Convert php array to JSON
     *
     * @param array [$response]
     * @access public
     * @return void
     */
    public function processResponse(array $response)
    {
        echo json_encode($response);
    }

    /**
     * Process POST request data
     *
     * @access protected
     * @return array
     */
    protected function getPost()
    {
        $arguments = func_get_args();
        $post = [];
        foreach ($arguments as $name) {
            if (!isset($_POST[$name])) {
                throw new Exception\InvalidParam("Error Processing POST Request, some params are missing.", 1);
                return;
            }
            $post[$name] = $_POST[$name];
        }
        return $post;
    }

    /**
     * Process GET request data
     *
     * @access protected
     * @return array
     */
    protected function getGet()
    {
        $arguments = func_get_args();
        $get = [];
        foreach ($arguments as $name) {
            if (!isset($_GET[$name])) {
                throw new Exception\InvalidParam("Error Processing GET Request, some params are missing.", 1);
                return;
            }
            $get[$name] = $_GET[$name];
        }
        return $get;
    }

    /**
     * Hash string with sha512 and salt from configuration
     *
     * @param mixed [$mixed]
     * @access protected
     * @return array
     */
    protected function hash($mixed)
    {
        return hash('sha512', Configuration::SECURITY_SALT . $mixed);
    }

}