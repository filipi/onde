<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
$tablename = trim(pg_escape_string($_GET['tablename']));
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 0;
$headerTitle = "Página de gabarito";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario

if (!$isdeveloper){
  echo "      <DIV CLASS=\"busy\">Não foi possível criar um formulário para a tabela " . $tablename . "</div";
  Warning("Apenas desenvolvedores podem criar novos formulários");  
  include "page_footer.inc";
  exit;
 }

//$query = "select tablename from pg_tables where tableowner = '" . $usuario_banco . "' and tablename = '" . $tablename . "'";
$query = "select oid from pg_class where relname = '" . $tablename . "'";
$result = pg_exec($conn, $query);
$row = pg_fetch_row($result, 0);
$oid = $row[0];
if (!$result || !$oid){
  echo "      <DIV CLASS=\"busy\">Não foi possível criar um formulário para a tabela " . $tablename  . "</div>";
  Warning("A atual configuração do sistema não tem acesso à tabela<BR><B>\"" . $tablename . "\"</B> ou esta tabela não existe.");  
  include "page_footer.inc";
  exit;
}
$row = pg_fetch_row($result, 0);
$key = $row[0];
$query  = "SELECT attname FROM pg_attribute WHERE attrelid = " .  $key . " and attstattarget<>0" ;
$result = pg_exec($conn, $query);
$campos = pg_fetch_all_columns($result);

$query  = "INSERT INTO forms (nome, titulo, tabela, campos, ordenarpor, \n";
$query .= "chave, funcao, formulario, remover, \"Termo para botões CRUD (plural)\", dono";
if (in_array("Usuário", $campos) || in_array("usuario", $campos) )
  $query .= ",  \"Campo para salvar usuário logado\" ";
$query .= ")\n";
$query .= "VALUES(\n";
$query .= " '" . $tablename . "',\n"; //nome
$query .= " '" . $tablename . "',\n"; //titulo
$query .= " '" . $tablename . "',\n"; //tabela
$query .= " '\"" . implode("\", \"", $campos) . "\"',\n"; //campos
$query .= " '" . $campos[0] . "',\n"; //ordenarpor
$query .= " " . intval(0) . ",\n"; //chave
$query .= " 'basename',\n"; //funcao
$query .= " 'detalhes',\n"; //formulario
$query .= " 't',\n"; // remover
$query .= " '" . $tablename . "',\n"; //termo para botoes crud
$query .= " '" . $_SESSION['matricula'] . "'\n"; //termo para botoes crud
if (in_array("Usuário", $campos)) 
  $query .= ",  'Usuário' ";
else
  if (in_array("usuario", $campos))
    $query .= ",  'usuario' ";
$query .= ")\n";

//echo "<PRE>" . $query . "</PRE>";

$result = pg_exec($conn, $query);
if (!$result)
  echo "      <DIV CLASS=\"busy\">Não foi possível criar um formulário para a tabela " . $tablename;
else
  echo "      <DIV CLASS=\"message\"><a href=\"forms.php?form=6\">Formulário</a> criado com sucesso.";

//echo "<PRE style=\"color: black;\">" . pg_last_error() . "</PRE>";


include "page_footer.inc";
?>
