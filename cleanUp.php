<?PHP
$headerTitle = "Cleaning all open session...";
error_reporting(1);


ini_set('session.save_path',"./session_files");
//session_save_path('./session_files');
session_name('onde');
session_start();

$PHPSESSID = $_GET[PHPSESSID];

if($_SESSION['h_log'] && $_SESSION['matricula']){
  session_destroy();
}

$command = "rm -rfv ./session_files/simulation" . $_GET['PHPSESSID'];
exec($command);

include "./include/page_header.inc";

echo "<PRE>\n";
passthru("rm -rf session_files/s*", $erro);
echo "</PRE>\n";

echo "
<!-- Retorna à página de LOGIN -->
<meta HTTP-EQUIV='Refresh' CONTENT='1; URL=./frm_login.php' TARGET='_self'>

<div class=coment>
<center>
<img src=./images/application-exit";
if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo "-glow"; 
echo ".png>
<br>
<b>Limpando sess&otilde;es abertas...</b>
<br>
Por favor, aguarde...
</center>
</div>
";

include "./include/page_footer.inc";
?>
