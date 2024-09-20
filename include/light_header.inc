<?PHP
  include_once("escapeConfVars.inc");
  include("conf.inc"); escapeConfVars();
  include_once("lib.inc");
  if($useSessions)
  include "start_sessao.inc";

  $connectionString  = "dbname =   " . $banco . "\n";
  $connectionString .= "user =     " . $usuario_banco . "\n";
  $connectionString .= "password = " . $senha_banco;
  $conn = pg_connect($connectionString);
  if (!$conn){
    if (!$ehXML){
    Warning("Banco de dados temporariamente indispon&iacute;vel.<BR>
             Tente novamente em alguns minutos.");
    }
    else{
      echo " [/!\\]  Banco de dados temporariamente indisponivel.\n";
      echo "        Tente novamente em alguns minutos.\n";
    }
    include "page_footer.inc";
    exit(1);
  }
  pg_set_client_encoding($conn, $encoding);



?>