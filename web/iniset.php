<?PHP
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $delimiter = ";";
  $slashPath = "\\";
} else {
  $delimiter = ":";
  $slashPath = "/";
}
$include_path  = $delimiter .  "." . $slashPath . "include";
$include_path .= $delimiter .  ".." . $slashPath . "include";
$include_path .= $delimiter .  ".." . $slashPath .".." . $slashPath . "include";
$myPATH = ini_get('include_path') . $include_path;
ini_set('include_path', $myPATH);
?>