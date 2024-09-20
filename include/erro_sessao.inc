<?PHP
$headerTitle = "Error - logging on!";
$useSessions = 0; $ehXML=0;
include "page_header.inc";

echo "<CENTER>\n";
if (stripos("_" . $_theme, 'fancy')  || stripos("_" . $_theme, 'tron'))
  echo "<IMG SRC=images/dialog-error.png>\n";
else   
  echo "<IMG SRC=images/icon_vazio.jpg>\n";


?>
<BR>
<BR>
<B>Este site requer autenticação.</B><BR>
Clique no botão abaixo pra efetuar login.<BR>
<BR>
<?PHP
echo "<A HREF=\"frm_login.php";
if ($demanda)
  echo "?demanda=" . $demanda;
else
  if ($form) echo "?form=" . $form; 
echo "\"";
if (!stripos("_" . $_theme, "frameless"))
  echo "TARGET=\"_main\"";
echo ">Efetuar Login</A>";?>
</CENTER>
<?PHP
include "page_footer.inc";
?>
