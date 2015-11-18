<?php

if(!defined('ENTRY_POINT')) return false;

require_once('./config.php');


class Socket {
    private $handle = null;

    public function fsockopen($hostname, $port = -1, &$errno = 0, &$errstr = '', $timeout = null)
    {
        $this->handle = fsockopen(
            $hostname,
            $port,
            $errno,
            $errstr,
            (is_null($timeout)
             ? ini_get("default_socket_timeout")
             : $timeout));

        if ($this->handle != false && $errno === 0 && $errstr === '') {
            return $this->handle;
        }
        return false;
    }
    
    public function fwrite($string, $length = null)
    {
        return fwrite($this->handle, $string, (is_null($length) ? strlen($string) : $length));
    }
    
    
    public function fgets($length = null)
    {
        return fgets($this->handle, $length);
    }
    
    public function feof()
    {
        return feof($this->handle);
    }
    
    public function fclose()
    {
        return fclose($this->handle);
    }
}


class ReCaptchaVerify {
    const RECAPTCHA_HOST = 'www.google.com';
    const SITE_VERIFY_PATH = '/recaptcha/api/siteverify';

    const APIKEY = '';
    const SECRET = RECAPTCHA_SECRET;

    const BAD_REQUEST = '{"success": false, "error-codes": ["invalid-request"]}';
    const BAD_RESPONSE = '{"success": false, "error-codes": ["invalid-response"]}';

    private $socket;
    private $response;

    public function __construct(Socket $socket = null)
    {
        if (!is_null($socket)) {
            $this->socket = $socket;
        } else {
            $this->socket = new Socket();
        }
    }

    /**
     * Submit the POST request with the specified parameters.
     *
     * @param RequestParameters $params Request parameters
     * @return string Body of the reCAPTCHA response
     */
    public function submit()
    {
        $errno = 0;
        $errstr = '';
        if (false === $this->socket->fsockopen('ssl://' . self::RECAPTCHA_HOST, 443, $errno, $errstr, 30)) {
            return self::BAD_REQUEST;
        }
        $content = http_build_query($this->getParams(), '', '&');
        $request = "POST " . self::SITE_VERIFY_PATH . " HTTP/1.1\r\n";
        $request .= "Host: " . self::RECAPTCHA_HOST . "\r\n";
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-length: " . strlen($content) . "\r\n";
        $request .= "Connection: close\r\n\r\n";
        $request .= $content . "\r\n\r\n";
        $this->socket->fwrite($request);
        $response = '';
        while (!$this->socket->feof()) {
            $response .= $this->socket->fgets(4096);
        }
        $this->socket->fclose();
        if (0 !== strpos($response, 'HTTP/1.1 200 OK')) {
            return self::BAD_RESPONSE;
        }
        $parts = preg_split("#\n\s*\n#Uis", $response);
        return $parts[1];
    }

    public function getParams()
    {
        $params = array(
            'secret' => self::SECRET,
            'response' => $this->response
        );

        if(!empty($remoteIp)) {
            $params['remoteip'] = $_SERVER['REQUEST_ADDR'];
        }

        return $params;
    }

    public function verify($response)
    {
        if(empty($response)) {
            return false;
        }

	$this->response = $response;

        $response = json_decode($this->submit(), true);
        if(!$response) {
            return false;
	}

        if(isset($response['success']) && $response['success'] == true) {
            return true;
	}

        return false;
    }
}
