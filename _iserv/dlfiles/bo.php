<?php
@require_once("../common/com.php");
@require_once("../common/ipn.php");
@require_once("files.php");
$expiredtime = 3600; 
$scriptfilename = basename( $_SERVER['SCRIPT_NAME'] );
$lang = "fr";
$ctr = "9fd147ec05c84d68c7699bd279526384";	
$MaxDownloads = -1;
$MaxPeriod = -1;
$perpage= 20;
$path = "data";
$DateFmt = ($lang == "fr") ? "d/m/y":"m/d/y";
if( $lang == "fr" ) {
	$DateFmt = "d/m/y";
	$DateFmtUI = "aaaa-mm-jj";
} else {
	$DateFmt = "m/d/y";
	$DateFmtUI = "yyyy-mm-dd";
}
$public_filenames = get_public_filenames_array(); 
$website = $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
$website = substr( $website, 0, strpos( $website, '/_iserv') );
function formatDateYMD2UI( $ymdDate ) {
	global $DateFmt;
	$sep = $DateFmt[1];
	$year = substr( $ymdDate, 2, 2 );
	$month = substr( $ymdDate, 5, 2 );
	$day = substr( $ymdDate, 8, 2 );
	if( $DateFmt[0] == "d" ) {
		return( $day.$sep.$month.$sep.$year );
	}
	return( $month.$sep.$day.$sep.$year );
}
if( $lang == "fr" ) 
{
	$OrderLabel = "Commande";
	$FilesLabel = "Fichiers";
	$DownloadLabel = "Téléchargements";
	$StatusLabel = "Etat";
	$EnabledLabel = "Activer les téléchargements de cette commande";
	$DisabledLabel = "Désactiver les téléchargements de cette commande";
	$ConfirmLabel = "Désirez-vous ";
	$ConfirmUpdate = "Confirmez-vous l'enregistrement des informations de téléchargement de cette commande ?";
	$ConfirmDownload = "Vous êtes sur le point de télécharger le fichier, ce qui incrémentera de 1 le nombre de téléchargements pour cette commande.\\n\\nConfirmez-vous le téléchargement ?";
	$EditBtn = "Editer";
	$DeleteBtn = "Supprimer";
	$DeleteAllBtn = "Tout supprimer";
	$DeleteConfirm = "ATTENTION : Cette action est irréversible !\\n\\nConfirmez-vous la suppression des téléchargements de cette commande ?";
	$ConfirmDelivery = "Désirez-vous également envoyer à ce client un email contenant les liens de téléchargement de son achat ?";
	$nofilemsg = "Aucun fichier";
	$loginLabel = "Identifiant:";
	$pwdLabel = "Mot de passe:";
	$LogonBtn = "Connexion";
	$LogoffBtn = "Déconnexion";
	$BackBtn = "< Retour";
	$SaveChangesBtn = "Enregistrer";
	$InternalNameLabel = "Nom de fichier interne";
	$DownloadsLabel = "Téléchargements déjà effectués";
	$ExpDateLabel = "Date d'expiration (année-mois-jour)";
	$InvalidDate = "Date incorrecte : ";
	$ExpiredLabel = "Interdit";
	$DesactivatedLabel = "Désactivé";
	$ActivatedLabel = "Activé";
	$NoExpiration = "Aucune";
	$ConfigurationInfo = "<p><b>Configuration actuelle du i-service de téléchargement</b></p><p>La limite maximale du nombre de téléchargements par commande est actuellement fixée à <b>" . ($MaxDownloads>0?$MaxDownloads:$NoExpiration) . "</b> et la période maximale (en jours) par défaut pour les téléchargements est fixée à <b>" . ($MaxPeriod>0?$MaxPeriod:$NoExpiration) . "</b>.</p>";
}
else
{
	$OrderLabel = "Order";
	$FilesLabel = "Files";
	$DownloadLabel = "Downloads";
	$StatusLabel = "Status";
	$EnabledLabel = "Enable the downloads of this order";
	$DisabledLabel = "Disable the downloads of this order";
	$ConfirmLabel = "Please confirm that you want to ";
	$ConfirmUpdate = "Please confirm that you want to save the downloads information of this order ?";
	$ConfirmDownload = "You are about to download the file, and this will increment download count of this order.\\n\\nPlease confirm this download ?";
	$EditBtn = "Edit";
	$DeleteBtn = "Delete";
	$DeleteAllBtn = "Delete all files";
	$DeleteConfirm = "CAUTION: This action is irreversible !\n\n Are you sure you want to delete the downloads of this order ?";
	$ConfirmDelivery = "Do you want to send to your customer an email containg the download link(s) of the purchased file(s) ?";
	$nofilemsg = "No file";
	$loginLabel = "Login:";
	$pwdLabel = "Password:";
	$LogonBtn = "Log in";
	$LogoffBtn = "Log out";
	$BackBtn = "< Back";
	$SaveChangesBtn = "Save";
	$InternalNameLabel = "Internal filename";
	$DownloadsLabel = "Download requests";
	$ExpDateLabel = "Expiration date (year-month-day)";
	$InvalidDate = "Date is not valid: ";
	$ExpiredLabel = "Refused";
	$DesactivatedLabel = "Disabled";
	$ActivatedLabel = "Enabled";
	$NoExpiration = "None";
	$ConfigurationInfo = "<p><b>Current configuration of the download i-service</b></p><p>The maximum of downloads per order is currently set to <b>" . ($MaxDownloads>0?$MaxDownloads:$NoExpiration) . "</b> and the maximum time (in days) by default for downloads is set to <b>" . ($MaxPeriod>0?$MaxPeriod:$NoExpiration) . "</b>.</p>";
}
$ConfigurationInfo = "<div style='text-align:center'>$ConfigurationInfo</div>";
$lg = $_POST['login'];
$pw = $_POST['pwd'];
if( $lg == "" )
{
	$lg = $_GET['dlogin'];
	$pw = $_GET['dpwd'];
}
if( $_POST['logoff'] != "" )
{
	setcookie("ollogin", "", time() - $expiredtime);
	unset( $_COOKIE['ollogin'] );
	setcookie("olpwd", "", time() - $expiredtime);
	unset( $_COOKIE['olpwd'] );
}
else if( strlen($lg) > 0 && strlen($pw) > 0 )
{
	setcookie("ollogin", $lg, time() + $expiredtime);
	setcookie("olpwd", $pw, time() + $expiredtime);
}
else
{
	$lg = $_COOKIE['ollogin'];
	$pw = $_COOKIE['olpwd'];
}
$curpage = intval($_POST['curpage']);
if( $curpage == 0 )
	$curpage = 1;
$top_html = '<!doctype html>
	  <html>
		<head>
			<meta http-equiv="content-type" content="text/html;charset=UTF-8">
			<link href="../../_scripts/bootstrap/css/bootstrap.min.css" rel="stylesheet">
			<link href="../../_scripts/bootstrap/css/font-awesome.min.css" rel="stylesheet">
			<link href="../../_scripts/colorbox/colorbox.css" rel="stylesheet" media="screen">
			<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
			<script src="../../_scripts/bootstrap/js/bootstrap.min.js"></script>
			<script src="../../_scripts/colorbox/jquery.colorbox-min.js"></script>			
			<style>
				html, body { font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif }
				h2 {text-align:center}
				td {padding:5px}
			</style>
		</head>
	  <body>';
if( $ctr != getCtr( $lg, $pw ) )
{
	unset( $_COOKIE['ollogin'] );
	unset( $_COOKIE['olpwd'] );
	die( "$top_html<h2>Backoffice $website</h2><br><br><div style='text-align:center;'><form style='display:inline-block' method=\"post\" action=\"$scriptfilename\" class='form-horizontal'>
	<div class='control-group'>
		<label class='control-label'>$loginLabel</label>
		<div class='controls'>
			<input type=\"text\" name=\"login\">
		</div>
	</div>
	<div class='control-group'>
		<label class='control-label'>$pwdLabel</label>
		<div class='controls'>
			<input type=\"password\" name=\"pwd\"></td></tr>
		</div>
	</div>
	<div class='control-group'>
		<div class='controls'>
			<button type=\"submit\" class='btn btn-primary'>$LogonBtn</button></td></tr>
		</div>
	</form></div></body></html>"
	);
}
$editfile = $_REQUEST['editfile'];
if( $editfile != "" && file_exists( "./data/$editfile" ) ) 
{
	echo "$top_html<h2>$DownloadLabel</h2><h4 style='text-align:center'>$OrderLabel ".substr($editfile, 0, strlen($editfile)-4)."</h4><br><div class='container'>";
	echo $ConfigurationInfo;
	$i = 1;
	$dlfiles = "";
	$file_content = file_get_contents( "./data/$editfile" );
	while( strstr( $file_content, "downloadcount_" ) !== false ) 
	{
		$fname = ExtractStringBetween( "downloadcount_", "=", $file_content );
		$dlcount = ExtractStringBetween( "downloadcount_".$fname."=", "\n", $file_content );
		$dlcount = strtok( $dlcount, "\r" );
		$dname = $public_filenames[ $fname ];
		if($dname == "" )
			$dname = $fname;
		$dlfiles .= "
			<h4 style='border-bottom: solid lightgray 1px;padding-bottom:6px'>$dname</h4>
			<input type='hidden' name='file".$i."' value='$fname'>
			<div class='control-group'>
				<label class='control-label' for='file".$i."_name'>$InternalNameLabel</label>
				<div class='controls'>
					<input type='text' class='input-block-level' name='file".$i."_name' id='file".$i."_name' value='$fname' disabled='true'>
				</div>
			</div>
			<div class='control-group'>
				<label class='control-label' for='file".$i."_downloads'>$DownloadsLabel</label>
				<div class='controls'>
					<input type='number' min='0' step='1' class='input-small' name='file".$i."_downloads' id='file".$i."_downloads' value='$dlcount'>
				</div>
			</div>";
		if( strstr( $file_content, "expiredate_" ) !== false ) 
		{
			$expdate = ExtractStringBetween( "expiredate_".$fname."=", "\n", $file_content );
			$expdate = strtok( $expdate, "\r" );
			if( $expdate == "0" )
				$expdate = "";
			$dlfiles .= "
			<div class='control-group'>
				<label class='control-label' for='file".$i."_expire'>$ExpDateLabel</label>
				<div class='controls'>
					<input type='text' class='input-small' name='file".$i."_expire' id='file".$i."_expire' value='$expdate' placeholder='$DateFmtUI'>
				</div>
			</div>";
		}
		$dlfiles .= "<br>";
		$file_content = str_replace( "downloadcount_".$fname."=", "", $file_content );
		$i++;
	}
	if( $dlfiles != "" ) 
		$dlfiles .= "<input type='hidden' name='update' value='$editfile'>";
	echo "<form class='form-horizontal' method='post' action='$scriptfilename' onsubmit='return(validate_update_form())'>			
			$dlfiles
			<div class='control-group'>
				<label class='control-label' for='submitaction'>
					<input class='btn' type='button' value='$BackBtn' onclick='javascript:history.go(-1)'>
				</label>
				<div class='controls' style='padding-top:5px'>
					<input class='btn btn-primary' type='submit' id='submitaction' name='submitaction' value='$SaveChangesBtn'>
				</div>
			</div>
		</form>
	</div>
	<script>
		Number.prototype.pad = function(size) {
			  var s = String(this);
			  if(typeof(size) !== 'number'){size = 2;}
			  while (s.length < size) {s = '0' + s;}
			  return s;
		}	
		function valid_ymdate(inp){
			if( !inp || inp.value == '' )
				return true;
			try{
				var D, d=inp.value.split(/\D+/);
				d[0]*=1;
				d[1]-=1;
				d[2]*=1;				
				D=new Date(d[0],d[1],d[2]);		
				if( (D.getFullYear() == d[0] || (D.getFullYear() == d[0]+1900) ) && D.getMonth()== d[1] && D.getDate()== d[2]) {
					if( d[0] < 100 )
						d[0] += 2000;
					d[1]+=1;
					inp.value = ((d[0]).pad(4))+'-'+((d[1]).pad())+'-'+((d[2]).pad());
					return( true );
				} else throw new Error( '$InvalidDate' + inp.value );
			}
			catch(er){  
				alert(er.message+\"\\n\\n$ExpDateLabel\");
				inp.focus();
				return( false );
			}
		}
		function validate_update_form()
		{
			var i, elt;
			for(i=1; i<1000; i++)
			{
				elt = document.getElementById( 'file'+i+'_expire' );
				if( !elt ) {
					break;
				} else if( !valid_ymdate(elt) )
					return( false );
			}
			return(confirm(\"$ConfirmUpdate\"));
		}
	</script>";
	die( "</body></html>");
}
	function is_valid_date($mydate) 
	{		
		list($yy,$mm,$dd)=explode("-",$mydate);
		if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {
			return checkdate($mm,$dd,$yy);
		}
		return false;           
	} 
$editfile = $_REQUEST["update"];
if( $editfile != "" ) 
{
	$file_content = "";
	$i = 1;
	$myfile = $_REQUEST["file".$i];
	while( $myfile != "" ) 
	{
		$dlcount = $_REQUEST["file".$i."_downloads"];
		if( $dlcount == "" || !ctype_digit(strval($dlcount)) ) 
			$dlcount = "0";
		$expired_date = $_REQUEST["file".$i."_expire"];		
		if( $expired_date == "" || !is_valid_date($expired_date) ) 
			$expired_date = "0";
		$file_content .= "downloadcount_".$myfile."=".$dlcount."\r\n";
		$file_content .= "expiredate_".$myfile."=".$expired_date."\r\n";
		$i++;
		$myfile = $_REQUEST["file".$i];
	}
	if( $file_content != "" ) 
	{
		if( !$fh = fopen( "./data/$editfile", "w+") ) {
		} else {
			fwrite( $fh, $file_content );
			fclose( $fh );
		}			
	}
}
echo "$top_html<h2>$DownloadLabel $website</h2><br><div class='container'>";
$delfile = $_REQUEST['delfile'];
if( strlen($delfile) > 5 && strlen($delfile) < 22 && ( substr($delfile, -4) == ".ini" || substr($delfile, -4) == ".mak" ) ) {
	@unlink("$path/$delfile");	
}
$delfile = $_REQUEST['disablefile'];
if( strlen($delfile) > 5 && strlen($delfile) < 22 && file_exists( "$path/$delfile.mak" ) ) {
	@rename( "$path/$delfile.mak", "$path/$delfile.ini" );
}
$delfile = $_REQUEST['enablefile'];
if( strlen($delfile) > 5 && strlen($delfile) < 22 && file_exists( "$path/$delfile.ini" ) ) {
	@rename( "$path/$delfile.ini", "$path/$delfile.mak" );	
	$deliver_order = $_REQUEST['deliver_'.$delfile];
	if( $deliver_order > 0 )
		DeliverOrderByEmail( $delfile );
}
echo $ConfigurationInfo;
$displayMode = 0; 
$dir_handle = @opendir($path) or die( $nofilemsg );      
$oarray = array();
while( false !== ($file = readdir($dir_handle)) ) 
{
	$ext = strtolower( substr($file, strrpos($file, '.') + 1) );
	if( $file == "." || $file == ".." || ( $ext != "ini" && $ext != "mak" ) )
		continue;
	if( $ext == "ini" && $displayMode == 1 )
		continue;
	if( $ext == "mak" && $displayMode == 2 )
		continue;			
	$ordernum = substr($file, 0, strrpos($file, '.'));
	if( file_exists( "../twsc/data/".$ordernum.".txt" ) ) {
		$filedate = filectime("../twsc/data/".$ordernum.".txt");
	} else if( file_exists( "../twsc/data/".$ordernum.".html" ) ) {
		$filedate = filectime("../twsc/data/".$ordernum.".html");
	} else
		$filedate = filectime("./data/".$ordernum.".ini");
	$oarray[] = array( $ordernum, $filedate );
}
function cmp($a, $b) {
	if($a[1] == $b[1])
		return 0;
	else 
		return ($a[1] < $b[1]) ? 1 : -1;
}
usort($oarray, 'cmp');
if( count( $oarray ) == 0 ) 
{
	echo "<div style='text-align:center'><br><b>$nofilemsg</b></div>";	
} 
else 
{
	$npages = ceil(count($oarray)/$perpage);
	if( $curpage > $npages && $npages > 0 )
		$curpage = $npages;
	$idx0 = ($curpage-1)*$perpage;
	$navbar = "";
	if( $perpage < count($oarray) ) {
		$navbar = "<div style='text-align:center'>";
		if( $curpage > 1 ) {
			$navbar .= "<form style='display:inline-block' method='post' action='$scriptfilename'><button class='btn' type='submit'>&lt;</button><input type='hidden' name='curpage' value='" . ($curpage-1) . "'></form>";
		}
		$navbar .= "&nbsp;Page $curpage/$npages&nbsp;";
		if( $idx0+$perpage < count($oarray) ) {
			$navbar .= "<form style='display:inline-block' method='post' action='$scriptfilename'><button class='btn' type='submit'>&gt;</button><input type='hidden' name='curpage' value='" . ($curpage+1) . "'></form>";
		}
		$navbar .= "</form></div>";
	}
	echo $navbar;
	echo "<table class='table table-condensed table-hover'>
	<thead>
		<tr>
		  <th class='hidden-xs hidden-phone'>Date</th>
		  <th>$OrderLabel</th>
		  <th style='text-align:center'>$StatusLabel</th>
		  <th>$FilesLabel ($DownloadLabel)</th>
		  <th style='text-align:center'>Expiration</th>
		  <th>Actions</th>
		</tr>
	</thead><tbody>";
	for( $i=$idx0;$i<min(count( $oarray ), $idx0+$perpage);$i++ )
	{
		$oid = $oarray[$i][0];
		if( file_exists( "./data/".$oid.".ini" ) ) {
			$file_ext = ".ini";
		} else {
			$file_ext = ".mak";
		}
		$file_content = file_get_contents( "./data/".$oid.$file_ext );
		if( file_exists( "../twsc/data/".$oid.".html" ) ) {
			$orderlink = "<a href='../twsc/so.php?oid=$oid&fmt=html&ctr=" . getCtr( $oid, "html" ) . "' rel='popup'>".$oid."</a>";
		} else {
			$orderlink = $oid;
		}	
		$dlfiles = "";
		$exdates = "";
		$ar_dates = array();
		$ar_downloads = array();
		while( strstr( $file_content, "downloadcount_" ) !== false ) 
		{
			$fname = ExtractStringBetween( "downloadcount_", "=", $file_content );
			$dlcount = ExtractStringBetween( "downloadcount_".$fname."=", "\n", $file_content );
			$dlcount = strtok( $dlcount, "\r" );
			$downloadover = false;
			$expired = false;
			if( $file_ext == ".mak" ) 
			{
				$expired_date = ExtractStringBetween( "expiredate_".$fname."=", "\n", $file_content );
				$expired_date = strtok( $expired_date, "\r" );
				if( $expired_date != "" && $expired_date != "0" ) {
					$expired = date("Y-m-d") > $expired_date;
					if( $expired ) {
						$exdates .= "<span style='color:darkgrey'>".formatDateYMD2UI($expired_date)."</span><br>";
					} else
						$exdates .= formatDateYMD2UI($expired_date)."<br>";
				} else {
					$expired = false;
					$exdates .= "$NoExpiration<br>";
				}
				array_push( $ar_dates, $expired );
			}
			$dname = $public_filenames[ $fname ];
			if($dname == "" )
				$dname = "<span style='color:red'>".$fname."<span>";	
			$max_reached = $MaxDownloads > 0 && $dlcount >= $MaxDownloads;
			array_push( $ar_downloads, $max_reached );
			if( $max_reached )
				$dlcount = "<span style='color:red;font-weight:bold'>$dlcount</span>";
			if( $file_ext == ".ini" || $expired || $max_reached ) {
				$dlfiles .= $dname . " (" . $dlcount . ")<br>";
			} else {
				$dlurl = "dl.php?dlfile=".$fname."&dlorder=".$oid."&dlkey=".getCtr($oid, $fname);
				$dlfiles .= "<a href='".$dlurl."' target='_blank' onclick='return(confirmDownload(\"d".md5("$i$fname")."\"))'>" . $dname . "</a> (<b><span id='d".md5("$i$fname")."'>". $dlcount . "</span></b>)<br>";
			}
			$file_content = str_replace( "downloadcount_".$fname."=", "", $file_content );
		}
		$expired = count($ar_dates) > 0;
		if( $expired ) {
			for( $n=0; $n<count($ar_dates); $n++)
				if( !$ar_dates[$n] ) {
					$expired = false;
					break;
				}
		}
		unset($ar_dates);
		$max_reached = count($ar_downloads) > 0;
		if( $max_reached ) {
			for( $n=0; $n<count($ar_downloads); $n++)
				if( !$ar_downloads[$n] ) {
					$max_reached = false;
					break;
				}
		}
		unset($ar_downloads);
		if( $expired || $max_reached ) {
			$icon_state = "<i class='fa fa-ban fa-lg' style='color:red;cursor:default' title='$ExpiredLabel'></i>";
		} else if( $file_ext == ".ini" ) {
			$icon_state = "<i class='fa fa-times fa-lg' style='color:red;cursor:default' title='$DesactivatedLabel'></i>";
		} else {
			$icon_state = "<i class='fa fa-check fa-lg' style='color:green;cursor:default' title='$ActivatedLabel'></i>";
		}
		echo "<tr>
				<td class='hidden-xs hidden-phone'>" . date($DateFmt, $oarray[$i][1]) . "</td>
				<td>" . $orderlink . "</td>
				<td style='text-align:center'>$icon_state</td>
				<td>$dlfiles</td>
				<td style='text-align:center'>$exdates</td>
				<td>";
		if( !$expired ) 
		{
			echo "<form style='margin:0;padding:0;display:inline-block;' action='$scriptfilename' method='post'>";
			if( $file_ext == ".mak") {
				echo "
					<button class='btn btn-small' type='submit' title='$DisabledLabel' onclick='return(confirm(\"".$ConfirmLabel.strtolower($DisabledLabel)."?\"));'><i class='fa fa-times' style='width:16px'></i></button>
					<input name='disablefile' value='$oid' type='hidden'>";
			} else {
				echo "
					<button class='btn btn-small' type='submit' title='$EnabledLabel' onclick='return(ActivateOrderDownload(\"$oid\"));'><i class='fa fa-check' style='width:16px'></i></button>
					<input name='enablefile' value='$oid' type='hidden'>
					<input id='deliver_$oid' name='deliver_$oid' value='0' type='hidden'>";
			}
			echo "</form>";
		}
		echo "  <form style='margin:0;padding:0;display:inline-block;' action='$scriptfilename' method='post'>
				<button class='btn btn-small' type='submit' title='$EditBtn');'><i class='fa fa-pencil-square-o fa-lg' style='width:16px'></i></button>
				<input name='editfile' value='$oid$file_ext' type='hidden'>
				</form>
				<form style='margin:0;padding:0;display:inline-block;' action='$scriptfilename' method='post'>
				<button class='btn btn-small' type='submit' title='$DeleteBtn' onclick='return(confirm(\"$DeleteConfirm\"));'><i class='icon-trash' style='width:16px'></i></button>
				<input name='delfile' value='$oid$file_ext' type='hidden'>
				</form>
				</td>
			</tr>";
	}
	echo "</tbody></table>$navbar";
}
echo "<br><div style='text-align:center'>" .
	"<form style='display:inline-block' method=\"post\" action=\"$scriptfilename\"><button class='btn' type=\"submit\">$LogoffBtn</button><input type=\"hidden\" name=\"logoff\" value=\"ok\"></form>";
echo "</div>";
echo "
	</div>
<script>
function ActivateOrderDownload(oid) {
	if( !confirm(\"".$ConfirmLabel.strtolower($EnabledLabel)."?\"))
		return false;
	if( confirm(\"$ConfirmDelivery\") )
		$(\"#deliver_\"+oid).val(\"1\");
	return(true);
}
function confirmDownload(elt) {
	var ret = confirm(\"$ConfirmDownload\");
	if( ret ) {
		var dl = 1 + eval( $('#'+elt).text() );
		if( $MaxDownloads < 0 || dl <= $MaxDownloads ) {
			if( dl == $MaxDownloads ) 
				dl = \"<span style='color:red'>\" + dl + \"</span>\";
			$('#'+elt).html(dl)
		}
	}
	return(ret);
}
$(document).ready(function(){
	$(\"a[rel='popup']\").colorbox({maxWidth:'90%',maxHeight:'90%'});
});
</script>
</body>
</html>";
?>
