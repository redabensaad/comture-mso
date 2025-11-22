<?php  
	$ACTION = "";    
	$NAME = "";
	$SIGN = "";
	$EMAIL = "";
	$URL_KO99 = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZCZhcG9zO2Fib25uZW1lbnQ8L2gxPjxkaXYgY2xhc3M9InR3LXBhcmEiPjxwPlVuIGVtYWlsIHZvdXMgYSDpdOkgZW52b3npLiBW6XJpZmlleiB2b3RyZSBtZXNzYWdlcmllIOlsZWN0cm9uaXF1ZSBkJmFwb3M7aWNpIHF1ZWxxdWVzIG1pbnV0ZXMgYWZpbiBkZSBjb25maXJtZXIgdm90cmUgZGVtYW5kZS4gKGVycm9yIDk5KTwvcD48YnI+PGJyPjwvZGl2PjwvZGl2Pg==";
	$URL_KO2 = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZCZhcG9zO2Fib25uZW1lbnQ8L2gxPjxkaXYgY2xhc3M9InR3LXBhcmEiPjxwPlVuIGVtYWlsIHZvdXMgYSDpdOkgZW52b3npLiBW6XJpZmlleiB2b3RyZSBtZXNzYWdlcmllIOlsZWN0cm9uaXF1ZSBkJmFwb3M7aWNpIHF1ZWxxdWVzIG1pbnV0ZXMgYWZpbiBkZSBjb25maXJtZXIgdm90cmUgZGVtYW5kZS4gKGVycm9yIDIpPC9wPjxicj48YnI+PC9kaXY+PC9kaXY+";
	$URL_SUB_CONF = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZCZhcG9zO2Fib25uZW1lbnQ8L2gxPjxkaXYgY2xhc3M9InR3LXBhcmEiPjxwPlZvdHJlIGRlbWFuZGUgZCZhcG9zO2Fib25uZW1lbnQgYSDpdOkgZW5yZWdpc3Ry6WUuIEVsbGUgc2VyYSBlZmZlY3RpdmUgZGFucyBxdWVscXVlcyBoZXVyZXMsIG1lcmNpLjwvcD48YnI+PGJyPjwvZGl2PjwvZGl2Pg==";
	$URL_UNSUB_CONF = "_message.html?PGRpdiBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXIiPjxoMT5Db25maXJtYXRpb24gZGUgZOlzYWJvbm5lbWVudDwvaDE+PGRpdiBjbGFzcz0idHctcGFyYSI+PHA+Vm90cmUgZGVtYW5kZSBkZSBk6XNhYm9ubmVtZW50IGEg6XTpIGVucmVnaXN0cullLiBFbGxlIHNlcmEgZWZmZWN0aXZlIGRhbnMgcXVlbHF1ZXMgaGV1cmVzLCBtZXJjaS48L3A+PGJyPjxicj48L2Rpdj48L2Rpdj4=";
	$HTTP_PREFIX = (false)?'https://':'http://';		
	while( list($key, $val) = each($_GET) ) {  
		if( $key == "mlaction" )
			$ACTION = $val;
		else if( $key == "mlsign" )
			$SIGN = $val;
		else if( $key == "mlemail" )
			$EMAIL = ( $val );    
		else if( $key == "mlname" )
			$NAME = ( $val );    
	}  
	$HREF = $HTTP_PREFIX.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	$HREF = substr( $HREF, 0, strpos( $HREF, '_iserv' ) );
	if( $SIGN !== md5( $EMAIL . "fb38f412cb9330b36c301ebf6119bc9d" ) ) {
		header( 'Location: ' . $HREF . $URL_KO99 );		
		exit;
	}
	$old_mask = umask( 0 );
	if( !is_dir( 'data' ) ) {
		if( mkdir( 'data', 0777 ) === FALSE ) {
			umask( $old_mask ); 
			header( 'Location: ' . $HREF . $URL_KO2 );		
			exit;
		}
		else {
			touch( 'data/index.html' );
		}
	}
	chdir( 'data' );
	if( $ACTION == 'sub' ) {
		if( file_exists( $EMAIL.'.unsub' ) )
			rename( $EMAIL.'.unsub', $EMAIL.'.sub' );
		touch( $EMAIL . '.sub' );
		header( 'Location: ' . $HREF . $URL_SUB_CONF );		
	}
	else if ( $ACTION == 'unsub' ) {
		if( file_exists( $EMAIL.'.sub' ) )
			rename( $EMAIL.'.sub', $EMAIL.'.unsub' );
		touch( $EMAIL . '.unsub' );
		header( 'Location: ' . $HREF . $URL_UNSUB_CONF );		
	}
?>  
