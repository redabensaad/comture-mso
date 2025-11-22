<?php
	@require_once("../common/ipenv.php");
	@require_once("../common/com.php");
	$BACKUP_ORDER	= true;		
	$MERCHANT_TO	= "<rplus.informatique@gmail.com>";		
	$MERCHANT_FROM	= "<rplus.informatique@gmail.com>";	
	$MERCHANT_BCC 	= false;		
	$MERCHANT_DATE_FORMAT = "dd/mm/yyyy";	
	$CRLF 			= "\r\n";				
	$HTTP_PREFIX	= (false)?'https://':'http://';		
	$VO_CTR = "ae494744677071d9524bdb16ef7bdfa8";					
	$MAX_PERIOD = -1;				
	$ORDER_EMAIL_ADD_URL = false;		
	$hostsite = $_SERVER['HTTP_HOST'];
	$c_subject = str_replace( '{SiteUrl}', $hostsite, stripslashes( $_REQUEST['client_subject'] ) );
	$v_subject = str_replace( '{SiteUrl}', $hostsite, stripslashes( $_REQUEST['vendor_subject'] ) );
	$cinst = stripslashes( $_REQUEST['cinst'] );		
	$oanchor = stripslashes( $_REQUEST['oanchor'] );	
	$ohtml = stripslashes( $_REQUEST['ohtml'] );		
	$otxt = stripslashes( $_REQUEST['otxt'] );			
	$otxt = str_replace( "<br/>", " ", $otxt );			
	$ojson = stripslashes( $_REQUEST['ojson'] );		
	$cemail = $_REQUEST['cemail'];
	$ctr = $_REQUEST['ctr'];
	$orderID = $_REQUEST['oid'];
	$result = '';
	$ohtml_url = "";
	$otxt_url = "";
	$otxt_server = "";	
	$odlfile_entry = "";	
	if( strstr($otxt, '[COMMANDE]') === false ) {
		$otxt_server = "[SERVER]\n";
		if( strstr($otxt, "_File = ") !== false )
			$odlfile_entry = "_File = ";
	} else {
		$otxt_server = "[SERVEUR]\n";		
		if( strstr($otxt, "_Fichier = ") !== false )
			$odlfile_entry = "_Fichier = ";	
	}
	$otxt_server .= "Client IP = " . PMA_getIp() . "\nDate = ";
	if( $MERCHANT_DATE_FORMAT == "dd/mm/yyyy" ) {
		$otxt_server .= date("d-m-Y @ H:i:s (\G\M\TO)") . "\n";
	} else
		$otxt_server .= date("m-d-Y @ H:i:s (\G\M\TO)") . "\n";
	if( $ctr == "" || $ctr != $VO_CTR )	
		die( 'ERR_CTR '  );
	if( $BACKUP_ORDER && is_dir( 'data' ) ) {
		$BOM = "\xEF\xBB\xBF";
		$logfile = "data/$orderID-log.txt";
		$fp=fopen( $logfile, "w" );
		if( $fp !== false ) { 
			fwrite( $fp, "error=$orderID" );
			fclose( $fp );
		} else
			$logfile = "";
		checkIServDataDir( '../', 'twsc', false );
		if( $odlfile_entry != "" ) {
			$odl_url = $HTTP_PREFIX . $hostsite . dirname( $_SERVER['SCRIPT_NAME'] ) . "/dl.php?";
			$odl_url = str_replace( "/twsc/", "/dlfiles/", $odl_url ); 
			$inifile_content = "";
			$dlf_index = 1;
			while ( strstr($otxt, "\n".$dlf_index.$odlfile_entry) !== false ) {
				$dlfile = ExtractStringBetween( "\n".$dlf_index.$odlfile_entry, "\n", $otxt );
				$otxt = str_replace( $dlfile, $odl_url."dlfile=".$dlfile."&dlorder=".$orderID."&dlkey=".getCtr($orderID, $dlfile), $otxt );
				$inifile_content .= "downloadcount_".$dlfile."=0\r\nexpiredate_".$dlfile."=";
				$inifile_content .= ( ( $MAX_PERIOD <= 0 ) ? "0" : date( "Y-m-d", strtotime( "+".$MAX_PERIOD." days" ) ) );
				$inifile_content .= "\r\n";
				$dlf_index++;
			} 
			if( is_dir( "../dlfiles/data" ) ) {
				if( !$fh = fopen( "../dlfiles/data/".$orderID.".ini", "w+") ) {
				} else {
					fwrite( $fh, $inifile_content );
					fclose( $fh );
				}
			}
		}
		$fp=fopen( "data/$orderID.txt", "w" );
		if( $fp !== false ) { 
			if( fwrite($fp, $BOM . $otxt) === false )	
				$result .= 'ERR_BKTXT ';
			else {
				$otxt_url = $HTTP_PREFIX . $hostsite . dirname( $_SERVER['SCRIPT_NAME'] ) . "/so.php?oid=$orderID&fmt=txt&ctr=" . getCtr( $orderID, "txt" );
				$otxt_server .= "Text Order = $otxt_url\n";
			}
			fclose($fp);
			$fp=fopen( "data/$orderID.html", "w" );
			if( $fp !== false ) { 
				if( fwrite($fp, $ohtml) === false )
					$result .= 'ERR_BKHTM ';
				else {
					$ohtml_url = $HTTP_PREFIX . $hostsite . dirname( $_SERVER['SCRIPT_NAME'] ) . "/so.php?oid=$orderID&fmt=html&ctr=" . getCtr( $orderID, "html" );
					$otxt_server .= "HTML Order = $ohtml_url\n";
				}
				fclose($fp);
			}
			$fp=fopen( "data/$orderID.json", "w" );
			if( $fp !== false ) { 
				if( fwrite($fp, $BOM . $ojson) === false )	
					$result .= 'ERR_BKJSON ';
				fclose($fp);
			}
			if( strlen($logfile) > 0 )
				unlink($logfile);
		}
	}
	$otxt = "$otxt_server\n$otxt";
	$to = "";
	$headers = 
		"MIME-Version: 1.0" . $CRLF .
		"Content-Type: text/plain; charset=utf-8" . $CRLF .
		"Content-Transfer-Encoding: 8bit" . $CRLF .	
		"From: $MERCHANT_FROM" . $CRLF .
		"Return-Path: $MERCHANT_FROM" . $CRLF .
		"X-Mailer: PHP/" . phpversion() . $CRLF;
	$to = $MERCHANT_TO;
	if( @mail( $to, '=?UTF-8?B?'.base64_encode($v_subject).'?=', $otxt, $headers ) === false )
		$result .= 'ERR_MLC ';
	if( $cemail != "" ) {
		sleep(1);
		$to = "";
		$headers = 
			"MIME-Version: 1.0" . $CRLF .
			"Content-Type: text/html; charset=utf-8" . $CRLF .
			"Content-Transfer-Encoding: 8bit" . $CRLF;	
		$to = $cemail;
		if( $MERCHANT_BCC )
			$headers .= "Bcc: $MERCHANT_TO" . $CRLF;
		$headers .= 
			"From: $MERCHANT_FROM" . $CRLF .
			"Return-Path: $MERCHANT_FROM" . $CRLF .
			"X-Mailer: PHP/" . phpversion() . $CRLF;
		if( $ORDER_EMAIL_ADD_URL )
			$cinst .= "<a href=\"$ohtml_url\">$oanchor</a>";
		if( strpos( $cinst, "<html" ) === false )
			$cinst = "<html><head><meta http-equiv=\"content-type\" content=\"text/html;charset=UTF-8\"></head><body>$cinst</body></html>";
		if( $ohtml_url !== '' )
			$cinst = str_replace( '<!--EOID-->', '</a>', str_replace( '<!--SOID-->', "<a href=\"$ohtml_url\">", $cinst ) );
		if( @mail( $to, '=?UTF-8?B?'.base64_encode($c_subject).'?=', $cinst, $headers ) === false )
			$result .= 'ERR_MLV ';
	}
	echo $result;
?>
