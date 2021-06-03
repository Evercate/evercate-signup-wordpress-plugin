<?php

class EvercateApiClient
{
	private string $authHeader;

	public function __construct(string $apikey)
    {
		if(empty($apikey))
			throw new Exception('No api key set');

		$this->authHeader = "Authorization: Bearer " . $apikey;
    }

	public function GetUserGroups()
	{
		try {

			$response = $this->apiCall("usergroups");

			if(!is_array($response) || empty($response))
			{
				throw new Exception('Group response is invalid: ' . json_encode($response));
			}

		} catch (Exception $e) {
			throw new Exception("Exception while fetching user groups: " . $e->getMessage());
		}
	
		return $response;
	}

	private function apiCall($callUri, $payload = NULL)
	{
		$ch = curl_init('https://api-v1.evercate.com/'.$callUri); // Initialise cURL
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->authHeader )); // Inject the token into the header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPGET, 1); // Specify the request method as POST
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
		$result = curl_exec($ch); // Execute the cURL statement		

		$curlErrNo = curl_errno($ch);
		$curlError = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch); // Close the cURL connection
		
		if($curlErrNo)
			throw new Exception("Curl error " . $curlErrNo . ": " . $curlError);

		if($httpCode >= 400)
			throw new Exception("Call failed with status code: " . $httpCode);
		
		return json_decode($result); // Return the received data
	}
}