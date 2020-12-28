<?php defined('MW_PATH') || exit('No direct script access allowed');

class JeebConnection implements iGateway
{
    /**
     * string
     * base url for the JEEB.io base on the api version 3
     */
    const API_URL = 'https://core.jeeb.io/api/v3';

    private $apiKey;

    public function __construct(string $_apiKey)
    {
        $this->apiKey = $_apiKey;
    }
    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     * request payment
     */
    public function issue(array $data = [])
    {
        try {
            return $this->_request('/payments/issue', $data);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @param array $data
     * @return bool|mixed|string
     * @throws Exception
     */
    public function status(array $data = [])
    {
        try {
            return $this->_request('/payments/status', $data);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @param array $data
     * @return bool|mixed|string
     * @throws Exception
     */
    public function seal(array $data = [])
    {
        try {
            return $this->_request('/payments/seal', $data);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
    public static function redirect(string $token, bool $permanent = false)
    {
        header('Location: ' . 'https://core.jeeb.io/api/v3/payments/invoice?token='.$token, true, $permanent ? 301 : 302);
        exit();
    }
    /**
     * @param string $path
     * @param array $data
     * @return bool|mixed|string
     * @throws Exception
     */
    private function _request(string $path = '',array $data = [])
    {
        $post = json_encode($data);
        $ch = curl_init(self::API_URL . $path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'X-API-KEY:'.$this->apiKey,
                'Content-Length: ' . strlen($post))
        );

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $data = json_decode($result, true);
        if(!is_null($data)) {
            return $data;
        }
        return $result;
    }
}

