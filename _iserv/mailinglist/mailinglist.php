<?php  
	$FROM = "rplus.informatique@gmail.com";
	$URL_OK = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZCZhcG9zO2Fib25uZW1lbnQ8L2gxPjxkaXYgY2xhc3M9InR3LXBhcmEiPjxoMj4gPC9oMj48cD5VbiBlbWFpbCB2b3VzIGEg6XTpIGVudm956S4gVulyaWZpZXogdm90cmUgbWVzc2FnZXJpZSDpbGVjdHJvbmlxdWUgZCZhcG9zO2ljaSBxdWVscXVlcyBtaW51dGVzIGFmaW4gZGUgY29uZmlybWVyIHZvdHJlIGRlbWFuZGUuPC9wPjxicj48YnI+PC9kaXY+PC9kaXY+";
	$URL_KO = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZCZhcG9zO2Fib25uZW1lbnQ8L2gxPjxkaXYgY2xhc3M9InR3LXBhcmEiPjxoMj4gPC9oMj48cD5E6XNvbOksIHVuZSBlcnJldXIgcyZhcG9zO2VzdCBwcm9kdWl0ZSBzdXIgbGUgc2VydmV1ci4gQ29udGFjdGV6IGwmYXBvczthZG1pbmlzdHJhdGV1ciBkdSBzaXRlLjwvcD48YnI+PGJyPjwvZGl2PjwvZGl2Pg==";
	$TO = "";
	$NAME = "";
	$CONFACTION = "";
	$CONFSUBJECT = "";
	$CONFTEXT = "";
	$CRLF = "\r\n";	
	while( list($key, $val) = each($_POST) ) {
		if( $key == "mlemail" )
			$TO = ( $val );
		else if( $key == "mlconfaction" )
			$CONFACTION = ( $val );
		else if( $key == "mlconfsubject" )
			$CONFSUBJECT = ( $val );
		else if( $key == "mlconftext" )
			$CONFTEXT = ( $val );
		else if( $key == "mlname" )
			$NAME = ( $val );
	}  
	$LANGEXT = substr($_SERVER['PHP_SELF'], -7);
	if( $LANGEXT[0] == '-' ) {
		$LANGEXT = substr($LANGEXT,0,3);
	} else
		$LANGEXT = "";
	$HREF = getenv("HTTP_REFERER");
	$HREF = substr( $HREF, 0, strrpos( $HREF, '/' ) + 1 );
	if( $TO != "" )
	{
		$SIGN = md5( $TO . "fb38f412cb9330b36c301ebf6119bc9d" );
		$URL = $HREF."_iserv/mailinglist/mlconfirm".$LANGEXT.".php?mlaction=".$CONFACTION."&mlsign=".$SIGN."&mlemail=".$TO."&mlname=".$NAME;
		$CONFSUBJECT = str_replace( "\'", "'", $CONFSUBJECT );
		$CONFTEXT = str_replace( "<br>", $CRLF, $CONFTEXT );
		$CONFTEXT = str_replace( "\'", "'", $CONFTEXT );		
		$headers = 
			"MIME-Version: 1.0" . $CRLF .
			"Content-Type: text/plain; charset=utf-8" . $CRLF .
			"Content-Transfer-Encoding: 8bit" . $CRLF .
			"From: $FROM" . $CRLF .
			"Return-Path: $FROM" . $CRLF .
			"X-Mailer: PHP/" . phpversion() . $CRLF;
		mail($TO, '=?UTF-8?B?'.base64_encode($CONFSUBJECT).'?=', $CONFTEXT . $CRLF . $URL, $headers );
		header( 'Location: ' . $HREF . $URL_OK );		
	} else
		header( 'Location: ' . $HREF . $URL_KO );		
?>  
