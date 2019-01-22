<?php

$text = "Hello World, this is my first Alexa Skill";

$array = array("version"=>"1.0", "response"=>array("outputSpeech"=>array("type"=>"plainText", "text"=>$text)));

echo json_encode($array);
?>