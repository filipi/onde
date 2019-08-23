<?PHP
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;
if (isset($_GET['alvo'])){
  $alvo = intval($_GET['alvo']);
  if ($alvo)
    while (strlen($alvo)<6) $alvo = "0" . $alvo;
 }
$headerTitle = "EasyBeasy login";
$useSessions = 0; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);

include "page_header.inc";

?><script>
/**
 * @brief Verify if the typed character is a number
 * @param evt key pressed
 * @return boolean false if is not a number else returns true.
 */
function isNumberKey(evt){
  var charCode = (evt.which) ? evt.which : event.keyCode
    var event = window.event  || evt;// || ffEvent ; //ffEvent is the function argument
  var intKeyCode = event.keyCode || event.which;
  if (intKeyCode > 31 && (intKeyCode < 48 || intKeyCode > 57))
    return false;
  return true;
}

/**
    Client-side login validation. Check if the inserted login is an e-mail or a registration number (8 digits needed)
 */
$(document).ready(function(){
  var validation = false;
 const mailGex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
 const matGex = /^\d{7}[0-9]$/;
 $("#username").focusout(function(){ 
    var content = $("#username").val();
    if(mailGex.exec(content) || matGex.exec(content)) {
      $("#username").css("border-color", "green");
      $("#loginStatus").hide();
      validation = true;
    }
    else{
      invalidUsername();
      validation = false;
    }
 });
 $("#LoginForm").submit(function(e){
    if(!validation)
      invalidUsername();
      e.preventDefault();
  });

  function invalidUsername(){
    $("#loginStatus").show();
    $("#loginStatus").html("Login inválido");
    $("#loginStatus").css("color", "red");
    $("#username").css("border-color", "red");
  }
});
</script><?PHP


if ($_GET[ERR] == '1'){
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
 <FORM ID='LoginForm' METHOD='POST'
       ACTION='./auth.php' <?PHP
        if ($demanda) echo "?demanda=" . $demanda; 
        if ($form) echo "?form=" . $form . ($alvo ? "&alvo=" . $alvo : ""); 
?>
 NAME='LOGIN'>
 <TABLE class=onde>
  <TR>
    <TH class=onde>EFETUAR LOGIN</TH>
 </TR>
  <TR>
   <TD class=onde>
   <DIV ID='coment'>
      <CENTER>
      Login:<BR>

    <!-- <INPUT CLASS='campo' TYPE='text' NAME="matricula" SIZE='30' MAXLENGTH='8' -->
     <INPUT id='username' CLASS='campo' TYPE='text' NAME="matricula" SIZE='60' 
      style = "width: 500px;"
      placeholder = "E-mail ou número de matrícula">
      <BR>
      </center>
      <!-- onKeyUp=<?PHP //echo "'return autoTab(this, 8, event); ' " ?> 
      onkeypress="return isNumberKey(event)"><BR>
      </center> -->
     <div id='loginStatus'></div>
  <?PHP /*
   Problems with keyCode on firefox. changed to charCode and works with chrome and firefox, test with others
   But special keys (backspace, for example) still not work with firefox
   onKeypress=<?PHP echo"'console.log(event); if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;'"?>><BR>    
   onKeypress=<?PHP echo"'console.log(event); if(event.charCode < 48 || event.charCode > 57) event.returnValue = false;'"?>><BR>
   // Perhaps this should work:
   //https://stackoverflow.com/questions/27813731/event-returnvalue-false-is-not-working-in-firefox

   */?>
   <center>
   Senha:<BR>
   
   <INPUT CLASS='campo' TYPE='password' NAME="senha" SIZE='12' MAXLENGTH='12'
    style="width: 150px;"
    >&nbsp;
    
   <INPUT CLASS='button' TYPE='submit' VALUE=' OK '>
   </center>
   </TD>
  <?PHP 
  if($debug == 1){ 
    echo $_POST['matricula'];
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
