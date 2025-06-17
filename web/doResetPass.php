<?PHP
$useSessions = 0; $ehXML = 0;
$withoutMenu[] = "doResetPass.php";
if (isset($_GET['demanda']))  $demanda = intval($_GET['demanda']);
if (isset($_GET['form'])) $form = intval($_GET['form']);
include "iniset.php";
//include "start_sessao.inc";

include "page_header.inc";

//echo "<PRE>" . print_r($_POST, true) . "</PRE>\n";

$hash = trim(pg_escape_string($conn, $_GET['h']));
if (trim($hash)==='')
  $hash = trim(pg_escape_string($conn, $_POST['hash']));

$query  = "select *,\n";
$query .= " now(), now()-quando as idade,\n";
$query .= "(case when (now() - quando) < '10 min'::interval then true else false end) as valido\n";
$query .= "from reseta_senha where hash = '" . $hash . "'";
//echo "<PRE>" . $query . "</PRE>";
$result = pg_exec($conn, $query);

if ($result){
  $solicitacao_de_reset = pg_fetch_assoc($result, 0);
  //echo "<PRE>" . print_r($solicitacao_de_reset, true) . "</PRE>\n";
 }

?>
<BR>
<BR>
<?PHP 
if ($solicitacao_de_reset['valido']  != 't') {
  echo "<CENTER>\n";
  echo "Seu link expirou.<BR>\n";
  echo "<small><a href=\"resetSenha.php\">(solicite um novo reset de senha)</a></small><BR>\n";
  echo "</CENTER>\n";
  include "page_footer.inc";
  exit(1);
}
if ($_POST['SEND'] == 't'){
  $hash = pg_escape_string($conn, $_POST['hash']);
  $SENHA_NEW_CAD = trim($_POST['SENHA_NEW_CAD']);
  $SENHA_NEW_REP = trim($_POST['SENHA_NEW_REP']);
  //echo $_POST['SENHA_NEW_CAD'] . $_POST['SENHA_NEW_REP'] . $_POST['SENHA_OLD'] .  $_SESSION['matricula'] ;
  $ERR = '';
  //echo "SENHA ANTIGA: " . $_SESSION['senha'] . "<BR><BR>\n";
  //exit(1);
  //if ($SENHA_OLD != $_SESSION['senha']) $ERR = $ERR.'1';
  if ($SENHA_NEW_CAD != $SENHA_NEW_REP) $ERR = $ERR.'2';
  if (strlen($SENHA_NEW_CAD) < 6) $ERR = $ERR.'3';


  $query = "select login from usuarios where email = (select email from reseta_senha where hash = '" . pg_escape_string($conn, $hash) . "')";
  $result = pg_exec($conn, $query);
  if ($result)
    $usuario = pg_fetch_assoc($result, 0);

  //echo "<PRE>" . $query . "</PRE>";
  //echo "<PRE>" . print_r($login, true) . "</PRE>";
  //echo "<PRE>" . print_r($_POST, true) . "</PRE>";
  if ($ERR == '' )  {
    $query = "UPDATE usuarios SET senha='" . crypt(trim($SENHA_NEW_CAD) ,'$1$') . "',first='f' WHERE login='" . $usuario['login'] . "'";
    $exec = pg_query($conn,$query);

    //echo "<PRE>" . $query . "</PRE>";

    if ($exec) {
      $useSessions = 1;
      include("startup.inc"); // Inicia a sessao.

      ini_set('session.save_path',"../session_files");
      session_name('onde');
      session_start();
      //echo "<PRE>" . print_r($_SESSION, true) . "</PRE>";

      $_SESSION['matricula'] = $usuario['login'];
      $_SESSION['h_log'] = date("Y-m-d H:i:s");
      $_SESSION['senha_crypt'] = crypt(trim($SENHA_NEW_CAD), '$1$');
      $_SESSION['senha'] = $SENHA_NEW_CAD;
      $_SESSION['first'] = 'f';

      $PHPSESSID = session_id();
      $_SESSION['PHPSESSID'] = $PHPSESSID;

      include "start_sessao.inc";

      //echo "<PRE>" . print_r($_SESSION, true) . "</PRE>";
      //echo "ID:  " . $PHPSESSID . "<BR>";
      //echo "ID:  " . $_SESSION['PHPSESSID'] . "<BR>";


  $_SESSION['grupos'] = "_";

  $query  = "SELECT grupos.nome\n";
  $query .= "  FROM usuarios_grupos, grupos\n";
  $query .= "  WHERE usuarios_grupos.usuario = '" . $usuario['login'] . "'\n";
  $query .= "   AND  grupos.codigo = usuarios_grupos.grupo\n";
  //if ($_debug)
  //echo "<PRE>" . $query . "</PRE><BR>\n";
   $result = pg_exec ($conn, $query);
   $total  = pg_num_rows($result);
   $linhas = 0;
   $grupos = "";
   while ($linhas<$total){
     $row = pg_fetch_row ($result, $linhas);
     $_SESSION['grupos'] .= $row[0];
     $linhas++;
   }

      echo "<CENTER>Senha alterada!</CENTER>";
      
     ?> 
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug) echo "10"; else echo "1";?>;
    URL=./f-main.php?PHPSESSID=<?PHP echo $PHPSESSID; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?>' TARGET='_self'><?PHP echo "\n";
      
    }
    else {
      $ERR = $ERR.'4';
      ?> <meta HTTP-EQUIV='Refresh' CONTENT='0; URL=./doResetPass.php?PHPSESSID=<?PHP echo $PHPSESSID . "&ERR=" . $ERR ; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?> ' TARGET='_self'> <?PHP
    }
  }
  else {
    ?> <meta HTTP-EQUIV='Refresh' CONTENT='0; URL=./doResetPass.php?PHPSESSID=<?PHP echo $PHPSESSID."&h=" . $hash . "&ERR=".$ERR ; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?>' TARGET='_self'> <?PHP
  }
  ?>
  <div class=coment>
  <center>
  <b>Processando sua solicitação.</b><br>
  Por favor, aguarde...
  </center>
  </div>
  <?PHP
}
else{
  ?>
  <center>
  <table width=400>
   <tr>
    <th>Troca de senha</th>
   </tr>
   <tr>
    <td>
    <div class=coment>
    <center>
    <b>ATENÇÃO!</b><br>
    <br>
    Por motivo de segurança, sua senha deve ser trocada.<br>
    <?PHP
    echo "<form method=POST action=\"" . basename($_SERVER['PHP_SELF']) . "\">\n";
    echo "<input type='hidden' name='hash' value='" . $hash . "'>\n";
    ?>
    <input type='hidden' name='SEND' value='t'>
    <table style='border-width: 0px;'>
     <tr>
      <td style='border-width: 0px;'> 
      <div class=coment> 
      <b>Nova senha:</b><br>
      <input type='password' name='SENHA_NEW_CAD' size='20' 
      class "ui-input ui-widget ui-corner-all"
      STYLE="height: 28px; width: 300px"       
      <?PHP /*maxlength='12' */?> ><br>
      <b>Repita a nova senha:</b><br>
      <input type='password' name='SENHA_NEW_REP' size='20'
      class "ui-input ui-widget ui-corner-all"
      STYLE="height: 28px; width: 300px"       
       <?PHP /*maxlength='12' */?> ><br>
      <br>
      <center>
      <input type='submit' value='Trocar Senha'
      CLASS="ui-button ui-widget ui-corner-all">
      </center>
      </div>
      </td>
     </tr>
    </table>
    </form>
    </center>
    </div>
    </td>
   </tr>
  </table>
  </center>
  <?PHP
}
if($debug == 1) echo $_SESSION['matricula'] ;

if ($_GET['ERR'] != ''){
  $ERR = $_GET['ERR'];
  ?>
  <div class=coment>
  <font color='#FF0000'>
  <center>
  <b>ATENÇÃO!</b><br>
  <br>
  Ocorreu um ERRO no processamento de sua solicitação.<br>
  Leia atentamente as messagens abaixo:<br>
  <br>
  <?PHP
  if (strstr($ERR,'2')){
    ?>
    Os campos "<b>Nova Senha</b>" e "<b>Repita a Nova Senha</b>" devem ser iguais.
    <br>
    <?PHP
  }
  if (strstr($ERR,'3')) {
    ?>
    A senha deve conter no <b>mínimo 6</b> caracteres.
    <br>
    <?PHP
  }
  if (strstr($ERR,'4')){

    ?>
    <font color='#FFCC00'>
    <br>
    Ocorreu um erro no Banco de Dados.
    <br>
    Por favor, tente novamente mais tarde.
    <br>
    <b>Se persistir o problema entre em contato com urgência com o Webmaster.</b>
    </font>
    <br>
    <?PHP
  }
  ?>
  <br>
  Por favor, repita a operação.
  </center>
  </font>
  </div>
  <?PHP
}

include "page_footer.inc";

?>
