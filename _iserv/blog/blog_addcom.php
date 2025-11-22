<?php
	@require_once("../common/mails.php");
	function lock($lock, $tries) {
	       $lock0 = "{$lock}.lck0";
	       $lock1 = "{$lock}.lck1";
	       for ($i=0; $i<$tries; $i++) {
	               if (!is_file($lock0)) {
	                       touch($lock0);
	                       if (!is_file($lock1)) {
	                               touch($lock1);
	                               return true;
	                       }
	               }
	               usleep(100);
	       }
	       return false;
	}
	function unlock ($lock) {
	       unlink("{$lock}.lck0");
	       unlink("{$lock}.lck1");
	}
	function checkip($file) {
		include '../common/ipenv.php';
		$ip = PMA_getIp();
		if( $ip === false ) {
			return 99;
		}
		else {
			$iplockfile = $file . '.ip.' . $ip;
			if(file_exists ($iplockfile)) {
				$dt = filectime($iplockfile);
				if(time() - $dt > 180 ){
					unlink($iplockfile);
				}
				else {
					return 1;
				}
			}
			touch($iplockfile);
			return 0;
		}
	}
	function xmlentities( $string )
	{
		return str_replace( array ( '&', '<', '>', '"', "'" ), array ( '&amp;', '&lt;', '&gt;', '&quot;', '&apos;' ), $string );
	}
	function strpos2( $haystack, $needle, $nth = 1 )
	{
	    $offset=0;
	    for( $i = 1; $i <= $nth; $i++ ) {
	        $offset = strpos( $haystack, $needle, $offset );
			if( $offset === false )
				return false;
			else if( $i == $nth )
				return $offset;
			else
				$offset += strlen( $needle );
		}
	}
	$js_errmsg = "Désolé, une erreur s\'est produite et votre commentaire n\'a pu être enregistré. Contactez l\'administrateur du site.";
	$js_errmsg_ip = "Vous avez posté un commentaire très récemment, merci de patienter quelques minutes avant de poster à nouveau.";
	$CRLF = "\r\n";	
	if( $_POST[ "PRoBSNkW" ] !== 'PRoBSNkWb45Mqq0Khb3L1jYeTBREkyaa' ) {
		exit;
	}
	$paraid = $_GET[ "paraid" ];
	$blog_file_root = "./data/" . $paraid;
	$blog_file_xml = $blog_file_root . ".xml";
	$blog_file_txt = $blog_file_root . ".txt";
	$author = stripslashes( trim( $_POST[ "author" ] ) );
	$email = stripslashes( trim($_POST[ "email" ] ) );
	$url = stripslashes( trim($_POST[ "url" ] ) );
	$text = stripslashes( trim($_POST[ "text" ] ) );
	$rate = $_POST[ "rate" ];
	if( strtolower( trim( $url ) ) == 'http://' )
		$url = '';
	$admin_mode = false;
	$admin_command = '';
	$admin_comment_idx = -1;
	if( md5( $author ) == 'f5bdfa94ca640ad7f333bcf52becc5a5' ) {
		if( md5( $email ) == '300a315e1c2fd27fca740a3ee06103be' ) {
			$admin_mode = true;
			$admin_command = substr( $url, 0, 3 );
			if( $admin_command == '' )
				$admin_comment_idx = -1;
			else if( $admin_command == 'DEL' || $admin_command == 'MOD' )
				$admin_comment_idx = substr( $url, 3 );
			else {
				echo "<script language=\"javascript\">alert('Commande administrative invalide, utilisez \"DELn\" ou \"MODn\" (avec \"n\" égal au numéro de commentaire à modérer). (" . $url . ")');</script>";
				exit;
			}
		}
		else {
			echo "<script language=\"javascript\">alert('Envoyer un message avec ce nom n\'est pas autorisé, merci d\'utiliser un autre nom (" . $author . ")');</script>";
			exit;
		}
	}
	else {
		if( $author == '' || $text == '' )
			exit;
	}
	$html_text = str_replace( array ( "\r\n", "\n" ), "<br/>", xmlentities( $text ) );
	$old_mask = umask( 0 );
	if( !is_dir( 'data' ) ) {
		if( mkdir( 'data', 0777 ) === FALSE ) {
			umask( $old_mask ); 
			echo "<script language=\"javascript\">alert('" . $js_errmsg . " (error 2)');</script>";
			exit;
		}
		else {
			touch( 'data/index.html' );
		}
	}
	if( $admin_mode == false ) {
		switch (checkip( $blog_file_root ) ) {
			case 0:
				break;
			case 1:
				echo "<script language=\"javascript\">alert('" . $js_errmsg_ip . "');</script>";
				exit;
				break;
			case 99:
				echo "<script language=\"javascript\">alert('" . $js_errmsg_ip . " (error 99)');</script>";
				exit;
				break;
		}
	}
	$xml = '';
	if( file_exists( $blog_file_xml ) ) {
		$fh = fopen( $blog_file_xml, 'r' );
		$xml = fread( $fh, filesize( $blog_file_xml ) );
		fclose( $fh );
	}
	else {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' .
		       "\n<blog>\n</blog>";
	}
	if( strpos( $xml, 'encoding="UTF-8"' ) === false ) {
		include '../common/iso2utf8.php';
		$enc_source = '';
		$startpos = strpos( $xml, 'encoding="' );
		if( $startpos !== false ) {
			$startpos += 10; 
			$endpos = strpos( $xml, '"', $startpos );
			if( $endpos !== false ) {
				$enc_source = substr( $xml, $startpos, $endpos - $startpos );
				$xml = substr_replace( $xml, 'UTF-8', $startpos, $endpos - $startpos );
			}
		}
		if( strlen( $enc_source ) > 0 ) {
			$startpos = strpos( $xml, '<text>' );
			while( $startpos !== false ) {
				$startpos += 6; 
				$endpos = strpos( $xml, '</text>', $startpos );
				if( $endpos !== false ) {
					$xml = substr_replace( $xml, iso2utf8( $enc_source, substr( $xml, $startpos, $endpos - $startpos ) ), $startpos, $endpos - $startpos );
				}
				$startpos = strpos( $xml, '<text>', $startpos  );
			}
		}
	}
	if( $admin_mode == true && $admin_command != '' ) {
		if( $admin_command == 'DEL' ) {
			$startpos = strpos2( $xml, '<comment>', $admin_comment_idx );
			if( $startpos === false || $startpos === -1 ) {
				echo "<script language=\"javascript\">alert('Index de commentaire invalide (" . $url . ")');</script>";
				exit;
			}
			else {
				while( $startpos > 0 && $xml[ $startpos-1 ] == ' ' )
					$startpos--;
				$endpos = strpos( $xml, '</comment>', $startpos );
				if( $endpos === false ) {
					echo "<script language=\"javascript\">alert('script error : startpos found but endpos not found !');</script>";
					exit;
				}
				else {
					$endpos += strlen( '</comment>' );
					$xml = substr_replace( $xml, '', $startpos, $endpos - $startpos );
				}
			}
		}
		else if( $admin_command == 'MOD' ) {
			$startpos = strpos2( $xml, '<text>', $admin_comment_idx );
			if( $startpos === false || $startpos === -1 ) {
				echo "<script language=\"javascript\">alert('ERR_BAD_ADMINCOMIDX (" . $url . ")');</script>";
				exit;
			}
			else {
				$startpos += strlen( '<text>' );
				$endpos = strpos( $xml, '</text>', $startpos );
				if( $endpos === false ) {
					echo "<script language=\"javascript\">alert('script error : startpos found but endpos not found !');</script>";
					exit;
				}
				else {
					$xml = substr_replace( $xml, $html_text, $startpos, $endpos - $startpos );
				}
			}
		}
	}
	else {
		$url = xmlentities( $url );
		$comment = 
			" <comment>\n" .
			"  <date>" . date( "YmdHis" ) . "</date>\n" .
		$comment .= ( $author === '' ) ? "  <author/>\n" : "  <author>${author}</author>\n";
		$comment .= ( $url === '' ) ? "  <url/>\n" : "  <url>${url}</url>\n";
		$comment .= 
			"  <text>" . $html_text . "</text>\n" .
			( ( $rate == '' ) ? "" : "  <rate>" . $rate . "</rate>\n" ) .
			" </comment>";
		$xml = str_replace("</blog>", $comment . "\n</blog>", $xml);
	}
	$lock_tries = 10;
	if( lock( $blog_file_root, $lock_tries ) !== true )	{
		echo "<script language=\"javascript\">alert('" . $js_errmsg . " (error 9)');</script>";
		exit;
	}
	$fh = fopen($blog_file_xml, 'w+');
	if($fh === false) {
		umask($old_mask); 
		echo "<script language=\"javascript\">alert('" . $js_errmsg . " (error 3)');</script>";
	}
	else {
		fwrite($fh, $xml);
		fclose($fh);
		if( $rate != '' ) {
			$rate = 0;
			$nrate = 0;
			$startpos = strpos( $xml, '<rate>' );
			while( $startpos !== false ) {
				$startpos += 6;	
				$endpos = strpos( $xml, '</rate>', $startpos );
				if( $endpos !== false ) {
					$nrate++;
					$rate += substr( $xml, $startpos, $endpos - $startpos );
				}
				$startpos = strpos( $xml, '<rate>', $startpos );
			}
			if( $nrate > 0 )
				$rate = $rate / $nrate;
		}
		if( $rate == '' ) {
			$fh = fopen($blog_file_txt, 'r');
			$txt = fread( $fh, filesize( $blog_file_txt ) );
			fclose($fh);
			$arr = explode( "|", $txt );
			if( count( $arr ) > 1 )
				$rate = $arr[ 1 ];
		}
		$fh = fopen($blog_file_txt, 'w+');
		fwrite($fh, substr_count( $xml, "<comment>" ) . ( ( $rate == '' ) ? '' : '|' . $rate ) );
		fclose($fh);
		umask($old_mask); 
		echo "<script language=\"javascript\">window.parent.location.reload();</script>";
		
	}
   	unlock( $blog_file_root );
?>
