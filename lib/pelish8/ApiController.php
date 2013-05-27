<?php

namespace pelish8;

abstract class ApiController
{
    const ACCESS_DENIED = 'access-denied';
    const NOT_FOUND = 'not-found';
    const PASSWORD_DO_NOT_MATCH = 'password-do-hot-match';
    const USER_EXISTS = 'user-exists';
    const ERROR_SENDIND_DATA = 'error-sending-data';
    const INVALID_PARAMS = 'invalid-params';
    const INVALID_CREDENTIALS = 'invalid-credentials';

    /**
     *
     *
     */
    public function __construct($action)
    {
        try {
            $response = "";

            if (method_exists($this, $action)) {
                $response = $this->$action();
            } else {
                $response = $this->errorResponse(self::NOT_FOUND);
            }

        } catch (\Exception $e) {
            $response = $this->errorResponse(self::NOT_FOUND);
        } catch (\PostException $e) {
            $response = $this->errorResponse(self::INVALID_PARAMS);
        }

        $this->processResponse($response);
    }

    /**
     *
     *
     */
    public function succesResponse($result = null)
    {
        $response = ['status' => 'ok'];

        if ($result !== null) {
            $response['result'] = $result;
        }

        return $response;
    }
    /**
     *
     *
     */
    public function errorResponse($reason)
    {
        return [
            'status' => 'error',
            'reason' => $reason,
        ];
    }

    /**
     *
     *
     */
    public function processResponse($response)
    {
        echo json_encode($response);
    }

    /**
     *
     *
     */
    protected function getPost()
    {
        return $_POST;
    }


    /**
     *
     *
     */
    protected function hash($string)
    {
        return hash('sha512', Configuration::SECURITY_SALT . $string);
    }

}