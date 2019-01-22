<?php

$input = file_get_contents('php://input');
$post = json_decode($input);

date_default_timezone_set('UTC');

$SignatureCertChainUrl = $_SERVER['HTTP_SIGNATURECERTCHAINURL'];

if ('amzn1.ask.skill.f190728c-6531-49c0-8be8-0c9f217469f1' == $post->session->application->applicationId AND $post->request->timestamp > date('Y-m-d\TH:i:s\Z', time()-150) AND preg_match('/https:\/\/s3\.amazonaws\.com(:433)?\/echo\.api\//', $SignatureCertChainUrl)) {
	$SignatureCertChainUrl_File = md5($SignatureCertChainUrl);
	$SignatureCertChainUrl_File = $SignatureCertChainUrl_File . '.pem';
	 
	if (!file_exists($SignatureCertChainUrl_File)) {
		file_put_contents($SignatureCertChainUrl_File, file_get_contents($SignatureCertChainUrl));
	}	

	$SignatureCertChainUrl_Content = file_get_contents($SignatureCertChainUrl_File);	
	$Signature_Content = $_SERVER['HTTP_SIGNATURE'];

	$SignatureCertChainUrl_Content_Array = openssl_x509_parse($SignatureCertChainUrl_Content);

	$Signature_PublicKey = openssl_pkey_get_public($SignatureCertChainUrl_Content);
	$Signature_PublicKey_Data = openssl_pkey_get_details($Signature_PublicKey);
	$Signature_Content_Decoded = base64_decode($Signature_Content);

	$Signature_Verify = openssl_verify($input, $Signature_Content_Decoded, $Signature_PublicKey_Data['key'], 'sha1');

	if (preg_match('/echo-api\.amazon\.com/', base64_decode($SignatureCertChainUrl_Content)) AND $SignatureCertChainUrl_Content_Array['validTo_time_t'] > time() AND $SignatureCertChainUrl_Content_Array['validFrom_time_t'] < time() AND $Signature_Content AND $Signature_Verify == 1) {
		header ('Content-Type: application/json');
		
		$PHP_Output = array('version' => '1.0', 'response' => array('outputSpeech' => array('type' => 'PlainText')));

		$PHP_Output['response']['outputSpeech']['text'] = 'Hello world!';

		echo json_encode($PHP_Output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
	else {
		http_response_code(400);
	}
}
else {
	http_response_code(400);
}

die();

?>
<?php

// $text = "Hello World, this is my first Alexa Skill";

// $array = array("version"=>"1.0", "response"=>array("outputSpeech"=>array("type"=>"plainText", "text"=>$text)));

// echo json_encode($array);
?>