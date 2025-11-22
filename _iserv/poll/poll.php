<?php
define( "HTML_FILE", "index.html" );
define( "DEFAULT_VOTE_COLOR", "#3399FF" );
define( "VISITOR_VOTE_COLOR", "#FF9900" );
$polls_def = array( 
"1" => array(
"texts" => array( "title" => "", "total" => "Total des votes:" ),
"opts" => array(	"1" => "1", "2" => "2" )
)
);

define('OPT_ID', 0);
define('OPT_TITLE', 1);
define('OPT_VOTES', 2);
if( !function_exists('json_encode') ) { 
  function json_encode($a=false) {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)) {
      if (is_float($a)) {
        return floatval(str_replace(",", ".", strval($a)));
      }
      if (is_string($a)) {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}
$poll_uid = $_REQUEST['uid'];
$options = $polls_def[ $poll_uid ][ "opts" ];
$poll_title = $polls_def[ $poll_uid ][ "texts" ][ "title" ];
$poll_total_msg = $polls_def[ $poll_uid ][ "texts" ][ "total" ];
require_once('../common/flatfile.php');
$db = new Flatfile();
$db->datadir = 'data/';
$VOTE_DB = $poll_uid . '.txt';
if( $_REQUEST['clear'] == '1' ) {
	unset( $_COOKIE[ "vote_".$poll_uid ] );
	setcookie( "vote_".$poll_uid, "", time()-3600, "/" ); 
	die( '<script>' .
		 'window.location.href="../../index.html";' .
		 '</script>' );
} else if( $_REQUEST['view'] == '1' ) {
    setcookie( "vote_".$poll_uid, "0", time()+31556926, "/" ); 
	die( '<script>window.location.href="../../index.html";</script>' );
} else if( $_REQUEST['vote'] ) {
  poll_ajax( $_REQUEST['vote'] );
} else if( $_REQUEST['poll'] ) {
  poll_submit( $_REQUEST['poll'] );
} else {
  poll_default();
}
function poll_ajax( $id ) {
  global $db, $options, $VOTE_DB, $poll_uid;
  if( $id != 'none' ) {
      $row = $db->selectUnique($VOTE_DB, OPT_ID, $id);
      if( !empty($row) ) {
        $new_votes = $row[OPT_VOTES]+1;    
        $db->updateSetWhere($VOTE_DB, array(OPT_VOTES => $new_votes), new SimpleWhereClause(OPT_ID, '=', $id));
      }
	  else if( $options[$id] ) {
        $new_row[OPT_ID] = $id;
        $new_row[OPT_TITLE] = $options[$id];
        $new_row[OPT_VOTES] = 1;
        $db->insert($VOTE_DB, $new_row);
      }
      setcookie( "vote_".$poll_uid, $id, time()+31556926, "/" ); 
  }
  for( $uid = 1; $uid <= count($options); $uid++ ) {
	  $row = $db->selectUnique($VOTE_DB, OPT_ID, $uid);
      if( !empty( $row ) ) 
		$db->updateSetWhere($VOTE_DB, array(OPT_TITLE => $options[$uid]), new SimpleWhereClause(OPT_ID, '=', $uid));
  }
  $rows = $db->selectWhere($VOTE_DB, new SimpleWhereClause(OPT_ID, "!=", 0), -1, new OrderBy(OPT_VOTES, DESCENDING, INTEGER_COMPARISON));
  print json_encode($rows);
}
function poll_submit( $id ) {
  global $db, $options, $VOTE_DB, $poll_uid;
  if( !isset($_COOKIE["vote_".$poll_uid]) ) {
	  $row = $db->selectUnique($VOTE_DB, OPT_ID, $id);
	  if (!empty($row)) {
	      $new_votes = $row[OPT_VOTES]+1;
	      $db->updateSetWhere($VOTE_DB, array(OPT_VOTES => $new_votes), new SimpleWhereClause(OPT_ID, '=', $id));
	  } else if ($options[$id]) {
	      $new_row[OPT_ID] = $id;
	      $new_row[OPT_TITLE] = $options[$id];
	      $new_row[OPT_VOTES] = 1;
	      $db->insert($VOTE_DB, $new_row);
	  }
      setcookie( "vote_".$poll_uid, $id, time()+31556926, "/" ); 
  }
  poll_return_results($id);
}
function poll_default() {
    poll_return_results();  
}
function poll_return_results($id = NULL) {
    global $db, $poll_title, $poll_uid, $poll_total_msg, $options, $VOTE_DB;
	$html = @file_get_contents( $_SERVER['HTTP_REFERER'] );
	if( strpos($html, '<div id="poll-container"' ) == false ) {
		$html = @file_get_contents( HTML_FILE );
	}
    $results_html = "<div id='poll-container'><div id='poll-results'><h3>" . $poll_title . "</h3><dl class='poll-graph'>\n";    
    $rows = $db->selectWhere( $VOTE_DB,
							  new SimpleWhereClause(OPT_ID, "!=", 0), -1,
							  new OrderBy(OPT_VOTES, DESCENDING, INTEGER_COMPARISON) );
    foreach ($rows as $row) {
      $total_votes = $row[OPT_VOTES]+$total_votes;
    }
	$id = 1;
    foreach ($rows as $row) {
	  $percent = round(($row[OPT_VOTES]/$total_votes)*100);
      $color = ($row[OPT_ID] == $id)? VISITOR_VOTE_COLOR:DEFAULT_VOTE_COLOR;
      $results_html .= "<dt class='bar-title'>". $options[$id] ."</dt><dd class='bar-container'><div id='bar". $row[OPT_ID] ."'style='width:$percent%;background-color:$color;'>&nbsp;</div><strong>$percent%</strong></dd>\n";
	  $id++;
    }
    $results_html .= "</dl><p>" . $poll_total_msg . " ". $total_votes ."</p></div></div>\n";
	if( strpos($html, '<div id="poll-container"' ) !== false ) {
	    $results_regex = '/<div id="poll-container">(.*?)<\/div>/s';
	    $return_html = preg_replace($results_regex, $results_html, $html);
	    print $return_html;	
	} else
		print $results_html;
}
