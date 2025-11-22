<?php
  $relativePath=".";
  $aDirs = array();
  $handler = opendir($relativePath);
  while( $file = readdir($handler) ) {
    if( $file != "." && $file != ".." && is_dir($file) ) {
      $aDirs[] = $file;
      if(is_dir("$file/data"))
        $aDirs[] = "$file/data";
    }
  }
  closedir($handler);

  echo "<h2>TW 6.2.3.703 / PHP ".phpversion()."</h2>";
  echo "<table>";
  echo "<tr style=\"font-weight:bold;\">";
    echo "<td>Dir</td>";
    echo "<td>Perms</td>";
    echo "<td>Writable ?</td>";
  echo "</tr>";
  foreach ($aDirs as $i => $value) {
    echo "<tr>";
      echo "<td>$aDirs[$i]</td>";
      echo "<td>".substr(sprintf("%o", fileperms("$relativePath/$aDirs[$i]")), -4)."</td>";
      echo "<td>".is_writable("$relativePath/$aDirs[$i]")."</td>";
    echo "</tr>";
  }
  echo "</table>";
?>
