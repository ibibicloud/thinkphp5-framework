<?php

namespace think;

// cURL请求类
class Curl
{
    // curl资源句柄
    private $ch;

    // 请求的URL
    private $url;

    // 请求头信息
    private $header = [];

    // POST数据（如果是POST请求）
    private $postData = [];

    // 是否返回响应头信息（默认不返回）
    private $responseHeader = false;

    // 是否允许重定向的设置（默认不允许重定向）
    private $followLocation = false;

    // 重定向最大次数
    private $maxRedirs = 3;

    // 是否验证SSL证书（默认不验证）
    private $verifySSL = false;

	// 请求的超时时间（默认10秒）
    private $timeout = 10;

    // 构造函数，初始化curl资源句柄并设置一些默认选项，包括https相关设置
    public function __construct()
    {
        if ( !is_resource($this->ch) ) {
            $this->ch = curl_init();
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $this->setVerifySSL();
    }

    // 设置请求的URL
    public function setUrl($url)
    {
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
    }

    // 设置请求头信息
    public function setHeader($header)
    {
        $this->header = $header;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
    }

    // 设置POST数据（用于POST请求）
    public function setPostData($postData)
    {
        $this->postData = $postData;
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);
    }

    // 设置是否返回响应头信息
    public function setResponseHeader($responseHeader = false)
    {
        $this->responseHeader = $responseHeader;
        curl_setopt($this->ch, CURLOPT_HEADER, (bool)$responseHeader);
    }

    // 设置是否允许重定向
    public function setFollowLocation($followLocation = false)
    {
        $this->followLocation = $followLocation;
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, (bool)$followLocation);
        if ( (bool)$followLocation ) {
        	curl_setopt($this->ch, CURLOPT_MAXREDIRS, $this->maxRedirs);
        }
    }

	// 设置是否验证SSL证书
	public function setVerifySSL($verifySSL = false)
	{
	    $this->verifySSL = $verifySSL;
	    // 设置验证SSL证书
	    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
	    curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->verifySSL);
	}

    // 设置请求超时时间
    public function setTimeout($timeout = 10)
    {
        $this->timeout = $timeout;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
    }

    // 执行请求并返回响应结果
	public function execute()
	{
        if ( empty($this->url) ) {
            throw new \Exception('URL is not set.');
        }
	    $response = curl_exec($this->ch);
	    if ( $response === false ) {
	        // 如果curl_exec执行失败，抛出异常并包含错误信息
	        throw new \Exception('cURL request failed for URL: '. $this->url .' - '. curl_error($this->ch));
	    }
	    if ( $this->responseHeader ) {
	        // 如果需要返回响应头信息，将响应头和主体内容分开
	        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
	        $header = substr($response, 0, $header_size);
	        $body = substr($response, $header_size);
	        $result = ['header' => $header, 'body' => $body];
	    } else {
	        $result = $response;
	    }
	    // 关闭curl资源句柄
	    curl_close($this->ch);
	    return $result;
	}

	// 析构函数关闭curl资源句柄
	public function __destruct()
	{
	    if (is_resource($this->ch)) {
	        curl_close($this->ch);
	    }
	}

}