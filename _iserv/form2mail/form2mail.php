<?php  
@require_once("../common/mails.php");
@require_once("../common/ipenv.php");
$F2M_RECEIPT	= false;		
$CRLF 			= "\r\n";				
$TO = "";    
$URL = "";
$SUBJECT = "";
$LANG = "en";
$SITELANG = "en";
$REPLY_TO = "";
$message = "";
$intro = "";  
$upload_dir = "data/";
$AUTHORIZED_EXT	= ""; 
$UPLOADMODE = 1;	  
$MAXFILESIZE = 0;
while( list($key, $val) = each($_POST) ) {  
	if( $key == "f2memail" ) {
		$TO = _local_replace_bad( $val );    
	} else if( $key == "f2memail_exp" ) {
		$REPLY_TO = _local_replace_bad( $val );    
	} else if( $key == "f2memailc" ) {
		$TO = str_rot13( _local_replace_bad( $val ) );    
	} else if( $key == "f2memailc_exp" ) {
		$REPLY_TO = _local_replace_bad( $val );    
	} else if( $key == "f2msubj" ) {
		$SUBJECT = _local_replace_bad( $val );    
	} else if( $key == "f2murl" ) {
		$URL = _local_replace_bad( $val );    
	} else if( $key == "f2mlang" ) {
		$LANG = strtolower( _local_replace_bad( $val ) );    
	} else if( $key == "f2msitelang" ) {
		$SITELANG = strtolower( _local_replace_bad( $val ) );    
	} else if( $key == "f2muploadmode" ) {
		$UPLOADMODE = strtolower( _local_replace_bad( $val ) );    
	} else if( $key == "f2mauthext" ) {
		$AUTHORIZED_EXT = strtolower( _local_replace_bad( $val ) );    				
	} else if( $key == "MAX_FILE_SIZE" ) {
		$MAXFILESIZE = _local_replace_bad( $val );
		$MAXFILESIZE /= 1024;
	} else if( substr($key,0,5) != "f2mcf" ) {  
		$message .= "$key = " . _local_replace_bad( $val ) . $CRLF;  
	}
}  
$HREF = getenv("HTTP_REFERER");
$HREF = substr( $HREF, 0, strrpos( $HREF, '/' ) + 1 );
if( $LANG != "fr" ) {
	$intro .= "This email comes from the website $HREF";
	if( $SITELANG != "" && $LANG != $SITELANG )
		$intro .= " (language=$SITELANG)";
} else {
	$intro .= "Cet email provient du site internet $HREF";
	if( $SITELANG != "" && $LANG != $SITELANG )
		$intro .= " (langue=$SITELANG)";
}
$ipsender = PMA_getIp();
if( $ipsender != "" ) {
	if( $LANG != "fr" ) {
		$intro .= " and was sent by the IP address : $ipsender";
	} else
		$intro .= " et a été envoyé par l'adresse IP : $ipsender";
}
$error_code = "";				
if( $TO != "" && $SUBJECT != "" ) {
	$headers = 
		"MIME-Version: 1.0" . $CRLF .
		"Content-Type: text/plain; charset=utf-8" . $CRLF .
		"Content-Transfer-Encoding: 8bit" . $CRLF;
	if( $REPLY_TO != "" )
		$headers .= "From: $REPLY_TO" . $CRLF . "Return-Path: $TO" . $CRLF;
	else
		$headers .= "From: $TO" . $CRLF . "Return-Path: $TO" . $CRLF;
	$headers .= "X-Mailer: PHP/" . phpversion() . $CRLF;
	if( $F2M_RECEIPT ) {
		$headers .= "Disposition-Notification-To: $TO" . $CRLF;
	}
	$upload_file = ( $_FILES['FilenameUpload']['name'] != "" );
	if( ! $upload_file ) {
		mail($TO, '=?UTF-8?B?'.base64_encode($SUBJECT).'?=', $intro.$CRLF.$CRLF.$message, $headers ); 
	} else { 
		$FilenameUpload_name = $_FILES['FilenameUpload']['name'];
		$FilenameUpload_name = str_replace( " ", "_", $FilenameUpload_name );
		$FilenameUpload_name = str_replace( "'", "_", $FilenameUpload_name );
		$FilenameUpload_name = str_replace( '"', '_', $FilenameUpload_name );
		$FilenameUpload_name = str_replace( '&', '_', $FilenameUpload_name );
		$FilenameUpload_name = str_replace( '..', '.', $FilenameUpload_name );
		preg_replace("/[^0-9^a-z^_^.^-]/", "", $FilenameUpload_name);
		if( $LANG == "fr" ) {
			$message .= $CRLF."Fichier joint = ";
		} else
			$message .= $CRLF."Attached file = ";
		if( $UPLOADMODE == 0 ) {
			$HREF = substr( $HREF, 0, strrpos( $HREF, '/' ) - 1 );
			$HREF = substr( $HREF, 0, strrpos( $HREF, '/' ) + 1 );
			$message .= $HREF . "_iserv/form2mail/data/" . $FilenameUpload_name;
		} else
			$message .= $_FILES['FilenameUpload']['name'];
		$m = new Mail;
		$m->From( $TO );
		$m->To( $TO );
		$m->Subject( $SUBJECT );
		$m->Body( $intro.$CRLF.$CRLF.$message );	
		$m->setReceipt( $F2M_RECEIPT );
		$delete_upload = false;
		if( $_FILES['FilenameUpload']['error'] == UPLOAD_ERR_OK ) {
			if( $AUTHORIZED_EXT != "" && $FilenameUpload_name != "" && strpos( $AUTHORIZED_EXT, GetExtensionName( "$FilenameUpload_name", true ) ) === false ) {
				$error_code = -2;
			} else {
				if( !move_uploaded_file( $_FILES['FilenameUpload']['tmp_name'], "$upload_dir" . "$FilenameUpload_name" ) ) {
					if( !copy( $_FILES['FilenameUpload']['tmp_name'], "$upload_dir" . "$FilenameUpload_name" ) ) {
						$error_code = 9;
					}
				}	
			}
		} else {
			$error_code = $_FILES['FilenameUpload']['error'];
		}
		if( $error_code == "" && $UPLOADMODE > 0 ) {
			$m->Attach( $upload_dir. "$FilenameUpload_name", mime_content_type( "$FilenameUpload_name" ) );
			$delete_upload = true;
		}
		if( $error_code == "" && $m->ErrMsg() != "" ) {
			$error_code = $m->ErrMsg();
		}
		if( $error_code == "" ) {
			if( $UPLOADMODE == 0 ) {
				mail($TO, '=?UTF-8?B?'.base64_encode($SUBJECT).'?=', $intro.$CRLF.$CRLF.$message, $headers );
			} else if( !$m->Send() ) {
				if( "$FilenameUpload_name" != "" ) {
					if( $LANG == "fr"  ) {
						$message .= $CRLF.$CRLF."ATTENTION: Le fichier joint n'a pas pu être envoyé par email mais il a été enregistré sur votre espace web.";
					} else {
						$message .= $CRLF.$CRLF."WARNING: The attached file could not be sent by email but is saved on your web space.";
					}
					if( !mail($TO, '=?UTF-8?B?'.base64_encode($SUBJECT).'?=', $intro.$CRLF.$CRLF.$message, $headers ) ) {
						$error_code = "-1";
					} else
						$delete_upload = false;	
				}
				else {
					$error_code = "-1";
				}
			}
		}
		if( $delete_upload ) {
			Unlink( $upload_dir . $FilenameUpload_name );
		}
	}
}
if( $error_code != "" ) {
	if( $error_code == "-1" ) {
		$error_code = "Mail server error.";
	} else if( $error_code == "-2" ) {
		$AUTHORIZED_EXT = str_replace( "|", ",", $AUTHORIZED_EXT );
		$AUTHORIZED_EXT = strtoupper( str_replace( ".", " ", $AUTHORIZED_EXT ) );
		if( $LANG == "fr"  ) {
			$error_code = "Type de fichier non accepté. Votre fichier doit être au format:" . $AUTHORIZED_EXT . ".";
		} else
			$error_code = "Type of file not accepted. File format must be:" . $AUTHORIZED_EXT . ".";
	} else if( $error_code == "1" || $error_code == "2" ) {
		if( $LANG == "fr"  ) {
			$error_code = "Votre fichier est trop gros. La taille maximale autorisée est " . $MAXFILESIZE. " Ko.";
		} else
			$error_code = "File is too big. Maximum size is " . $MAXFILESIZE . " KB.";
	} else if( $error_code == "3" || $error_code == "9" ) {
		$error_code = "Upload of the attached file failed (error ".$error_code.").";
	} else if( $error_code == "4" ) {
		$error_code = "Attached file is empty.";
	} else
		$error_code = "ERROR ".$error_code . ": ";
	echo "<script language=\"javascript\" charset=\"utf-8\">window.location='javascript:history.back()';alert('$error_code');</script>";
} else if( $URL != "" ) {
	redir("$URL");
} else {
	$URL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$URL = explode( '/_iserv', $URL );
	if( stripos($URL[0], "http://") === false && stripos($URL[0], "https://") === false ) {
		redir( "http://".$URL[0] );
	} else
		redir( $URL[0] );	
}
?>  
