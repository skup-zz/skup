<?php

/**
* Implements a client for the FontServ server
*/
class FontServ {
	/**
	* URL of server
	* @param string
	*/
	private $server_url;

	/**
	* API Key
	* @param string
	*/
	private $api_key;

	/**
	* Constructor
	*
	* @param string $url      Location of FontServ server
	* @param string $username Optional Basic Auth username
	* @param string password  Optional Basic Auth password
	*/
	public function __construct($url, $key = null, $local_url){
		$this->server_url = $url;
		$this->api_key   = $key;
		$this->local_url = $local_url;
	}

	/**
	* PHP magic function for handling the calling of services
	*
	* @param string $name Name of function called
	* @param array  $args Arguments passed
	*/
	public function __call($name, $args){

		$postdata = array(
						'key' => $this->api_key,
						'url' => $this->local_url,
						'version' => ANYFONT_VERSION,
						'service' => $name,
						'args' => serialize($args)
						);
		if(function_exists('wp_remote_request')){
			$result = wp_remote_request($this->server_url, array( 'timeout' => 20,'method' => 'POST', 'body' => array("c" => base64_encode(serialize($postdata)))));
			$response = is_array($result) ? unserialize(base64_decode($result['body'])) : false;
			if (false === $response){
				$response = array('msg' => __('<p><strong>An unknown HTTP error occurred.</strong></p><p>Please <a href="http://fontserv.com/account/" target="_blank" >contact support</a> and include the following details:<br /><ul><li>Browser version</li><li>WordPress version</li><li>PHP version</li></ul></p>'));
			}
		} else {
			$response = array('msg' => __('<p>This feature requires WordPress version 2.7 or higher. Please upgrade your WordPress.</p>'));
		}
		return $response;
	}
}


/**
* An RPC Exception class, used for handling, surprisingly,
* errors.
*/
class fontservException extends Exception{}

?>