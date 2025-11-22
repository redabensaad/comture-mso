<?php
	@require_once("../common/com.php");
	function getsourl( $oid, $fmt ) {
		return "so.php?oid=" . $oid . "&fmt=" . $fmt . "&ctr=" . getCtr( $oid, $fmt );
	}
	$path = "data";	
	$expiredtime = 300; 
	$scriptfilename = basename( $_SERVER['SCRIPT_NAME'] );
	$lang = "fr";
	$perpage = 20; 
	$ctr = "9fd147ec05c84d68c7699bd279526384";	
	$DateFmt = "m/d/y H:i:s";
	$website = $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
	$website = substr( $website, 0, strpos( $website, '/_iserv') );
	if( $lang == "fr" ) 
	{
		$DateFmt = "d/m/y H:i:s";
		$OrderLabel = "Commandes du site";
		$DeleteBtn = "Supprimer";
		$DeleteAllBtn = "Tout supprimer";
		$DeleteConfirm = "Cette action est irréversible ! Etes-vous sûr de vouloir supprimer cette commande ?";
		$DeleteAllConfirm = "Cette action est irréversible ! Etes-vous sûr de vouloir supprimer TOUS vos fichiers de commande ?";
		$noroderfiles = "Aucun fichier de commande";
		$loginLabel = "Identifiant:";
		$pwdLabel = "Mot de passe:";
		$LogonBtn = "Connexion";
		$LogoffBtn = "Déconnexion";
		$TXTVersion = "TXT";
		$JSONVersion = "JSON";
		$HTMLVersion = "HTML";
		$Click4Details = "Cliquez pour voir le détail de la commande";		
	}
	else
	{
		$DateFmt = "m/d/y H:i:s";
		$OrderLabel = "Orders of";
		$DeleteBtn = "Delete";
		$DeleteAllBtn = "Delete all files";
		$DeleteConfirm = "This action is irreversible ! Are you sure you want to delete this order ?";
		$DeleteAllConfirm = "This action is irreversible ! Are you sure you want to delete ALL your order files ?";
		$noroderfiles = "No order file";
		$loginLabel = "Login:";
		$pwdLabel = "Password:";
		$LogonBtn = "Log in";
		$LogoffBtn = "Log out";
		$TXTVersion = "TXT";
		$JSONVersion = "JSON";
		$HTMLVersion = "HTML";
		$Click4Details = "Click to view order details";
	}
	$dir_handle = @opendir($path) or die( $noroderfiles );      
	$lg = $_POST['login'];
	$pw = $_POST['pwd'];
	if( $lg == "" )
	{
		$lg = $_GET['dlogin'];
		$pw = $_GET['dpwd'];
	}
	$curpage = intval($_POST['curpage']);
	if( $curpage == 0 )
		$curpage = 1;
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
	$top_html = '<!doctype html>
		  <html>
			<head>
				<meta http-equiv="content-type" content="text/html;charset=UTF-8">
				<link href="../../_scripts/bootstrap/css/bootstrap.min.css" rel="stylesheet">
				<link href="../../_scripts/bootstrap/css/font-awesome.min.css" rel="stylesheet">
				<script type="text/javascript" src="//code.jquery.com/jquery.min.js"></script>
				<script src="../../_scripts/bootstrap/js/bootstrap.min.js"></script>
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
	echo "$top_html<h2>$OrderLabel $website</h2><br><div class='container'>";
	$delfile = $_REQUEST['delallfiles'];
	if( strlen($delfile) > 0 ) 
	{
		while ( $file = readdir($dir_handle) ) 
		{
			if( $file == "." || $file == ".." || $file == $scriptfilename )          
				continue;         
			$ext = strtolower( substr($file, strrpos($file, '.') + 1) );
			if( $ext == "txt" || $ext == "html" || $ext == "json")
				unlink("$path/$file");
		}
	} 
	else
	{
		function getOList() {
			global $path, $dir_handle;
			$oarray = array();
			while( false !== ($file = readdir($dir_handle)) ) 
			{
				$ext = strtolower( substr($file, strrpos($file, '.') + 1) );
				if($file == "." || $file == ".." || $ext != "txt" )
					continue;
				$oarray[] = array( substr($file, 0, strrpos($file, '.')), filectime("$path/$file"));
			}
			function cmp($a, $b) {
				if($a[1] == $b[1])
					return 0;
				else
					return ($a[1] < $b[1]) ? 1 : -1;
			}
			usort($oarray, 'cmp');
			return $oarray;
		}
		$delfile = $_REQUEST['delfile'];
		if( strlen($delfile) > 5 && strlen($delfile) < 22 && substr($delfile, -4) == ".txt" ) {
			unlink("$path/$delfile");	
			unlink(str_replace(".txt", ".html", "$path/$delfile"));	
			unlink(str_replace(".txt", ".json", "$path/$delfile"));	
		}
		$oarray = getOList();
		$npages = ceil(count($oarray)/$perpage);
		if( $curpage > $npages )
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
		if( count($oarray) > 0 )
		{
			echo $navbar;
			echo "<table class='table table-condensed table-hover'>
			<thead>
				<tr>
				  <th>Date</th>".
				  ( ( $lang == "fr" ) ? "<th>Commande #</th>" : "<th>Order #</th>" ) .
				  "<th>Client</th>
				  <th style='text-align:right'>Total</th>
				  <th>Action</th>
				</tr>
			</thead><tbody>";
			for( $i=$idx0;$i<min(count( $oarray ), $idx0+$perpage);$i++ )
			{
				$oid = $oarray[$i][0];
				$file = $oarray[$i][0].".txt";	
				$href = file_exists( "./$path/$oid.html" ) ? getsourl( $oid, "html" ) : getsourl( $oid, "txt" );
				echo "<tr style='cursor:pointer' onclick=\"location.href='$href';\">
				<td>" . date($DateFmt, $oarray[$i][1]) . "</td>";
				$json = '';
				if(file_exists( "data/$oid.json" ))
					$json = substr( file_get_contents( "data/$oid.json" ), 3 );	
				if($json !== '') {
					if( !function_exists('json_decode') ) {
						@require_once('../common/json.php');
						function json_decode($data) {
							$json = new Services_JSON();
							return( $json->decode($data) );
						}
					}
					$json = json_decode( $json );
					echo "<td>$oid<br><small>" . $json->shipping_mode . "<br>" . $json->payment_mode . "</td>" .
						"<td>" . $json->shipping_details->csi_firstname . ' ' . $json->shipping_details->csi_lastname . 
						"<br><small>" . $json->shipping_details->csi_address1 . 
						"<br>" . $json->shipping_details->csi_zip . ' ' . $json->shipping_details->csi_city . 
						( ($json->shipping_details->csi_email == '') ? "" : "<br><a onclick='location.href=\"mailto:" . $json->shipping_details->csi_email . "\";event.stopPropagation();' href='#'>" . $json->shipping_details->csi_email . "</a>" ) .
						"</small></td>
						<td style='text-align:right'>" . $json->total_str . "</td>";
				}
				else {
					echo "<td>$oid</td>" .
						 "<td colspan='2'><small>$Click4Details</small></td>";
				}
				echo "<td><form method=\"post\" action=\"$scriptfilename\" style='margin:0;padding:0;display:inline-block;'><button class=\"btn btn-small\" type=\"submit\" title=\"$DeleteBtn\" onclick='return(confirm(\"$DeleteConfirm\"));' ><i class=\"icon-trash\"></i></button><input type=\"hidden\" name=\"delfile\" value=\"$file\"></form>&nbsp;" .
					"<a href=\"" . getsourl( $oid, "txt" ) . "\">$TXTVersion</a>" .
						"</td>";
			}      
			echo "</tbody></table><br>$navbar";
		}
	}
	if( count($oarray) == 0 )
		echo "<h5 style='text-align:center'>$noroderfiles&nbsp;</h5><br><br>";
	echo "<div style='text-align:center'>" .
		"<form style='display:inline-block' method=\"post\" action=\"$scriptfilename\"><button class='btn btn-primary' type=\"submit\">$LogoffBtn</button><input type=\"hidden\" name=\"logoff\" value=\"ok\"></form>";
	echo "</div></div></body></html>";
	closedir($dir_handle);  
?>  
