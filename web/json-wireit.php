<?PHP
 /**
  * $Id: json-wireit.php,v 1.4 2018/08/21 18:25:30 filipi Exp $
  * á
  */
if (isset($_GET['id']))    $id      = pg_escape_string($_GET['id']); else $id = 0;
if (isset($_GET['top']))   $top     = intval($_GET['top']);          else $top = 0;
if (isset($_GET['left']))  $left    = intval($_GET['left']);         else $left = 0;
if (isset($_GET['height'])) $height = intval($_GET['height']);       else $height = 0;
if (isset($_GET['width'])) $width   = intval($_GET['width']);        else $width = 0;
$useSessions = 1; $ehXML = 1;
include "iniset.php";
include "page_header.inc";

$erro = "";
$query = "SELECT id FROM umlpositions WHERE id = '" . $id  . "'";
$result = pg_exec ($conn, $query);
if (!$result){
  $erro = $query;
  $erro .= "\n" . pg_last_error() . "\n";
}
$total  = pg_numrows($result);
if ($total){ //update
  $query  = "UPDATE umlpositions SET ";
  $query .= "toppos = " . $top .    ",\n";
  $query .= "leftpos = " . $left .  ",\n";
  $query .= "height = " . $height . ",\n";
  $query .= "width = " . $width .   " \n";
  $query .= " WHERE id = '" . $id . "'\n";
}
else{// insert
  $query  = "INSERT INTO umlpositions (id, toppos, leftpos, height, width) VALUES (";
  $query .= "'" . $id . "', " . intval($top) . ", " . intval($left) . ", " . intval($height) . ", " . intval($width) . ")";
}
//echo $height . "\n";
//echo $query;
$result = pg_exec ($conn, $query);
//echo pg_last_error();
if (!$result){
  $erro = $query;
  $erro .= "\n" . pg_last_error() . "\n";
}
/*
$command_line  = "echo \"Recebendo dados: \n";
$command_line .= "id: " . $id;
$command_line .= "\ntop: " . $top;
$command_line .= "\nleft: " . $left;
$command_line .= "\n\n" . $erro;

$command_line .= "\"  >> session_files/teste";
`$command_line`;
*/

include "page_footer.inc";
?>
