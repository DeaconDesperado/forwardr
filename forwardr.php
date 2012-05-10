<?php

/**
  * Catch-all adaptive HTTP forwarder for circumventing cross-domain issues
  * 
  * Forwardr can be set up as an endpt listener to forward any requests to another
  * endpt, included all passed parameters from the $_GET or $_POST super globals.
  *
  * @package Forwardr
  * @author Mark Grey <mark@deacondesperado.com>
  * @license http://www.opensource.org/licenses/bsd-license.php
  */

require_once('oocurl.php');

class Forwardr{

    private $_base = '';
    private $_permanent_params = array();
    
    /**
      * Set debugging mode as a public property
      */

    public $debug = False;

    /**
      * If true, will return remote response status code to answered response
      */

    public $set_headers = False;

    /**
      * If set headers is True, this will determine output mimetype
      */

    public $mimetype = 'text/plain';

    private $http_codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
            );


    /**
      * Construct a Forwardr and prepare to listen
      *
      * @param string $base The base domain to send all requests to
      * @param array $params The permanent params to use for every request (great for hmacs or auth creds)
      */

    public function __construct($base,$params=array()){
        $this->_base = trim($base,'/');
        $this->_permanent_params = array();
    }

    public function exec($path = False){
        $method = $_SERVER['REQUEST_METHOD'];
        if(!$path){
            $path = isset($_SERVER['PATH_INFO']) ? trim(substr($_SERVER['PATH_INFO'],1),'/') : '/';
        }
        
        try{
            $response = call_user_func(array($this,'_'.strtolower($method)),$path);
            return $response;
        }catch(Exception $e){

        }
    }

    private function _getQS(){
        $params = array_merge($_GET,$this->_permanent_params);
        $qs = '';
        if(!empty($params)){
            $qs = http_build_query($params);
        }
        return $qs;
    }

    private function _getCurl($path){
        $qs = $this->_getQS();
        $url = sprintf('%s/%s?%s',$this->_base,$path,$qs);
        $ch = curl_init($url);
        if($this->debug){
            curl_setopt($ch,CURLOPT_FAILONERROR,False);
        }else{
            curl_setopt($ch,CURLOPT_FAILONERROR,True);
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
        return $ch;
    }

    private function _get($path){
        $ch = $this->_getCurl($path);
        return $this->_send($ch);
    }

    private function _post($path){
        $ch = $this->_getCurl($path);
        curl_setopt($ch,CURLOPT_POST, True);
        curl_setopt($ch,CURLOPT_POSTFIELDS,array_merge($_POST,$this->_permanent_params));
        return $this->_send($ch);
    }

    private function _put($path){
        $ch = $this->_getCurl($path);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch,CURLOPT_POSTFIELDS,array_merge($_POST,$this->_permanent_params));
        return $this->_send($ch);
    }

    private function _delete($path){
        $ch = $this->_getCurl($path);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, 'DELETE');
        return $this->_send($ch);
    }

    private function _send($ch){
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            //The request failed
            if($this->set_headers){
                $status_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
                $header_str = sprintf('%d %s', $status_code, $this->http_codes[$status_code] );
                header('HTTP/1.0 '.$header_str);
            }
            return $response;
        }else{
            if($this->set_headers){
                header('Content-type: '.$this->mimetype);
            }
            return $response;
        }
    }
}
