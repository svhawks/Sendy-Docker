<?php
/**
 * Lightweight API interface with the Amazon Simple Notification Service
 *
 * @author Chris Barr <chris.barr@ntlworld.com>
 * @link http://aws.amazon.com/sns/
 * @link http://docs.amazonwebservices.com/sns/latest/api/
 */
class AmazonSNS {
	/** @var string $access_key */
	private $access_key;
	/** @var string $secret_key */
	private $secret_key;
	/** @var string $protocol */
	private $protocol = 'https://'; // http is allowed
	/** @var string $endpoint */
	private $endpoint = ''; // Defaults to us-east-1
	/** @var array $endpoints */
	private $endpoints = array(
		'us-east-1' => 'sns.us-east-1.amazonaws.com',
		'us-west-2' => 'sns.us-west-2.amazonaws.com',
		'us-west-1' => 'sns.us-west-1.amazonaws.com',
		'eu-west-1' => 'sns.eu-west-1.amazonaws.com',
		'eu-central-1' => 'ec2.eu-central-1.amazonaws.com',
		'ap-southeast-1' => 'sns.ap-southeast-1.amazonaws.com',
		'ap-southeast-2' => 'sns.ap-southeast-2.amazonaws.com',
		'ap-northeast-1' => 'sns.ap-northeast-1.amazonaws.com',
		'sa-east-1' => 'sns.sa-east-1.amazonaws.com'
	);
	/**
	 * Instantiate the object - set access_key and secret_key and set default region
	 *
	 * @param string $access_key
	 * @param string $secret_key
	 * @param string $region [optional]
	 * @throws InvalidArgumentException
	 */
	public function __construct($access_key, $secret_key, $region = 'us-east-1') {
		$this->access_key = $access_key;
		$this->secret_key = $secret_key;
		if(empty($this->access_key) || empty($this->secret_key)) {
			throw new InvalidArgumentException('Must define Amazon access key and secret key');
		}
		$this->setRegion($region);
	}
	/**
	 * Set the SNS endpoint/region
	 *
	 * @link http://docs.amazonwebservices.com/general/latest/gr/index.html?rande.html
	 * @param string $region
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function setRegion($region) {
		if(!isset($this->endpoints[$region])) {
			throw new InvalidArgumentException('Region unrecognised');
		}
		return $this->endpoint = $this->endpoints[$region];
	}
	//
	// Public interface functions
	//
	/**
	 * Add permissions to a topic
	 *
	 * Example:
	 * 	$AmazonSNS->addPermission('topic:arn:123', 'New Permission', array('987654321000' => 'Publish', '876543210000' => array('Publish', 'SetTopicAttributes')));
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_AddPermission.html
	 * @param string $topicArn
	 * @param string $label Unique name of permissions
	 * @param array $permissions [optional] Array of permissions - member ID as keys, actions as values
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function addPermission($topicArn, $label, $permissions = array()) {
		if(empty($topicArn) || empty($label)) {
			throw new InvalidArgumentException('Must supply TopicARN and a Label for this permission');
		}
		// Add standard params as normal
		$params = array(
			'TopicArn' => $topicArn,
			'Label' => $label
		);
		// Compile permissions into separate sequential arrays
		$memberFlatArray = array();
		$permissionFlatArray = array();
		foreach($permissions as $member => $permission) {
			if(is_array($permission)) {
				// Array of permissions
				foreach($permission as $singlePermission) {
					$memberFlatArray[] = $member;
					$permissionFlatArray[] = $singlePermission;
				}
			}
			else {
				// Just a single permission
				$memberFlatArray[] = $member;
				$permissionFlatArray[] = $permission;
			}
		}
		// Dummy check
		if(count($memberFlatArray) !== count($permissionFlatArray)) {
			// Something went wrong
			throw new InvalidArgumentException('Mismatch of permissions to users');
		}
		// Finally add to params
		for($x = 1; $x <= count($memberFlatArray); $x++) {
			$params['ActionName.member.' . $x] = $permissionFlatArray[$x];
			$params['AWSAccountID.member.' . $x] = $memberFlatArray[$x];
		}
		// Finally send request
		$this->_request('AddPermission', $params);
		return true;
	}
	/**
	 * Confirm a subscription to a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_ConfirmSubscription.html
	 * @param string $topicArn
	 * @param string $token
	 * @param bool|null $authenticateOnUnsubscribe [optional]
	 * @return string - SubscriptionARN
	 * @throws InvalidArgumentException
	 */
	public function confirmSubscription($topicArn, $token, $authenticateOnUnsubscribe = null) {
		if(empty($topicArn) || empty($token)) {
			throw new InvalidArgumentException('Must supply a TopicARN and a Token to confirm subscription');
		}
		$params = array(
			'TopicArn' => $topicArn,
			'Token' => $token
		);
		if(!is_null($authenticateOnUnsubscribe)) {
			$params['AuthenticateOnUnsubscribe'] = $authenticateOnUnsubscribe;
		}
		$resultXml = $this->_request('ConfirmSubscription', $params);
		return strval($resultXml->ConfirmSubscriptionResult->SubscriptionArn);
	}
	/**
	 * Create an SNS topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_CreateTopic.html
	 * @param string $name
	 * @return string - TopicARN
	 * @throws InvalidArgumentException
	 */
	public function createTopic($name) {
		if(empty($name)) {
			throw new InvalidArgumentException('Must supply a Name to create topic');
		}
		$resultXml = $this->_request('CreateTopic', array('Name' => $name));
		return strval($resultXml->CreateTopicResult->TopicArn);
	}
	/**
	 * Delete an SNS topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_DeleteTopic.html
	 * @param string $topicArn
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function deleteTopic($topicArn) {
		if(empty($topicArn)) {
			throw new InvalidArgumentException('Must supply a TopicARN to delete a topic');
		}
		$this->_request('DeleteTopic', array('TopicArn' => $topicArn));
		return true;
	}
	/**
	 * Get the attributes of a topic like owner, ACL, display name
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_GetTopicAttributes.html
	 * @param string $topicArn
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getTopicAttributes($topicArn) {
		if(empty($topicArn)) {
			throw new InvalidArgumentException('Must supply a TopicARN to get topic attributes');
		}
		$resultXml = $this->_request('GetTopicAttributes', array('TopicArn' => $topicArn));
		// Get attributes
		$attributes = $resultXml->GetTopicAttributesResult->Attributes->entry;
		// Unfortunately cannot use _processXmlToArray here, so process manually
		$returnArray = array();
		// Process into array
		foreach($attributes as $attribute) {
			// Store attribute key as array key
			$returnArray[strval($attribute->key)] = strval($attribute->value);
		}
		return $returnArray;
	}
	/**
	 * List subscriptions that user is subscribed to
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_ListSubscriptions.html
	 * @param string|null $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 */
	public function listSubscriptions($nextToken = null) {
		$params = array();
		if(!is_null($nextToken)) {
			$params['NextToken'] = $nextToken;
		}
		$resultXml = $this->_request('ListSubscriptions', $params);
		// Get subscriptions
		$subs = $resultXml->ListSubscriptionsResult->Subscriptions->member;
		return $this->_processXmlToArray($subs);
	}
	/**
	 * List subscribers to a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_ListSubscriptionsByTopic.html
	 * @param string $topicArn
	 * @param string|null $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function listSubscriptionsByTopic($topicArn, $nextToken = null) {
		if(empty($topicArn)) {
			throw new InvalidArgumentException('Must supply a TopicARN to show subscriptions to a topic');
		}
		$params = array(
			'TopicArn' => $topicArn
		);
		if(!is_null($nextToken)) {
			$params['NextToken'] = $nextToken;
		}
		$resultXml = $this->_request('ListSubscriptionsByTopic', $params);
		// Get subscriptions
		$subs = $resultXml->ListSubscriptionsByTopicResult->Subscriptions->member;
		return $this->_processXmlToArray($subs);
	}
	/**
	 * List SNS topics
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_ListTopics.html
	 * @param string|null $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 */
	public function listTopics($nextToken = null) {
		$params = array();
		if(!is_null($nextToken)) {
			$params['NextToken'] = $nextToken;
		}
		$resultXml = $this->_request('ListTopics', $params);
		// Get Topics
		$topics = $resultXml->ListTopicsResult->Topics->member;
		return $this->_processXmlToArray($topics);
	}
	/**
	 * Publish a message to a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_Publish.html
	 * @param string $topicArn
	 * @param string $message
	 * @param string $subject [optional] Used when sending emails
	 * @param string $messageStructure [optional] Used when you want to send a different message for each protocol.If you set MessageStructure to json, the value of the Message parameter must: be a syntactically valid JSON object; and contain at least a top-level JSON key of "default" with a value that is a string.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function publish($topicArn, $message, $subject = '', $messageStructure = '') {
		if(empty($topicArn) || empty($message)) {
			throw new InvalidArgumentException('Must supply a TopicARN and Message to publish to a topic');
		}
		$params = array(
			'TopicArn' => $topicArn,
			'Message' => $message
		);
		if(!empty($subject)) {
			$params['Subject'] = $subject;
		}
		if(!empty($messageStructure)) {
			$params['MessageStructure'] = $messageStructure;
		}
		$resultXml = $this->_request('Publish', $params);
		return strval($resultXml->PublishResult->MessageId);
	}
	/**
	 * Remove a set of permissions indentified by topic and label that was used when creating permissions
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_RemovePermission.html
	 * @param string $topicArn
	 * @param string $label
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function removePermission($topicArn, $label) {
		if(empty($topicArn) || empty($label)) {
			throw new InvalidArgumentException('Must supply a TopicARN and Label to remove a permission');
		}
		$this->_request('RemovePermission', array('Label' => $label));
		return true;
	}
	/**
	 * Set a single attribute on a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_SetTopicAttributes.html
	 * @param string $topicArn
	 * @param string $attrName
	 * @param mixed $attrValue
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function setTopicAttributes($topicArn, $attrName, $attrValue) {
		if(empty($topicArn) || empty($attrName) || empty($attrValue)) {
			throw new InvalidArgumentException('Must supply a TopicARN, AttributeName and AttributeValue to set a topic attribute');
		}
		$this->_request('SetTopicAttributes', array(
			'TopicArn' => $topicArn,
			'AttributeName' => $attrName,
			'AttributeValue' => $attrValue
		));
		return true;
	}
	/**
	 * Subscribe to a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_Subscribe.html
	 * @param string $topicArn
	 * @param string $protocol - http/https/email/email-json/sms/sqs
	 * @param string $endpoint
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function subscribe($topicArn, $protocol, $endpoint) {
		if(empty($topicArn) || empty($protocol) || empty($endpoint)) {
			throw new InvalidArgumentException('Must supply a TopicARN, Protocol and Endpoint to subscribe to a topic');
		}
		$this->_request('Subscribe', array(
			'TopicArn' => $topicArn,
			'Protocol' => $protocol,
			'Endpoint' => $endpoint
		));
		return true;
	}
	/**
	 * Unsubscribe a user from a topic
	 *
	 * @link http://docs.amazonwebservices.com/sns/latest/api/API_Unsubscribe.html
	 * @param string $subscriptionArn
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function unsubscribe($subscriptionArn) {
		if(empty($subscriptionArn)) {
			throw new InvalidArgumentException('Must supply a SubscriptionARN to unsubscribe from a topic');
		}
		$this->_request('Unsubscribe', array('SubscriptionArn' => $subscriptionArn));
		return true;
	}
	/**
	 * Create Platform endpoint
	 *
	 * @link http://docs.aws.amazon.com/sns/latest/api/API_CreatePlatformEndpoint.html
	 * @param string $platformApplicationArn
	 * @param string $token 
	 * @param string $userData
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function createPlatformEndpoint($platformApplicationArn, $token, $userData) {
		if(empty($platformApplicationArn) || empty($token) || empty($userData)) {
			throw new InvalidArgumentException('Must supply a PlatformApplicationArn,Token & UserData to create platform endpoint');
		}
		$response = $this->_request('CreatePlatformEndpoint', array(
			'PlatformApplicationArn' => $platformApplicationArn,
			'Token' => $token,
			'CustomUserData' => $userData
		));
		return strval($response->CreatePlatformEndpointResult->EndpointArn);
	}
	/**
	 * Delete endpoint
	 *
	 * @link http://docs.aws.amazon.com/sns/latest/api/API_DeleteEndpoint.html
	 * @param string $deviceArn
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function deleteEndpoint($deviceArn) {
		if(empty($deviceArn)) {
			throw new InvalidArgumentException('Must supply a DeviceARN to remove platform endpoint');
		}
		$this->_request('DeleteEndpoint', array(
			'EndpointArn' => $deviceArn,
			
		));
		return true;
	}
	//
	// Private functions
	//
	/**
	 * Perform and process a cURL request
	 *
	 * @param string $action
	 * @param array $params [optional]
	 * @return SimpleXMLElement
	 * @throws SNSException|APIException
	 */
	private function _request($action, $params = array()) {
		// Add in required params
		$params['Action'] = $action;
		$params['AWSAccessKeyId'] = $this->access_key;
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s.000\Z');
		$params['SignatureVersion'] = 2;
		$params['SignatureMethod'] = 'HmacSHA256';
		// Sort and encode into string
		uksort($params, 'strnatcmp');
		$queryString = '';
		foreach ($params as $key => $val) {
			$queryString .= "&{$key}=".rawurlencode($val);
		}
		$queryString = substr($queryString, 1);
		// Form request string
		$requestString = "GET\n"
			. $this->endpoint."\n"
			. "/\n"
			. $queryString;
		// Create signature - Version 2
		$params['Signature'] = base64_encode(
			hash_hmac('sha256', $requestString, $this->secret_key, true)
		);
		// Finally create request
		$request = $this->protocol . $this->endpoint . '/?' . http_build_query($params, '', '&');
		// Instantiate cUrl and perform request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		// Close cUrl
		curl_close($ch);
		// Load XML response
		$xmlResponse = simplexml_load_string($output);
		// Check return code
		if($this->_checkGoodResponse($info['http_code']) === false) {
			// Response not in 200 range
			if(isset($xmlResponse->Error)) {
				// Amazon returned an XML error
				trigger_error(sprintf(strval($xmlResponse->Error->Code) . ': ' . strval($xmlResponse->Error->Message)), E_USER_WARNING);
				//throw new SNSException(strval($xmlResponse->Error->Code) . ': ' . strval($xmlResponse->Error->Message), $info['http_code']);
				throw new SNSException(strval($xmlResponse->Error->Code), $info['http_code']);
			}
			else {
				// Some other problem
				throw new APIException('There was a problem executing this request', $info['http_code']);
			}
		}
		else {
			// All good
			return $xmlResponse;
		}
	}
	/**
	 * Check the curl response code - anything in 200 range
	 *
	 * @param int $code
	 * @return bool
	 */
	private function _checkGoodResponse($code) {
		return floor($code / 100) == 2;
	}
	/**
	 * Transform the standard AmazonSNS XML array format into a normal array
	 *
	 * @param SimpleXMLElement $xmlArray
	 * @return array
	 */
	private function _processXmlToArray(SimpleXMLElement $xmlArray) {
		$returnArray = array();
		// Process into array
		foreach($xmlArray as $xmlElement) {
			$elementArray = array();
			// Loop through each element
			foreach($xmlElement as $key => $element) {
				// Use strval() to make sure no SimpleXMLElement objects remain
				$elementArray[$key] = strval($element);
			}
			// Store array of elements
			$returnArray[] = $elementArray;
		}
		return $returnArray;
	}
}
// Exception thrown if there's a problem with the API
class APIException extends Exception {}
// Exception thrown if Amazon returns an error
class SNSException extends Exception {}