<?php
function is_mail_valid($email)
{
	return preg_match('/^[a-z\'0-9]+([._-][a-z\'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/i', $email);
}
function _local_replace_bad($value) 
{
	$report_to = "";
	$suspicious_str = array
	(
		"/content-type:/i"
		,"/charset=/i"
		,"/mime-version:/i"
		,"@multipart/mixed@i"
		,"/bcc:/i"
	);
	$suspect_found = false;
	$value = stripslashes($value);
	foreach($suspicious_str as $suspect) {
		if(preg_match($suspect, strtolower($value))) {
			$suspect_found = true;  
			$value = preg_replace($suspect, "(anti-spam-".$suspect.")", $value);
		}
	}
	if ($suspect_found) {
		$ip = (empty($_SERVER['REMOTE_ADDR'])) ? 'empty' : $_SERVER['REMOTE_ADDR'];
		$rf = (empty($_SERVER['HTTP_REFERER'])) ? 'empty' : $_SERVER['HTTP_REFERER'];
		$ua = (empty($_SERVER['HTTP_USER_AGENT'])) ? 'empty' : $_SERVER['HTTP_USER_AGENT'];
		$ru = (empty($_SERVER['REQUEST_URI'])) ? 'empty' : $_SERVER['REQUEST_URI'];
		$rm = (empty($_SERVER['REQUEST_METHOD'])) ? 'empty' : $_SERVER['REQUEST_METHOD'];
		if ($ua == "empty") {
			exit();
		}
		if(isset($report_to) && !empty($report_to)) {
			@mail(
				$report_to,
				"[ABUSE] [SUSPECT] @ " . $_SERVER['HTTP_HOST'] . " by " . $ip,
				"Stopped possible mail-injection @ " .
				$_SERVER['HTTP_HOST'] . " by " . $ip .
				" (" . date('d/m/Y H:i:s') . ")\r\n\r\n" .
				"*** IP/HOST\r\n" . $ip . "\r\n\r\n" .
				"*** USER AGENT\r\n" . $ua . "\r\n\r\n" .
				"*** REFERER\r\n" . $rf . "\r\n\r\n" .
				"*** REQUEST URI\r\n" . $ru . "\r\n\r\n" .
				"*** REQUEST METHOD\r\n" . $rm . "\r\n\r\n" .
				"*** SUSPECT\r\n-----\r\n" . $value . "\r\n-----"
			);
		}
	} 
	return($value);
}
class Mail {
	var $sendto = array();
	var $from, $msubject, $mreceipt;
	var $acc = array();
	var $abcc = array();
	var $aattach = array();
	var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
	var $err_msg = "";
	var $eol = PHP_EOL;	
	function Mail() {
		$this->autoCheck( false );
		$this->setReceipt( false );
	}
	function autoCheck( $bool ) {
		if( $bool )
			$this->checkAddress = true;
		else
			$this->checkAddress = false;
	}
	function setReceipt( $bool ) {
		if( $bool )
			$this->mreceipt = true;
		else
			$this->mreceipt = false;
	}
	function Subject( $subject ) {
		$this->msubject = strtr( $subject, "\r\n" , "  " );
	}
	function From( $from ) {
		if( is_array( $from ) )
			$this->from = $from;
		else
			$this->from[] = $from;
		$this->from = $from;
	}
	function To( $to ) {
		if( is_array( $to ) )
			$this->sendto = $to;
		else
			$this->sendto[] = $to;
		if( $this->checkAddress == true )
			$this->CheckAddresses( $this->sendto );
	}
	function Cc( $cc ) {
		if( $cc != "" ) {
			if( is_array($cc) )
				$this->acc = $cc;
			else
				$this->acc[] = $cc;
			if( $this->checkAddress == true )
				$this->CheckAddresses( $this->acc );
		}
	}
	function Bcc( $bcc ) {
		if( $bcc != "" ) {
			if( is_array($bcc) ) {
				$this->abcc = $bcc;
			} else {
				$this->abcc[] = $bcc;
			}
			if( $this->checkAddress == true )
				$this->CheckAddresses( $this->abcc );
		}
	}
	function ErrMsg() {
		return(	$this->err_msg );
	}
	function Body( $body ) {
	    $this->body = $body;
	}
	function Send( ) {
		$this->_build_headers();
		if( sizeof( $this->aattach ) > 0 ) {
			$this->_build_attachement();
			$body = $this->fullBody . $this->attachment;
		}
		else
			$body = $this->body;
		for( $i = 0; $i < sizeof($this->sendto); $i++ ) {
			$res = mail($this->sendto[$i], $this->msubject, $body, $this->headers);
		}
		return $res;
	}
	function Organization( $org ) {
		if( trim( $org != "" )  )
			$this->organization = $org;
	}
	function Priority( $priority ) {
		if( !intval( $priority ) )
				return false;
		if( !isset( $this->priorities[$priority-1]) )
				return false;
		$this->priority = $this->priorities[$priority-1];
		return true;
	}
	function Attach( $filename, $filetype='application/x-unknown-content-type', $disposition = "inline" ) {
	        $this->aattach[] = $filename;
	        $this->actype[] = $filetype;
	        $this->adispo[] = $disposition;
	}
	function Get() {
		$this->_build_headers();
		if( sizeof( $this->aattach ) > 0 ) {
				$this->_build_attachement();
				$this->body = $this->body . $this->attachment;
		}
		$mail = $this->headers;
		$mail .= $this->eol . "$this->body";
		return $mail;
	}
	function ValidEmail($address) {
		if( ereg( ".*<(.+)>", $address, $regs ) ) {
			$address = $regs[1];
		}
		if( ereg( "^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|fr|com|gov|mil|org|edu|int)\$",$address) )
			return true;
		else
			return false;
	}
	function CheckAddresses( $aad ) {
		for( $i = 0; $i < sizeof( $aad ); $i++ ) {
			if( ! $this->ValidEmail( $aad[$i]) ) {
				$this->err_msg = "ERROR : Invalid Email Address $aad[$i]";
				return false;
			}
		}
		return true;
	}
	function _build_headers() {
		$this->headers = "From: $this->from".$this->eol;
		$this->to = implode( ", ", $this->sendto );
		if( count($this->acc) > 0 ) {
				$this->cc = implode( ", ", $this->acc );
				$this->headers .= "CC: $this->cc".$this->eol;
		}
		if( count($this->abcc) > 0 ) {
				$this->bcc = implode( ", ", $this->abcc );
				$this->headers .= "BCC: $this->bcc".$this->eol;
		}
		if( $this->organization != ""  )
				$this->headers .= "Organization: $this->organization".$this->eol;
		if( $this->priority != "" )
				$this->headers .= "X-Priority: $this->priority".$this->eol;
		if( $this->mreceipt )
				$this->headers .= "Disposition-Notification-To: $this->from".$this->eol;
	}
	function _build_attachement() {
	        $this->boundary = "------------" . md5( uniqid("myboundary") ); 
	        $this->headers .= "MIME-Version: 1.0".$this->eol."Content-Type: multipart/mixed; boundary=\"$this->boundary\"".$this->eol.$this->eol;
	        $this->fullBody = "This is a multi-part message in MIME format.".$this->eol.$this->eol."--$this->boundary\nContent-Type: text/plain; charset=utf-8".$this->eol."Content-Transfer-Encoding: 8bit" . $this->eol . $this->eol . $this->body .$this->eol;
	        $ata = array();
	        $k = 0;
	        for( $i = 0; $i < sizeof( $this->aattach ); $i++ ) {
	                $filename = $this->aattach[$i];
	                $basename = basename($filename);
	                $ctype = $this->actype[$i];       
	                $disposition = $this->adispo[$i];
	                if( !file_exists( $filename) ) {
						$this->err_msg = "ERROR : file $filename can't be found"; 
						return;
	                }
	                $subhdr = "--".$this->boundary.$this->eol."Content-type: application/octet-stream; name=\"$basename\"".$this->eol."Content-Transfer-Encoding: base64".$this->eol."Content-Disposition: $disposition; filename=\"$basename\"".$this->eol;
	                $ata[$k++] = $subhdr;
	                $data = base64_encode( file_get_contents( $filename ) );
	                $ata[$k++] = chunk_split( $data ) .$this->eol. "--".$this->boundary."--";
	        }
	        $this->attachment = implode( $this->eol, $ata );
	}
} 
function GetExtensionName( $FileName, $Dot )
{
	if( $Dot == true) { 
		return( strtolower( strrchr( $FileName, '.' ) ) ); 
	} 
	return( strtolower( substr( $FileName, strrpos($FileName, '.') + 1 ) ) );
}
if( !function_exists('mime_content_type') ) {
    function mime_content_type($filename) {
        $mime_types = array(
            '.txt' => 'text/plain',
            '.htm' => 'text/html',
            '.html' => 'text/html',
            '.php' => 'text/html',
            '.css' => 'text/css',
            '.js' => 'application/javascript',
            '.json' => 'application/json',
            '.xml' => 'application/xml',
            '.swf' => 'application/x-shockwave-flash',
            '.flv' => 'video/x-flv',
            '.png' => 'image/png',
            '.jpe' => 'image/jpeg',
            '.jpeg' => 'image/jpeg',
            '.jpg' => 'image/jpeg',
            '.gif' => 'image/gif',
            '.bmp' => 'image/bmp',
            '.ico' => 'image/vnd.microsoft.icon',
            '.tiff' => 'image/tiff',
            '.tif' => 'image/tiff',
            '.svg' => 'image/svg+xml',
            '.svgz' => 'image/svg+xml',
            '.zip' => 'application/zip',
            '.rar' => 'application/x-rar-compressed',
            '.exe' => 'application/x-msdownload',
            '.msi' => 'application/x-msdownload',
            '.cab' => 'application/vnd.ms-cab-compressed',
            '.mp3' => 'audio/mpeg',
            '.qt' => 'video/quicktime',
            '.mov' => 'video/quicktime',
            '.pdf' => 'application/pdf',
            '.psd' => 'image/vnd.adobe.photoshop',
            '.ai' => 'application/postscript',
            '.eps' => 'application/postscript',
            '.ps' => 'application/postscript',
            '.doc' => 'application/msword',
            '.rtf' => 'application/rtf',
            '.xls' => 'application/vnd.ms-excel',
            '.ppt' => 'application/vnd.ms-powerpoint',
            '.odt' => 'application/vnd.oasis.opendocument.text',
            '.ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
		if( !$type = $mime_types[ GetExtensionName( $filename, true ) ] ) 
			$type = "application/octet-stream";	
        return( $type );
    }
}
function redir($url_redir) {
	echo "<script language=\"javascript\">window.location=('$url_redir');</script>";
}
?>
