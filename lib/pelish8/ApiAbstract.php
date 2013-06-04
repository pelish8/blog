<?php

namespace pelish8;

abstract class ApiAbstract
{
    const ACCESS_DENIED = 'access-denied';
    const NOT_FOUND = 'not-found';
    const PASSWORD_DO_NOT_MATCH = 'password-do-hot-match';
    const USER_EXISTS = 'user-exists';
    const ERROR_SENDING_DATA = 'error-sending-data';
    const INVALID_PARAMS = 'invalid-params';
    const INVALID_CREDENTIALS = 'invalid-credentials';

    protected $apiMap = [];
    /**
     *
     *
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

        } catch (Exception\InvalidParamException $e) { // @TO_DO implement
            $response = $this->errorResponse(self::INVALID_PARAMS);
        } catch (\Exception $e) {
            // echo $e->getMessage() . ' -- ' . $e->getFile() . ' -- ' . $e->getLine();
            $response = $this->errorResponse(self::ERROR_SENDING_DATA);
        }

        $this->processResponse($response);
    }

    /**
     *
     *
     *
     */
    public function map($path, $method)
    {
        $this->apiMap[$path] = $method;
    }

    /**
    *
    *
    *
    */
    public function mapAppend(array $mapArray)
    {
        $this->apiMap = array_merge($this->apiMap, $mapArray);
    }

    /**
     * Prepare success response
     * @access public
     */
    public function successResponse($result = null)
    {
        $response = ['status' => 'ok'];

        if ($result !== null) {
            $response['result'] = $result;
        }

        return $response;
    }
    /**
     * Prepare error response
     * @access public
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
     * @access public
     */
    public function processResponse($response)
    {
        echo json_encode($response);
    }

    /**
     * Process POST request data
     * @access protected
     */
    protected function getPost()
    {
        $arguments = func_get_args();
        $post = [];
        foreach ($arguments as $name) {
            if (!isset($_POST[$name])) {
                throw new \pelish8\Exception\InvalidParamException("Error Processing POST Request, some params are missing.", 1);

                return;
            }
            $get[$name] = $_POST[$name];
        }
        return $post; // @TO_DO implement
    }

    /**
     * Process GET request data
     * @access protected
     */
    protected function getGet()
    {
        $arguments = func_get_args();
        $get = [];
        foreach ($arguments as $name) {
            if (!isset($_GET[$name])) {
                throw new \pelish8\Exception\InvalidParamException("Error Processing GET Request, some params are missing.", 1);
                return;
            }
            $get[$name] = $_GET[$name];
        }
        return $get; // @TO_DO implement
    }

    /**
     * Hash strnig with sha512 adn salt from configuration
     *
     */
    protected function hash($string)
    {
        return hash('sha512', Configuration::SECURITY_SALT . $string);
    }

}