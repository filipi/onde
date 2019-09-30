<?PHP
/**
 * Faz a autenticacao do usuario e inicia a sessao.
 * $Id: auth.php,v 1.22 2017/10/03 14:00:35 filipi Exp $
 */
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;
if (isset($_GET['alvo'])){
  $alvo = intval($_GET['alvo']);
  if ($alvo)
    while (strlen($alvo)<6) $alvo = "0" . $alvo;
 }

$ehXML = 1; $useSessions = 0;
$headerTitle = "ONDE loging in";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
ini_set ( "error_reporting", "E_ALL" );

/**
 * Trecho de codigo para autenticacao.
 */
$login = $_POST['login'];
$senha = $_POST['senha'];
$senha_crypt = crypt(trim($senha),'9$');
$ip = getenv(REMOTE_ADDR);
$type = intval($_POST['type']);

// Informacaoes sobre a sessao.
session_cache_expire(1);
//session_save_path("./session_files");
ini_set('session.save_path',"./session_files");
//session_start();
//session_save_path('./session_files');
session_name('onde');
session_start();

$user = new userInfo($login, $type, $senha_crypt);

$query_adm  = "SELECT * \n";
$query_adm .= "  FROM usuarios\n";
if($type){
  $query_adm .= "  WHERE email='" . $login . "' AND\n";
} else {
  $query_adm .= "  WHERE login='" . $login . "' AND\n";
}
$query_adm .= "        senha='" . $senha_crypt . "' AND ativo = true";
$exec_adm = pg_exec($conn,$query_adm);
$nro_linhas =  pg_NumRows($exec_adm);
//$login = pg_fetch_row($exec_adm);
//$authlog_query  = "INSERT INTO authlog(matricula, senha, IP, success)\n";
//$authlog_query .= "  VALUES ('" . $login . "', '" . $senha . "',\n";

$authlog_query  = "INSERT INTO authlog(matricula, IP, success)\n";
$authlog_query .= "  VALUES ('" . $login . "', \n";
$authlog_query .= "          '" . $ip . "', ";
$authlog_query .= ($user->isValidUser() ? "true" : "false") . ")\n";
$authlog_exe = pg_exec($conn,$authlog_query);

if($user->isValidUser()){
  $h_log = date("Y-m-d H:i:s");

  /*Depois do merge*/
  $login = $linha[0];
  $senha_crypt = $linha[1];
  $nome = $linha[2];
  $email = $linha[3];
  $last_login = $linha[4];
  $first = $linha[5];
  /*Depois do merge*/
  

/* Antes do merge
  $login = $user->getUserInfo('login');
  $first = $user->getUserInfo('first');
  $_SESSION['h_log']       = $h_log;
  $_SESSION['matricula']   = $login;
  $_SESSION['senha']       = $senha;
  $_SESSION['senha_crypt'] = $user->getUserInfo('senha');
  $_SESSION['nome']        = $user->getUserInfo('nome');
  $_SESSION['email']       = $user->getUserInfo('email');
  $_SESSION['last_login']  = $user->getUserInfo('last_login');
  $_SESSION['first']       = $first;
  $_SESSION['ip']          = $ip;
*/

/*

  session_register("h_log","matricula","senha","senha_crypt",
		   "nome","email","last_login","first","ip");

*/
/*
  $_SESSION['h_log']       = $h_log;
  $_SESSION['matricula']   = $login;
  $_SESSION['senha']       = $senha;
  $_SESSION['senha_crypt'] = $senha_crypt;
  $_SESSION['nome']        = $nome;
  $_SESSION['email']       = $email;
  $_SESSION['last_login']  = $last_login;
  $_SESSION['first']       = $first;
  $_SESSION['ip']          = $ip;

*/
//echo $_SESSION['matricula'] ;
//echo "<BR>\nSID=" . SID . "<BR>\n";
//echo "PASSEI";
//echo intval($nro_linhas);


  if ($linha[9]=="t") $_SESSION['genero'] = "masculino";
  if ($linha[9]=="f") $_SESSION['genero'] = "feminino";

  $PHPSESSID = session_id();

  $_SESSION['grupos'] = "_";

  $query  = "SELECT grupos.nome\n";
  $query .= "  FROM usuarios_grupos, grupos\n";
  $query .= "  WHERE usuarios_grupos.usuario = '" . $login . "'\n";
  $query .= "   AND  grupos.codigo = usuarios_grupos.grupo\n";
  //if ($_debug) echo "<PRE>" . $query . "</PRE><BR>\n";
   $result = pg_exec ($conn, $query);
   $total  = pg_numrows($result);
   $linhas = 0;
   $grupos = "";
   while ($linhas<$total){
     $row = pg_fetch_row ($result, $linhas);
     $_SESSION['grupos'] .= $row[0];
     $linhas++;
   }

  if ($first == "f"){?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug>1) echo "10"; else echo "1";?>;
    URL=./f-main.php?PHPSESSID=<?PHP echo $PHPSESSID; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form . ($alvo ? "&alvo=" . $alvo : ""); ?>' TARGET='_self'><?PHP
  }
  else{?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug>1) echo "10"; else echo "1";?>;
    URL=./first.php?PHPSESSID=<?PHP
     echo $PHPSESSID; if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form . ($alvo ? "&alvo=" . $alvo : ""); ?>' TARGET='_self'><?PHP  
  }
}
else{
?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug) echo "10000"; else echo "1";?>;
    URL=./frm_login.php?ERR=1<?PHP if ($demanda) echo "&demanda=" . $demanda; else if ($form) echo "&form=" . $form . ($alvo ? "&alvo=" . $alvo : ""); ?>' TARGET='_self'> <?PHP
}



if ($_debug){
  echo "<PRE>\n";
  echo $query_adm . "\n";
  echo "<B>matricula: " . $login . "</B>\n";
  echo "<B>senha: " . $senha . "</B>\n";
  echo "<B>IP: " . $ip . "</B>\n";
  echo "<B>conn: " . $conn . "</B>\n";
  echo "<B>exec_adm: " . $exec_adm . "</B>\n";
  echo "<B>nro_linhas: " . $nro_linhas . "</B>\n";
  echo "<B>\$linha: </B>";  
  echo var_dump($linha);
  echo "\n";
  echo "<B>\$_SESSION: </B>";  
  echo var_dump($_SESSION);
  echo "</PRE>\n";  
}

$ehXML = 0;  $useSessions = 0;
include "page_header.inc";
?>
<CENTER
<DIV ID=coment>
<B>Autenticando usuário.</B><BR>
<BR>
Por favor, aguarde...
</DIV>
</CENTER>
<?PHP
include "page_footer.inc";
?>