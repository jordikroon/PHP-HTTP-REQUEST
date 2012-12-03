<?php
 
/** HTTP Request
 * 
 * @author Jordi Kroon
 * @version 1.0.1
 * @copyright 2012 
 */

class HTTPRequest {
	
	public $method;
	public $useragent;

	private $host; 
	private $path;
	private $errorMessage;
	
	public $postFields = array();
	public $timeout;
	public $cookiedir;
	
	function __construct($host) {
		$this->host = $host;
	}

	private function setErrorMessage($message) {
		$this->errorMessage = $message;
	}
	
	public function getErrorMessage() {
		return $this->errorMessage;	
	}
	
	public function send() {
		if (function_exists('curl_init')) {
			return $this->sendCURLRequest($this->host);
		}
		else {
			return $this->sendContentRequest($this->host);
		}
	}

	private function sendCurlRequest($host) {
		
		try {
			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_URL, $host);
			
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
			$this->setResult($result);
			
			curl_close($ch);
			
			return true;
		} 
		
		catch(Exception $e) {
			
			$this->setResult('Error');
			$this->setErrorMessage('Exception: ' . $e->getMessage());
			
			return false;
			
		}
	}

	public function sendContentRequest($host) {
		
		try {
			
			if($this->method == 'post') {
				$postdata = http_build_query($this->postFields);
				
				
				$opts = array('http' =>
				    array(
				        'method'  => 'POST',
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'content' => $postdata
				    )
				);
				
			}
			
			if(!empty($this->userAgent)) {
				$opts['http']['header'] .= "User-Agent: " . $this->userAgent;
			}	
			
			if(!empty($this->timeout)) {
				$opts['http']['timeout'] .= $this->timeout;
			}					
			
			
			$query  = stream_context_create($opts);
			if(!$result = @file_get_contents($host,false,$query)) {
				$error = error_get_last();
				
				throw new Exception("Request failed, " . $error['message']);
				
			}
			
			$this->setResult($result);
			
			return true;
		} 
		
		catch(Exception $e) {
			
			$this->setResult('Error');
			$this->setErrorMessage('Exception: ' . $e->getMessage());
			
			return false;
			
		}
		
	}
	
	public function getResult() {
		return $this->result;
	}
	
	private function setResult($result) {
		$this->result = $result;
	}
}

?>