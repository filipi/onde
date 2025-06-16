<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
$codigo = intval(trim($_GET['codigo']));
$target = "forms.php?form=259&toggle[]=M285&buttonrow[" . $codigo . "]=detalhes";
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 0;

$headerTitle = "AGU 2019 > <a href=" . $target . ">Scripts python</a>";
include "iniset.php";
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario

$query  = "SELECT s.codigo, s.script, \n";
$query .= "       a.caminho, s.\"Mostrar STDERR\" as showstderr \n";
$query .= "  FROM \"Scripts python\" AS s,\n";
$query .= "       \"Ambiente python\" AS a\n";
$query .= "  WHERE s.codigo = " . $codigo . "\n";
$query .= "        AND s.\"Kernel python\" = a.codigo";
$result = pg_exec ($conn, $query);
if ($result){
  $script = pg_fetch_assoc ($result, 0);
  if ($_debug){
    echo "<PRE>";
    echo $query ."\n\n";
    var_dump($script);
    echo "</PRE>";
  }
}
else{
  Warning(pg_last_error());
}
require_once("class.phpPythonPipe.php");

$python = new phpPythonPipe();
$python->kernelPath = $script['caminho'];

echo "showSTDERR: <B>" . $script['showSTDERR'] . "</B><BR>\n";

$python->showSTDERR = ( $script['showstderr'] == 't' ? true : false );
$python->code = $script['script'];
$python->exec();
echo "<PRE>\n";
$python->print();
echo "</PRE>\n";


include "page_footer.inc";
?>
