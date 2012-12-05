<?php

/** HTTP Request
 * 
 * @author Jordi Kroon
 * @version 1.1.0
 * @copyright 2012 
 */
 
class HTTPRequest {
	
	public $method;
	public $useragent;

	private $url;
	private $path;
	private $errorMessage;
	
	public $postFields = array();
	public $timeout = 10;
	public $cookiedir = 'tmp';
	public $requestType = 1; // (1 = CURL 2 = ContentRequest)
	public $forceOneConnection = false;
	
	/** setURL()
	 * set connection URL
	 * 
	 * @param string $host
	 */
	 
	public function setURL($host) {
		$this->url = $host;
	}
	
	/** getErrorMessage()
	 * get last error message
	 * 
	 * @return string
	 */

	public function getErrorMessage() {
		return $this->errorMessage;	
	}

	/** send()
	 * main send function for sending requests
	 * 
	 * @return bool
	 */

	public function send() {
		
		if($this->requestType !== 2 && function_exists('curl_init')) {
			$output = $this->sendCURLRequest($this->url);
		} else {
			$output = $this->sendContentRequest($this->url);			
		}

		if(empty($this->result) && $this->forceOneConnection == false && $this->requestType !== 2) {
			
			$output = $this->sendContentRequest($this->url);
		}
		
		return $output;
	}

	/** sendCurlRequest()
	 * send CURL Request
	 * 
	 * @param string $url
	 * 
	 * @return bool
	 */

	 
	private function sendCurlRequest($url) {
		
		try {
			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_URL, $url);
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
					
			if(!empty($this->userAgent)) {
				curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
			}
			
			if(!empty($this->timeout)) {
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			}
			
			if(!empty($this->cookiedir)) {
				$ckfile = tempnam($this->cookiedir, 'CURLCOOKIE');
				curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile); 
				curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile); 
			}
			if($this->method == 'post') {
		
				curl_setopt($ch,CURLOPT_POST, count($this->postFields));
				curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($this->postFields));
			}
			
			$result = curl_exec($ch);
			
			if(curl_error($ch)) {
				throw new Exception('CURL Request failed, ' . curl_error($ch));
			}
			$this->result = $result;
			
			curl_close($ch);
			
			return true;
		} 
		
		catch(Exception $e) {
			
			$this->setResult = 'Error';
			$this->errorMessage = 'Exception: ' . $e->getMessage();
			
			return false;
			
		}
	}

	/** sendContentRequest()
	 * send content Request @see file_get_contents()
	 * 
	 * @param string $url
	 * 
	 * @return bool
	 */

	public function sendContentRequest($url) {
		
		try {

			$opts = array('http' => array());
				
			$opts['http']['method'] = 'GET';
			$opts['http']['header'] = '';
				
			if(!empty($this->userAgent)) {
				$opts['http']['header'] .= "User-Agent: " . $this->userAgent;
			}	
				
			if(!empty($this->timeout)) {
				$opts['http']['timeout'] = $this->timeout;
			}	

			if($this->method == 'post') {
	
				$postdata = http_build_query($this->postFields);
				
				if(!is_array($postdata)) {
					throw new Exception("Request failed, Invalid POST data ");
				} else {
					$opts['http']['method'] = 'POST';	
					$opts['http']['header'] .= "Content-type: application/x-www-form-urlencoded\r\n";
					$opts['http']['content'] = $postdata;
				}
										
			}
			
			$query  = stream_context_create($opts);
			if(!$result = @file_get_contents($url,false,$query)) {
				$error = error_get_last();
				
				throw new Exception("Request failed, " . $error['message']);
				
			}
			
			$this->result = $result;
			
			return true;
		} 
		
		catch(Exception $e) {
			
			$this->result = 'Error';
			$this->errorMessage = 'Exception: ' . $e->getMessage();
			
			return false;
			
		}
		
	}
	
	/** getResult()
	 * get result 
	 * 
	 * @return mixed
	 */
	 
	public function getResult() {
		return $this->result;
	}
	
}

?>