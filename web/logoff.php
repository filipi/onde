<?PHP
if (isset($_GET['PHPSESSID'])) $PHPSESSID = $_GET['PHPSESSID']; else $PHPSESSID = 0;
$headerTitle = "Closing ONDE";
include "iniset.php";
include "page_header.inc";

error_reporting(1);

ini_set('session.save_path',"./session_files");
//session_save_path('./session_files');
session_name('onde');
session_start();


if($_SESSION['h_log'] && $_SESSION['matricula']){
  session_destroy();
}

if (file_exists("session_files/simulation" .  $_GET['PHPSESSID'])){
  $command = "rm -rfv ./session_files/simulation" . $_GET['PHPSESSID'];
  exec($command);
}

echo "
<!-- Retorna à página de LOGIN -->
<meta HTTP-EQUIV='Refresh' CONTENT='1; URL=./frm_login.php' TARGET='_self'>

<div class=coment>
<center>
<img src=./images/application-exit";
if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo "-glow"; 
echo ".png>
<br>
<b>Efetuando Logoff...</b>
<br>
Por favor, aguarde...
</center>
</div>
";
include "page_footer.inc";
?>
