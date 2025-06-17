<?PHP
/**
 * Faz a autenticacao do usuario e inicia a sessao.
 * $Id: auth.php,v 1.22 2017/10/03 14:00:35 filipi Exp $
 */
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['h'])) $hash = pg_escape_string($_GET['h']); else $hash = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;

if (isset($_GET['landingPage']) && file_exists(basename($_GET['landingPage'])))
  $landingPage =  basename($_GET['landingPage']);
else
  $landingPage = 0;
if (isset($_GET['alvo'])){
  $alvo = intval($_GET['alvo']);
  if ($alvo)
    while (strlen($alvo)<6) $alvo = "0" . $alvo;
 }
$ehXML = 1; $useSessions = 0;
$headerTitle = "ONDE loging in";
include "iniset.php";
include "page_header.inc";
ini_set ( "error_reporting", "E_ALL" );
include "moodle.inc";

if ($hash){
  //include_once("startup.inc"); // Neste include carrega o conf e conecta com o banco.
  $queryGetForm  = "SELECT form, button_row_index FROM access_hashes\n";
  $queryGetForm .= "  WHERE hash = '" . $hash . "'"; 
  $result = pg_exec ($conn, $queryGetForm);
  if ($result){
    $row = pg_fetch_row ($result, 0);
    $form = $row[0];
    $buttonrow[$row[1]]="detalhes";
    //echo "<PRE>"; var_dump($_GET['buttonrow']); echo "</PRE>";
  }
}

/**
 * Trecho de codigo para autenticacao.
 */

if (trim($_POST['matricula']) && intval(trim($_POST['matricula'])))
  $matricula = intval(trim($_POST['matricula']));
 else
  $matricula = pg_escape_string(trim($_POST['matricula']));
$email = pg_escape_string(trim($_POST['email']));

$matricula_ou_email = pg_escape_string(trim($_POST['matricula_ou_email']));
$senha = pg_escape_string(trim($_POST['senha']));
$ip = $_SERVER['REMOTE_ADDR'];

// Informacaoes sobre a sessao.
session_cache_expire(1);
//session_save_path("../session_files");
ini_set('session.save_path',"../session_files");
//session_start();
//session_save_path('../session_files');
session_name('onde');
session_start();

if ($_integracaoMoodleLigada){
  switch ($login_field){
    case 1:
      $moodleData['usuario'] = $email;
      break;
    case 2:
      $moodleData['usuario'] = $matricula_ou_email;
      break;
    case 0:
    default:
      $moodleData['usuario'] = $matricula;
   }

  $moodleData['usuario'] = addslashes($moodleData['usuario']);
  $moodleData['senha'] = addslashes($moodleData['senha']);
  //$_debug = 2;

  if (!timeOutCheck($URL_moodle)){
    if ($_debug) echo "timeout OK<BR>";
    $path = "../session_files/moodle/";
    if (!file_exists($path)){
      mkdir("./" . $path, 0777);  
    }
    $tentativas = 0;
    //echo "Tentando login no moodle...<BR>";
    //$_debug = 1;

    $moodleData = moodleGetToken($path, $moodleData);
    if ($moodleData && $moodleData['token']){
      $moodleData['senha'] = $senha;
      $moodleData = moodleLogin($path, $moodleData);
      if ($moodleData && $moodleData['id']){
        $moodleData = moodleGetProfileData($path, $moodleData);      
        $moodleData = moodleGetProfileExtendedData($path, $moodleData);
      }
    }
    //if ($moodleData && $moodleData['id']){
    //}
    //$_debug = 1;
    /*
    echo "<B>Token: </B> " . $moodleData['token'] . "<BR>";      
    echo "<B>id: </B>" . $moodleData['id'] . "<BR>";
    */
    //echo "<B>nome: </B>" . $moodleData['nome'] . "<BR>";
    //echo "<B>email: </B>" . $moodleData['email'] . "<BR>";
    //echo "<B>matricula: </B>" . $moodleData['matricula'] . "<BR>";
    //echo "<B>matricula2: </B>" . $moodleData['matricula2'] . "<BR>";
    //echo "<B>posicao: </B>" . $moodleData['posicao'] . "<BR>";

    if (trim(pg_escape_string($moodleData['nome'])) ||
        trim(pg_escape_string($moodleData['posicao'])) ||
        trim(pg_escape_string($moodleData['matricula'])) ||
        trim(pg_escape_string($moodleData['matricula2'])) ||
        trim(pg_escape_string($moodleData['email'])) ||
        trim(pg_escape_string($moodleData['cidade'])) ||
        trim(pg_escape_string($moodleData['disciplinas']))){

      $query_moodle_log  = "INSERT INTO moodle_login_log (\n";
      $query_moodle_log .= "    nome, posicao, matricula, matricula2, email,\n";
      $query_moodle_log .= "    cidade, disciplinas, moodle_id) VALUES (\n";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['nome']) . "',";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['posicao']) . "',";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['matricula']) . "',";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['matricula2']) . "',";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['email']) . "',";
      $query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['cidade']) . "',";
	  //echo "PASSEI<BR>";
      //echo "<PRE>";
	  //var_dump($moodleData['disciplinas']);
      //echo "</pre>";
      //$query_moodle_log .= "'" . pg_escape_string($conn, $moodleData['disciplinas']) . "',";
	  //echo "PASSEI<BR>";
      $query_moodle_log .= "" . intval($moodleData['id']) . ")";

      $exec_moodle_logp = pg_exec($conn,$query_moodle_log);

      //$comandos = "echo \" \n===========================\nquery_moodle_log: " . print_r($query_moodle_log, true). "\" >> /tmp/saveTemp.log";
      //$log = `$comandos`;
    }

    //echo "<PRE>" . $query_moodle_log . "</pre>";

    /*
    posicaoes de alunos: FACIN, engenharia mecanica
    echo "<B>cidade: </B>" . $moodleData['cidade'] . "<BR>";
    foreach($moodleData['disciplinas'] as $disciplina){
      if ($disciplina['codcred']){
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>nome: </B>" . $disciplina['nome'] . "<BR>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>codcred: </B>" . $disciplina['codcred'] . "<BR>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>semestre: </B>" . $disciplina['semestre'] . "<BR>";
      }
    }
    */
  }
}

// Autentica na base do ONDE
$query_base  = "SELECT login, senha, nome, email, last_login, first, ativo, aniversario, tema, masculino, dark, moodle_id, prefer_moodle_login \n";
$query_base .= "  FROM usuarios\n";
$query_base .= "  WHERE ";
switch ($login_field){
  case 1:
    $query_base .= " email='" . $email . "' ";
    break;
  case 2:
    $query_base .= " (email='" . $matricula_ou_email . "' OR login = '" . $matricula_ou_email . "') ";
    break;
  case 0:
  default:
    $query_base .= " login='" . $matricula . "' ";
 }
$query_adm = $query_base;
//$query_adm .= " AND\n";
//$query_adm .= "        senha='" . $senha_crypt . "'
$query_adm .= " AND ativo = true";
$exec_adm = pg_exec($conn,$query_adm);
$autenticado = false;
if ($exec_adm){
  //$nro_linhas =  pg_num_rows($exec_adm);
  $resultado = pg_fetch_assoc($exec_adm, 0);

  if ( substr($resultado['senha'], 0, 2)=='9$'){ // senha velha
    $senha_crypt = crypt($senha, '9$');
    if ($senha_crypt == $resultado['senha']){ // senha velha OK
      $autenticado = true;
      $nova_senha_crypt = crypt($senha, "$1$");
      $query_atualiza_senha  = "UPDATE usuarios SET senha = '" . $nova_senha_crypt . "'\n";
      $query_atualiza_senha .= " WHERE login = '" . $resultado['login'] . "'";
      pg_exec($conn, $query_atualiza_senha);
    }
  }
  $nova_senha_crypt = crypt($senha, "$1$");
  if ( substr($resultado['senha'], 0, 3)=='$1$'){ // senha nova
    if ($nova_senha_crypt == $resultado['senha']){ // senha velha OK
      $autenticado = true;
    }
  }
}

//echo "PASSEI<BR><PRE>" . $query_base . "</PRE><BR>";

//$authlog_query  = "INSERT INTO authlog(matricula, senha, IP, success)\n";
//$authlog_query .= "  VALUES ('" . $matricula . "', '" . $senha . "',\n";
$authlog_query  = "INSERT INTO authlog(matricula, IP, success)\n";
$authlog_query .= "  VALUES ('" . (!$matricula?$matricula_ou_email:$matricula) . "', \n";
$authlog_query .= "          '" . $ip . "', ";
$authlog_query .= (($autenticado===true) ? "true" : "false") . ")\n";
$authlog_exe = pg_exec($conn,$authlog_query);

//$comandos = "echo '\n\n insert into authlog\n: " . print_r($authlog_query, true). "' >> /tmp/saveTemp.log";
//$log = `$comandos`;
//$comandos = "echo \"\n ERRO:\n " . print_r(pg_last_error(), true). "\" >> /tmp/saveTemp.log";
//$log = `$comandos`;

//$comandos = "echo \"\n autenticado: " . print_r(intval($autenticado), tru´e). "\" >> /tmp/saveTemp.log";
//$log = `$comandos`;

// Nao encontrou no login
$listagem = 0;

if ( !$autenticado && $moodleData['id'] ){
  //$_debug = 1;
  //echo "Usuário não autenticado no onde, mas autenticado no moodle <BR>";

  //$comandos = "echo \" query_base: " . print_r($query_base, true). "\" >> /tmp/saveTemp.log";
  //$log = `$comandos`;

  if ($moodleData['nome'] && ( ($moodleData['matricula2'] && strlen($moodleData['matricula2'])<=8) ||
                               ($moodleData['matricula'] && strlen($moodleData['matricula'])<=8) ) ){
    // Aqui executa a query_base, pois é para decidir se inclui ou não na lista de usuarios.
    // A diferenca entre base e adm é que na adm, é verificado se o usuario esta ativo.
    // Aqui não deve verificar se ativo, pois se já tiver um inativo, não deve inserir
    $exec_base = pg_exec($conn,$query_base);
    //echo "<PRE>" . $query_base . "</PRE><BR>";
    $nro_linhas =  pg_num_rows($exec_base);
    if ($nro_linhas){
      $autenticado = true;
      //echo "Usuário existe no onde. <BR>";
      $linha = pg_fetch_row($exec_base,0);
      $h_log = date("Y-m-d H:i:s");
      $matricula = $linha[0];
      $senha_crypt = $linha[1];
      $nome = $linha[2];
      $email = $linha[3];
      $last_login = $linha[4];
      //$first = $linha[5];
      $ativo = $linha[6];
      $tema = $linha[9];
      //$_theme = $tema;
      $dark = $linha[10];
      $moodleId = $linha[11];
      $moodleLogin = $linha[12];
      if ( ($matricula == $moodleData['matricula2'] || $matricula == $moodleData['matricula']) && $moodleLogin && $moodleId)
        echo " <BR>";
      else{
	$autenticado = false;
        $nro_linhas = 0;
      }
      if ($ativo == 'f'){
	$ativa_usuarios_autenticados_com_moodle = "update usuarios set ativo = true where login = '" . $matricula . "'";
	$exec_reativa = pg_exec($conn,$ativa_usuarios_autenticados_com_moodle);
        //$comandos = "echo \" QUERY REATIVA usuarios: " . print_r($query, true). "\" >> /tmp/saveTemp.log";
        //$log = `$comandos`;
        //$comandos = "echo \" ERRO: " . print_r(pg_last_error(), true). "\" >> /tmp/saveTemp.log";
        //$log = `$comandos`;
      }
    }
    else{
      //$_debug = 1;
      //echo "Usuário não existe no onde, inserindo... <BR>";
      if ($moodleData['matricula2'] && strlen($moodleData['matricula2'])<=8 && $moodleData['nome'] && $moodleData['email'] && $moodleData['id']){
        $query  = "INSERT INTO usuarios (login, nome, email, moodle_id, prefer_moodle_login, first) VALUES (\n";
        $query .= "'" . (pg_escape_string($moodleData['matricula2'])?pg_escape_string($moodleData['matricula2']):
			 (pg_escape_string($moodleData['matricula'])?pg_escape_string($moodleData['matricula']):$matricula)) . "',\n";
        $query .= "'" . pg_escape_string($moodleData['nome']) . "',\n";
        $query .= "'" . pg_escape_string($moodleData['email']) . "',\n";
        $query .= "" . intval($moodleData['id']) . ",\n";
        $query .= "'t', 'f')\n";
        //echo "<PRE>" . $query . "</PRE>";

        //$comandos = "echo \" QUERY INSERT INTO usuarios: " . print_r($query, true). "\" >> /tmp/saveTemp.log";
        //$log = `$comandos`;

        //echo "PASSEI<BR>";
        $exec_newUser = pg_exec($conn,$query);

	//$comandos = "echo \" INSERINDO NO ONDE!! " . print_r($exec_newUser, true). "\" >> /tmp/saveTemp.log";
	//$log = `$comandos`;
	//$comandos = "echo \" deu erro?:" . pg_last_error() . "\" >> /tmp/saveTemp.log";
	//$log = `$comandos`;

        if ($exec_newUser){
          //echo "PASSEI<BR>" . $moodleData['matricula2'];
          //echo "PASSEI<BR>" . $moodleData['matricula'];
	  $autenticado = true;
          $nro_linhas = 1;
          $matricula = $moodleData['matricula2'];
          $nome = $moodleData['nome'];
          $email = $moodleData['email'];
          $first = 'f';
          $dark = 'f';

	  //$query  = "SELECT (login, nome, email, moodle_id, prefer_moodle_login, first FROM usuarios where\n";
          //$query .= "login = '" . pg_escape_string($moodleData['matricula']) . "'\n";
          //$query .= "and nome = '" . pg_escape_string($moodleData['nome']) . "'\n";
          //$query .= "and email = '" . pg_escape_string($moodleData['email']) . "'\n";
          //$query .= "and moodle_id = " . intval($moodleData['id']) . "\n";
          //$query .= "and prefer_moodle_login = 't' and firs = 'f')\n";

	  // Adiciona no grupo doacoes
          //$query_add_group = "INSERT INTO usuarios_grupos (usuario, grupo) values ('" . $matricula . "', 116)";
          //$exec_group = pg_exec($conn,$query_add_group);

	  // Adiciona no grupo usuarios
          $query_add_group = "INSERT INTO usuarios_grupos (usuario, grupo) values ('" . $matricula . "', 4)";
          $exec_group = pg_exec($conn,$query_add_group);

	  /*
          //echo "<B>POSICAO:</B> " . $moodleData['posicao'] . "<BR>";
	  // Adiciona no grupo matera poli
	  if ($moodleData['posicao'] == "EP - Professor"
            ||$moodleData['posicao'] == "EP - Funcionario"){           
            $query_add_group = "INSERT INTO usuarios_grupos (usuario, grupo) values ('" . $matricula . "', 80)";
            $exec_group = pg_exec($conn,$query_add_group);
            //echo "<PRE>" . $query_add_group . "</pre>";
	  }
	  */

          //echo "<B>POSICAO:</B> " . $moodleData['posicao'] . "<BR>";
	  if ($moodleData['posicao'] == "EC - Professor"
            ||$moodleData['posicao'] == "EC - Funcionario"
            ||$moodleData['posicao'] == "ECSV - Professor"
            ||$moodleData['posicao'] == "ECSV - Funcionario"){           
            $query_add_group = "INSERT INTO usuarios_grupos (usuario, grupo) values ('" . $matricula . "', 120)";
            $exec_group = pg_exec($conn,$query_add_group);
            //echo "<PRE>" . $query_add_group . "</pre>";
	  }
          //$_debug = 1;
          //exit()

          //exit();
	}
	else{
          if ($_debug) echo "message: " . pg_last_error() . "\n";
	  $autenticado = false;
          $nro_linhas = 0;
	}
      }
    }
  }
}
else{ 
  //$_debug = 2;
  if ($_debug){
    echo "PASSEI nao tem linhas<BR>";
    echo "Matricula1: " . $moodleData['matricula'] ."<BR>\n";
    echo "Matricula2: " . $moodleData['matricula2'] ."<BR>\n";
    echo "Id: " . $moodleData['id'] ."<BR>\n";
    echo "Matricula: " . $matricula ."<BR>\n";
  }

  $matricula = (pg_escape_string($moodleData['matricula2'])?pg_escape_string($moodleData['matricula2']):
	       (pg_escape_string($moodleData['matricula'])?pg_escape_string($moodleData['matricula']):$matricula));

  if ($matricula && $moodleData['id']){
    $query_insere_moodle_id_no_onde = "update usuarios set moodle_id = " . intval($moodleData['id']) . " where login = '" . $matricula . "'";
    if ($_debug) echo "<PRE>" . $query_insere_moodle_id_no_onde . "</PRE>";
    $exec_moodle_id = pg_exec($conn,$query_insere_moodle_id_no_onde);
    if ($_debug) echo "message: " . pg_last_error() . "\n";       

  }
  if ($_debug) echo "Matricula: " . $matricula ."<BR>\n";
  //$matricula = 
}

//echo "Numero de linhas: " . $nro_linhas . "<BR>\n";
//$_debug = 1;
if ($autenticado){
  if ( $moodleData['matricula2']){// && $moodleLogin && $moodleId){
    //$_debug =1;
    $nome = $moodleData['nome'];
    $email = $moodleData['email'];
    $first = 'f';
    $dark = 'f';
  }else{
    $linha = pg_fetch_row($exec_adm,0);
    $matricula = $linha[0];
    $senha_crypt = $linha[1];
    $nome = $linha[2];
    $email = $linha[3];
    $last_login = $linha[4];
    $first = $linha[5];
    $dark = $linha[10];
  }
  $h_log = date("Y-m-d H:i:s");

  //echo "First: " . $first . "<BR>";
  //exit();
/*

  session_register("h_log","matricula","senha","senha_crypt",
		   "nome","email","last_login","first","ip");

*/
  $_SESSION['h_log']       = $h_log;
  $_SESSION['matricula']   = $matricula;
  $_SESSION['senha']       = $senha;
  $_SESSION['senha_crypt'] = $senha_crypt;
  $_SESSION['nome']        = $nome;
  $_SESSION['email']       = $email;
  $_SESSION['last_login']  = $last_login;
  $_SESSION['first']       = $first;
  $_SESSION['ip']          = $ip;
  $_SESSION['dark']        = $dark;


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
  $query .= "  WHERE usuarios_grupos.usuario = '" . $matricula . "'\n";
  $query .= "   AND  grupos.codigo = usuarios_grupos.grupo\n";
  if ($_debug) echo "<PRE>" . $query . "</PRE><BR>\n";
   $result = pg_exec ($conn, $query);
   $total  = pg_num_rows($result);
   $linhas = 0;
   $grupos = "";
   while ($linhas<$total){
     $row = pg_fetch_row ($result, $linhas);
     $_SESSION['grupos'] .= $row[0];
     $linhas++;
   }
   //$_debug = 1;
  if ($first == "f"){?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug>1) echo "10000"; else 
      if ( (date("m", time()) == 4) && (date("d", time()) == 1) )
        echo "4";
      else 
        echo "1";?>; URL=./<?PHP if ($landingPage) echo $landingPage; else echo "f-main.php";  ?>?PHPSESSID=<?PHP echo $PHPSESSID . ($demanda ? "&demanda=" . $demanda : "") . ($form ? "&form=" . $form : "") . ($alvo ? "&alvo=" . $alvo : "") . ($landingPage ? "&landingPage=" . $landingPage : "") . ($hash ? "&h=" . $hash : ""); ?>' TARGET='_self'><?PHP
  }
  else{?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug>1) echo "10000"; else echo "1";?>;
    URL=./first.php?PHPSESSID=<?PHP
     echo $PHPSESSID . ($demanda ? "&demanda=" . $demanda : "") . ($form ? "&form=" . $form : "") . ($alvo ? "&alvo=" . $alvo : "") . ($landingPage ? "&landingPage=" . $landingPage : "") . ($hash ? "&h=" . $hash : ""); ?>' TARGET='_self'><?PHP  
  }
}
else
{?>
    <META HTTP-EQUIV='Refresh' CONTENT='
    <?PHP if ($_debug) echo "10000"; else echo "1";?>;
    URL=./frm_login.php?ERR=1<?PHP echo ($demanda ? "&demanda=" . $demanda : "") . ($form ? "&form=" . $form : "") . ($alvo ? "&alvo=" . $alvo : "") . ($landingPage ? "&landingPage=" . $landingPage : "") . ($hash ? "&h=" . $hash : ""); ?>' TARGET='_self'> <?PHP
}

if ($_debug){
  echo "<PRE>\n";
  echo $query_adm . "\n";
  echo "<B>matricula: " . $matricula . "</B>\n";
  echo "<B>email: " . $email . "</B>\n";
  echo "<B>matricula_ou_email: " . $matricula_ou_email . "</B>\n";
  echo "<B>senha: " . $senha . "</B>\n";
  echo "<B>IP: " . $ip . "</B>\n";
  echo "<B>conn: " . print_r($conn, true) . "</B>\n";
  echo "<B>exec_adm: " . print_r($exec_adm, true) . "</B>\n";
  echo "<B>nro_linhas: " . $nro_linhas . "</B>\n";
  echo "<B>autenciado: " . $autenticado . "</B>\n";
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
<?PHP
if ( (date("m", time()) == 4) && (date("d", time()) == 1) )
  echo "<H1>Verificando seus dados junto a <B>Polícia Federal</B>...</H1>";
?>
<BR>
Por favor, aguarde...
</DIV>
</CENTER>
<?PHP
include "page_footer.inc";
?>
