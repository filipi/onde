<?PHP
  /**
   * script php adaptado do script em perl (fastcgi) de Koji Nakamaru
   * $Id: imgtex.php,v 1.5 2018/08/01 20:07:22 filipi Exp $
   */
include "include/conf.inc";
include "include/lib.inc";
//$_debug = 1;
if (!$_debug)
  header("Content-type: image/png");

$path_to_temp_dir = pg_escape_string(trim($path_to_temp_dir));
if (substr($path_to_temp_dir, -1) != '/')
  $path_to_temp_dir .= '/';

// check dependencies.
$deps = array("tex", "dvipng", "temp_dir");
if (!depsOK($deps)){
  $handle = @fopen("images/faltamDependencias.png", "r");
  if ($handle) {
    while (!feof($handle)) {
      $buffer = fgets($handle, 4096);
      echo $buffer;
    }
    fclose($handle);
  }
  exit();
}

$cmd = $_GET['cmd'];
//echo $cmd;
//$cmd = str_replace(" ", "+", $cmd);
// + is a reserved word. So the RFC demands it to be replaced
// by a white space.
//
// To obtain a + (plus sign) one must to send %2b in the GET string
$cmd = str_replace("\\\\", "\\", $cmd);

$tex_file  = "\\documentclass{article}\n";
$tex_file .= "\\usepackage{type1cm}\n";
$tex_file .= "\\usepackage[psamsfonts]{amssymb}\n";
$tex_file .= "\\usepackage{amsmath,color}\n";

$tex_file .= "\\usepackage[utf8]{inputenc}\n";
$tex_file .= "\\usepackage[T1]{fontenc}\n";
$tex_file .= "\\usepackage{color}\n";


$tex_file .= "\\begin{document}\n";
$tex_file .= "\\thispagestyle{empty}\n";

if (strpos("_" . strtoupper($_theme), "TRON"))
  $tex_file .= "\\colorbox{black}{\\color{cyan}\$" . $cmd . " \$}\n";
else
  $tex_file .= "\$\$ " . $cmd . " \$\$\n";
$tex_file .= "\\end{document}\n";


// echo "seed 1: " .  round(time() / 10 * rand(1,10))  . "\n";
// echo "time: " .  time()  . "\n";
// echo "microtime: " .  microtime() . "\n";
// echo "microtime: " .  microtime()*1000000 . "\n";
// echo "seed 2: " .  round((time() . microtime())*100 / rand(1,10))  . "\n";
// echo "rand(1,10) " .  rand(1,10)  . "\n";

//echo "Temp dir: ";
//echo $path_to_temp_dir . "\n";

//$path_to_temp_dir = "./session_files/";

if (!isset($_GET['figure']) || !$_GET['figure']){
  //$filename = round(microtime() / 10 * rand(1,10)); 
  //$filename = round((time() . microtime())*1000 / rand(1,10));
  //$filename = $filename;
  $filename = md5($cmd);
  if ($_debug) echo "<PRE>FILENAME: " . $filename . "</PRE>\n";
  
  //$command = "cd  " . $path_to_temp_dir . "; pwd; whoami";
  //echo `$command`;
  $tex = fopen($path_to_temp_dir . $filename . ".tex", "w");
  fputs($tex , $tex_file . "\n");
  fclose($tex);}
else
  $path_to_temp_dir .= $_GET['figure'];

if ($_debug) echo "<PRE>FILENAME: " . $filename . "</PRE>\n";

if (!isset($_GET['res']))
  $res = 100;
else
 $res = $_GET['res'];

$command  = "cd $path_to_temp_dir;\n";
$command .= $path_to_tex  . " --interaction=nonstopmode " . $filename . ".tex > /dev/null 2>&1 ;\n";
$command .= $path_to_dvipng . " -bg transparent -D " . $res . "  -q -T tight ". $filename . ".dvi -o " . $filename . ".png > /dev/null 2>&1 ;\n";

if ($_debug){
  $command  = "cd $path_to_temp_dir;\n";
  $command .= $path_to_tex  . " --interaction=nonstopmode " . $filename . ".tex;\n";
  $command .= $path_to_dvipng . " -bg transparent -D " . $res . "  -q -T tight ". $filename . ".dvi -o " . $filename . ".png;\n";
}

if ($_debug){
  echo "<PRE>cmd: " . $_GET['cmd'] . "\n</PRE>";
  echo "<PRE>filename: " . $filename . "\n</PRE>";
  echo "<PRE>GET['filename']:" . $_GET['filename'] . "\n</PRE>";
  echo "<PRE>command: \n" . $command . "\n</PRE>";
  echo "<PRE>tex_file:\n" . $tex_file . "\n</PRE>";
  echo "<PRE>CAT:\n";
  passthru("/bin/cat " . $path_to_temp_dir .  $filename . ".tex");
  echo "</PRE>\n";
}

/* Usar a execucao por crase para colocar o output do comando numa variavel,
   pois o dvipng gera uma saida que nao pode ser desabilitada na linha de
   comando com o argumento --quiet. */
$output = `$command`;
//passthru($command);
//$output = exec($command);

$handle = @fopen($path_to_temp_dir . $filename . ".png", "r");
if ($handle) {
  while (!feof($handle)) {
    $buffer = fgets($handle, 4096);
    echo $buffer;
  }
  fclose($handle);
}

if ($_debug){
  echo "<PRE>\n";
  passthru("ls -l /private/tmp");
  passthru("rm -rfv " . $path_to_temp_dir . "*.tex");
  passthru("rm -rfv " . $path_to_temp_dir . "*.log");
  passthru("rm -rfv " . $path_to_temp_dir . "*.aux");
  passthru("rm -rfv " . $path_to_temp_dir . "*.dvi");
  passthru("rm -rfv " . $path_to_temp_dir . "*.png");
  echo "</PRE>\n";
}
else{
  unlink($path_to_temp_dir . $filename . ".tex");
  unlink($path_to_temp_dir . $filename . ".log");
  unlink($path_to_temp_dir . $filename . ".aux");
  unlink($path_to_temp_dir . $filename . ".dvi");
  unlink($path_to_temp_dir . $filename . ".png");
}

?>
