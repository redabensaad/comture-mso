<?php
	@require_once("../common/com.php");
	$path = "data";	
	$expiredtime = 300; 
	$scriptfilename = basename( $_SERVER['SCRIPT_NAME'] );
	$lang = "fr";
	$perpage = 5; 
	$ctr = "9fd147ec05c84d68c7699bd279526384";	
	$website = $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
	$website = substr( $website, 0, strpos( $website, '/_iserv') );
	if( $lang == "fr" ) 
	{
		$OrderLabel = "Fichiers";
		$DeleteBtn = "Supprimer";
		$DeleteAllBtn = "Tout supprimer";
		$DeleteConfirm = "Cette action est irrÃ©versible ! Etes-vous sÃ»r de vouloir supprimer ce fichier ?";
		$DeleteAllConfirm = "Cette action est irrÃ©versible ! Etes-vous sÃ»r de vouloir supprimer TOUS vos fichiers de commande ?";
		$noroderfiles = "Aucun fichier";
		$loginLabel = "Identifiant:";
		$pwdLabel = "Mot de passe:";
		$LogonBtn = "Connexion";
		$LogoffBtn = "DÃ©connexion";
		$TXTVersion = "Fichier TXT";
		$HTMLVersion = "Fichier HTML";
		$DateFmt = "d/m/y H:i:s";
	}
	else
	{
		$OrderLabel = "Files";
		$DeleteBtn = "Delete";
		$DeleteAllBtn = "Delete all files";
		$DeleteConfirm = "This action is irreversible ! Are you sure you want to delete this file ?";
		$DeleteAllConfirm = "This action is irreversible ! Are you sure you want to delete ALL your files ?";
		$noroderfiles = "No file";
		$loginLabel = "Login:";
		$pwdLabel = "Password:";
		$LogonBtn = "Log in";
		$LogoffBtn = "Log out";
		$TXTVersion = "TXT file";
		$HTMLVersion = "HTML file";
		$DateFmt = "m/d/y H:i:s";
	}
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
				<link href="../../_scripts/colorbox/colorbox.css" rel="stylesheet" media="screen">
				<script type="text/javascript" src="//code.jquery.com/jquery.min.js"></script>
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
	echo "$top_html<center><h2>$OrderLabel $website</h2>";
	$dir_handle = @opendir($path) or die( $noroderfiles );      
	$delfile = $_REQUEST['delallfiles'];
	if( strlen($delfile) > 0 ) 
	{
		while ( $file = readdir($dir_handle) ) 
		{
			if( $file == "." || $file == ".." || $file == $scriptfilename || $file == "index.html" )          
				continue;         
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
				if($file == "." || $file == ".." || $file == "index.html" )
					continue;
				$oarray[] = array( "$path/$file", filectime("$path/$file"), " $file" );
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
		if( strlen($delfile) > 5 ) {
			unlink("$delfile");	
		}
		echo "<table cellspacing=0 cellpadding=0 border=0><tr>".
			"</tr>";
		$oarray = getOList();
		if( count( $oarray ) == 0 ) 
		{
			echo "<div style='text-align:center'><br><b>$noroderfiles</b></div>";	
		} 
		else 
		{
			$npages = ceil(count($oarray)/$perpage);
			if( $curpage > $npages )
				$curpage = $npages;
			$idx0 = ($curpage-1)*$perpage;
			$navbar = "";
			if( $perpage < count($oarray) ) {
				$navbar = "<tr><td colspan='5' style='padding:5px;' align='center'><table cellspacing=0 cellpadding=0 border=0><tr>";
				if( $curpage > 1 ) {
					$navbar .= "<td style='padding:5px;' valign='center'><form method='post' action='$scriptfilename'><input type='submit' class='btn' value='<'><input type='hidden' name='curpage' value='" . ($curpage-1) . "'></form></td>";
				}
				$navbar .= "<td style='padding:5px;' valign='center'>Page $curpage/$npages</td>";
				if( $idx0+$perpage < count($oarray) ) {
					$navbar .= "<td style='padding:5px;' valign='center'><form method='post' action='$scriptfilename'><input type='submit' class='btn' value='>'><input type='hidden' name='curpage' value='" . ($curpage+1) . "'></form></td>";
				}
				$navbar .= "</tr></table></td></tr>";
			}
			echo $navbar;
			for( $i=$idx0;$i<min(count( $oarray ), $idx0+$perpage);$i++ )
			{
				$filename = $oarray[$i][2];
				$file = $oarray[$i][0];	
				echo "<tr>
				<td style='padding:5px;' valign='center'>" . date($DateFmt, $oarray[$i][1]) . "</td>
				<td style='padding:5px;' valign='center'><a href=\"" .$file. "\">".$filename."</a></td>
				<td style='padding:5px;'><form method=\"post\" action=\"$scriptfilename\" style=\"margin:0;padding:0\"><input class='btn' type=\"submit\" onclick='return(confirm(\"$DeleteConfirm\"));' value=\"$DeleteBtn\"><input type=\"hidden\" name=\"delfile\" value=\"$file\"></form></td>
				<tr>";      
			}      
			echo $navbar;
			echo "</table><br/>";
			if( count($oarray) >= 5 )
				echo "<form method='post' action='$scriptfilename'><input type='submit' onclick='return(confirm(\"$DeleteAllConfirm\"));' value=\"$DeleteAllBtn\"><input type=\"hidden\" name=\"delallfiles\" value=\"ok\"></form>";
		}
	}
	echo "&nbsp;<br><form method=\"post\" action=\"$scriptfilename\"><input type=\"submit\"  class='btn btn-primary' value=\"$LogoffBtn\"><input type=\"hidden\" name=\"logoff\" value=\"ok\"></form>";
	echo "</center></body></html>";
	closedir($dir_handle);  
?>  
