<?php

//$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
//echo $lang ."\n\n";

//choose_language_by_accept_lang($lang);



//$string = "en;q=0.8, en-US;q=0.5 ,ru";
//echo choose_language_by_accept_lang($string) ."\n";

function choose_language_by_accept_lang($string) {
    $available_lang = array("en", "ru");
    $lang = null;
    $priority = 0;
    $proposed_lang = explode(',', $string);
    $pattern = "/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i";
    preg_match_all($pattern, $string, $matches);
    if(count($matches[1])) {
	$langs = array_combine($matches[1], $matches[4]);
	foreach($langs as $lang => $value) {
	    if($value === '') {
		$langs[$lang] = '1.0';
	    }
	}
	arsort($langs, SORT_NUMERIC);
	foreach($langs as $lang => $quality) {
	    foreach($available_lang as $key => $value) {
		if(strpos($lang, $value) === 0) {
		    return $value;
		}
	    }
	}
    }
    return null;
}
