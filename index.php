<?php

$EchoJArray = json_decode(file_get_contents('php://input'));
$RequestType = $EchoJArray->request->type;



$JsonOut 	= GetJsonMessageResponse($RequestType,$EchoJArray);
$size 		= strlen($JsonOut);
header('Content-Type: application/json');
header("Content-length: $size");
echo $JsonOut;

//-----------------------------------------------------------------------------------------//
//					     Some functions
//-----------------------------------------------------------------------------------------//

//This function returns a json blob for output
function GetJsonMessageResponse($RequestMessageType,$EchoJArray)
{

	$RequestId = $EchoJArray->request->requestId;
	$ReturnValue = "";
	
	if( $RequestMessageType == "LaunchRequest" ){
		$ReturnValue= '
		{
		  "version": "1.0",
		  "sessionAttributes": {
			"countActionList": {
			  "read": true,
			  "category": true,
			  "currentTask": "none",
			  "currentStep": 0
			}
		  },
		  "response": {
			"outputSpeech": {
		      "type": "PlainText",
		      "text": "Today will provide you a new learning opportunity.  Stick with it and the possibilities will be endless."
		    },
		    "card": {
		      "type": "Simple",
		      "title": "Horoscope",
		      "content": "Today will provide you a new learning opportunity.  Stick with it and the possibilities will be endless."
		    },
		    "reprompt": {
		      "outputSpeech": {
		        "type": "PlainText",
		        "text": "Can I help you with anything else?"
		      }
		    },
		    "shouldEndSession": false
		  }
		}';
	}
	
	if( $RequestMessageType == "SessionEndedRequest" )
	{
		$ReturnValue = '{
		  "type": "SessionEndedRequest",
		  "requestId": "$RequestId",
		  "timestamp": "' . date("c") . '",
		  "reason": "USER_INITIATED "
		}
		';
		
	}
	
	if( $RequestMessageType == "IntentRequest" ){
	
		$NextNumber = 0;
		$EndSession = "false";
		$SpeakPhrase = "The next number is ";
		if( $EchoJArray->request->intent->name == "next" )
		{
			$NextNumber = $EchoJArray->session->attributes->countActionList->currentStep + 1;
			$SpeakPhrase = "The next number is $NextNumber";
			
			if( $EchoJArray->session->attributes->countActionList->currentStep == 3 )
			{
				$EndSession = "true";
				$SpeakPhrase = "Thank you for counting and good bye";
			}
		}
		if( $EchoJArray->request->intent->name == "attendance" )
		{
			$NextNumber = $EchoJArray->session->attributes->countActionList->currentStep + 1;
			$SpeakPhrase = "35 out of 850 students";
			
			// if( $EchoJArray->session->attributes->countActionList->currentStep == 3 )
			// {
			// 	$EndSession = "true";
			// 	$SpeakPhrase = "Thank you for counting and good bye";
			// }
		}
		
	
		$ReturnValue= '
		{
		  "version": "1.0",
		  "sessionAttributes": {
			"countActionList": {
			  "read": true,
			  "category": true,
			  "currentTask": "none",
			  "currentStep": '.$NextNumber.'
			}
		  },
		  "response": {
			"outputSpeech": {
			  "type": "PlainText",
			  "text": "' . $SpeakPhrase . '"
			},
			"card": {
			  "type": "Simple",
			  "title": "Our Ace count example",
			  "content": "' . $SpeakPhrase . '"
			},
			"reprompt": {
			  "outputSpeech": {
				"type": "PlainText",
				"text": "Say next item to continue."
			  }
			},
			"shouldEndSession": ' . $EndSession . '
		  }
		}';
	}
	return $ReturnValue;
}// end function GetJsonMessageResponse
?>