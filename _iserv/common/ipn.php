<?php
//--- IPN.PHP : interface pour la notification (au client & au webmaster) suite à un achat d'une commande en ligne (y compris avec fichiers en téléchargement)

//@require_once("../common/ipenv.php");
@require_once("../common/com.php");

// fonction d'activation des fichiers d'une commande
function EnableDownloadFiles( $orderid )
{
	if( @file_exists( "../dlfiles/data/$orderid.ini" ) ) {
		@rename( "../dlfiles/data/$orderid.ini", "../dlfiles/data/$orderid.mak" );
	} 
}

// retourne la liste des noms des fichiers DLs de la commande avec les URLs de download associées
function GetFilesPurchased( $file_content, $bHTML )
{
	if( strstr($file_content, "/dlfiles/dl.php?dlfile=") == false )
		return( "" );
	$BR   = "<br/>";
	$CRLF = "\n";

	if( strstr($file_content, "[ORDER]" ) == false ) {
	  $PDNAME = "_Nom =";
	  $PDCODE = "_Code =";
	  $PDFILEURL = "_Fichier =";
	} else {
	  $PDNAME = "_Name =";
	  $PDCODE = "_Code =";
	  $PDFILEURL = "_File =";
	}
	$result = "";
	for( $i=1; $i<1000; $i++ ) 
	{
		$pdName = trim( ExtractStringBetween( "".$i.$PDNAME, "\n", $file_content ) );
		if( $pdName == "" ) break;
		$pdFileUrl = trim( ExtractStringBetween( "".$i.$PDFILEURL, "\n", $file_content ) );
		if( $pdFileUrl == "" ) break;
		$pdCode = trim( ExtractStringBetween( "".$i.$PDCODE, "\n", $file_content ) );
		if( $pdCode == "" ) break;
		if( $bHTML ) {
			$result .= $pdName . $BR . "<a href='$pdFileUrl'>$pdFileUrl</a>" . $BR . $BR;
		} else {
			$result .= $pdName . $CRLF . $pdFileUrl . $CRLF . $CRLF;
		}
	}
	return( $result );
}

function GetCustomerEmail( $orderid ) {
	$orderfilename = '../twsc/data/'.$orderid.'.txt';
	if( !file_exists( $orderfilename ) )
		return( "" );
	$file_content = file_get_contents( $orderfilename );
	$CRLF = "\n";
	return( trim( ExtractStringBetween( $CRLF."email =", $CRLF, $file_content ) ) );
}

function GetCustomerLang( $orderid ) {
	$LANG = strtolower("fr");
	$orderfilename = '../twsc/data/'.$orderid.'.txt';
	if( !file_exists( $orderfilename ) )
		return( "" );
	$file_content = file_get_contents( $orderfilename );
	$CRLF = "\n";
	if( $LANG == "fr" ) {
	  $result = trim( ExtractStringBetween( $CRLF."Langue =", $CRLF, $file_content ) );
	} else {
	  $result = trim( ExtractStringBetween( $CRLF."Language =", $CRLF, $file_content ) );
	}
	return( $result );
}

function FillDeliveryMessage( $templatemessage, $orderid )
{
	$orderfilename = '../twsc/data/'.$orderid.'.txt';
	// vérification de l'existence de la commande	
	if( !file_exists( $orderfilename ) )
		return( "" );
	$LANG = strtolower("fr");
	$CRLF = "\n";	
	$file_content = file_get_contents( $orderfilename );
	// vérification de l'existence d'au moins 1 fichier DL dans la commande
	if( strpos( $file_content, "_File = http" ) === false && strpos( $file_content, "_Fichier = http" ) === false )
		return( "" );
	// remplissage des champs prédéfinis	
	$result = $templatemessage;
	$result = str_replace( "{DLFILES}", GetFilesPurchased( $file_content, false ), $result );
	$result = str_replace( "{OrderID}", $orderid, $result );
	$ORDERTOTAL = trim( ExtractStringBetween( $CRLF."TOTAL =", $CRLF, $file_content ) );
	$SITEURL = trim( ExtractStringBetween( $CRLF."Site =", $CRLF, $file_content ) );
	$CUSTEMAIL = trim( ExtractStringBetween($CRLF."email =", $CRLF, $file_content ) );
	if( $LANG == "fr" ) {
		$CUSTFIRSTNAME = trim( ExtractStringBetween( $CRLF."prénom =", $CRLF, $file_content ) );
		$CUSTLASTNAME = trim( ExtractStringBetween( $CRLF."nom =", $CRLF, $file_content ) );
		$CUSTNAME = $CUSTFIRSTNAME." ".$CUSTLASTNAME;
		$CUSTPHONE = trim( ExtractStringBetween( $CRLF."téléphone =", $CRLF, $file_content ) );
		$CUSTADDR1 = trim( ExtractStringBetween( $CRLF."adresse1 =", $CRLF, $file_content ) );
		$CUSTADDR2 = trim( ExtractStringBetween( $CRLF."adresse2 =", $CRLF, $file_content ) );
		$CUSTCITY = trim( ExtractStringBetween( $CRLF."ville =", $CRLF, $file_content ) );
		$CUSTZIP = trim( ExtractStringBetween( $CRLF."code postal =", $CRLF, $file_content ) );
		$CUSTCOUNTRY = trim( ExtractStringBetween( $CRLF."pays =", $CRLF, $file_content ) );
		$CUSTSTATE = trim( ExtractStringBetween( $CRLF."état =", $CRLF, $file_content ) );
		$CLANG = trim( ExtractStringBetween( $CRLF."Langue =", $CRLF, $file_content ) );
	} else {
		$CUSTFIRSTNAME = trim( ExtractStringBetween( $CRLF."firstname =", $CRLF, $file_content ) );
		$CUSTLASTNAME = trim( ExtractStringBetween( $CRLF."lastname =", $CRLF, $file_content ) );
		$CUSTNAME = $CUSTFIRSTNAME." ".$CUSTLASTNAME;
		$CUSTPHONE = trim( ExtractStringBetween( $CRLF."phone =", $CRLF, $file_content ) );
		$CUSTADDR1 = trim( ExtractStringBetween( $CRLF."address1 =", $CRLF, $file_content ) );
		$CUSTADDR2 = trim( ExtractStringBetween( $CRLF."address2 =", $CRLF, $file_content ) );
		$CUSTCITY = trim( ExtractStringBetween( $CRLF."city =", $CRLF, $file_content ) );
		$CUSTZIP = trim( ExtractStringBetween( $CRLF."zip =", $CRLF, $file_content ) );
		$CUSTCOUNTRY = trim( ExtractStringBetween( $CRLF."country =", $CRLF, $file_content ) );
		$CUSTSTATE = trim( ExtractStringBetween( $CRLF."state =", $CRLF, $file_content ) );
		$CLANG = trim( ExtractStringBetween( $CRLF."Language =", $CRLF, $file_content ) );
	}
	$result = str_replace( "{OrderTotal}", $ORDERTOTAL, $result );
	$result = str_replace( "{SiteUrl}", $SITEURL, $result );
	$result = str_replace( "{ClientName}", $CUSTNAME, $result );
	$result = str_replace( "{ClientEmail}", $CUSTEMAIL, $result );
	$result = str_replace( "{ClientPhone}", $CUSTPHONE, $result );
	$result = str_replace( "{ClientFirstname}", $CUSTFIRSTNAME, $result );
	$result = str_replace( "{ClientLastname}", $CUSTLASTNAME, $result );
	$result = str_replace( "{ClientAddress}", $CUSTADDR1, $result );
	$result = str_replace( "{ClientAddress2}", $CUSTADDR2, $result );
	$result = str_replace( "{ClientCity}", $CUSTCITY, $result );
	$result = str_replace( "{ClientPostalCode}", $CUSTZIP, $result );
	$result = str_replace( "{ClientCountryCode}", $CUSTCOUNTRY, $result );
	$result = str_replace( "{ClientStateCode}", $CUSTSTATE, $result );
	$result = str_replace( "{SiteLang}", $CLANG, $result );
	// **TODO BP** : completer en gérant aussi ces champs là :
	//{OrganizationName}
	//{OrganizationAddress}
	//{OrganizationContact}
	//{OrderDate}
	return( $result ); 
}

function SendOrderEmail( $orderid, $emailtitle, $emailmessage ) {
	$MERCHANT_FROM = "<rplus.informatique@gmail.com>";
	$MERCHANT_BCC = false;
	$CRLF = "\r\n";
	$cust_email = GetCustomerEmail( $orderid );
	if( $cust_email != "" ) 
	{
		// on effectue la livraison par email
		$emailtitle = FillDeliveryMessage( $emailtitle, $orderid );
		$emailmessage = FillDeliveryMessage( $emailmessage, $orderid );
		if( $emailtitle != "" && $emailmessage != "" ) 
		{
			$headers = 
				"MIME-Version: 1.0" . $CRLF .
				"Content-Type: text/plain; charset=utf-8" . $CRLF .
				"Content-Transfer-Encoding: 8bit" . $CRLF .
				"From: $MERCHANT_FROM" . $CRLF .
				( ( $MERCHANT_BCC ) ? "Bcc: $MERCHANT_FROM" . $CRLF : "" ) .
				"Return-Path: $MERCHANT_FROM" . $CRLF .
				"X-Mailer: PHP/" . phpversion() . $CRLF;
			//echo "$emailtitle<br>$emailmessage";
			@mail( $cust_email, '=?UTF-8?B?'.base64_encode($emailtitle).'?=', $emailmessage, $headers );
		}
	}
}

function DeliverOrderByEmail( $orderid ) 
{
	// textes multilingues pour les emails de livraison
	$IPN_DEFAULT_LANG = "fr";
	$IPN_SUPPORTED_LANGS = array( "fr" ); 
	$IPN_EMAIL_FDL_TITLE = array( "fr" => "Livraison de votre commande {OrderID}" );
	$IPN_EMAIL_FDL_MSG = array( "fr" => "Bonjour {ClientName},\r\nveuillez trouver ci-dessous les fichiers que vous avez achetés dans votre commande {OrderID}:\r\n\r\n{DLFILES}\r\nMerci et à bientôt." );
	// récupération de la langue du client
	$lang = GetCustomerLang( $orderid );
	if( $lang == "" || !in_array( $lang, $IPN_SUPPORTED_LANGS ) ) 
		$lang = $IPN_DEFAULT_LANG;
	// on rend disponible les eventuels fichiers downloads achetés dans cette commande
	EnableDownloadFiles( $orderid );
	// livraison de l'email de la commande en fonction de la langue du client	
	SendOrderEmail( $orderid, $IPN_EMAIL_FDL_TITLE[ $lang ], $IPN_EMAIL_FDL_MSG[ $lang ] );
}

/*
DeliverOrderByEmail( "14869D34720" ); // en @ publimedia
echo "<hr>";
DeliverOrderByEmail( "14869E66622" ); // fr @ contact
*/
/*
	$fileext = strtolower(substr(basename($templatefile), strrpos(basename($templatefile), '.')+1));
	$filecontent = '../twsc/'.$templatefile;
	if( !file_exists( $filecontent ) )
		return( "" );	
	$file_content = file_get_contents( $filecontent );
*/	

?>