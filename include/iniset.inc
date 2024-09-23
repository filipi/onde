<?PHP
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $delimiter = ";";
  $slashPath = "\\";
} else {
  $delimiter = ":";
  $slashPath = "/";
}
$include_path  = $delimiter .  "." . $slashPath . "include" . $slashPath . "classes";
$include_path .= $delimiter .  ".." . $slashPath . "include" . $slashPath . "classes";
$include_path .= $delimiter .  ".." . $slashPath .".." . $slashPath . "include" . $slashPath . "classes";
$myPATH = ini_get('include_path') . $include_path;
ini_set('include_path', $myPATH);
?>