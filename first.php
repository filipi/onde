<?PHP
$useSessions = 1; $ehXML = 0;
$withoutMenu[] = "first.php";
if (isset($_GET['demanda']))  $demanda = intval($_GET['demanda']);
if (isset($_GET['form'])) $form = intval($_GET['form']);
include "./include/start_sessao.inc";
include "./include/page_header.inc";

//echo "<PRE>"; var_dump($_SESSION); echo "</PRE>";

echo "<DIV CLASS='titulo'>Bem vind";
if ($_SESSION['genero'] == 'masculino')
  echo "o";
 else
  echo "a";
echo " <B>" . $_SESSION[nome] . "</B>!</DIV>\n";

?>

<BR>
<BR>
<?PHP 
if ($_POST[SEND] == 't'){
  $SENHA_OLD = trim($_POST[SENHA_OLD]);
  $SENHA_NEW_CAD = trim($_POST[SENHA_NEW_CAD]);
  $SENHA_NEW_REP = trim($_POST[SENHA_NEW_REP]);
  if($debug == 1) echo $_POST[SENHA_NEW_CAD] . $_POST[SENHA_NEW_REP] . $_POST[SENHA_OLD] .  $_SESSION[matricula] ;
  $ERR = '';
  //echo "SENHA ANTIGA: " . $_SESSION['senha'] . "<BR><BR>\n";
  //exit(1);
  //if ($SENHA_OLD != $_SESSION['senha']) $ERR = $ERR.'1';
  if ($SENHA_NEW_CAD != $SENHA_NEW_REP) $ERR = $ERR.'2';
  if (strlen($SENHA_NEW_CAD) < 6 || strlen($SENHA_NEW_CAD) > 12) $ERR = $ERR.'3';
  if ($ERR == '')  {
    $query = "UPDATE usuarios SET senha='" . crypt(trim($SENHA_NEW_CAD) ,'9$') . "',first='f' WHERE login='" . $_SESSION[matricula] . "'";
    $exec = pg_query($conn,$query);
    if ($exec) {
      $_SESSION[senha_crypt] = crypt(trim($SENHA_NEW_CAD), '9$');
      $_SESSION[senha] = $SENHA_NEW_CAD;
      $_SESSION[first] = 'f';
	?> 
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug) echo "10"; else echo "1";?>;
    URL=./f-main.php?PHPSESSID=<?PHP echo $PHPSESSID; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?>' TARGET='_self'><?PHP echo "\n";
    }
    else {
      $ERR = $ERR.'4';
      ?> <meta HTTP-EQUIV='Refresh' CONTENT='0; URL=./first.php?PHPSESSID=<?PHP echo $PHPSESSID . "&ERR=" . $ERR ; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?> ' TARGET='_self'> <?PHP
    }
  }
  else {
    ?> <meta HTTP-EQUIV='Refresh' CONTENT='0; URL=./first.php?PHPSESSID=<?PHP echo $PHPSESSID."&ERR=".$ERR ; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form; ?>' TARGET='_self'> <?PHP
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
    <th>PRIMEIRO LOGIN</th>
   </tr>
   <tr>
    <td>
    <div class=coment>
    <center>
    <b>ATENÇÃO!</b><br>
    <br>
    Por questão de segurança, sua senha deve ser trocada na primeira vez que é efetuado o LOGIN no sistema.<br>
    No formulário abaixo informe a senha que lhe foi fornecida e cadastre uma nova senha que contenha no <b>mínimo 6</b> e no <b>máximo 12</b> caracteres.<br>
    <form method=POST action=first.php?PHPSESSID=<?PHP 
    echo $PHPSESSID;
    if ($demanda) echo "&demanda=" . $demanda; 
    ?>>
    <input type='hidden' name='PHPSESSID' value='<?PHP echo $PHPSESSID; ?>'>
    <input type='hidden' name='SEND' value='t'>
    <table style='border-width: 0px;'>
     <tr>
      <td style='border-width: 0px;'> 
      <div class=coment> 
      <b>Senha Antiga:</b><br>
      <input type='password' name='SENHA_OLD' size='20' maxlength='12' value='<?PHP echo $_SESSION[senha]; ?>'><br>
      <b>Nova Senha:</b><br>
      <input type='password' name='SENHA_NEW_CAD' size='20' maxlength='12' ><br>
      <b>Repita a Nova Senha:</b><br>
      <input type='password' name='SENHA_NEW_REP' size='20' maxlength='12' ><br>
      <br>
      <center>
      <input type='submit' value='Trocar Senha'>
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
if($debug == 1) echo $_SESSION[matricula] ;

if ($_GET[ERR] != ''){
  $ERR = $_GET[ERR];
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
  if (strstr($ERR,'1')){
    ?>
    A senha antiga não confere com a previamente cadastrada.
    <br>
    <?PHP
  }
  if (strstr($ERR,'2')){
    ?>
    Os campos \"<b>Nova Senha</b>\" e \"<b>Repita a Nova Senha</b>\" devem ser iguais.
    <br>
    <?PHP
  }
  if (strstr($ERR,'3')) {
    ?>
    A senha deve conter no <b>mínimo 6</b> e no <b>máximo 12</b> caracteres.
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

include "./include/page_footer.inc";

?>
