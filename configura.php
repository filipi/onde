<?PHP
 /**
  * Pagina para configuracao do sistema.
  * $Id: configura.php,v 1.49 2018/12/19 20:27:09 filipi Exp $
  */
$headerTitle = "P&aacute;gina de configura&ccedil;&atilde;o do sistema";
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include_once("masterFormStartup.inc");
include "page_header.inc";

if (trim($_POST['banco'])){
  /* Salva as configuracoes */

  $arquivo_de_configuracao = fopen("./include/conf.new.buffer.inc", "w"); /* w trunca o arquivo para 0 e c nao trunca */
  //fclose($arquivo_de_configuracao);			 

 if (!$arquivo_de_configuracao){
    Warning("Imposs&iacute;el abrir arquivo de configura&ccedil;&atilde;o
             para escrita!<BR>Verifique as permiss&otilde;es.");
    include "page_footer.inc";
    exit(1);
  }

  $developer_array = explode(",", $_POST['developer']);

  $confStr  = "<?PHP\n";
  $confStr .= "  \$banco = \"" . pg_escape_string(trim($_POST['banco'])) . "\";\n";
  $confStr .= "  \$banco_cadastro = \"" . pg_escape_string(trim($_POST['banco_cadastro'])) . "\";\n";
  $confStr .= "  \$usuario_banco = \"" . pg_escape_string(trim($_POST['usuario_banco'])) . "\";\n";
  $confStr .= "  \$senha_banco = \"" . pg_escape_string(trim($_POST['senha_banco'])) . "\";\n";

  $confStr .= "  \$system_mail_from = \"" . pg_escape_string(trim($_POST['system_mail_from'])) . "\";\n";
  $confStr .= "  \$system_mail_from_name = \"" . pg_escape_string(trim($_POST['system_mail_from_name'])) . "\";\n";
  $confStr .= "  \$system_mail_host = \"" . pg_escape_string(trim($_POST['system_mail_host'])) . "\";\n";
  $confStr .= "  \$system_mail_mailer = \"" . pg_escape_string(trim($_POST['system_mail_mailer'])) . "\";\n";
  $confStr .= "  \$debug_mail_recipient = \"" . pg_escape_string(trim($_POST['debug_mail_recipient'])) . "\";\n";
  
  $confStr .= "  \$URL = \"" . pg_escape_string(trim($_POST['URL'])) . "\";\n";
  $confStr .= "  \$email = \"" . pg_escape_string(trim($_POST['email'])) . "\";\n";
  $confStr .= "  \$fone = \"" . pg_escape_string(trim($_POST['fone'])) . "\";\n";
  $confStr .= "  \$organizationWebSiteURL = \"" . pg_escape_string(trim($_POST['organizationWebSiteURL'])) . "\";\n";  
  reset($developer_array);
  while (list($key, $val) = each($developer_array)){
    $confStr .= "  \$developer[" . $key . "] = \"";
    $confStr .= pg_escape_string(trim($developer_array[$key])) . "\";\n";
  }
  $confStr .= "  \$_menu_from_db = " . intval( (boolean) trim($_POST['menu_from_db']) ) . ";\n";
  $confStr .= "  \$_debug = " . (integer) trim($_POST['debug']) . ";\n";
  $confStr .= "  \$login_field = " . (integer) trim($_POST['login_field']) . ";\n";  
  $confStr .= "  \$_singleQueue = " . (integer) trim($_POST['singleQueue']) . ";\n";
  $confStr .= "  \$_remoteAssets = " . (integer) trim($_POST['remoteAssets']) . ";\n";
  $confStr .= "  \$_theme = \"" .  pg_escape_string(trim($_POST['theme'])) . "\";\n";
  $confStr .= "  \$verificaEmail = " . (integer) trim($_POST['verificaEmail']) . ";\n";
  $confStr .= "  \$encoding = \"" . pg_escape_string(trim($_POST['encoding'])) . "\";\n";
  $confStr .= "  \$mem_limit = \"" . (integer) trim($_POST['mem_limit']) . "\";\n";
  $confStr .= "  \$max_execution_time = \"" . (integer) trim($_POST['max_execution_time']) . "\";\n";

  foreach($deps as $dep){
    $dep = trim(str_replace("-", "_", $dep));
    $confStr .= "  \$path_to_" . $dep . " = \"" . pg_escape_string(trim($_POST['path_to_' . $dep])) . "\";\n";
  }

  $confStr .= "?>\n";
  fputs($arquivo_de_configuracao, $confStr);
  if (fclose($arquivo_de_configuracao)){
    $command_to_copy_files = $path_to_cp . " ./include/conf.new.buffer.inc ./include/conf.inc";
    $error = `$command_to_copy_files`;
    echo "<PRE>\n";
    echo htmlentities($error);
    echo "\n</PRE>\n";
    echo "<DIV class=\"message\">Configura&ccedil;&otilde;es salvas";
    echo " com sucesso!</DIV>\n";
  }
  else
    Warning("Erro gravando configura&ccedil;&otilde;es!");

  include_once("php_backwards_compatibility.inc");
  include_once("escapeConfVars.inc");
  include "conf.inc"; escapeConfVars();
  
  ///////////////////////////////////////////
  $query_adm  = "SELECT * \n";
  $query_adm .= "  FROM usuarios\n";
  $query_adm .= "  WHERE login='" . $_POST['matricula'] . "'\n";
  $exec_adm = pg_exec($conn,$query_adm);
  $nro_linhas =  pg_NumRows($exec_adm);
  if ($nro_linhas > 0){
    $linha = pg_fetch_row($exec_adm,0);
    $h_log = date("Y-m-d H:i:s");
    $matricula = $linha[0];
    $senha_crypt = $linha[1];
    $nome = $linha[2];
    $email = $linha[3];
    $last_login = $linha[4];
    $first = $linha[5];

    $_SESSION['h_log']       = $h_log;
    $_SESSION['matricula']   = $matricula;
    $_SESSION['senha_crypt'] = $senha_crypt;
    $_SESSION['nome']        = $nome;
    $_SESSION['email']       = $email;
    $_SESSION['last_login']  = $last_login;
    $_SESSION['first']       = $first;
    $_SESSION['ip']          = $ip;
  }
  /////////////////////////////////////////
}
//echo (integer) isset($_POST['remoteAssets']);
?>
    <FORM NAME="configuracao" ACTION="" METHOD="POST">
    <BR>
<?PHP
  //    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  //    <INPUT TYPE="SUBMIT" CLASS="SUBMIT" VALUE="Salvar...">
  //    <BR>
  togglePoint("visuais", "Configurações visuais da fLameWork O.N.D.E.", 1, false, NULL);
?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>URL do sistema:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="URL" SIZE="40"
    VALUE="<?PHP echo $URL; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>E-mail mostrado no rodap&eacute; do sistema:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="email" SIZE="40"
    VALUE="<?PHP echo $email; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Telefone mostrado no rodap&eacute; do sistema:</B><BR>					       
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="fone" SIZE="40"
    VALUE="<?PHP echo $fone; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>URL do website da organiza&ccedil;&atilde;o:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="organizationWebSiteURL" SIZE="40"
    VALUE="<?PHP echo $organizationWebSiteURL; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Encoding:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="encoding" SIZE="40"
      VALUE="<?PHP echo $encoding; ?>"><BR>
    <BR>
<?PHP
// Note that !== did not exist until 4.0.0-RC2
$i = 0;
if ($handle = opendir('themeAssets')) {
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != ".." && 
      $file != ".htaccess" && $file != "CVS") {
      $themes[] = "$file";
    }
  }
  closedir($handle);
}
echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
echo "    <B>Tema visual:</B><BR>\n";
echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
echo "    <SELECT class=\"chosen-select\" NAME=\"theme\" SIZE=\"1\" >\n";
foreach($themes as $val){
  $dep = trim(str_replace("-", "_", $dep));
  if (isset($_POST['theme'])){
    if ($_POST['theme']==$val){
      echo "      <OPTION SELECTED VALUE=\"" . $val . "\">";
      echo $val . "</OPTION>\n";
    }
    else{
      echo "      <OPTION VALUE=\"" . $val . "\">";
      echo $val . "</OPTION>\n";
    }
  }
  else{
    if ($_theme == $val){
      echo "      <OPTION SELECTED VALUE=\"" . $val . "\">";
      echo $val . "</OPTION>\n";
    }
    else{
      echo "      <OPTION VALUE=\"" . $val . "\">";
      echo $val . "</OPTION>\n";
    }
  }
}
  echo "    </SELECT><BR><BR>\n";
  $label  = "    <B>Remote assets:</B> (para os temas visuais)<BR>\n";
  $label .= "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  $label .= "    <I>Alguns temas visuais utilizam elementos carregados de sites remotos.<BR>\n";
  $label .= "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; \n";
  $label .= "    Quando habilitado o &ldquo;Remote assets&rdquo;, esses elementos são carregados<BR>\n";
  $label .= "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; \n";
  $label .= "    do servidor de aplicação.</I><BR>\n";

  togglePoint("remoteAssets", $label, 1, true, $_remoteAssets);
?>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Endereço do servidor:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="host_siteIdeia" SIZE="40"
    VALUE="<?PHP echo $host_siteIdeia; ?>"><BR>
    <BR>
<?PHP
  echo $closeDIV;

echo $closeDIV;
echo "<BR>\n";

togglePoint("mailing", "Configurações de envio de e-mail", 1, false, NULL);
?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Endereço do remetente:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="system_mail_from" SIZE="40"
    VALUE="<?PHP echo $system_mail_from; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Nome do remetente:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="system_mail_from_name" SIZE="40"
    VALUE="<?PHP echo $system_mail_from_name; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Endereço do servidor de e-mail:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="system_mail_host" SIZE="40"
    VALUE="<?PHP echo $system_mail_host; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Protocolo de email utilizado:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="system_mail_mailer" SIZE="10"
    VALUE="<?PHP echo $system_mail_mailer; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Destinário do e-mail de debug:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="debug_mail_recipient" SIZE="40"
    VALUE="<?PHP echo $debug_mail_recipient; ?>"><BR>
    <BR>
<?PHP
echo $closeDIV;
echo "<BR>\n";

    togglePoint("avancado", "Configurações avançadas", 1, false, NULL);
?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Matrícula do usuário desenvolvedor:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="developer" SIZE="40"
    VALUE="<?PHP
    reset($developer);
    while (list($key, $val) = each($developer)){
      echo $developer[$key];
      if (isset($developer[$key+1])) echo ", ";
    }
    ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Carregar menu a partir do banco de dados:</B><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Habilitar:				   
    <INPUT TYPE="CHECKBOX" <?PHP if (intval($_menu_from_db)!=0) echo "CHECKED"; ?>
    NAME="menu_from_db"><br><br>


    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Campo para utilizar como login:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($login_field==0) echo "CHECKED"; ?>
    NAME="login_field" VALUE="0">Matrícula numérica com 8 dígitos<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($login_field==1) echo "CHECKED"; ?>
    NAME="login_field" VALUE="1">E-mail<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($login_field==2) echo "CHECKED"; ?>
    NAME="login_field" VALUE="2">Matrícula ou e-mail (detectar automáticamente)<BR>
    <br>
				    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>N&iacute;vel de debug:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($_debug==0) echo "CHECKED"; ?>
    NAME="debug" VALUE="0">0&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($_debug==1) echo "CHECKED"; ?>
    NAME="debug" VALUE="1">1&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($_debug==2) echo "CHECKED"; ?>
    NAME="debug" VALUE="2">2&nbsp;
    <INPUT TYPE="RADIO" <?PHP if ($_debug==3) echo "CHECKED"; ?>
    NAME="debug" VALUE="3">3&nbsp;<BR><BR>
<?PHP
echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";

if (strpos($_SESSION['grupos'], 'manuten')){
  echo "<B>USU&Aacute;RIO LOGADO:</B><BR>\n";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  if (isset($_SESSION['nome']))
    dbcombo("usuarios", "login", "nome", $conn, "matricula", 100, $_SESSION['nome'], $submit, NULL, NULL, NULL, NULL);
  else
    dbcombo("usuarios", "login", "nome", $conn, "matricula", 100, "selecione o usuario", $submit, NULL, NULL, NULL, NULL);
  echo "<BR>\n";
  echo "<BR>\n";
}
if ($_debug){ // Aqui eh pra criar quando jah vem preenchido
  echo "<BR><BR>\n<PRE>\n";
  var_dump($_SESSION);
  echo "</PRE><BR>\n";
}
echo $closeDIV;
echo "<BR>\n";

togglePoint("DEPENCENDIAS", "Dependências", 1, false, NULL);
foreach($deps as $dep){
  $depp = $dep;
  $dep = trim(str_replace("-", "_", $dep));
  echo "<BR>\n";
  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "    <B>Caminho para o " . $dep . ":</B><BR>\n";
  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"path_to_" . $dep . "\" SIZE=\"40\"\n";
  $variable = "path_to_" . $dep;
  echo "      VALUE=\"";
  if (isset($$variable))
    echo $$variable;
  else
    echo "/usr/bin/" . $depp;
  echo "\"><BR>\n";
}
echo $closeDIV;
echo "<BR>\n";
    togglePoint("banco", "Configurações de acesso ao banco de dados interno do sistema", 1, false, NULL);
?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Banco de dados utilizado:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="banco" SIZE="40"
    VALUE="<?PHP echo $banco; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Banco de dados do cadastro de alunos:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="banco_cadastro" SIZE="40"
     VALUE="<?PHP echo $banco_cadastro; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Usu&aacute;rio do banco:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="usuario_banco" SIZE="40"
    VALUE="<?PHP echo $usuario_banco; ?>"><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Senha do banco:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="PASSWORD" CLASS="PASSWORD" NAME="senha_banco" SIZE="40"
    VALUE="<?PHP echo $senha_banco; ?>"><BR><BR>
<?PHP
    echo $closeDIV;
    echo "<BR>\n";
togglePoint("deprecated", "Opções de retrocompatibilidade da fLameWork O.N.D.E.", 1, false, NULL);
?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>URL do Sipesq:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="URL_sipesq" SIZE="40"
    VALUE="<?PHP echo $URL_sipesq; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Uso de uma &uacute;nica fila para os projetos:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Habilitar: <INPUT TYPE="CHECKBOX" NAME="singleQueue"
    VALUE="1" <?PHP if($_singleQueue) echo "CHECKED"; ?>><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Executa valida&ccedil;&atilde;o de e-mail:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Habilitar: <INPUT TYPE="CHECKBOX" NAME="verificaEmail"
    VALUE="1" <?PHP if($verificaEmail) echo "CHECKED"; ?>><BR><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Memory limit:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="mem_limit" SIZE="4"
      VALUE="<?PHP echo $mem_limit; ?>"><BR>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <B>Max execution time:</B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="TEXT" CLASS="TEXT" NAME="max_execution_time" SIZE="3"
      VALUE="<?PHP echo $max_execution_time; ?>"><BR>
    <BR>
<?PHP echo $closeDIV; ?>
    <BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="SUBMIT" CLASS="SUBMIT" VALUE="Salvar...">
  </FORM>
  <BR><BR>
<?PHP
include "page_footer.inc";
?>
