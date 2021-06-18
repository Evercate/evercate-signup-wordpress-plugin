<?php

require_once('Model/EvercateUserGroup.php');
require_once('Model/EvercateTagType.php');
require_once('Model/EvercateTag.php');

class EvercateApiClient
{
	private string $authHeader;

	public function __construct(string $apikey)
    {
		if(empty($apikey))
			throw new Exception('No api key set');

		$this->authHeader = "Authorization: Bearer " . $apikey;
    }

	public function GetUserGroup($id)
	{
		$allGroups = $this->GetUserGroups();

		foreach($allGroups as $loopGroup)
		{
			if($loopGroup->Id === $id)
			{
				return $loopGroup;
			}
		}

		return NULL;
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

		$userGroups = array();

		foreach ($response as $responseGroup) 
		{
			
			$userGroup = new EvercateUserGroup();
			$userGroup->Id = $responseGroup->Id;
			$userGroup->Name = $responseGroup->Name;

			$userGroups[] =  $userGroup;

			foreach ($responseGroup->Tags as $responseTag) 
			{
				$tagType = NULL;

				//Due to response sent as only tags we need to see if we have the tag type since before
				foreach($userGroup->EvercateTagTypes as $existingTagType) {
					if ($existingTagType->Id === $responseTag->TagTypeId) {
						$tagType = $existingTagType;
						break;
					}
				}

				if($tagType === NULL)
				{
					$tagType = new EvercateTagType();
					$tagType->Id = $responseTag->TagTypeId;
					$tagType->Name = $responseTag->TagType;
					$userGroup->EvercateTagTypes[] =  $tagType;
				}
	
				$tag = new EvercateTag();
				$tag->Id = $responseTag->Id;
				$tag->Name = $responseTag->Name;

				$tagType->EvercateTags[] = $tag;
				$userGroup->AllEvercateTags[] =  $tag;

			}

			
		}

	
		return $userGroups;
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