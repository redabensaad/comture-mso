<?php
	@require_once("../common/com.php");
	$oid = stripslashes( $_REQUEST['oid'] );
	$fmt = stripslashes( $_REQUEST['fmt'] );
	$ctr = stripslashes( $_REQUEST['ctr'] );
	if( $ctr != getCtr( $oid, $fmt ) )
		die( 'error 99' );
	$result = "Désolé, la commande $oid est introuvable. Merci de contacter l'administrateur du site pour plus d'informations.";
	$result = "<!doctype html><head><meta charset='utf-8'></head><body>".$result."</body></html>";
	$ofile = "./data/$oid.$fmt";
	if( file_exists( $ofile ) ) {
		if (function_exists('file_get_contents')) 
		{
			$result = file_get_contents( $ofile );
		} 
		else 
		{
			$fp = fopen( $ofile, 'rb' );
			$result = fread( $fp, filesize( $ofile ) );
			fclose( $fp );
		}
	}
	if( $fmt == "txt" )
		$result = str_replace( "\n", '<br/>', $result );
	echo $result;
?>
