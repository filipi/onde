<?PHP
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;
if (isset($_GET['alvo'])){
  $alvo = intval($_GET['alvo']);
  if ($alvo)
    while (strlen($alvo)<6) $alvo = "0" . $alvo;
 }
$headerTitle = "ONDE login";
$useSessions = 0; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);

include "page_header.inc";

?><script>
function isNumberKey(evt){
  var charCode = (evt.which) ? evt.which : event.keyCode
    var event = window.event  || evt;// || ffEvent ; //ffEvent is the function argument
  var intKeyCode = event.keyCode || event.which;
  if (intKeyCode > 31 && (intKeyCode < 48 || intKeyCode > 57))
    return false;
  return true;
}
</script><?PHP


if ($_GET['ERR'] == '1'){
 ?>
  <DIV CLASS=coment>
  <CENTER>
  <FONT COLOR='#FF0000'><B>ERRO!</B><BR>
  <BR>
  Nome de usuário ou senha inválidos<BR>
  Por favor, tente novamente.
  </FONT>
  </CENTER>
  </DIV>
 <?PHP
}

if ($_manutencao){
  echo "<CENTER>MANUTENÇÃO</CENTER><BR>\n";
  include "page_footer.inc";
  exit;
}

?>
<CENTER>
 <FORM METHOD='POST' ACTION='./auth.php<?PHP if ($demanda) echo "?demanda=" . $demanda; if ($form) echo "?form=" . $form . ($alvo ? "&alvo=" . $alvo : ""); ?>' NAME='LOGIN'>
 <TABLE class=onde>
  <TR>
    <TH class=onde>EFETUAR LOGIN</TH>
 </TR>
  <TR>
   <TD class=onde>
   <DIV ID='coment'><?PHP
  switch ($login_field){
  case 1:?>
   E-mail:<BR>
   <INPUT CLASS='campo' TYPE='text' NAME="email" SIZE='100' MAXLENGTH='128' 
    style="width: 300px; font-size:3em;"><BR>
  <?PHP
  break;
  case 2:?>
   Matrícula ou email:<BR>
   <INPUT CLASS='campo' TYPE='text' NAME="matricula_ou_email" SIZE='30' MAXLENGTH='128' 
    style="width: 300px; font-size:3em;"><BR>
  <?PHP
  break;  
  case 0:
  default:?>
   Matrícula:<BR>
   <INPUT CLASS='campo' TYPE='text' NAME="matricula" SIZE='30' MAXLENGTH='8' 
    style="width: 200px; font-size:4em;"
    onKeyUp= <?PHP echo "'return autoTab(this, 8, event); ' " ?> 
    onkeypress="return isNumberKey(event)"><BR>
  <?PHP
  }
?>
  <?PHP /*
   Problems with keyCode on firefox. changed to charCode and works with chrome and firefox, test with others
   But special keys (backspace, for example) still not work with firefox
   onKeypress=<?PHP echo"'console.log(event); if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;'"?>><BR>    
   onKeypress=<?PHP echo"'console.log(event); if(event.charCode < 48 || event.charCode > 57) event.returnValue = false;'"?>><BR>
   // Perhaps this should work:
   //https://stackoverflow.com/questions/27813731/event-returnvalue-false-is-not-working-in-firefox

   */?>
   Senha:<BR>
   <INPUT CLASS='campo' TYPE='password' NAME="senha" SIZE='12' MAXLENGTH='12'
    style="width: <?PHP if (!$login_field) echo "150"; else echo "245"; ?>px;"
    >&nbsp;
   <INPUT CLASS='button' TYPE='submit' VALUE=' OK '>
   </TD>
  <?PHP 
  if($debug == 1){ 
    echo $_POST['matricula'];
    echo $_POST['email'];
    echo  $_POST['senha']; echo $conn;
  }?>
  </TR>
 </TABLE>
 </FORM>
</CENTER>

<!--
<DIV style="float:right; border: 1px solid #c86060;">
afsdjfçlaksdjfçalksdjf
</DIV>
-->

<?PHP
include "page_footer.inc";
?>
