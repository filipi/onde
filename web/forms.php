<?PHP
$useSessions = 1; $ehXML = 0;
include("iniset.php");
$mostraForm = true;
require_once "eos.class.php";

include_once("startup.inc"); // Neste include carrega o conf e conecta com o banco.

if (isset($_GET['h'])){
  $hash = pg_escape_string($_GET['h']);
  $queryGetForm  = "SELECT form, button_row_index FROM access_hashes\n";
  $queryGetForm .= "  WHERE hash = '" . $hash . "'"; 
  $result = pg_exec ($conn, $queryGetForm);
  if ($result){
    $row = pg_fetch_row ($result, 0);
    $_GET['form'] = $row[0];
    $_GET['buttonrow'][$row[1]]="detalhes";
    //echo "<PRE>"; var_dump($_GET['buttonrow']); echo "</PRE>";
  }
  //$_debug = 2;
  //echo "<PRE>" . print_r($_GET, true) . "</PRE>\n";
}

$codigo = intval($_GET['form']);
$query  = "SELECT * FROM forms WHERE codigo = " . $codigo;
$resultFORMULARIO = pg_exec ($conn, $query);
$formulario  = pg_fetch_array ($resultFORMULARIO, 0);

//$queryPermissoes = "select \"permissão\" from forms_permissoes where form = " . $codigo;
//$resultPermissoes = pg_exec ($conn, $queryPermissoes);
//$formulario  = pg_fetch_array ($resultPermissoes, 0);
//$permissoes = pg_fetch_array ($resultPermissoes, 0);

//$mailFormColumnsBlackList[] = ''
if (isset($formulario['Ocultar estas colunas ao enviar form por e-mail'])){
  $mailFormColumnsBlackList = explode(',', $formulario['Ocultar estas colunas ao enviar form por e-mail']);
  for($i=0;$i<count($mailFormColumnsBlackList);$i++){
    $mailFormColumnsBlackList[$i]=str_replace(";", ",", $mailFormColumnsBlackList[$i]);   
    $mailFormColumnsBlackList[$i]=trim($mailFormColumnsBlackList[$i]);
  }
} 

if (trim($formulario['Dicas (formulário)']))
  $dicas_formulario = json_decode(trim($formulario['Dicas (formulário)']));
else
  $dicas_formulario = "";

if (isset($formulario['Não exigir login para este formulário'])
    && $formulario['Não exigir login para este formulário'] == 't'){
  $useSessions = 0;
  $withoutMenu[] = "forms.php";  
 }

if (isset($formulario['Não carregar cabeçalhos html'])
    && $formulario['Não carregar cabeçalhos html'] == 't'){
  $ehXML = 1;
 }
if (isset($formulario['Desabilitar menu'])
    && $formulario['Desabilitar menu'] == 't')
  $withoutMenu[] = "forms.php";

//include_once("masterFormStartup.inc");
include "start_sessao.inc";
include "page_header.inc";
?>
<style>

.modal-label{
display: inline-block;
  line-height: 2.2em;
padding: 0 0.62em;
border: 1px solid #666;
    border-radius: 0.25em;
  background-image: linear-gradient( to bottom, #fff, #ccc );
				     box-shadow: inset 0 0 0.1em #fff, 0.2em 0.2em 0.2em rgba( 0, 0, 0, 0.3 );
				     font-family: arial, sans-serif;
				     font-size: 0.8em;
				     }

  .modal-label:hover {
    border-color: #3c7fb1;
    background-image: linear-gradient( to bottom, #fff, #a9dbf6 );
				       }

    .modal-label:focus {
    padding: 0  0.56em 0 0.68em;
    }
</style>
<?PHP
if (isset($_manutencao) && $_manutencao){
  echo "<CENTER>MANUTENÇÃO</CENTER><BR>\n";
  include "page_footer.inc";
  exit;
}

$orderBy = pg_escape_string($_GET['orderby']);
$desc = pg_escape_string($_GET['desc']);

if ( $isdeveloper ){
  echo "<div class=\"developerEditToolBar\">\n";
  echo "[<a style=\"cursor: pointer;\" onclick=\"salva_ordem();\">Salvar reordenação</a>] ";
  echo "[<a style=\"cursor: pointer;\" onclick=\"reordenar();\">Reordenar campos</a>] ";
  echo "[<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=6&";
  foreach ($toggle as $value) echo "&t[]=" . $value;  
  echo "\">Listar formulários</a>]\n";
  echo "[<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=6&buttonrow[" . $codigo . "]=detalhes";
  foreach ($toggle as $value) echo "&t[]=" . $value;  
  echo "\">Editar este formulário</a>]\n";
  echo "</DIV>\n";
}

$argumentKey = 0;

if(isset($_GET['buttonrow']) && !isset($_POST['buttonrow'])){
  $_POST['buttonrow'] = $_GET['buttonrow'];
 }

if (isset($_POST['buttonrow']) 
    && trim($formulario['formulario'])
    && $formulario['Apenas form, sem tabela'] == 't'
    && $formulario['Não exigir login para este formulário'] == 't'
    && !$hash
   ){
  unset($_POST['buttonrow']);
  unset($_GET['buttonrow']);
}
//echo $formulario['formulario'] . "<BR>";
//echo $formulario['Apenas form, sem tabela'] . "<BR>";
//echo $formulario['Não exigir login para este formulário'] . "<BR>";
// if ($isdeveloper)  echo "<B>\$buttonrow_key:</B> " . intval($buttonrow_key) . "<BR>";
// if ($isdeveloper) echo "\$queryarguments<PRE>" . print_r($queryarguments, true) . "</PRE>";

// if (isset($buttonrow_key) && $queryarguments[0]['value']){
// 		echo "PASSEI\n";
// 	}


if (isset($_POST['buttonrow'])){
    foreach($_POST['buttonrow'] as $buttonrow_key => $buttonrow_val){
    $queryarguments[$argumentKey]['key'] = 0;
    $queryarguments[$argumentKey]['value'] = pg_escape_string($buttonrow_key);
    $queryarguments[$argumentKey]['type'] = 0; //string

    if (is_numeric($argvalue))
      if (is_float($argvalue)){
        $queryarguments[$argumentKey]['value'] = floatval($buttonrow_key);
        $queryarguments[$argumentKey]['type'] = 1; // float
      }
      else{
        $queryarguments[$argumentKey]['value'] = intval($buttonrow_key);
        $queryarguments[$argumentKey]['type'] = 2; // int
      }
    else{
      $queryarguments[$argumentKey]['value'] = pg_escape_string($buttonrow_key);
      $queryarguments[$argumentKey]['type'] = 0; //string;
    }
  }
 
}
$argumentKey++;
 // if (issset($buttonrow_key) && !isset($queryarguments[0]['value']))
 //   $queryarguments[0]
 
// if ($isdeveloper)  echo "<B>\$buttonRowKey:</B> " . intval($buttonRowKey) . "<BR>";
// if ($isdeveloper)  echo "<B>\$buttonrow_key:</B> " . intval($buttonrow_key) . "<BR>";
// if ($isdeveloper) echo "\$queryarguments<PRE>" . print_r($queryarguments, true) . "</PRE>";

 if (isset($_POST['a']) && !(isset($_GET['a']) && (isset($_GET['args'])))){
   $_GET['a'] = $_POST['a'];
 }
if (isset($_GET['args']) || isset($_GET['a'])){
  if (isset($_GET['a']))
    $arguments = $_GET['a'];
  else
    $arguments = $_GET['args'];
  foreach($arguments as $argkey => $argvalue){
    if (is_numeric($argkey))
      if (is_float($argkey))
	$queryarguments[$argumentKey]['key'] = floatval($argkey) + 1;
      else
	$queryarguments[$argumentKey]['key'] = intval($argkey) + 1;
    else
      $queryarguments[$argumentKey]['key'] = pg_escape_string($argkey);
    $queryarguments[$argumentKey]['value'] = pg_escape_string($argvalue);
    $queryarguments[$argumentKey]['type'] = 0; //string

	//echo "<PRE>" . print_r($queryarguments, true) . "</PRE>";

    $argumentKey++;
    if (is_numeric($argvalue))
      if (is_float($argvalue)){
        $queryarguments[$argumentKey]['value'] = floatval($argvalue);
        $queryarguments[$argumentKey]['type'] = 1; // float
      }
      else{
        $queryarguments[$argumentKey]['value'] = intval($argvalue);
        $queryarguments[$argumentKey]['type'] = 2; // int
      }
    else{
      $queryarguments[$argumentKey]['value'] = pg_escape_string($argvalue);
      $queryarguments[$argumentKey]['type'] = 0; //string;
    }
  }
}
//if ($isdeveloper) echo "<PRE>" . print_r($queryarguments, true)  . "</PRE>";
 
// if ($isdeveloper)  echo "<B>\$buttonrow_key:</B> " . intval($buttonrow_key) . "<BR>";
// if ($isdeveloper) echo "\$queryarguments<PRE>" . print_r($queryarguments, true) . "</PRE>";

function getReferencingTables($tableName, $column){
  global $formulario, $conn, $_debug, $toggle;

  $queryReferencing .= "select R.TABLE_NAME\n";
  $queryReferencing .= "from INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE u\n";
  $queryReferencing .= "inner join INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS FK\n";
  $queryReferencing .= "    on U.CONSTRAINT_CATALOG = FK.UNIQUE_CONSTRAINT_CATALOG\n";
  $queryReferencing .= "    and U.CONSTRAINT_SCHEMA = FK.UNIQUE_CONSTRAINT_SCHEMA\n";
  $queryReferencing .= "    and U.CONSTRAINT_NAME = FK.UNIQUE_CONSTRAINT_NAME\n";
  $queryReferencing .= "inner join INFORMATION_SCHEMA.KEY_COLUMN_USAGE R\n";
  $queryReferencing .= "    ON R.CONSTRAINT_CATALOG = FK.CONSTRAINT_CATALOG\n";
  $queryReferencing .= "    AND R.CONSTRAINT_SCHEMA = FK.CONSTRAINT_SCHEMA\n";
  $queryReferencing .= "    AND R.CONSTRAINT_NAME = FK.CONSTRAINT_NAME\n";
  $queryReferencing .= "WHERE U.COLUMN_NAME = '" . $column . "'\n";
  //$queryReferencing .= "  AND U.TABLE_CATALOG = 'b'\n";
  //$queryReferencing .= "  AND U.TABLE_SCHEMA = 'c'\n";
  $queryReferencing .= "  AND U.TABLE_NAME = '" . $tableName . "'\n";

  $getReferencingResult = pg_exec ($conn, $queryReferencing);
  $getReferencing = pg_fetch_row ($getReferencingResult, 0);

  if ($_debug > 1) show_query($queryReferencing, $conn);
}

function getReferencedCaption($relations, $referencedCaption, $array_row_0){
  global $formulario, $conn, $_debug, $toggle;

  $queryDataType  = "SELECT t.typname\n";
  $queryDataType .= "  FROM pg_attribute as a,\n";
  $queryDataType .= "       pg_type as t,\n";
  $queryDataType .= "       pg_class as c\n";
  $queryDataType .= "  WHERE \n";
  $queryDataType .= "        a.attname = '" .  ($relations['Array']['referencedfield'] ? $relations['Array']['referencedfield'] : 'codigo') . "'\n";
  $queryDataType .= "     AND \n";
  $queryDataType .= "      a.attstattarget <> 0\n";
  $queryDataType .= "    AND \n";
  $queryDataType .= "      t.oid = a.atttypid\n";
  $queryDataType .= "    AND\n";
  $queryDataType .= "      c.relname = '" . $relations['Array']['referenced'] . "'\n";
  $queryDataType .= "    AND\n";
  $queryDataType .= "      a.attrelid = c.oid\n";
  $getDataTypeResult = pg_exec ($conn, $queryDataType);
  $getDataType = pg_fetch_row ($getDataTypeResult, 0);

  $charIndicator = "";
  if ($_debug > 1) show_query($queryDataType, $conn);

  switch ($getDataType[0]){
  case 'interval':
  case 'text':
  case 'citext':
  case 'varchar':
  case 'timestamp':
  case 'date':
  case 'char':
  case 'name':
    $charIndicator = "'";
    break;
  case 'int4':
  case 'int8':
    $array_row_0 = intval($array_row_0);
  default:
    $charIndicator = "";
  }

  $getCaption  = "SELECT " . ($referencedCaption ? $referencedCaption : 'nome') . " FROM \"" . $relations['Array']['referenced'] . "\"";
  $getCaption .= "  WHERE \"" . ($relations['Array']['referencedfield'] ? $relations['Array']['referencedfield'] : 'codigo') . "\" = ";
  $getCaption .= $charIndicator;
  $getCaption .=trim( $array_row_0);
  $getCaption .= $charIndicator;
  //if ($_debug > 1)
  //echo "<PRE>" . $getCaption . "</PRE>\n";
  $getCaptionResult = pg_exec ($conn, $getCaption);
  //echo "<PRE>result " . print_r($getCaptionResult, true) . "</PRE>";
  /* echo "<script>\n"; */
  /* echo "console.log(\"\$getCaption: " . addslashes(pg_escape_string($getCaption)) . "\")\n;"; */
  /* echo "</script>\n"; */ 
  if ($getCaptionResult){
	  $getCaptionRow = pg_fetch_row ($getCaptionResult, 0);
      //$getCaptionRow = pg_fetch_all ($getCaptionResult);
	  //echo "<PRE>row: " . print_r($getCaptionRow, true) . "</PRE>";
    return $getCaptionRow[0];
  }else return false;
}

/// fim do bloco das funcoes.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Verifica se este usuario logado pode acessar esse form.
$clearance = 0;
$clearanceQuery  = "select count(codigo) from grupos\n";
$clearanceQuery .= "     where codigo in (SELECT grupo from usuarios_grupos where usuario = '" . trim($_SESSION['matricula']) . "')\n";
$clearanceQuery .= "     and codigo in (SELECT grupo from forms_grupos where form = " . intval($codigo) . ")\n";
$clearanceResult = pg_exec ($conn, $clearanceQuery);
$clearanceArray = pg_fetch_all($clearanceResult);
$clearance = intval($clearanceArray[0]['count']);

if (isset($formulario['Não exigir login para este formulário'])
    && $formulario['Não exigir login para este formulário'] == 't')
  $clearance++;

if (!$clearance){
    changeTitle("Proibido!");
    echo "   <CENTER>\n";
    echo "        <IMG SRC=images/icon_vazio.jpg>\n";
    echo "        <BR>\n";
    echo "        <BR>\n";
    echo "        <B>Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar\n";
    if (isset($headerTitle))
      echo "           \"" . $headerTitle . "\".</B><BR>\n";
    else
      echo "           esta página.</B><BR>\n";
    echo "         <BR>\n";
    include("versions.inc");
    include "page_footer.inc";
    exit(1);
}

// check groups
$groups_query = "select distinct grupo from forms_grupos where form = " . $codigo;
$resultGRUPOS = pg_exec ($conn, $groups_query);
$groups_form = pg_fetch_all ($resultGRUPOS);
//echo "<PRE>\n";
//echo $groups_query . "\n";
//var_dump($groups_form);
//echo "</PRE>\n";

//if (isset($_POST['encoded_query'])){
//Campos a desobfuscar durante envio do form
if (trim($formulario['Campos a obfuscar durante envio do form'])){  
  $obfuscatedEncodedFields = explode(',', $formulario['Campos a obfuscar durante envio do form']);
  for($i=0;$i<count($obfuscatedEncodedFields);$i++)
    $obfuscatedEncodedFields[$i]=str_replace(";", ",", $obfuscatedEncodedFields[$i]);   
}
foreach ($obfuscatedEncodedFields as $key => $obfuscatedEncodedField){
  $obfuscatedEncodedFields[$key] = trim($obfuscatedEncodedField);
  $obfuscatedEncodedField = trim($obfuscatedEncodedField);
    
  echo "<script>\n";
  echo "console.log('Campo obfuscado a desobfuscar: " . fixField(trim($obfuscatedEncodedField)) . "');\n";
  echo "console.log('Campo obfuscado a desobfuscar: " . fixField($obfuscatedEncodedField) . "');\n";
  echo "console.log('Campo obfuscado a desobfuscar conteúdo: " . $_POST['encoded_onde_' . fixField($obfuscatedEncodedField)] . "');\n";
  echo "console.log('Campo obfuscado a desobfuscar conteúdo: " . tiraQuebrasDeLinha(addslashes($_POST[fixField($obfuscatedEncodedField)]),"\\n") . "');\n";
  echo "</SCRIPT>\n";
  
  $obfuscatedEncodedField = fixField($obfuscatedEncodedField);
  if (isset($_POST['encoded_onde_' . $obfuscatedEncodedField])){
    $encoded[$obfuscatedEncodedField] = trim($_POST['encoded_onde_' . $obfuscatedEncodedField]);
    $_POST[$obfuscatedEncodedField] = rawurldecode(base64_decode($encoded[$obfuscatedEncodedField]));
    $_POST[$obfuscatedEncodedField] = trim($_POST[$obfuscatedEncodedField]);
  }
}
 
if (trim($formulario['Filtro (accept) para campos do tipo bytea'])){  
  $acceptString = explode(',', $formulario['Filtro (accept) para campos do tipo bytea']);
  for($i=0;$i<count($acceptString);$i++)
    $acceptString[$i]=str_replace(";", ",", $acceptString[$i]);   
}
 
$result = $resultFORMULARIO;
if (!$result){
  Warning("Erro montando formulário!\n<PRE>" . pg_last_error(). "</PRE>");
  include "page_footer.inc";
  exit(1);
 }
if (!pg_numrows($result)){
  echo "<DIV CLASS=\"busy\">Formulário vazio.</DIV>\n";
  include "page_footer.inc";
  exit(1);
 }
//$formulario  = pg_fetch_array ($result, 0);

if (!$_debug)
  $_debug = intval($formulario['Nível de debug para este formulário']);

$termo = "";
if ($formulario['Termo para botões CRUD']){
  $termo = $formulario['Termo para botões CRUD'];
 }
if ($formulario['Termo para botões CRUD (plural)']){
  $termos = $formulario['Termo para botões CRUD (plural)'];
 }
if ($formulario['Tratar termo CRUD no feminino']){
  $feminino = $formulario['Tratar termo CRUD no feminino'];
 }

$stringNovo =  "Nov" . ($feminino == 't' ? "a" : "o") . ($termo ? " " . ucfirst($termo) : "");
$stringRemover = "Remover " . ($termos ? " " . ucfirst($termos) . "  " : " Linhas ") . "Marcad" . ($feminino == 't' ? "a" : "o") . "s";
$stringDuplicar = "Duplicar " . ($termos ? " " . ucfirst($termos) . "  " : " Linhas ") . "Marcad" . ($feminino == 't' ? "a" : "o") . "s";

$stringSalvar =  "Salvar alterações n" . ($feminino == 't' ? "as" : "os") . ($termos ? " " . ucfirst($termos) : "");


//if (!isset($_POST['buttonrow'])
//    && $formulario['Apenas form, sem tabela'] == 't')
//  $_POST['botao']=$stringNovo;

//echo "<PRE>"; var_dump($formulario); echo "</PRE>";
//echo $formulario["Esconde primeira coluna"];
?>

<script type="text/javascript">
function atualizaPlaceholder(id){
  var elemento = document.getElementById(id);
  var file = elemento.files[0].name;
  var dflt = $(elemento).attr("placeholder");
  if($(elemento).val()!=""){
    $(elemento).next().text(file + ' (clique para trocar de arquivo...)');
  } else {
    $(elemento).next().text(dflt);
  }
}
</script>

<?PHP
if (trim($formulario['Reference cláusulas de ordenação'])){
  $ReferencesOrder = explode(',', $formulario['Reference cláusulas de ordenação']);
  for($i=0;$i<count($ReferencesOrder);$i++)
    $ReferencesOrder[$i]=str_replace(";", ",", $ReferencesOrder[$i]);
 }
if (trim($formulario['Reference Captions'])){
  $ReferencedCaptions = explode(',', $formulario['Reference Captions']);
  for($i=0;$i<count($ReferencedCaptions);$i++)
    $ReferencedCaptions[$i]=str_replace(";", ",", $ReferencedCaptions[$i]);
 }
if (trim($formulario['Reference filters'])){
  $formulario['Reference filters'] = str_replace("\$onde_user", "'" . $_SESSION['matricula'] . "'", $formulario['Reference filters']);
  $ReferencedFilters = explode(',', $formulario['Reference filters']);
  for($i=0;$i<count($ReferencedFilters);$i++){
    $ReferencedFilters[$i]=str_replace(";", ",", $ReferencedFilters[$i]);
    if (isset($queryarguments)){
      foreach($queryarguments as $queryargument){
        //echo "<script>console.log('button_row: " . $queryarguments[0]['value'] . "');</script>\n";
        $ReferencedFilters[$i]=str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $ReferencedFilters[$i]);
      }
    }
    $ReferencedFilters[$i]=str_replace('$', '', $ReferencedFilters[$i]);
    //if ($isdeveloper)echo "<PRE>" . $ReferencedFilters[$i] . "</PRE><BR>";
    //if ($isdeveloper)echo "<PRE>" . $ReferencedFilters[$i] . "</PRE><BR>";
  }   
}

if (trim($formulario['Reference onChange functions'])){
  $referenceOnChangeFunctions = explode(',', $formulario['Reference onChange functions']);
  //for($i=0;$i<count($referencedOnChangeFunctions);$i++)
  //  $referenceOnChangeFunctions[$i]=str_replace(";", ",", $referenceOnChangeFunctions[$i]);
 }

if (trim($formulario['titulo'])){
  if (isset($queryarguments))
	  foreach($queryarguments as $queryargument){
      $formulario['titulo'] = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $formulario['titulo']);
     $formulario['nome'] = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $formulario['nome']);
}
  echo "<script>document.title = '" . $formulario['nome'] . "';\n</script>";
  echo "<DIV CLASS=\"titulo\">" . $formulario['titulo'] . "</DIV>\n<BR>\n";
 }

if ($_debug > 1) echo "input_vars = " .     count($_POST) . "<BR>\n";

if ($formulario['Mostra botão para exportar para CSV']=='t'){
  echo "<a href=\"exportToExcell.php?form=" . $codigo;
  if (isset($arguments) && is_array($arguments))
      //if (isset($_GET['args']))
      //foreach($_GET['args'] as $argkey => $argvalue)
    foreach($arguments as $argkey => $argvalue)
      echo "&args[" . $argkey . "]=" . $argvalue;
  
  if ($orderBy) echo "&orderby=" . trim($orderBy);
  if ($desc) echo "&desc=" . intval($desc);
  echo "\">";
  echo "<img src=\"images/export_to_excell3.gif\" border=0></a>";
}

if(trim($formulario['totalrow'])){
  $totalRowCollum = explode(",", str_replace(' ', '',$formulario['totalrow']));
  /*echo "<PRE>";
  print_r($totalRowCollum);
  echo "</PRE>";
  show_query($query, $conn, $orderBy, $desc, $formata,
           $references, $form, $boolean, $link, $destak,
            $extraGet, $hideByQuery, $showNum, $boldCondition,
      $secondOrder, $limite, $totalRowCollum);
*/
}

if ($formulario['Mostra botão de imprimir dentro do frame']=='t'){
  if (!stripos("_" . $_theme, "frameless")){
    echo "<a href=\"javascript:window.print();\">";
    echo "<img src=\"images/bot_print.gif\" border=0></a><br>\n";
  }
  else{
    echo "<a href=\"javascript:window.open('formImpressao.php?";
    if (isset($_POST['buttonrow']))
  	  foreach($_POST['buttonrow'] as $buttonrow_key => $buttonrow_val)
		  if ($buttonrow_key) echo "buttonrow[" . intval($buttonrow_key) . "]=detalhes&";
    foreach($_GET as $key => $value){
      if (is_array($value)){
	foreach($value as $innerKey => $innerValue){
          echo $key . "[" . $innerkey . "]=" . $innerValue . "&";
	}
      }
      else
        echo $key . "=" . $value . "&";
    }
    echo "','ONDE','toolbar=no,location=no,directories=no,menubar=no,status=no,scrollbars=yes,resizable=yes,width=970,height=650');";
    echo "ONDE_janela.focus();\">";
    echo "<img src=\"images/bot_print.gif\" border=0></a></p>\n";
  }
 }

if ($formulario['Mostra botão de imprimir dentro do frame']!='t') echo "<br>\n";
if (trim($formulario['tabela'])){

  if (trim($formulario['campos'])){
    $query  = "SELECT " . $formulario['campos'] . " FROM \"" . trim($formulario['tabela']) . "\"";
    $campos = explode(",", $formulario['campos']);
  }
  else {
    $query  = "SELECT * FROM \"" . trim($formulario['tabela']) . "\"";
  }
 }

if (trim($formulario['consulta'])){
  $query = $formulario['consulta'];
  if (isset($_SESSION['matricula']) && trim($_SESSION['matricula']))
    $query = str_replace("\$onde_user", "'" . $_SESSION['matricula'] . "'", $query);
  if (isset($_SESSION['referencias']))
    foreach($_SESSION['referencias'] as $variavel => $valor){
      $query = str_replace("\$" . $variavel, $valor, $query);
    }
  if (isset($queryarguments)){
	  foreach($queryarguments as $queryargument){
		  //if ($isdeveloper) echo "<PRE>" . print_r($queryargument, true) . "</PRE>";
		  
		if (intval($queryargument['key'])){
          $query = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $query);
        }
        //if ($isdeveloper) echo "Chave: " . $queryargument['key'] . " = " . $queryargument['value'] . "<BR>";
	  }
    }
  //https://HOST.DOMAIN/forms.php?form=450
  //&toggle[]=M54
  //1 &args[]=Filipi%20Damasceno%20Vianna
  //2 &args[]=814
  //3 &args[]=Mar%C3%A7o/2025
  //4 &args[]=10056942
  //5 &args[]=2025-03-14
  //6 &args[]=2025
  //7 &args[]=03
  
  //echo "<PRE>" . htmlentities($query) . "</PRE>";

  //echo "<PRE>\$_GET['strvalues']:\n"; var_dump($_GET['strvalues']); echo "</PRE>";
  //echo "<PRE>\$queryarguments:\n"; var_dump($queryarguments); echo "</PRE>";

  //if ($isdeveloper) echo "<PRE>" . $query . "</PRE>";
  
 }
//echo "<PRE>" . $query . "</PRE>";

if (trim($formulario['Enviar email para notificações']) == 't'){
  if (intval($formulario['Evento que irá disparar o e-mail'])){
    $queryGetEvent  = "SELECT trim(upper(nome)) \n";
    $queryGetEvent .= "  FROM eventosdeemail \n";
    $queryGetEvent .= "  WHERE codigo = ";
    $queryGetEvent .= intval($formulario['Evento que irá disparar o e-mail']);
    if ($_debug) show_query($queryGetEvent, $conn);
    $result = pg_exec ($conn, $queryGetEvent);
    $row = pg_fetch_row ($result, 0);
    $emailEvent = "_" . trim($row[0]);
  }
  if (intval($formulario['Template para email de notificação'])){
    $queryGetEvent  = "SELECT * \n";
    $queryGetEvent .= "  FROM emailtemplates \n";
    $queryGetEvent .= "  WHERE codigo = ";
    $queryGetEvent .= intval($formulario['Template para email de notificação']);
    if ($_debug) show_query($queryGetEvent, $conn);
    $result = pg_exec ($conn, $queryGetEvent);
    $emailTemplate = pg_fetch_array ($result, 0);

    if ($emailTemplate['Usar os dados do usuário logado como remetente'] == 't'){
      $logadoQuery = "SELECT nome, email from usuarios where login = '" . trim(pg_escape_string($_SESSION['matricula'])) . "'";
      $logadoResult = pg_exec($conn, $logadoQuery);
      $logado = pg_fetch_assoc($logadoResult, 0);
      if ($logado['email']) $emailTemplate['Endereço do remetente'] = $logado['email'];
      if ($logado['nome']) $emailTemplate['Nome do remetente'] = $logado['nome'];
      //echo "<PRE>" . print_r($emailTemplate, true) . "</PRE>";
    }
  }
}

if (trim($formulario['Campo(s) para utilizar como caption em relações N:N'])){
  $NNCaptions = explode(',', $formulario['Campo(s) para utilizar como caption em relações N:N']);
  for($i=0;$i<count($NNCaptions);$i++)
    $NNCaptions[$i]=str_replace(";", ",", $NNCaptions[$i]);
}

if (trim($formulario['Condição(ões) para utilizar como filtro em relações N:N'])){
  $NNFilters = explode(',', $formulario['Condição(ões) para utilizar como filtro em relações N:N']);
  for($i=0;$i<count($NNFilters);$i++){
    $NNFilters[$i]=str_replace(";", ",", $NNFilters[$i]);
    if (isset($queryarguments)){
      //if ($isdeveloper) echo "<PRE>query arguments: " . print_r($queryarguments, true) . "</PRE>";
      foreach($queryarguments as $queryargument){
        //echo "<script>console.log('button_row: " . $queryarguments[0]['value'] . "');</script>\n";
        $NNFilters[$i]=str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $NNFilters[$i]);
      }
    }
    $NNFilters[$i]=str_replace('$', '', $NNFilters[$i]);
  }
}

if (trim($formulario['Texto para ser utilizado como etiqueta para as tabelas N:N'])){
  $NNLabels = explode(',', $formulario['Texto para ser utilizado como etiqueta para as tabelas N:N']);
  for($i=0;$i<count($NNLabels);$i++)
    $NNLabels[$i]=str_replace(";", ",", $NNLabels[$i]);
}

if (trim($formulario['Forms aninhados'])){
  $validatedCodigosForms = ""; 
  $nestedFormsCodigos = explode(',', $formulario['Forms aninhados']);
  foreach ($nestedFormsCodigos as $nestedFormCodigo){
    if (intval($nestedFormCodigo))
      $validatedCodigosForms .= intval($nestedFormCodigo);
        /* o que pegar do formsEncodeFile
         tabela, query, reference caption, reference filter, usuario, condicao de exclusao, permite excluir
         talvez incluir na forms, os campos que podem ser exibidos e utilizados no form

         O formulario aninhado pode ser feito com um formulario aninhado, pois referencia a tablea
         forms, com o campo nome, e pode ser usato o campo limite
         deverá ser criado um form apontando para a tabela forms, que inclua codigo, nome, limite, excluir, o nome pode usar um reference caption e reference filter
         incluir relacoes NN dos forms aninhados?
         Considerar as permissoes de grupo (leitura e escrita) do form apontado.
        */
  }
}

echo "<CENTER>\n";
if (trim($formulario['formata']))
  $formata = explode(',', $formulario['formata']);

if ($formulario['descendent']=="t" && !is_numeric($desc) )
  $desc = 1;
$eq = new eqEOS();    
if ($formulario['formulario']){
  $form['name'] = $formulario['formulario'];
  $form['field'] = $campos[intval($formulario['chave'])];
  $form['action'] = $formulario['acao'];
  //alter table forms add column "Habilitar botão de duplicar linhas"  boolean not null default false;
  $form['duplicar'] = 0;
  if ($formulario['Habilitar botão de duplicar linhas'] == 't'){
    $form['duplicar'] = 1;
  }
  if ($formulario['remover'] == "t")
    $form['delete'] = 1;
  if (!$form['action']){
    if (!$formulario['argumento'] || !$formulario['funcao'])
      $form['action'] = basename($_SERVER['PHP_SELF']);
    $str = $formulario['argumento'];
    eval("\$str = \"$str\";");
    if ($formulario['funcao'])
      $form['action']  = call_user_func($formulario['funcao'], $str);
  }
  $form['action'] .= "?form=" . $codigo;
  if ($hash)
    $form['action'] .= "&h=" . $hash;
  foreach ($toggle as $value)
    $form['action'] .= "&toggle[]=" . $value;
  if (isset($arguments) && is_array($arguments))
	  foreach($arguments as $arg_chave => $value){
      //foreach ($_GET['args'] as $value){
      $form['action'] .= "&a[" . $arg_chave . "]=" . $value;
    }

  if ($orderBy) $form['action'] .= "&orderby=" . $orderBy . "&desc=" . $desc;

				   //if ($isdeveloper) echo "<PRE>" . $form['action'] . "</PRE>";

  #  echo "<h1>" . $formulario['chave'] . "</H1>";
  #  echo "<h1>" . $formulario['tabela'] . "</H1>";
  #  $query_teste  = "

  if ($_FILES){
    if ($_debug){
      echo "</CENTER>";
      echo "<B>VARDUMP(\$_FILES)</B>:<BR>\n<PRE>\n";
      var_dump($_FILES);
      echo "</PRE>\n";
    }
    //$code = round(time() / 10 * rand(1,10));
    foreach($_FILES as $field => $file){
      if (isset($file['name']) && $file['name'])
        $fileArray[$field]['name'] = $file['name'];
      if (isset($file['type']) && $file['type'])
        $fileArray[$field]['type'] = $file['type'];

      //echo "filename: " . $file['tmp_name'] . "<BR>\n";
      if (isset($file['tmp_name']) && $file['tmp_name']){
        echo "<script>console.log('upload do arquivo " . $fileArray[$field]['name'] . "');</script>\n";
        $fileArray[$field]['contents'] = file_get_contents($file['tmp_name']); //////// Lê o conteúdo da imagem principal
      }
      if (!$fileArray[$field]['contents'] && !$file['error'])
        Warning("Não foi possível carregar o arquivo para o campo " . $field . ".");

      if (isset($fileArray[$field]) && $fileArray[$field] && $fileArray[$field]['contents']){
        //echo "<B>" . $field . "</B><BR>\n";
        $fileData[$field] = formsEncodeFile($fileArray[$field]);
        $_POST[$field] = $fileData[$field];
	//echo "PASSEI!!!!";
	//unset($fileArray);
	//unset($fileData);
      }
    }    
    //echo "<CENTER>";
  }
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Quando detectar que tem relacoes N:N, deve ver se o indice tem sequence, caso tenha, deve pedir o nextval para reserva
  // e inserir manualmente no insert, para poder montar as referencias da relacao N:N
  // http://stackoverflow.com/questions/9325017/error-permission-denied-for-sequence-cities-id-seq-using-postgres
  // GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ideia;

  $queryPrepare = "set DateStyle TO 'ISO,MDY'";
  $prepareResult = pg_exec ($conn, $queryPrepare);

  //Tenta descobrir relacoes N:N
  // 1. Lista todas as tabelas para as quais a chave primaria desta tabela eh chave estrangeira
  $queryNN  = "SELECT tc.table_name, kcu.column_name, ccu.table_name\n";
  $queryNN .= "AS foreign_table_name, ccu.column_name AS foreign_column_name\n";
  $queryNN .= "  FROM information_schema.table_constraints tc\n";
  $queryNN .= "  JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name\n";
  $queryNN .= "  JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name\n";
  $queryNN .= "  WHERE constraint_type = 'FOREIGN KEY'\n";
  $queryNN .= "  AND ccu.table_name='" . $formulario['tabela'] . "'\n";

  $queryNN .= "    AND ccu.column_name = 'codigo'\n";
  $queryNN .= "      AND (SELECT COUNT(*) \n";
  $queryNN .= "         FROM information_schema.columns \n";
  $queryNN .= "  	 WHERE table_name = tc.table_name) < 4\n";

  $queryNN .= "      AND (SELECT COUNT(*) \n";
  $queryNN .= "         FROM information_schema.columns \n";
  $queryNN .= "  	 WHERE table_name = tc.table_name) > 1 \n";


  $resultNN = pg_Exec($conn, $queryNN);
  $NNtables = pg_fetch_all($resultNN);
  echo "<script>console.log(\"passei NNtables fetchall\");</script>\n";
  if ($_debug > 1) show_query($queryNN, $conn);
  //if ($isdeveloper) echo "</CENTER>query: <PRE>" . print_r($queryNN, true) . "</PRE><CENTER>";
  
  //echo "</CENTER><PRE>";var_dump($NNtables);echo "</PRE><CENTER>";

  // 2. Para cada uma destas tabelas, lista todas as chaves estrangeiras
  foreach($NNtables as $NNkey => $NNtable){
    $formulario['campo_chave'] =  $NNtable['foreign_column_name'];
    $queryNN  = "SELECT\n";
    $queryNN .= "    tc.constraint_name, tc.table_name, kcu.column_name, \n";
    $queryNN .= "    ccu.table_name AS foreign_table_name,\n";
    $queryNN .= "    ccu.column_name AS foreign_column_name \n";
    $queryNN .= "FROM \n";
    $queryNN .= "    information_schema.table_constraints AS tc \n";
    $queryNN .= "    JOIN information_schema.key_column_usage AS kcu\n";
    $queryNN .= "      ON tc.constraint_name = kcu.constraint_name\n";
    $queryNN .= "    JOIN information_schema.constraint_column_usage AS ccu\n";
    $queryNN .= "      ON ccu.constraint_name = tc.constraint_name\n";
    $queryNN .= "WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='" . $NNtable['table_name'] . "';\n";

    //if ($isdeveloper) echo "</CENTER>query: <PRE>" . print_r($queryNN, true) . "</PRE><CENTER>";
    $resultNN = pg_Exec($conn, $queryNN);
    $NNrelations = pg_fetch_all($resultNN);
    $NNtables[$NNkey]['relations'] = $NNrelations;

    if ( $NNtables[$NNkey]['relations'][1]['foreign_table_name'] == $formulario['tabela']){
      $NNbridge[0] = $NNtables[$NNkey]['relations'][1];
      $NNbridge[1] = $NNtables[$NNkey]['relations'][0];
    }
    else{
      $NNbridge[0] = $NNtables[$NNkey]['relations'][0];
      $NNbridge[1] = $NNtables[$NNkey]['relations'][1];
    }
    $NNtables[$NNkey]['relations'][0] = $NNbridge[0];
    $NNtables[$NNkey]['relations'][1] = $NNbridge[1];
    $NNrelations[0]=$NNbridge[0];
    $NNrelations[1]=$NNbridge[1];

    //echo "</CENTER><PRE>"; var_dump($NNtables); echo "</PRE><CENTER>";
    $NNtables[$NNkey]['lines'] = pg_numrows($resultNN);

    if ($_debug > 1){
      echo "<H1>" . $NNtables[$NNkey]['lines'] . "</H1>\n";
      echo "TABELA: " . $NNtable['table_name'] . "<BR>\n";
      show_query($queryNN, $conn);
    }
    $query_tamanho  = "SELECT count(a.attname)\n";
    $query_tamanho .= "  FROM pg_attribute as a, pg_type as t, pg_class as c\n";
    $query_tamanho .= "  WHERE a.attrelid = c.oid AND\n";
    $query_tamanho .= "        a.attstattarget<>0 AND \n";
    $query_tamanho .= "        t.oid=a.atttypid AND\n";
    $query_tamanho .= "        c.relname='" . $NNtable['table_name'] . "'\n";
    if ($_debug > 1) show_query($query_tamanho, $conn);
    $tamanho = pg_exec($conn, $query_tamanho);
    $tamanho_linhas = pg_fetch_row($tamanho, 0);
    //if ($isdeveloper) {
    //  echo "TAMANHO DE " . $NNtable['table_name'] . " = " . $tamanho_linhas[0] . "<BR>\n";
    //}
    if ($_debug > 1) echo "TAMANHO DE " . $NNtable['table_name'] . " = " . $tamanho_linhas[0] . "<BR>\n";
    $NNtables[$NNkey]['size'] = $tamanho_linhas[0];


    //>>>>>>>>>>>>>>>>>>>>>>>      3. A tabela que tiver apenas 2 chaves estrangeiras, eh uma relacao N:N
    // [NN_ORDER] Tentando incluir ordem para campos N:N
    // [NN_ORDER] aqui o lines pode ser >= 2
    

    if ( $isdeveloper ){
      // Não montar formularios aninhados para referencias a mesma tabela
      // um campo vai associar a tabela a um form para puxar os campos relativos aos relacionamentos
      /*
      echo "<script>console.log(\"Tabela: " . $formulario['tabela'];
      echo "\");console.log(\"Referenciada por: " . $NNtable['table_name'];
      echo "\");console.log(\"F keys: " . $NNtables[$NNkey]['lines'];
      echo "\");\nconsole.log(\"campos: ". $NNtables[$NNkey]['size'] . "\");\n</script>";
      */
    }

    //           chaves estrangeiras                 campos
   if ( ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 2 ) ||
        ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 3)
      ){
       $NNtables[$NNkey]['sortable'] = 0;
	   if ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 3){
         $NNtables[$NNkey]['sortable'] = 1;
         //echo "Tem que descobrir qual é a coluna que guarda a ordenação. ";
         //echo "E tem que descobrir se ela aceita nulos e se tem um valor default. ";
	   }
	  
      // O chosen select tem uma extencao para sortable com demonstracao em 
      // https://antom.github.io/jquery-chosen-sortable/new/
      //
      //echo "</CENTER><PRE>"; var_dump($NNtables[$NNkey]); echo "</PRE><CENTER>";
      // quando forem poucos itens, usa check box.
      // com o NN_ORDER, deve ser checkbox e sortable
      // aqui tem um checkbox list sortable https://codepen.io/dedering/pen/yaNPJd
      // olhar que o jquery tem um sortable element.

  if ($innerResult)
      $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

      // "SELECT a.attname, t.typname, a.atttypmod\n"
      // "  FROM pg_attribute as a, pg_type as t\"n
      // "  WHERE attrelid = 78214 AND\n"
      // "        attstattarget<>0 AND t.oid=a.atttypid\n";

      if ($_debug){
	//'Campo(s) para utilizar como caption em relações N:N:';
        echo "NNKey: " . $NNkey . "<BR>\n";
        echo "<B>Campo(s) para utilizar como caption em relações N:N: </B>: ";
        echo $formulario['Campo(s) para utilizar como caption em relações N:N:'] . "<BR>\n";
        echo "<PRE>\n";
        var_dump($NNCaptions);
        echo "</PRE>\n";
      }

      // Confere se campos codigo e nome existesm, se nao existirem, pega a chave e o primeiro campo depois da chave
      //if ($isdeveloper) echo "Tabela: "  . $NNrelations[1]['foreign_table_name'] . "<BR>\n";

      $queryOrdinal  = "SELECT column_name, ordinal_position\n";
      $queryOrdinal .= "FROM information_schema. columns\n";
      $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $NNrelations[1]['foreign_table_name'] . "'\n";
      $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";

	  //echo "<PRE>" . $queryOrdinal . "</pre>"; // query para descobrir a posicao ordinal da coluna chave, que é a ordem que o forms pega.

      $resOrdinal = pg_query($conn, $queryOrdinal);
      if ($resOrdinal){
	$campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
      }

      //if ($isdeveloper) echo "</CENTER><PRE>" . $queryOrdinal . "</PRE><CENTER>";
      $queryCheckBoxes  = "SELECT " . ($campo_chave?$campo_chave:"codigo") . ", ";
      if ($NNCaptions[$NNkey])
	$queryCheckBoxes .= " (" . $NNCaptions[$NNkey] . ") as nome ";
      else
	$queryCheckBoxes .= "nome";      
      $queryCheckBoxes .= ",\n";
      $queryCheckBoxes .= "  (select case when \"" . $NNrelations[1]['foreign_table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['foreign_column_name'] . " = \"" . $NNtable['table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['column_name'] . " then true else false end\n";
      $queryCheckBoxes .= "    from \"" . $NNtable['table_name'] . "\" \n";

      if (isset($_POST['buttonrow'])){
	$queryCheckBoxes .= "    where \"" . $NNtable['table_name'] . "\".\"" . $NNrelations[0]['column_name'] . "\" = ";
	//reset($_POST['buttonrow']);
	foreach($_POST['buttonrow'] as $buttonrow_key => $buttonrow_val){
	  if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	    $queryCheckBoxes  .= intval($buttonrow_key);
	  else
	    $queryCheckBoxes  .= "'" . pg_escape_string($buttonrow_key) . "'";
	}

      $queryCheckBoxes .= "  AND \"" . $NNrelations[1]['foreign_table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['foreign_column_name'] . " = \"" . $NNtable['table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['column_name'] . "\n";
		  
      }
      $queryCheckBoxes .= " ) as checked\n";

      if ($NNtables[$NNkey]['sortable']){
		  //echo "<H1>Sortable: " . $NNtables[$NNkey]['sortable'] . "</H1>\n";
		  //echo "<PRE>" . print_r($NNrelations, true) . "</PRE>";
          // Get sortabale column
		  $queryGetSortableColumn = "select * from \"" . $NNrelations[1]['table_name'] . "\" limit 1";
		  //echo $queryGetSortableColumn . "<BR>";
          $resultGetSortableColumn = pg_exec($conn, $queryGetSortableColumn);
		  if ($resultGetSortableColumn){       
			  $fetchGetSortableColumn = pg_fetch_assoc($resultGetSortableColumn, 0);
			  //echo "<PRE>" . print_r($fetchGetSortableColumn, true) . "</PRE>";
              $totalDeCampos=pg_num_fields($resultGetSortableColumn);
			   $colunas = 0;
               while ($colunas<$totalDeCampos){ /* Nomes dos campos no cabecalho.    */
                 $columnName = pg_field_name($resultGetSortableColumn, $colunas);
			     $colunas++;
				 //}			  
				 //foreach($fetchGetSortableColumn as $columnName => $columnValue){
				  if ($columnName != $NNrelations[0]['column_name'] &&
				      $columnName != $NNrelations[1]['column_name']){
					  //echo "<PRE>Coluna de ordenacao: " . $columnName . "</PRE>";
                      $NNtables[$NNkey]['sortable_collumn'] = $columnName;

					  //echo "Coluna de ordenacao: " . $NNtables[$NNkey]['sortable_collumn'] . " " . $formulario['tabela'] . "<BR>";
					  if (strpos("_" . $NNtables[$NNkey]['sortable_collumn'], $formulario['tabela'])){
						  //echo "não pode reordenar<BR>";
						  $NNtables[$NNkey]['can_save_new_sort'] = 0;
                      }
					  else{
						  $NNtables[$NNkey]['can_save_new_sort'] = 1;
						  //echo "pode reordenar<BR>";
					  }
				  }
			   }
		  }		  
		  $queryCheckBoxes .= ",\n  (select case when \"" . $NNrelations[1]['foreign_table_name'] . "\".";
		  $queryCheckBoxes .= $NNrelations[1]['foreign_column_name'] . " = \"" . $NNtable['table_name'] . "\".";
		  $queryCheckBoxes .= $NNrelations[1]['column_name'] . " then ";
          $queryCheckBoxes .= $NNrelations[1]['table_name'] . ".ordem else NULL end\n";
		  $queryCheckBoxes .= "    from \"" . $NNtable['table_name'] . "\" \n";

		  if (isset($_POST['buttonrow'])){
			  $queryCheckBoxes .= "    where \"" . $NNtable['table_name'] . "\".\"" . $NNrelations[0]['column_name'] . "\" = ";
			  //reset($_POST['buttonrow']);
			  foreach($_POST['buttonrow'] as $buttonrow_key => $buttonrow_val){
				  if (strpos("_" . $row[1], "int") && $row[1] != "interval")
					  $queryCheckBoxes  .= intval($buttonrow_key);
				  else
					  $queryCheckBoxes  .= "'" . pg_escape_string($buttonrow_key) . "'";
			  }
      $queryCheckBoxes .= "  AND \"" . $NNrelations[1]['foreign_table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['foreign_column_name'] . " = \"" . $NNtable['table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['column_name'] . "\n";
			  
		  }
		  $queryCheckBoxes .= " order by " . $NNrelations[1]['table_name'] . ".ordem ";
		  $queryCheckBoxes .= " limit 1) as ordem\n";
      }// fim do sortable
	  
      $queryCheckBoxes .= "FROM  \"" . $NNrelations[1]['foreign_table_name'] . "\"\n";

      if ($NNFilters[$NNkey]){
	$queryCheckBoxes .= " WHERE " . $NNFilters[$NNkey];
      }
      if ($NNtables[$NNkey]['sortable']) $queryCheckBoxes .= " order by ordem";

      //if ($isdeveloper) echo "</center>queryCheckBoxes:<BR><PRE>" . print_r($queryCheckBoxes, true) . "</PRE>\n " ;
      //if ($isdeveloper) echo "NNkey:<BR><PRE>" . print_r($NNkey, true) . "</PRE>\n " ;
      //if ($isdeveloper) echo "NNFilters:<BR><PRE>" . print_r($NNFilters, true) . "</PRE><CENTER>\n " ;
      //if ($isdeveloper) echo "<PRE>Sortable: " . print_r($NNtables[$NNkey]['sortable'], true) . "</pre>";

      //$queryCheckBoxes  = "SELECT " . $NNrelation['foreign_column_name'] . "\n";
      //$queryCheckBoxes .= "from " . $NNrelation['foreign_table_name'];
      //var_dump($row);

      if ($_debug) {
        echo "Query checkboxes: "; 
        echo "<PRE>" . $queryCheckBoxes . "</PRE>";
        show_query($queryCheckBoxes, $conn);
        echo "LAST ERROR: " . pg_last_error();
        echo "<BR>";
      }
      // Revisar essa consulta.
      // é realizada antes de montar o formulario
      // Talvez possa ser removida.

      //$checkBoxesResult = pg_Exec($conn, $queryCheckBoxes);
      if ($checkBoxesResult) $checkBoxes = pg_fetch_all($checkBoxesResult);

      //if ($isdeveloper) echo "<PRE>" .print_r($checkBoxes,true) . "</PRE>\n";
      //if ($isdeveloper) echo "<PRE>" . $queryCheckBoxes . "</PRE>\n";
      //echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      //echo "<B>" . ucfirst($NNrelations[1]['foreign_table_name']) . ":</B><BR>\n";
      //foreach($checkBoxes as $checkBox){
	//echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	//echo "<INPUT TYPE=\"checkbox\" NAME=\"";
	//echo $NNrelations[1]['foreign_table_name'] . "[" . $NNrelations[1]['foreign_column_name'] . "] ";
	//echo "VALUE=\"" . $checkBox['codigo'] . "\" " . ($checkBox['checked'] == 't' ? "CHECKED" : "") . ">";
	//echo $checkBox['nome'] . "<BR>\n";
      //}
      //echo "<BR>";
    }
    if ($_debug > 1) show_query($queryNN, $conn);	
  }
  //$eq1 = new eqEOS();    

  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //echo "<script>console.log('\$emailEvent: " . $emailEvent . "');</script>";
  //echo "<script>console.log('\$_POST[\'envia\']: " . $_POST['envia'] . "');</script>";
  if ( $_POST['carregar'] ){
    //echo "------------------------------------------------------------------------------------------------------------";
    //echo $formulario['tabela'];
    //echo intval($_POST['codigo']);
    $_SESSION['referencias'][$formulario['tabela']] = intval($_POST['codigo']);
  } 
  if ( $_POST['envia']==" Salvar " ||
       $_POST['envia']==" Inserir " ||
       $_POST['envia']==" Enviar " ){

    if ($formulario['Enviar email para notificações']=='t' && $emailEvent && $emailTemplate
	&&(
	   ($_POST['envia']==" Salvar "  && (strpos($emailEvent, "SALVAR"))) ||
	   ($_POST['envia']==" Inserir " && (strpos($emailEvent, "INSERIR"))) ||
	   ($_POST['envia']==" Enviar "  && (strpos($emailEvent, "ENVIAR"))))
      ){

      require("class.phpmailer.php");
      require_once('class.html2text.inc');
      $emailTemplate['Introdução'] = str_replace("\\", "", $emailTemplate['Introdução']);
      $emailTemplate['Rodapé'] = str_replace("\\", "", $emailTemplate['Rodapé']);

      ///////////////////////////////////////////////////////////////////////////////
      $innerQuery = $dataDictionary;
      $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
      if ($_debug) show_query($innerQuery, $conn);
	  //echo "<PRE>" . $innerQuery  . "</PRE>";
      $innerResult = pg_exec ($conn, $innerQuery);
      $innerTotal  = pg_numrows($innerResult);
      $linhas = 0;
      $html = "";
      $html .= $emailTemplate['Introdução'] . "<BR>\n";

      $innerQuery = $dataDictionary;
      $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
      //$innerQuery.= " AND\n    t.tablename='forms'";
      if ($_debug) show_query($innerQuery, $conn);
      $innerResult = pg_exec ($conn, $innerQuery);
	  //echo "<PRE>" . $innerQuery  . "</PRE>";
      $innerTotal  = pg_numrows($innerResult);

      $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query']);
      //if ($isdeveloper) echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";
      if ($queryIncluiLinha){
        $whereString .= $campos[intval($formulario['chave'])] . " = ";
        if (strpos("_" . $row[1], "int") && $row[1] != 'interval'){// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<< aqui
	  if (trim($_POST[fixField($row[0])])=="")
            $whereString .= "NULL";
	  else
            $whereString .= intval($_POST[fixField($row[0])]);
	}
        else
          $whereString .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
        $queryIncluiLinha = str_replace("/*where*/", $whereString, $queryIncluiLinha);
        //echo "<PRE>" . htmlentities($whereString) . "</PRE>";

        if (isset($_SESSION['referencias']))
          foreach($_SESSION['referencias'] as $variavel => $valor){
            $queryIncluiLinha = str_replace("\$" . $variavel, $valor, $queryIncluiLinha);
          }

        if (isset($queryarguments)){
			foreach($queryarguments as $queryargument){
				if (intval($queryargument['key'])){
					//if ($isdeveloper) echo "Chave: " . $queryargument['key'] . " = " . $queryargument['value'] . "<BR>";
                  $queryIncluiLinha = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $queryIncluiLinha);
                }
            }
        }

        if ($_debug) show_query($queryIncluiLinha, $conn);
		//$queryIncluiLinha = preg_replace('/(\$\d)/i','0',  $queryIncluiLinha);
        $resultIncluiLinha = pg_exec ($conn, $queryIncluiLinha);
        if (pg_numrows($resultIncluiLinha)){
          $rowIncluiLinha = pg_fetch_row ($resultIncluiLinha, 0);
          //echo "<PRE>" . htmlentities($rowIncluiLinha[0]) . "</PRE>"
          $pattern = '/<IMG.*?(>)/i';
          $replacement = '';
          $rowIncluiLinha[0] =  preg_replace($pattern, $replacement, $rowIncluiLinha[0]);
        }
      }
      //echo "<PRE>" . $queryIncluiLinha . "</PRE>";
      //echo "\$rowIncluiLinha[0]: " . print_r($rowIncluiLinha, true) . "<BR>";
      $html .= $rowIncluiLinha[0];

      while ($linhas<$innerTotal){
	$row = pg_fetch_row ($innerResult, $linhas);
        //$relations = checkRelations($linhas);
        $relations = checkRelations($row[4]);
        if ($row[0]!=trim($formulario['Campo para salvar usuário logado'])
	    && !in_array($row[0], $mailFormColumnsBlackList) )
	  if ($relations['total']){
	    // echo "\$relations: " . print_r($relations, true) . "<BR>";

	    //$row[1] = "string";
	    $relations['Array'] = pg_fetch_array ($relations['result'], 0);
	    $caption = getReferencedCaption($relations, $ReferencedCaptions[$linhas], $_POST[fixField($row[0])]);
            if (trim($caption)){
	      $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B><BR>";
	      $html .= "" . htmlspecialchars_decode($caption, ENT_QUOTES) . "<BR>\n";	      
	    }
	  }
          else // Testanto para aparecer campo chave.
	    if ( $linhas!=intval($formulario['chave']) ){	      
	      if ( (strpos("_" . $row[1], "int") && $row[1] != "interval") || strpos("_" . $row[1], "float")){
		if (strpos("_" . $row[1], "int"))
		  if (trim($_POST[fixField($row[0])])==''){

		  }
		  else{
		    $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B>&nbsp;";
		    $html .= intval($_POST[fixField($row[0])]) . "<BR>\n";
		  }
		else
		  if (strpos("_" . $row[1], "float")){
		    $html .= "<B>". $row[0] . "</B>:&nbsp;";
		    if (strpos("_" . $row[0], "$")){//if ($formata[$colunas]=='$'){
                      $html .= number_format(floatval(    str_replace(",", ".", trim($_POST[fixField($row[0])]))    ), 2, ",", ".") . "<BR>\n";
	            }
                    else{                
		      $html .= str_replace(".", ",", floatval(str_replace(",", ".", trim($_POST[fixField($row[0])]))    )) . "<BR>\n";
		    }
   		    //$html .= "<B>Teste float: " . $_POST[fixField($row[0])] . "<B><BR>";
		  }
	      }
	      else
		if ($row[1]=="bool"){
		  if ($_POST[fixField($row[0])]=="true"){
		    $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B>&nbsp;";
		    $html .= "Sim<BR>\n";
		  }
		  else{
		    $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B>&nbsp;";
		    $html .= "Não<BR>\n";
		  }
		}
		else
		  if ( trim($_POST[fixField($row[0])])==''){
		    //echo $row[0] . "<BR>";
                    //echo $campos[intval($formulario['chave'])] . "<BR>";
                    //echo $formulario['campo_chave'] . "<BR>";
                    //echo "Envia anexo: " . $formulario['Enviar os anexos ao enviar e-mails'] . "<BR>\n";
                    if ($row[1] == 'bytea' and $formulario['Enviar os anexos ao enviar e-mails'] == 't'){
                      //echo "</center>Tem material para anexar...<BR><center>";
                      // verificar se é ou não para enviar esta coluna
		      if ($_FILES[fixField($row[0])]){

			if (isset($formulario['campo_chave'])&&$formulario['campo_chave'])
			  $campo_chave = $formulario['campo_chave'];
			else{
			  $campo_chave = "codigo";
			  $queryOrdinal  = "SELECT column_name, ordinal_position\n";
			  $queryOrdinal .= "FROM information_schema. columns\n";
			  $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $formulario['tabela'] . "'\n";
			  $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";
			  $resOrdinal = pg_query($conn, $queryOrdinal);
			  if ($resOrdinal){
			    $campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
			  }
			}

			$queryFormAttach  = "SELECT encode(\"" . $row[0] . "\", 'base64') AS field  \n";
			$queryFormAttach .= "  FROM \"" . $formulario['tabela'] . "\"\n";
			$queryFormAttach .= "  WHERE \"" . $campo_chave . "\" = " . ($keyIsQuoted ? "'" : '') . intval($_POST[fixField($campo_chave)]) . ($keyIsQuoted ? "'" : '');
			/*
			echo "</CENTER><PRE>";
			echo $queryFormAttach;
			echo "</PRE><CENTER>";
			*/
			//unset($raw);
			$res = pg_query($conn, $queryFormAttach);
			if ($res){
			  //echo "CAMPO: " . $row[0] . "<BR>";
			  //echo "\$res: " . $res . "<BR>";			
			  $raw[fixField($row[0])] = pg_fetch_result($res, 'field');
			  //if ($raw[fixField($row[0])]){
			  //  echo "\$raw tem conteudo<BR>";
			  //}
			}
		      }
		      if ($_FILES){
			if ($_debug){
			  echo "</CENTER>";
			  echo "<B>VARDUMP(\$_FILES)</B>:<BR>\n<PRE>\n";
			  var_dump($_FILES);
			  echo "</PRE>\n";
			}
			//$code = round(time() / 10 * rand(1,10));
			foreach($_FILES as $field => $file){
			  //echo "\$field: " . $field . "<BR>";
			  //echo "\$row[0]: " . $row[0] . "<BR>";

			  //if ($field == fixField($row[0])){
                            if (isset($file['name']) && $file['name'])
			      $fileArray[$field]['name'] = $file['name'];
                            if (isset($file['type']) && $file['type'])
			      $fileArray[$field]['type'] = $file['type'];
			    //echo "filename: " . $file['tmp_name'] . "<BR>\n";
                            if (isset($file['tmp_name']) && $file['tmp_name'])
			      $fileArray[$field]['contents'] = file_get_contents($file['tmp_name']); //////// Lê o conteúdo da imagem principal
			    if (!$fileArray[$field]['contents'] && $res && $raw[$field]){
			      //echo $row[0];
			      //echo $field;
                              $fileArray[$field] = formsDecodeFile(base64_decode($raw[$field]));
			      unset($res);

			    }

			    if (!$fileArray[$field]['contents'] && !$file['error'])
			      Warning("Não foi possível carregar o arquivo para o campo " . $field . ".");

			    if (isset($fileArray[$field]) && $fileArray[$field] && $fileArray[$field]['contents']){
			      //echo "</center>&nbsp;&nbsp;&nbsp;&nbsp;<B>campo com anexo (dentro):" . $field . "</B><BR><center>\n";
			      //echo "</CENTER>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>type: </B>" . $fileArray[$field]['type'] . "<BR>";
			      //echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>filename: </B>" . $fileArray[$field]['name'] . "<BR><CENTER>";

			      $fileData = formsEncodeFile($fileArray[$field]);
			      $_POST[$field] = $fileData;
			      unset($fileData);
			      //unset($fileArray);
			    }
			  //if ($raw) exit();

			    //echo "</CENTER>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>\$row[0]: </B>" . $row[0] . "<BR>";
			    //echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>\$row[0]: </B>" . fixField($row[0]) . "<BR></CENTER>";
		      //if ($fileArray[fixField($row[0])]){
                      //  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>type: </B>" . $fileArray[fixFiedl($row[0])]['type'] . "<BR>";
		      //  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>filename: </B>" . $fileArray[fixFiel($row[0])]['name'] . "<BR><CENTER>";
		      //}

                      if ($fileArray[$field]['contents']){
                        //echo "</center>anexando... " . $field . "...<CENTER><BR>";
                        //$mail->addStringAttachment($fileArray['contents'], $fileArray['name']);
			$attachArray[$field]['filename'] = $fileArray[$field]['name'];
			$attachArray[$field]['contents'] = $fileArray[$field]['contents'];
			$attachArray[$field]['type'] = $fileArray[$field]['type'];
			$attachArray[$field]['fieldname'] = $row[0];
  		        //echo "arquivo: " . $attachArray[$field]['filename'] . "<BR>";;
		      }
		      //if ($attachArray) exit();
		    }
			}
		      }

		  }
		  else{
                    if ($row[1] != 'bytea'){
		      $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B><BR>";
		      $html .= "" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "<BR>\n";
		    }
                    
		  }
	    }
	    else{
	      if ($formulario['Esconde primeira coluna'] == 'f'){
		//echo "passei";
		// Envia o campo chave. Testar se o campo deve ser oculto pelas configuracoes do formulario.
		$html .= "<B>" . mb_ucfirst(($row[0]=='codigo'?"Código":$row[0]), $encoding) . ":</B><BR>";
		$html .= "" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "<BR>\n";
	      }
	    }
	$linhas++;
      }

      $html .= "<BR>\n" . $emailTemplate['Rodapé'] . "<BR>\n";

      $h2t = new \Html2Text\Html2Text($html);
      $text = $h2t->get_text();
      /////////////////////////////////////////////////////////////////////////////
      if (isset($formulario['Scripts para anexar saída no e-mail enviado'])){
        //echo "<PRE>" . print_r($formulario['Scripts para anexar saída no e-mail enviado'], true) . "</PRE>";
        // separar em array,
        $scriptsToAttach = explode(",", $formulario['Scripts para anexar saída no e-mail enviado']);
	// substituir $onde_user, (talvez não precise substituir, mas sempre enviar pois o script
	// não terá sessão iniciada)
	foreach($scriptsToAttach as $scriptIndex => $scriptToAttach){
	  // verificar se o arquivo existe
	  //$arrayScript = explode(" ", $attachItem);
	  //$scriptToAttach = trim($arrayScript[0]);
	  //echo `pwd` . $scriptToAttach . "<BR>";
	  if (file_exists($scriptToAttach)){
	    //echo "Script existe " . $scriptToAttach . " <BR>";
	    // verificar erros de sintaxe
	    $command = $path_to_php . " -l " . $scriptToAttach;
	    $syntaxCheck = `$command`;
	    if ($syntaxCheck === ("No syntax errors detected in " . trim($scriptToAttach) . "\n")){
	      //echo "<PRE>" . $syntaxCheck . "</PRE>";
	      // substituir argumentos
	      //if (isset($_GET['args'])){
	      //  foreach($_GET['args'] as $argkey => $argvalue){
	      //echo "<PRE>buttonrow: " . print_r($_POST['buttonrow'], true) . "</PRE>";
	      foreach($_POST['buttonrow'] as $key => $values)
	        $buttonRowKey = $key;
	      $scriptArg  = intval($buttonRowKey);
              $scriptArg .= " " . $_SESSION['matricula'];
              // Converte para inteiro para evitar injecao
	      /* Não incluir os argumentos que vem pelo get e post para evitar ingeção de codigo na shell
	      if (isset($arguments) && is_array($arguments)){
	        foreach($arguments as $argkey => $argvalue){  
		  $scriptArg .= " " . $argValue;
	        }
	      }
	      */
	      //echo "\$scriptArg: " . print_r($scriptArg, true);
	      $command = $path_to_php . " -l " . $scriptToAttach . " " . $scriptArg;
	      $syntaxCheck = `$command`;
	      if ($syntaxCheck === ("No syntax errors detected in " . trim($scriptToAttach) . "\n")){
	        //echo "<PRE>" . $syntaxCheck . "</PRE>";
  	        // executar com o PHP e direcionar saida para um arquivo
	        $command = $path_to_php . " " . $scriptToAttach . " " . $scriptArg;
		//echo $command . "<BR>";
		$fileFromScript = `$command`;
		//echo "<B>ARQUIVO:</B> [". substr($fileFromScript, 1, 3) . "]<BR>";
		switch(substr($fileFromScript, 1, 3)){
		case "PDF":
		  $type = "application/pdf";
		  $extension = ".pdf";
		  break;
		case "PNG":
		  $type = "image/png";
		  $extension = ".png";
		  break;
		default:
		  $type = "application/x-binary";
		  $extension = "";
		}
		//substr($fileFromScript, 1, 3)
		$filename = pathinfo($scriptToAttach, PATHINFO_FILENAME);
		//echo "<B>". $filename . "_" . $scriptArg . $extension . "</B>";
		$attachArray['script' . $scriptIndex]['filename'] = $filename . "_" . $scriptArg . $extension;
		$attachArray['script' . $scriptIndex]['contents'] = $fileFromScript;
		$attachArray['script' . $scriptIndex]['type'] = $type;
		$attachArray['script' . $scriptIndex]['fieldname'] = "script" . $scriptIndex;
	      }
	      //else erro de sintax no script com argumento
	    }
	    //else erro de sintaxe no script
	  }
	  // else script não encontrado
	}
      }
      $mail = new PHPMailer();
      $mail->From     = $emailTemplate['Endereço do remetente'] ? $emailTemplate['Endereço do remetente'] : $system_mail_from;
      $mail->FromName = $emailTemplate['Nome do remetente'] ? $emailTemplate['Nome do remetente'] : $system_mail_from_name;
      $mail->Sender   = $system_mail_from;
      $mail->HostName = $system_mail_host;
      $mail->Host     = $system_mail_host;
      $mail->Port     = $system_mail_port;
      $mail->Mailer   = $system_mail_mailer;
      if (isset($system_mail_user) && $system_mail_user && isset($system_mail_password)){
        $mail->SMTPAuth = true;
		$mail->Username  = $system_mail_user;
		$mail->Password  = $system_mail_password;
	  }
      $mail->CharSet = $encoding;

      if (trim($emailTemplate['Enviar confirmação de recebimento para']))
        $mail->ConfirmReadingTo = trim($emailTemplate['Enviar confirmação de recebimento para']);

      $mail->Subject  = stripAccents($emailTemplate['assunto'] ? $emailTemplate['assunto'] : "[IDEIA] Mensagem enviada pelo sistema interno");
      // Plain text body (for mail clients that cannot read HTML)

      $htmlTemp = stripslashes($html);
      $imageFilesToSend = getHtmlImage($htmlTemp, NULL);
      $htmlTemp = changeHtmlImage($htmlTemp, $imageFilesToSend);
      //$htmlTemp = changeHtmlImage($htmlTemp, "cid:1272542224.13304.4.camel@brainstorm" );

      if ($_debug){
	echo "<PRE>\n";
	echo htmlentities($html);
	echo "\n---------------------\n";
	echo htmlentities($htmlTemp);
	echo "</PRE>\n";
      }


      $mail->Body  = $htmlTemp;
      $mail->AltBody = $text;//"texto alternativo";$text;
      $mail->AddAddress($emailTemplate['Endereço do destinatário'] ,
                        $emailTemplate['Nome do destinatário'] ? $emailTemplate['Nome do destinatário'] : $emailTemplate['Endereço do destinatário'] );
      if ($emailTemplate['Endereço para Cc']){
        $mail->AddCC($emailTemplate['Endereço para Cc'] ,
		     $emailTemplate['Nome para Cc'] ? $emailTemplate['Nome para Cc'] : $emailTemplate['Endereço para Cc'] );
      }

      //$mail->AddEmbeddedImage('images/logo_novo.png', '1272542224.13304.1.camel@brainstorm', 'images/logo.png');

      $imagekey = 0;
      foreach ($imageFilesToSend as $imageFileToSend){
        $imagekey++;
        $mail->AddEmbeddedImage($imageFileToSend, '1272542224.13304.' . $imagekey . '.camel@brainstorm', str_replace("../session_files/", "", $imageFileToSend));
        //echo htmlentities($imageFileToSend) . "<BR>\n";
      }

      $queryEmailLog  = "INSERT INTO formsemaillog (tabela, email, form, row) VALUES (";
      $queryEmailLog .= "'" . $formulario['tabela'] . "', ";
      $queryEmailLog .= "'" . $emailTemplate['Endereço do destinatário'] . "', ";
      $queryEmailLog .= intval($codigo) . ", ";
    }

  }

  //echo "<script>console.log('passei');</script>";
  if ($_POST['envia']==" Salvar " || 
      //$_POST['envia']==" Inserir " ||
      $_POST['envia']==" Enviar " ){
    //set DateStyle TO 'SQL,DMY';

    // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // echo "</CENTER>";
    // echo "<B>VARDUMP(FILES)</B>:<BR>\n<PRE>\n";
    // var_dump($_FILES);
    // $data =  file_get_contents($_FILES['userdoc']['tmp_name']);
    // //echo "\ntmp_name: " . $_FILES['userdoc']['tmp_name'] . "\n";
    // //var_dump($data);
    // $teste_insert  = "INSERT INTO documents (filename, usuario, filehash, type, data) VALUES (";
    // $teste_insert .= "'" . $_FILES['userdoc']['name'] . "', '" . $_SESSION['matricula'] . "', '" . md5_file($_FILES['userdoc']['tmp_name']) . "', '" . $_FILES['userdoc']['type'] . "', '" . pg_escape_bytea($data) . "')";
    // $result = pg_exec ($conn, $teste_insert);
    // if (!$result) {
    //   echo "<BR>NAO DEU<BR>";
    //   echo pg_last_error();
    //   echo "<BR>";
    // }
    // echo "\n";
    // //echo $teste_insert;
    // echo "</PRE>\n";
    // echo "<CENTER>";
    // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $queryPrepare = "set DateStyle='DMY'";
    $prepareResult = pg_exec ($conn, $queryPrepare);

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    //$innerQuery.= " AND\n    t.tablename='forms'";

    if ($_debug) show_query($innerQuery, $conn);
    $innerResult = pg_exec ($conn, $innerQuery);
	//echo "<PRE>" . $innerQuery  . "</PRE>";
    $innerTotal  = pg_numrows($innerResult);
    $linhas = 0; $campos = 0;


    if ($_debug>1) {
      echo "</CENTER>\n";
      echo "<PRE>\n";
      echo "POST\n";
      var_dump($_POST);
      echo "</PRE>\n";
      echo "<CENTER>\n";
    }

    $queryUPDATE  = "UPDATE \"" . trim($formulario['tabela']) . "\" SET \n";
    if (trim($formulario['Campo para salvar usuário logado'])){
      $queryUPDATE .= "  \"" . trim($formulario['Campo para salvar usuário logado']) . "\" = ";
      $queryUPDATE .= "'" . $_SESSION['matricula'] . "'";
      $campos++;
    }
    while ($linhas<$innerTotal){

      $row = pg_fetch_row ($innerResult, $linhas);
      if ($linhas!=intval($formulario['chave'])){
	$campos++;
        if ($row[0]!=trim($formulario['Campo para salvar usuário logado'])){
  	  if ($campos>1){
	    if ($row[1] != 'bytea') $queryUPDATE .= ",\n";
  	    if ($row[1] == 'bytea' && $_POST[fixField($row[0])])  $queryUPDATE .= ",\n";
	  }	  
          if ($row[1] != 'bytea') $queryUPDATE .= "  \"" . $row[0] . "\" = ";

	  //if ($row[1] == 'bytea') echo "\$row[0] = " . $row[0] . "<BR>";

	  if ($row[1] == 'bytea' && $_POST[fixField($row[0])]){
  	    //echo "update: " . $row[0] . "<BR>";
            $queryUPDATE .= "  \"" . $row[0] . "\" = ";
	  }
	  //echo "</CENTER><PRE>" . print_r($_POST, true) . "</PRE><CENTER>";
	  ///////////////////////////////////////////////////////////////////////////// <<<< AQUI
	  if ( (strpos("_" . $row[1], "int")
		&& $row[1] != "interval") 
	       || strpos("_" . $row[1], "float") ||
		  strpos("_" . $row[1], "real") ||
		  strpos("_" . $row[1], "numeric") ||
		  strpos("_" . $row[1], "double") ||
		  strpos("_" . $row[1], "decimal")
	       ){
  	    if (strpos("_" . $row[1], "int"))
              if (trim($_POST[fixField($row[0])])=='')
                $queryUPDATE .= " NULL ";
              else{
		if (trim($_POST[fixField($row[0])])!=''){
			//echo "<PRE> calculos: " . print_r($formulario['Desabilitar cálculos em campos numéricos'], true) . "</PRE>";
			if ($formulario['Desabilitar cálculos em campos numéricos'] == 'f'){
              $valorcalculado = round($eq->solveIF(str_replace("x", "*",
    	                             str_replace(",", ".", 
												 $_POST[fixField($row[0])]))));
  	          $queryUPDATE .= intval($valorcalculado);
			}
			else $queryUPDATE .= intval($_POST[fixField($row[0])]);
		}
		else $queryUPDATE .= "NULL";
	      }
            else
              if (strpos("_" . $row[1], "float") ||
		  strpos("_" . $row[1], "real") ||
		  strpos("_" . $row[1], "numeric") ||
		  strpos("_" . $row[1], "double") ||
		  strpos("_" . $row[1], "decimal") ){
		if (trim($_POST[fixField($row[0])])!=''){
			if ($formulario['Desabilitar cálculos em campos numéricos'] == 'f'){
                  $valorcalculado = $eq->solveIF(str_replace("x", "*",
	                                    str_replace(",", ".", 
							$_POST[fixField($row[0])]
							)));
  	          $queryUPDATE .= floatval(str_replace(",", ".", $valorcalculado));
			}
			else $queryUPDATE .= floatval($_POST[fixField($row[0])]);
		}
		else $queryUPDATE .= "NULL";
	      }
	  }
  	  else
	    if ($row[1]=="bool"){
	      if ($_POST[fixField($row[0])]=="true")
	        $queryUPDATE .= "'t'";
	      else
	        $queryUPDATE .= "'f'";
	    }
	    else
	      //if ( ($row[1]=="timestamp" || $row[1]=="date") && trim($_POST[fixField($row[0])])=='')
	      if ( trim($_POST[fixField($row[0])])=='' && $row[1] != "text"){
		//echo "\$row[1]: " . $row[1] . "<BR>\n";
	        if ($row[1] != 'bytea') $queryUPDATE .= "NULL";
	      }
	      else
	        //$queryUPDATE .= "'" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "'";
		//$queryUPDATE .= "'" . $_POST[fixField($row[0])] . "'";
		//echo "<PRE>****" . $row[0] . "</PRE>";
		//echo "<PRE>****" . $_POST[fixField($row[0])] . "</PRE>";
		$queryUPDATE .= "'" . trim(pg_escape_string($_POST[fixField($row[0])])) . "'";
	  // Se usar o pg_escape_string no update e insert deve se usar o stripslashes no echo
	}
      }
      $linhas++;
    }


    $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

    $queryUPDATE .= "\nWHERE \"" . $row[0] . "\" = ";
    if (strpos("_" . $row[1], "int") && $row[1] != "interval")
      $queryUPDATE .= intval($_POST[fixField($row[0])]);
    else
      $queryUPDATE .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";

    //if ($_debug) 
    //echo "</CENTER><PRE>" . $queryUPDATE . "</PRE><CENTER>\n";

    if ($_POST['envia'] != " Inserir "){
      $result = pg_exec ($conn, $queryUPDATE);
      if (!$result){
        Warning("Erro atualizando " . ($termo ? $termo : "formulário") . "!\n<PRE>" . pg_last_error(). "</PRE>");      
        //Warning("Erro atualizando formulário!\n<PRE>" . pg_last_error(). "</PRE>");
        include "page_footer.inc";
        exit(1);
      }
      else{
      echo "</CENTER>\n";       
        echo "<DIV CLASS=\"message\">" . ($termo ? mb_ucfirst($termo, $encoding) : "Formulário") . " atualizad" . ($feminino =='t' ? "a" : "o") . " com sucesso.</DIV>\n";
      }
    }
    echo "</CENTER>\n";

    if (  (strpos($emailEvent, "SALVAR") && ($_POST['envia']==" Salvar ")) ||
	  //(strpos($emailEvent, "ENVIAR") && ($_POST['envia']==" Inserir ")) ||
	  (strpos($emailEvent, "ENVIAR") && ($_POST['envia']==" Enviar ")) ) {

      //echo "CODIGO: " .   $formulario['chave'] . "<BR>\n";
      //echo "CODIGO: " .  $campos[intval($formulario['chave'])] . "<BR>\n";
      //echo "<PRE>\n";
      //var_dump($_POST);
      //echo "</PRE>\n";
      if ($formulario['Enviar os anexos ao enviar e-mails'] == 't'){
	foreach($mailFormColumnsBlackList as $key => $unfixedField){
	  $fixedFieldsBlackList[$key] = fixField($unfixedField);
	}
      }
      foreach($attachArray as $field => $attach){
	//echo "Field: " . $field . "<BR>";
	//echo "<PRE>";var_dump($fixedFieldsBlackList); echo "</PRE>";
	//echo "<PRE>";var_dump($mailFormColumnsBlackList); echo "</PRE>";
	//if (!in_array($field, $mailFormColumnsBlackList)){
		  if (isset($fixedFieldsBlackList) && !in_array($field, $fixedFieldsBlackList)){
	  echo "      <DIV CLASS=\"message\">Arquivo \"" . $attach['filename'] . "\" anexado com sucesso.</DIV>";
	  if ($attach['filename'] && $attach['contents'])
	    $mail->AddStringAttachment($attach['contents'], $attach['filename']);
	}
      }

	  // adicionei esse if aqui, mas antes estava dentro do if destinatarios a partir de sql
	  if (isset($formulario['campo_chave'])&&$formulario['campo_chave'])
		  $campo_chave = $formulario['campo_chave'];
	  else{
		  $campo_chave = "codigo";
		  $queryOrdinal  = "SELECT column_name, ordinal_position\n";
		  $queryOrdinal .= "FROM information_schema. columns\n";
		  $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $formulario['tabela'] . "'\n";
		  $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";
		  $resOrdinal = pg_query($conn, $queryOrdinal);
		  if ($resOrdinal){
			  $campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
		  }
	  }
	  if ($emailTemplate['Cópia carbono (Cc) a partir de SQL (campos name, email)']){
		  foreach($_POST['buttonrow'] as $key => $values) $buttonRowKey = $key;
		  $emailTemplate['Cópia carbono (Cc) a partir de SQL (campos name, email)'] = str_replace("/*where*/", "\"" . trim($campo_chave) . "\" = " . $buttonRowKey . " ", $emailTemplate['Cópia carbono (Cc) a partir de SQL (campos name, email)']);

		  $copiesResult = pg_exec ($conn, $emailTemplate['Cópia carbono (Cc) a partir de SQL (campos name, email)']);
          if ($copiesResult){
			  $copies = pg_fetch_all($copiesResult);
		  }else{
			  if ($_debug){
				  echo "</CENTER>\n";
				  echo "<PRE>\n";
				  echo $emailTemplate['Cópia carbono (Cc) a partir de SQL (campos name, email)'] . "\n";
				  echo "</PRE>\n";
				  if (!$addressesResult)
					  echo "<B>" . pg_last_error() . "</B><BR>\n";
				  else{
					  echo "Resultado:<BR>\n";
					  echo "<PRE>\n";
					  print_r($copies);
					  echo "</PRE>\n";
				  }
				  echo "<CENTER>\n";
			  }			  
		  }
	  }
	  
    if ($emailTemplate['Destinatários a partir de SQL (campos name, email)']){
	//echo "<PRE>\n";
	//echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";

	    if (isset($formulario['campo_chave'])&&$formulario['campo_chave'])
              $campo_chave = $formulario['campo_chave'];
            else{
	      $campo_chave = "codigo";
	      $queryOrdinal  = "SELECT column_name, ordinal_position\n";
	      $queryOrdinal .= "FROM information_schema. columns\n";
	      $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $formulario['tabela'] . "'\n";
	      $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";
              $resOrdinal = pg_query($conn, $queryOrdinal);
	      if ($resOrdinal){
		$campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
	      }
		}

	    //echo print_r($_POST['buttonrow']);
	    foreach($_POST['buttonrow'] as $key => $values)
	      $buttonRowKey = $key;
	    $emailTemplate['Destinatários a partir de SQL (campos name, email)'] = str_replace("/*where*/", "\"" . trim($campo_chave) . "\" = " . $buttonRowKey . " ", $emailTemplate['Destinatários a partir de SQL (campos name, email)']);

	    //echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";

	    //echo "</PRE>\n";
	$addressesResult = pg_exec ($conn, $emailTemplate['Destinatários a partir de SQL (campos name, email)']);
    if ($addressesResult){
	$addresses = pg_fetch_all($addressesResult);
        if ($_debug){
	  echo "</CENTER>\n";
	  echo "<PRE>\n";
          echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";
	  echo "</PRE>\n";
	  if (!$addressesResult)
	    echo "<B>" . pg_last_error() . "</B><BR>\n";
	  else{
	    echo "Resultado:<BR>\n";
	    echo "<PRE>\n";
	    print_r($addresses);
	    echo "</PRE>\n";
	  }
	  echo "<CENTER>\n";
	}
	
	if (count($addresses)){
	  $sendError=0;

	  ///////////////////////////////////////////////////////////////////////////////////
	  foreach ($addresses as $address){
	    $mail->ClearAddresses();

	    //echo trim($address['email']) . "  " . trim($address['name']) . " ";
	    //$_debug = 1;
	    if ($_debug)
	      $mail->AddAddress($debug_mail_recipient , "ONDE debug mail recipient");
	    else
	      $mail->AddAddress(trim($address['email']) ,
				trim($address['name']) ? trim($address['name']) : trim($address['email']) );

        if (isset($copies) && is_array($copies) && count($copies)){
		  foreach($copies as $cc)
		    $mail->AddCC(trim($cc['email']), trim($cc['name'])?trim($cc['name']):trim($cc['email']));
		}

	    if(!$mail->Send()){
	      $sendError++;
	      //echo " FAIL <BR>\n";
	      $success = 'f';
	    }
	    else{
	      //echo " OK <BR>\n";
	      $success = 't';

	    }
	    $queryEmailLog  = "INSERT INTO formsemaillog (tabela, email, form, row, success) VALUES (";
	    $queryEmailLog .= "'" . $formulario['tabela'] . "', \n ";
	    $queryEmailLog .= "'" . trim($address['email']) . "',  \n ";
	    $queryEmailLog .= intval($codigo) . ", \n ";
	    $queryEmailLog .= intval($_POST[fixField($row[0])]) . ", \n ";
	    $queryEmailLog .= "'" . $success . "' \n ";	    
	    $queryEmailLog .= ")";
	    
	    if ($_debug)
	      show_query($queryEmailLog, $conn);
	    else
	      $resultEmailLog = pg_exec ($conn, $queryEmailLog);
	    
	  } /// Foreach
	  ///////////////////////////////////////////////////////////////////////////////////

	  if($sendError){
	    echo "<DIV class=\"schedulled\">Falha ao enviar mensagem para " . $sendError . " de " . count($addresses) . " destinatário" . (count($addresses)>1 ? "s" : "");
	    echo "</DIV>\n";
	  }
	  else {
	    echo "      <DIV CLASS=\"message\">Messagem enviada com sucesso para todos os destinatários";
	    echo "</DIV>\n";
	  }
	}
	}
	}
    else{
	if(!$mail->Send()){
	  echo "<DIV class=\"schedulled\">Falha ao enviar mensagem ";
	  echo ( $emailTemplate['Endereço do destinatário'] ? " para " . ($emailTemplate['Nome do destinatário'] ? $emailTemplate['Nome do destinatário'] . "&lt;" . $emailTemplate['Endereço do destinatário'] . "&gt;" : $emailTemplate['Endereço do destinatário'] ):"");
	  echo ($emailTemplate['Endereço para Cc'] ? " Cc para " . ($emailTemplate['Nome para Cc'] ? $emailTemplate['Nome para Cc'] . "&lt;" . $emailTemplate['Endereço para Cc'] . "&gt;" : $emailTemplate['Endereço para Cc'] ) : "");
	  echo "</DIV>\n";
	}
	else {
	  echo "      <DIV CLASS=\"message\">Messagem enviada com sucesso";
	  echo ( $emailTemplate['Endereço do destinatário'] ? " para " . ($emailTemplate['Nome do destinatário'] ? $emailTemplate['Nome do destinatário'] . "&lt;" . $emailTemplate['Endereço do destinatário'] . "&gt;" : $emailTemplate['Endereço do destinatário'] ):"");
	  echo ($emailTemplate['Endereço para Cc'] ? " Cc para " . ($emailTemplate['Nome para Cc'] ? $emailTemplate['Nome para Cc'] . "&lt;" . $emailTemplate['Endereço para Cc'] . "&gt;" : $emailTemplate['Endereço para Cc'] ) : "");
	  echo "</DIV>\n";
	  $queryEmailLog .= intval($_POST[fixField($row[0])]);
	  $queryEmailLog .= ")";
	  if ($_debug)
	    show_query($queryEmailLog, $conn);
	  else
	    $resultEmailLog = pg_exec ($conn, $queryEmailLog);
	}
      }
      // Clear all addresses and attachments for next loop
      $mail->ClearAddresses();
      $mail->ClearAttachments();
    }
    echo "<CENTER>\n";
  }

  //echo "<script>console.log('passei');</script>";

  if ($_POST['envia']==" Inserir "){
    $queryPrepare = "set DateStyle='DMY'";
    $prepareResult = pg_exec ($conn, $queryPrepare);

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    //$innerQuery.= " AND\n    t.tablename='forms'";

    if ($_debug)  show_query($innerQuery, $conn);
    $innerResult = pg_exec ($conn, $innerQuery);
	//echo "<PRE>" . $innerQuery  . "</PRE>";
    $innerTotal  = pg_numrows($innerResult);
    $linhas = 0; $campos = 0;

    $queryINSERT  = "INSERT INTO \"" . trim($formulario['tabela']) . "\" (\n";
    if (trim($formulario['Campo para salvar usuário logado'])){
      $queryINSERT .= "\"" . trim($formulario['Campo para salvar usuário logado']) . "\"";
      $campos++;
    }
    if ($_debug>1) {echo "</CENTER><PRE>"; var_dump($_POST); echo "</PRE><CENTER>";}
    if ($_debug) echo "input_vars = " .     count($_POST) . "<BR>\n";
    foreach($NNtables as $NNkey => $NNtable){
      //echo "<H1>" . intval($NNtable['lines']) . "</H1>\n";
      if ($NNtable['lines'] == 2 && ($NNtable['size'] == 2 || $NNtable['size'] == 3) ){
	$inserirCampoChave = true;
      }
    }
    while ($linhas<$innerTotal){
      $row = pg_fetch_row ($innerResult, $linhas);
      //echo "fixField(\$row[0]): " . fixField($row[0]) . "<BR>\n";       
      //echo "<B>\$_POST[fixField(\$row[0]): " . $_POST[fixField($row[0])] . " " . $linhas . " " . $formulario['chave'] . "</B><BR>\n";
      if ( trim($_POST[fixField($row[0])])<>'' && (($linhas!=intval($formulario['chave'])) || $inserirCampoChave) ){
	$campos++;
	if ($campos>1) $queryINSERT .= ",\n";
	if ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
	  $queryINSERT .= "\"" . $row[0] . "\"";
      }
      $linhas++;
    }
    $queryINSERT .= ") VALUES (\n";
    $linhas = 0; $campos = 0;
    if (trim($formulario['Campo para salvar usuário logado'])){
      $queryINSERT .= "'" . $_SESSION['matricula'] . "'";
      $campos++;
    }
    /*
     Para colocar valor calculado, tem que fazer com que o filtro de apenas
     numeros, permita as operacoes matematicas
     e tem que fazer a conta no insert, update ou ainda fazer no javascript
     */
    $eq = new eqEOS();    
    while ($linhas<$innerTotal){
      $row = pg_fetch_row ($innerResult, $linhas);
      if ( trim($_POST[fixField($row[0])])<> '' && ($linhas!=intval($formulario['chave']) || $inserirCampoChave) ){
	$campos++;
	if ($campos>1)
	  $queryINSERT .= ",\n";
	if ($row[0]!=trim($formulario['Campo para salvar usuário logado'])){	  
	  if (strpos("_" . $row[1], "int") && $row[1] != "interval"){
	    // O calculo deve ser feito nos inteiros e floats, tanto no insert quanto no update
	    // Deve tambem aumentar o tamanho limite do campo
	    if (trim($_POST[fixField($row[0])])!=''){
            $valorcalculado = round($eq->solveIF(str_replace("x", "*",
	                                  str_replace(",", ".", 
	                                  $_POST[fixField($row[0])]
	  				))));
	        $queryINSERT .= intval($valorcalculado);
	    }
	    else $queryINSERT .= "NULL";
	  }
	  else
	    if (
		strpos("_" . $row[1], "float") ||
		strpos("_" . $row[1], "real") ||
		strpos("_" . $row[1], "numeric") ||
		strpos("_" . $row[1], "double") ||
		strpos("_" . $row[1], "decimal")
		){
	      if (trim($_POST[fixField($row[0])])!=''){
              $valorcalculado = $eq->solveIF(str_replace("x", "*",
	                                    str_replace(",", ".", 
	                                    $_POST[fixField($row[0])]
							)));
              //echo "<B>calculado" . $valorcalculado . "</B>";
	      //$queryINSERT .= floatval(str_replace(",", ".", $_POST[fixField($row[0])]));
	      $queryINSERT .= floatval(str_replace(",", ".", $valorcalculado));
	      }
	      else $queryINSERT .= "NULL";
	    }
	    else{
	      // $row[0] eh o nome do campo (que tambem é a label que está no form)
	      // $row[1] eh o valor
	      // No caso de existirem arquivos, o que será incluido é o conteúdo deles.
	      // Verificar com a funcao
	      //    if (is_uploaded_file ($_FILES['arquivo']['tmp_name'])){

	      //$queryINSERT .= "'" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "'";
	      //$queryINSERT .= "'" . $_POST[fixField($row[0])] . "'";
	      $valueToInsert  =  "'" . trim(pg_escape_string($_POST[fixField($row[0])])) . "'";
	      if ($formulario['Impedir injeção de código HTML / javascript'] == 't' ){
		//echo "PASSEI";
                $valueToInsert = strip_tags($valueToInsert);
	      }
	      $queryINSERT .= $valueToInsert;            
	    }
	}
      }
      // foreach($NNtables as $NNkey => $NNtable){
      // 	if ($NNtable['lines'] == 2 && $NNtable['size'] == 2){
      // 	  $campos++;
      // 	  if ($campos>1) $queryINSERT .= ",\n";
      // 	}
      // }
      $linhas++;
    }

    $queryINSERT .= ")";

    //if ($_debug) echo "</CENTER><PRE>" . $queryINSERT . "</PRE><CENTER>\n";
    if ($campos){
      $queryOrdinal  = "SELECT column_name, ordinal_position\n";
      $queryOrdinal .= "FROM information_schema. columns\n";
      $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $formulario['tabela'] . "'\n";
      $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";
      $resOrdinal = pg_query($conn, $queryOrdinal);
			//echo "<PRE>campos " . print_r($queryOrdinal, true) . "</PRE>";
      if ($resOrdinal){
	      $campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
      }	  
	  // if ($isdeveloper){
	  // 	  echo "</CENTER>";
	  // 	  echo "\$_POST['demanda']:  "  . $_POST['demanda'] . "<BR>";
	  // 	  echo "<PRE>" . $queryINSERT . "</PRE>";
	  // 	  echo "<CENTER>";
	  // }
      $result = pg_exec ($conn, $queryINSERT);
      echo "</CENTER>\n";
	  //echo "<PRE>campos " . print_r($campos, true) . "</PRE>";
      //echo "campo_chave: "  . $campo_chave . "<BR>";;
      if ($campo_chave)
        $queryPegaValorChave = "SELECT max(\"" . $campo_chave . "\") from \"" . $formulario['tabela'] . "\"";
        //echo "<PRE>" . $queryPegaValorChave . "</PRE>";
        $resultChave = pg_exec($conn, $queryPegaValorChave);
        // foreach($queryarguments as $queryargument)
        //   echo "Key = "  . $queryargument['key'] . ", value = " . $queryargument['value'] . "<BR>";
        // echo "<PRE>". print_r($queryarguments, true) . "</PRE>";
		if ($resultChave){
			$queryArgumentsCount = (is_array($queryarguments)?count($queryarguments):0);
		  $data=pg_fetch_row($resultChave, 0);
          $queryarguments[$queryArgumentsCount+1]['key'] = 0;
          $queryarguments[$queryArgumentsCount+1]['value'] = $data[0];
          $queryarguments[$queryArgumentsCount+1]['type'] = 2;
          $_POST['buttonrow'][$data[0]] = "Detalhes...";
          $buttonrow_key = $data[0];
          //echo "valor chave:  " . $data[0];
		}
        //echo "<PRE>". print_r($queryarguments, true) . "</PRE>";
		
        if (!$result){
          $erroPostgresql = tiraQuebrasDeLinha(pg_last_error(), " "); 
          if (strpos("_" . $erroPostgresql, 'duplicate key value violates unique constraint')){
	        $pattern = '/(.*)?\"(.*)?\".*/i'; // pega o nome do campo
	        $replacement = '$2';
	        $campo = preg_replace($pattern, $replacement, $erroPostgresql);
	        $pattern = '/(.*?Key.*?\".*?\".*)?\((.*)?\).*/i'; // valor duplicado
	        $replacement = '$2';
	        $valor = preg_replace($pattern, $replacement, $erroPostgresql);
	        Warning("Erro inserindo " . ($termo ? $termo : "formulário") . "!\n<BR>Já existe um <B>" . $campo . "</B><BR> com <B>" . $valor . "</B> cadastrado.<BR><BR>Seus dados não foram salvos.");
	        echo "<BR>";
	      }
          else{
  	        Warning("Erro enviando " . ($termo ? $termo : "formulário") . "!\n<PRE>" . pg_last_error(). "</PRE>");
 	        include "page_footer.inc";
	        exit(1);
	      }
        }
        else{
          echo "</CENTER>\n";
          echo "<DIV CLASS=\"message\">" . ($termo ? mb_ucfirst($termo, $encoding) : "Formulário") . " enviad" . ($feminino =='t' ? "a" : "o") . " com sucesso.</DIV>\n";	
          //echo "<DIV CLASS=\"message\">PASSEI.</DIV>\n";
          $mostraForm = false;
          if ($formulario['Gerar um hash para acessar registro individual'] && $formulario['Gerar um hash para acessar registro individual'] == 't'){

          // echo "\$formulario['Gerar um hash para acessar registro individual']: " . $formulario['Gerar um hash para acessar registro individual'] . "<BR>\n";
          // echo "<PRE>" . $queryINSERT . "</PRE>\n";          
          // echo "tabela: " . $formulario['tabela'] . "<BR>\n";
          // echo "Formulario: " . $form['name'] . "<BR>\n";
          // echo "campo: " . $form['field'] . "<BR>\n";
          // echo "chave: " . $campos[intval($formulario['chave'])] . "<BR>\n";
          // $campoChave = $campos[intval($formulario['chave'])];
          
	  //////////////////////////////////////////////////////////////////
          $campos = 0;
          $queryCheckSelect  = "SELECT " . $form['field'] . " FROM \"";
          $queryCheckSelect .= trim($formulario['tabela']) . "\"\n";
          $queryCheckSelect .= "WHERE ";

	  if (trim($formulario['Campo para salvar usuário logado'])){
	    $queryCheckSelect .= "\"";
            $queryCheckSelect .= trim($formulario['Campo para salvar usuário logado']);
            $queryCheckSelect .= "\"";
	    $queryCheckSelect .= " = '" . $_SESSION['matricula'] . "'";
	    $campos++;
	  }
	  foreach($NNtables as $NNkey => $NNtable){
	    if ($NNtable['lines'] == 2 && ($NNtable['size'] == 2 || $NNtable['size'] == 3) ){
	      $inserirCampoChave = true;
	    }
	  }
          $linhas = 0;
	  while ($linhas<$innerTotal){
	    $row = pg_fetch_row ($innerResult, $linhas);
	    if ( $_POST[fixField($row[0])] &&
                ($linhas!=intval($formulario['chave']) || 
                 $inserirCampoChave) ){
	      $campos++;
	      if ($campos>1)
		$queryCheckSelect .= "\n  AND ";
	      if ($row[0]!=trim($formulario['Campo para salvar usuário logado'])){	  
                $queryCheckSelect .= "\"" . $row[0] . "\" = ";
		if (strpos("_" . $row[1], "int") && $row[1] != "interval"){
		  $queryCheckSelect .= intval($_POST[fixField($row[0])]);
		}
		else
		  //if (strpos("_" . $row[1], "float")){
		  if (
		      strpos("_" . $row[1], "float") ||
		      strpos("_" . $row[1], "real") ||
		      strpos("_" . $row[1], "numeric") ||
		      strpos("_" . $row[1], "double") ||
		      strpos("_" . $row[1], "decimal")
		      ){
		    //echo "PASSEI---------------------------------------------------------------";
		    $queryCheckSelect .= floatval(str_replace(",", ".", $_POST[fixField($row[0])]));
		  }
		  else{
		    $valueToInsert  =  "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
		    if ($formulario['Impedir injeção de código HTML / javascript'] == 't' ){
		      $valueToInsert = strip_tags($valueToInsert);
		    }
		    $queryCheckSelect .= $valueToInsert;
		  }
	      }
	    }
	    $linhas++;
	  }
	  if ($_debug) echo "</CENTER><PRE>" . $queryCheckSelect . "</PRE><CENTER>\n";
          $result = pg_exec ($conn, $queryCheckSelect);
          $row = pg_fetch_row ($result, 0);
          $button_row_index = $row[0];
          if ($_debug) echo "<H1>INDEX: " . $button_row_index . "</H1>";
          $hash = sha1($queryCheckSelect);
          $queryCheckHash  = "SELECT button_row_index FROM access_hashes";
          $queryCheckHash .= " WHERE hash = '" . $hash . "'";
          $result = pg_exec ($conn, $queryCheckHash);
          if (!$result) echo pg_last_error();
          if (pg_numrows($result)){
            $queryHash  = "UPDATE access_hashes\n";
            $queryHash .= "  SET button_row_index = " . $button_row_index . " \n";
	  }else{          
            $queryHash  = "INSERT INTO access_hashes (hash, form, button_row_index)\n";
            $queryHash .= "  VALUES (\n";
            $queryHash .= "  '" . $hash . "', \n";
            $queryHash .= "  " . $formulario['codigo'] . ", \n";
            $queryHash .= "  " . $button_row_index . " \n";
            $queryHash .= ")";
	  }
          $result = pg_exec ($conn, $queryHash);
          if (!$result) echo pg_last_error();
          if ($_debug) echo "</CENTER><PRE>" . $queryHash . "</PRE><CENTER>\n";
          if ($result){
            echo "<DIV CLASS=\"message\">Para fazer alterações, est" . ($feminino =='t' ? "a" : "e") . " " . ($termo ? mb_ucfirst($termo, $encoding) : "Formulário") . " pode ser acessad" . ($feminino =='t' ? "a" : "o") . " diretamente pelo endereço <a href=" . $URL . "?h=" . $hash . ">" . $URL . "?h=" . $hash . "</a>.</DIV>\n";	
			//echo "<DIV CLASS=\"message\">Passei....</DIV>\n";

	  }
	  //////////////////////////////////////////////////////////////////////
	}
      }
    }
    else{
      echo "</CENTER>\n";
      echo "<DIV CLASS=\"schedulled\">Nenhum campo foi preenchido. Nada foi inserido no bando de dados.</DIV>\n";
    }
    //echo "PASSEI<BR>";
    //echo "\$emailEvent: " . $emailEvent . "<BR>";
    if ($hash && strpos($emailEvent, "INSERIR")){
      $htmlTemp .= "Para fazer alterações, est" . ($feminino =='t' ? "a" : "e") . " " . ($termo ? mb_ucfirst($termo, $encoding) : "Formulário") . " pode ser acessad" . ($feminino =='t' ? "a" : "o") . " diretamente pelo endereço <a href=" . $URL . "?h=" . $hash . ">" . $URL . "?h=" . $hash . "</a>.<BR>\n";	
      $mail->Body  = $htmlTemp;
    }

    if (strpos($emailEvent, "INSERIR") && !$emailTemplate['Destinatários a partir de SQL (campos name, email)']){

      if(!$mail->Send()){
	echo "<DIV class=\"schedulled\">Falha ao enviar mensagem ";
	echo ( $emailTemplate['Endereço do destinatário'] ? " para " . ($emailTemplate['Nome do destinatário'] ? $emailTemplate['Nome do destinatário'] . "&lt;" . $emailTemplate['Endereço do destinatário'] . "&gt;" : $emailTemplate['Endereço do destinatário'] ):"");
	echo ($emailTemplate['Endereço para Cc'] ? " Cc para " . ($emailTemplate['Nome para Cc'] ? $emailTemplate['Nome para Cc'] . "&lt;" . $emailTemplate['Endereço para Cc'] . "&gt;" : $emailTemplate['Endereço para Cc'] ) : "");
	echo "</DIV>\n";
      }
      else {
	echo "      <DIV CLASS=\"message\">Messagem enviada com sucesso";
	echo ( $emailTemplate['Endereço do destinatário'] ? " para " . ($emailTemplate['Nome do destinatário'] ? $emailTemplate['Nome do destinatário'] . "&lt;" . $emailTemplate['Endereço do destinatário'] . "&gt;" : $emailTemplate['Endereço do destinatário'] ):"");
	echo ($emailTemplate['Endereço para Cc'] ? " Cc para " . ($emailTemplate['Nome para Cc'] ? $emailTemplate['Nome para Cc'] . "&lt;" . $emailTemplate['Endereço para Cc'] . "&gt;" : $emailTemplate['Endereço para Cc'] ) : "");
	echo "</DIV>\n";
	$queryEmailLog .= intval($_POST[fixField($row[0])]);
	$queryEmailLog .= ")";
	if ($_debug)
	  show_query($queryEmailLog, $conn);
	else
	  $resultEmailLog = pg_exec ($conn, $queryEmailLog);

      }
      // Clear all addresses and attachments for next loop
      if (isset($mail)){
        $mail->ClearAddresses();
        $mail->ClearAttachments();
      }
    }
    else if ($emailTemplate['Destinatários a partir de SQL (campos name, email)']){
      //echo "<PRE>\n";
      //echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";
      $emailTemplate['Destinatários a partir de SQL (campos name, email)'] = str_replace("/*where*/", "codigo = " . $_POST['codigo'] . " ", $emailTemplate['Destinatários a partir de SQL (campos name, email)']);
      //echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";
      //echo "</PRE>\n";
      $addressesResult = pg_exec ($conn, $emailTemplate['Destinatários a partir de SQL (campos name, email)']);
      $addresses = pg_fetch_all($addressesResult);

	
      if ($_debug){
	echo "</CENTER>\n";
	echo "<PRE>\n";
	echo $emailTemplate['Destinatários a partir de SQL (campos name, email)'] . "\n";
	echo "</PRE>\n";
	if (!$addressesResult)
	  echo "<B>" . pg_last_error() . "</B><BR>\n";
	else{
	  echo "Resultado:<BR>\n";
	  echo "<PRE>\n";
	  print_r($addresses);
	  echo "</PRE>\n";
	}
	echo "<CENTER>\n";
      }

      if (count($addresses) && isset($mail)){
	$sendError=0;

	///////////////////////////////////////////////////////////////////////////////////
	foreach ($addresses as $address){
	  $mail->ClearAddresses();

	  //echo trim($address['email']) . "  " . trim($address['name']) . " ";
	  //$_debug = 1;
	  if ($_debug)
	    $mail->AddAddress($debug_mail_recipient, "ONDE debug mail recipient");
	  else
	    $mail->AddAddress(trim($address['email']) ,
			      trim($address['name']) ? trim($address['name']) : trim($address['email']) );
	  if(!$mail->Send()){
	    $sendError++;
	    //echo " FAIL <BR>\n";
	    $success = 'f';
	  }
	  else{
	    //echo " OK <BR>\n";
	    $success = 't';

	  }
	  $queryEmailLog  = "INSERT INTO formsemaillog (tabela, email, form, row, success) VALUES (";
	  $queryEmailLog .= "'" . $formulario['tabela'] . "', \n ";
	  $queryEmailLog .= "'" . trim($address['email']) . "',  \n ";
	  $queryEmailLog .= intval($codigo) . ", \n ";
	  //$queryEmailLog .= intval($_POST[fixField($row[0])]) . ", \n ";
	  $queryEmailLog .= intval($buttonrow_key) . ", \n ";
	  $queryEmailLog .= "'" . $success . "' \n ";	    
	  $queryEmailLog .= ")";
	    
	  if ($_debug)
	    show_query($queryEmailLog, $conn);
	  else
	    $resultEmailLog = pg_exec ($conn, $queryEmailLog);
	    
	} /// Foreach
	  ///////////////////////////////////////////////////////////////////////////////////

	if($sendError){
	  echo "<DIV class=\"schedulled\">Falha ao enviar mensagem para " . $sendError . " de " . count($addresses) . " destinatário" . (count($addresses)>1 ? "s" : "");
	  echo "</DIV>\n";
	}
	else {
	  echo "      <DIV CLASS=\"message\">Messagem enviada com sucesso para todos os destinatários";
	  echo "</DIV>\n";
	}
      }
    }
    echo "<CENTER>\n";
  }
  //echo "<script>console.log('passei ULTIMO');</script>";
  if ($_POST['envia']==" Salvar "  ||
      $_POST['envia']==" Inserir " ||
      $_POST['envia']==" Enviar " ){
      echo "</CENTER>\n";

    /*
     // Inserir e salvar dados das relacoes N:N
     if ($_POST['envia']==" Salvar "  ||
     $_POST['envia']==" Inserir " ||
     $_POST['envia']==" Enviar " ){
     if ($formulario['Enviar email para notificações']=='t' && $emailEvent && $emailTemplate){
     require("class.phpmailer.php");
    */
      //echo "<script> console.log('ABRE for each das chaves estrangeiras');\n</script>";
      foreach($NNtables as $NNkey => $NNtable){
			if ( ($NNtable['sortable']  && $NNtable['can_save_new_sort']) || !$NNtable['sortable']){
		  echo "<script> console.log('ABRE IF indicando que tabela só tem duas chaves estrangeiras e que deve ser N:N, lines = " . $NNtable['lines']  . ", size = " . $NNtable['size'] . "');\n</script>";
	if (intval($NNtable['lines']) == 2 && intval($NNtable['size']) == 3){
		//$NNtable['sortable'] = 1;
      $NNtable['orderIndex'] = 1;
    }
	  
	  if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
	  $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

      //echo "<script>console.log('Tem que descobrir qual é a coluna que guarda a ordenação.'); \n";
      //echo "console.log('E tem que descobrir se ela aceita nulos e se tem um valor default.');</script> ";

	  echo "<script> console.log('BEGIN (salvar relacoes N:N)');\n</script>";
          //$result = pg_exec($conn, "BEGIN");
	  if ($_debug){
	    echo "<PRE>PASSEI\n\n";
	    echo "campo: " . fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "\n";
	    var_dump($_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) ] );
	  
	    echo "campo: lastState_" . fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_multiple\n";
	    var_dump($_POST[ "lastState_" . fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_multiple" ] );
	    echo "campo: lastState_" . fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_checkBoxes\n";
	    var_dump($_POST[ "lastState_" . fixField($NNtable['table_name'] . "_" . $NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_checkBoxes" ] );
	    echo "</PRE>\n";
	  }
	  echo "<script> console.log('ABRE IF DOS CHECKBOXES MARCADOS????');\n</script>";
	          $queryDelete  = "DELETE FROM \"" . $NNtable['table_name'] . "\" WHERE \"";
	          $queryDelete .= $NNtable['relations'][0]['column_name'] . "\" = '" .  intval($_POST[$row[0]]) . "'";
	          $resultDelete = pg_exec($conn, $queryDelete);
	          $erro = 0;
            
						// if ($isdeveloper){
            //   echo "<PRE>" . $queryDelete . "\n";
            //   echo "" . $formulario['tabela'] . "\n";
            //   echo "coluna de ordenacao: " . $NNtable['sortable_collumn'] . "\n";
            //   echo "relations[0]: " . print_r($NNtable['relations'][0], true) . "\n";
            //   echo "relations[1]: " . print_r($NNtable['relations'][1], true) . "</pre>";
            // }

	    if ($_debug) echo "<PRE>" . $queryDelete . "</PRE>";
	    if (!$resultDelete){
              //echo "<script> console.log('ROLLBACK');\n</script>";
	      $resultDelete = pg_exec($conn, "ROLLBACK");
	      $erro++;
	      warning("Erro deletando " . $NNtable['relations'][1]['column_name'] . " do " . $NNtable['relations'][0]['column_name'] .
  	  	      "!<BR>\nOpera&ccedil;&atilde;o desfeita!" . ($_debug ? "<PRE>" . pg_last_error() . "</PRE>" : ""));
  	      //break;
	    }

			if ( !is_null($_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_multiple" ]) ||
               !is_null($_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_checkBoxes" ]) ||
               !is_null($_POST[ "lastState_" . $NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name'] ])
	       ){
	    //echo "<script> console.log('BEGIN (salvar relacoes N:N)');\n</script>";
            $result = pg_exec($conn, "BEGIN");

			  //echo "<PRE>PASSEI -- deletando...\n\n";
	  
	    //echo "<PRE>"; var_dump($NNbridge); echo "</PRE>";
	    if ($_debug){
	      echo "<PRE>PASSEI\n\n";
	      echo "campo: " . fixField($NNtable['relations'][1]['foreign_table_name']) . "\n";
	      var_dump($_POST[ fixField($NNtable['relations'][1]['foreign_table_name']) ] );
	      echo "</PRE>\n";
	    }
	    //echo "<BR>AQUI: " . "\"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." . $NNtable['relations'][1]['foreign_column_name'] . "<BR>";
	    $getDataTypeQuery  = "select data_type from information_schema.columns\n";
            $getDataTypeQuery .= "  where table_name = '" . $NNtable['relations'][1]['foreign_table_name'] . "' and column_name = '" . $NNtable['relations'][1]['foreign_column_name'] . "';";
	    //echo "<PRE>" . $getDataTypeQuery . "</PRE>"; 
	    $getDataTypeResult = pg_exec($conn, $getDataTypeQuery);
	    if ($getDataTypeResult) $dataType = pg_fetch_row($getDataTypeResult, 0);
	    ////echo "<PRE>"; var_dump($dataType); echo "</PRE>";
	    //echo "<PRE>"; var_dump( $_POST[ fixField($NNtable['relations'][1]['foreign_table_name']) ] ); echo "</PRE>";
	    //echo "<PRE>\$NNtable['relations'][1]['foreign_table_name']: " . $NNtable['relations'][1]['foreign_table_name'] . "</PRE>";

	    //
	    // Aqui tem que diferenciar se é um select multipe ou um conjunto de checkboxes NN_INSERT
	    //
	    // Quando é checkbox, inclui apenas os que tem check
			if (isset($_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_multiple" ])){
			  //echo "<PRE>AQUI:\n" . print_r($_POST[fixField($NNtable['table_name'] . "_" .$NNtable['relations'][1]['foreign_table_name']) . "_multiple" ], true) . "</PRE>";
        $valuesToBeScanned = $_POST[ fixField($NNtable['table_name'] . "_" .	$NNtable['relations'][1]['foreign_table_name']) . "_multiple" ];
        $scanForVal = True;
        $scanForKey = False;
	    }
      if (isset($_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_checkBoxes" ])){
        $valuesToBeScanned = $_POST[ fixField($NNtable['table_name'] . "_" . $NNtable['relations'][1]['foreign_table_name']) . "_checkBoxes" ];
        $scanForVal = False;
        $scanForKey = True;
	    }

	    foreach( $valuesToBeScanned as $campo => $valores){
				// echo "<PRE>"; var_dump( $valores ); echo "</PRE>";
  	    // echo "<PRE>???\$row[0]: "; var_dump( $row[0] ); echo "</PRE>";
			  // echo "oi";

			/*
campos de relacoes NN devem indicar o nome da tabela intermediaria, para que não haja confusao.
			 */
	      //while (list($key, $val) = each($valores)) {
              foreach($valores as $key => $val){
				  
	        $queryINSERT  = "INSERT INTO \"" . $NNtable['table_name'] . "\"(\"". $NNtable['relations'][0]['column_name'];
	        $queryINSERT .= "\", \"" . $NNtable['relations'][1]['column_name'] . "\"";
            //echo "coluna de ordenacao: " . $NNtable['sortable_collumn'] . "<BR>";
            if ($NNtable['sortable']) $queryINSERT .= ", \"" . (isset($NNtable['sortable_collumn'])?$NNtable['sortable_collumn']:"ordem") . "\" ";
            $queryINSERT .= ") VALUES (";

	        if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		  $queryINSERT .= intval($_POST[fixField($row[0])]);
	        else
		  $queryINSERT .= "'" . pg_escape_string($_POST[fixField($NNtable['table_name'] . "_" . $row[0])]) . "'";

	        $queryINSERT .= ", ";
	        if (!(strpos("_" . $dataType[0], "int") && $dataType[0]  != "interval"))
	          $queryINSERT .= "'";

                if ($scanForVal)
	          $queryINSERT .= $val;
                else
	          $queryINSERT .= $key;

	        if (!(strpos("_" . $dataType[0], "int") && $dataType[0]  != "interval"))
	          $queryINSERT .= "'";
            if ($NNtable['sortable']){
				$queryINSERT .= ", " . intval($NNtable['orderIndex']);
				$NNtable['orderIndex']++;
			 }
	        $queryINSERT .= ");";

		    // if ($isdeveloper){
		    //   echo "\$row[0]: " . $row[0] . "<BR>\n";
		    //   echo "\$_POST[fixField(\$row[0])]): ";
		    //   echo $_POST[fixField($row[0])] . "<BR>\n";
		    //   echo "<PRE>" . $queryINSERT . "</PRE>";
        // }

	        $resultINSERT = pg_exec($conn, $queryINSERT);
	        if ($_debug) echo "<PRE>" . $queryINSERT . "</PRE>\n";
	        if (!$result){
                  //echo "<script> console.log('ROLLBACK');\n</script>";
		  $resultINSERT = pg_exec($conn, "ROLLBACK");

		  
		  $erro++;
		  warning("Erro atualizando " . $NNtable['relations'][1]['column_name'] . " do " . $NNtable['relations'][0]['column_name'] .
			  "!<BR>\nOpera&ccedil;&atilde;o desfeita!" . ($_debug ? "<PRE>" . pg_last_error() . "</PRE>" : "")
			  );
		  break;
	        }
		  }
	    }
	    if ($erro){
              //echo "<script> console.log('ROLLBACK');\n</script>";
	      $result = pg_exec($conn, "ROLLBACK");
	      warning("Erro atualizando " . $NNtable['relations'][1]['column_name'] . " do " . $NNtable['relations'][0]['column_name'] .
		      "!<BR>\nOpera&ccedil;&atilde;o desfeita!" . ($_debug ? "<PRE>" . pg_last_error() . "</PRE>" : ""));
	    }
	    else{
              //echo "<script> console.log('COMMIT');\n</script>";
	      $result = pg_exec($conn, "COMMIT");
	      //echo "      <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	      echo "      <DIV CLASS=\"message\">" . trim(mb_ucfirst($NNtable['relations'][1]['column_name'], $encoding));
	      echo " salvo com sucesso!</DIV>\n";
	    }
	    //echo "<script> console.log('passou do if erro que dá COMMIT ou ROLLBACK');\n</script>";
	  }
	  //echo "<script> console.log('FECHA IF DOS CHECKBOXES MARCADOS');\n</script>";
	}
	//echo "<script> console.log('FECHA IF indicando que tabela só tem duas chaves estrangeiras e que deve ser N:N');\n</script>";
			}
			else{
				if ( ($NNtable['sortable']  && isset($NNtable['can_save_new_sort']) && !$NNtable['can_save_new_sort'])){
					messageBar("schedulled", ucfirst(($NNtable['relations'][0]['foreign_table_name']==$formulario['tabela']?$NNtable['relations'][1]['foreign_table_name']:$NNtable['relations'][0])) . " não foram atualizados para não corromper a ordencação dos " . ($NNtable['relations'][0]['foreign_table_name']==$formulario['tabela']?$NNtable['relations'][0]['foreign_table_name']:$NNtable['relations'][1]) . ".");
				    //echo "passei <PRE>" . print_r($formulario['tabela'] , true) . "</PRE>";
                 }
			}
	//echo "<script> console.log('FECHA IF indicando que tabela só tem duas chaves estrangeiras e que deve ser N:N');\n</script>";
	  }
      //echo "<script> console.log('FECHA for each das chaves estrangeiras');\n</script>";
 if (isset($buttonrow_key) && !isset($queryarguments[0]['value'])){
    if (is_numeric($buttonrow_key))
      if (is_float($buttonrow_key)){		  
        $queryarguments[0]['value'] = floatval($buttonrow_key);
        $queryarguments[0]['type'] = 1; // float
      }
      else{
        $queryarguments[0]['value'] = intval($buttonrow_key);
        $queryarguments[0]['type'] = 2; // int
      }
    else{
      $queryarguments[0]['value'] = pg_escape_string($buttonrow_key);
      $queryarguments[0]['type'] = 0; //string;
	}
 }
   if (isset($formulario['Incluir script PHP depois de inserir ou salvar']) &&
     trim($formulario['Incluir script PHP depois de inserir ou salvar']))
     include(trim($formulario['Incluir script PHP depois de inserir ou salvar']));
     echo "<CENTER>\n";
  }
  //echo "</CENTER>";
// echo "<B>\$buttonrow_key:</B> " . intval($buttonrow_key) . "<BR>";
// echo "<B>\$queryarguments[0]['value']:</B>  " . $queryarguments[0]['value'] . "<BR>";


 
  if (isset($_POST['CloneCheckBox']) &&
      substr(trim($_POST['botao']), 0, 8)=="Duplicar"){
    $queryDefaultValues  = "SELECT column_name--, column_default, ordinal_position, data_type\n";
    $queryDefaultValues .= "     FROM information_schema.columns\n";
    $queryDefaultValues .= "  WHERE (table_schema, table_name) = ('public', '" . pg_escape_string($formulario['tabela']) . "')\n";
    $queryDefaultValues .= " and column_default is not null\n";
    $queryDefaultValues .= "  and data_type = 'timestamp without time zone'\n";
    $queryDefaultValues .= "  ORDER BY ordinal_position;\n";

    $indexResult = pg_exec ($conn, $queryDefaultValues);
    $columnsWithDefaultValues = pg_fetch_all ($indexResult);
	$notDuplicateThis = [];
    foreach($columnsWithDefaultValues as $currentColumn)
      $notDuplicateThis[] = $currentColumn['column_name'];

    //echo "</CENTER><pre>";
    //echo $queryDefaultValues . "\n";
    //var_dump($columnsWithDefaultValues);
    //var_dump($notDuplicateThis);
    //echo "</pre><CENTER>";

    $queryCheckIndexes  = "SELECT indexname, indexdef\n";
    $queryCheckIndexes .= " FROM pg_indexes\n";
    $queryCheckIndexes .= " WHERE tablename = '" . pg_escape_string($formulario['tabela']) . "'";
    //echo "</CENTER><PRE>" . $queryCheckIndexes . "</PRE><CENTER>";
    $indexResult = pg_exec ($conn, $queryCheckIndexes);
    $table_indexes = pg_fetch_all ($indexResult);
    foreach($table_indexes as $table_index){
      $pattern = '/.*?\((.*?)\)/i';
      $replacement = '${1}';
      $string = $table_index['indexdef'];
      $indexes[] = preg_replace($pattern, $replacement, $string);
    }

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    $innerResult = pg_exec ($conn, $innerQuery);

    $row = pg_fetch_row ($innerResult, $formulario['chave']);

    //echo "</CENTER><PRE>"; var_dump($row); echo "</PRE><CENTER>";
    //echo "</CENTER><PRE>"; var_dump($indexes); echo "</PRE><CENTER>";

    $clone = $_POST['CloneCheckBox'];
    //echo "<script> console.log('BEGIN (para opeação de duplicar linhas)');\n</script>";
    pg_Exec ($conn, "BEGIN"); // Inicia a transacao
    if ($_debug) echo "</CENTER><PRE>\n";
    //while (list($key, $val) = each($clone)) {
    foreach($clone as $key => $val){
      if ($_debug) echo $key . " = " . $clone[$key] . "\n";
      if  ($clone[$key]){
	$query_liga  = "INSERT INTO \"" . trim($formulario['tabela']) . "\" (\n      ";

	$total  = pg_numrows($innerResult);
	if ($total){
	  $linhas = 0;
	  while ($linhas<$total){
	    if ($linhas <> $formulario['chave']){
	      $clone_row = pg_fetch_row ($innerResult, $linhas);
		  //echo "<PRE>"; var_dump($notDuplicateThis); echo "</PRE>";
		  if (!in_array($clone_row[0], $notDuplicateThis)){
	        $query_liga .= "\"" . $clone_row[0] . "\"";
	        if ($linhas+1 < $total)
		  $query_liga .= ", ";
	        $query_liga .= "\n      ";
	      }
	    }
	    $linhas++;
	  }
	}
	$query_liga .= ") SELECT ";
	$total  = pg_numrows($innerResult);
	if ($total){
	  $linhas = 0;
	  while ($linhas<$total){
	    if ($linhas <> $formulario['chave']){
	      $clone_row = pg_fetch_row ($innerResult, $linhas);
              if (!in_array($clone_row[0], $notDuplicateThis)){
	        $query_liga .= "\"" . $clone_row[0] . "\"";
                //echo "<PRE>\$indexes:\n "; var_dump($indexes);echo"</PRE>";
                //echo "\$clone_row[0]: " . $clone_row[0] . "<BR>\n";
                //if (in_array($clone_row[0], $indexes) )
		if (in_array($clone_row[0], $indexes) OR in_array('"' . $clone_row[0] . '"', $indexes) )
                  $query_liga .= "||' (1)'";
                if ($linhas+1 < $total)
                  $query_liga .= ", ";
	        $query_liga .= "\n      ";
	      }
	    }
	    $linhas++;
	  }
	}
	$query_liga .= " FROM \"" . trim($formulario['tabela']) . "\" ";

	$query_liga .= "WHERE \"" . $row[0] . "\" = ";
	if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	  $query_liga .= intval($key);
	else
	  $query_liga .= "'" . pg_escape_string($key) . "'";
      }
      $query_liga .= "\n";

      //echo "</CENTER><PRE>" . $query_liga . "</PRE><CENTER>";

      if ($_debug) echo $query_liga;
      $result = pg_Exec ($conn, $query_liga);


      if (!$result){
	$mensagem_de_erro = pg_last_error();
        //echo "<script> console.log('ROLLBACK');\n</script>";
	pg_Exec ($conn, "ROLLBACK");
	echo "</CENTER>";
	// echo "<PRE>" . $query_liga . "</PRE><BR>";
    // echo $mensagem_de_erro . "<BR>";
	messageBar("busy", "Falhou duplicando " . ($termos ? $termos : "itens") . " marcad" . ($feminino =='t' ? "as" : "os") . ". Ação desfeita.");
	echo "<CENTER>";
	break;
      }
    }
    //echo "<script> console.log('COMMIT');\n</script>";
    pg_exec ($conn, "COMMIT");
    if ($_debug) echo "</PRE>";
    if (!isset($mensagem_de_erro)){
      echo "</CENTER>";
      messageBar("message", ($termos ? mb_ucfirst($termos, $encoding) : "Itens") . " duplicad" . ($feminino =='t' ? "as" : "os") . " com sucesso.");
      echo "<CENTER>";
    }
    if ($_debug) echo "<CENTER>\n";
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////
  if (isset($_POST['DeleteCheckBox']) &&
      substr(trim($_POST['botao']), 0, 7)=="Remover"){

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    $innerResult = pg_exec ($conn, $innerQuery);
    $row = pg_fetch_row ($innerResult, $formulario['chave']);

    $delete = $_POST['DeleteCheckBox'];
    //echo "<script> console.log('BEGIN (para operacao de excluir linhas');\n</script>";
    pg_Exec ($conn, "BEGIN"); // Inicia a transacao
    if ($_debug) echo "</CENTER><PRE>\n";
    //while (list($key, $val) = each($delete)) {
    foreach ($delete as $key => $val){
      if ($_debug) echo $key . " = " . $delete[$key] . "\n";
      if  ($delete[$key]){
	$query_liga  = "DELETE FROM \"" . trim($formulario['tabela']) . "\"\n";
	$query_liga .= "WHERE \"" . $row[0] . "\" = ";
	if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	  $query_liga .= intval($key);
	else
	  $query_liga .= "'" . pg_escape_string($key) . "'";
      }
      $query_liga .= "\n";
      if ($_debug) echo $query_liga;
      $result = pg_Exec ($conn, $query_liga);
      if (!$result){
	$mensagem_de_erro = pg_last_error();
        //echo "<script> console.log('ROLLBACK');\n</script>";
	pg_Exec ($conn, "ROLLBACK");
	echo "</CENTER>";
	messageBar("busy", "Falhou excluindo " . ($termos ? $termos : "itens") . " marcad" . ($feminino =='t' ? "as" : "os") . ". Ação desfeita.");
	if (strpos($mensagem_de_erro, "is still referenced from table")){
	  messageBar("schedulled", "Um" . ($feminino == 't' ? 'a' : '') . " ou mais d" . ($feminino == 't' ? 'a' : 'o') . "s " . ($termos ? $termos : "itens") . " marcad" . ($feminino == 't' ? 'a' : 'o') . "s para exclusão contém referências em outros cadastros.");
	}
	echo "<CENTER>";
	break;
      }
    }
    //echo "<script> console.log('COMMIT');\n</script>";
    pg_exec ($conn, "COMMIT");
    if ($_debug) echo "</PRE>";
    if (!isset($mensagem_de_erro)){
      echo "</CENTER>";
      messageBar("message", "Exclusão realizada com sucesso.");
      echo "<CENTER>";
    }
    if ($_debug) echo "<CENTER>\n";
  }
  //$mostraForm = true;
  //echo "mostraForm: " . print_r($mostraForm, true) . "<BR>";
  if($_POST['buttonrow']  || $_POST['botao']==$stringNovo || ($formulario['Apenas form, sem tabela'] == 't' && $mostraForm)){
    if ($_debug) echo "apenas form sem tabela : <B>" . $formulario['Apenas form, sem tabela'] . "</B>";

    //echo '<hr width="100%" style="height:1px;border:none;color:#333;background-color:#333;" />';

    if ($_POST['botao']!=$stringNovo){

      $queryPrepare = "set DateStyle TO 'SQL,DMY'";
      $prepareResult = pg_exec ($conn, $queryPrepare);

      $innerQuery = $dataDictionary;
      $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
      $innerResult = pg_exec ($conn, $innerQuery);

      if ($_debug) {
	echo "</CENTER><PRE>" . $innerQuery . "</PRE><CENTER>\n";
	show_query($innerQuery, $conn);
      }
      $row = pg_fetch_row ($innerResult, $formulario['chave']);
      //var_dump($_POST['buttonrow']);
      //reset($_POST['buttonrow']);
      //while (list($key, $val) = each($_POST['buttonrow'])){
	foreach($_POST['buttonrow'] as $key => $val){
        //echo $key;
	$innerQuery  = "SELECT * FROM \"" . trim($formulario['tabela']) . "\"\n";
	$campoChave = $campos[intval($formulario['chave'])];
	$innerQuery .= "\nWHERE \"" . $row[0] . "\" = ";
	if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	  $innerQuery .= intval($key);
	else
	  $innerQuery .= "'" . pg_escape_string($key) . "'";
      }
      //echo $innerQuery;
      if ($_debug) show_query($innerQuery, $conn);
      $innerResult = pg_exec ($conn, $innerQuery);
      $array = pg_fetch_array ($innerResult, 0);
    }
    //echo "</CENTER>";
    //echo "<PRE>innerQuery:\n" . $innerQuery . "</PRE>";
    //echo "<PRE>";var_dump($array);echo "</PRE>";
    //echo "<CENTER>";
    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    //$innerQuery.= " AND\n    t.tablename='forms'";

    if ($_debug) show_query($innerQuery, $conn);
    $innerResult = pg_exec ($conn, $innerQuery);
	// query para pegar as colunas da tabela
	//echo "<PRE>" . $innerQuery  . "</PRE>";
    $innerTotal  = pg_numrows($innerResult);
    $linhas = 0;
    echo "</CENTER>\n";

    if ($formulario['Enviar email para notificações']=='t' && $_POST['buttonrow']){
      echo "<DIV class=\"message\">";
      echo "Este formulário envia e-mails!";
      echo $closeDIV;
      $mailLog['form'] = $formulario['codigo'];
      //reset($_POST['buttonrow']);
      //while (list($key, $val) = each($_POST['buttonrow']))
      foreach($_POST['buttonrow'] as $key => $val)
        $mailLog['row'] = intval($key);

      $queryMailCheck  = "SELECT to_char(quando, 'DD')||'/'||to_char(quando, 'MM')||'/'||to_char(quando, 'YYYY') as data, quando, replace(replace(replace(replace(email, '<', ''), '>', ''), '/', ''),'\\', '') as email ";
      $queryMailCheck .= "\n,   (select count(*) from formsemaillog as fl\n";
      $queryMailCheck .= "    where fl.form = " . $mailLog['form'] . " and  fl.row = " . $mailLog['row'];
      $queryMailCheck .= " and success <> 't' and fl.quando = ";

      $queryMailCheck .= " (select max(quando) from formsemaillog as flb\n";
      $queryMailCheck .= "    where flb.form = " . $mailLog['form'] . " and  flb.row = " . $mailLog['row'] . ")) as status \n";

      $queryMailCheck .= "  ,success \n";
      $queryMailCheck .= "  FROM formsemaillog \n";
      $queryMailCheck .= "  WHERE ";
      $queryMailCheck .= "    tabela = (select tabela from forms where codigo = " . $mailLog['form'] . ")\n";
      //$queryMailCheck .= "    form = " . $mailLog['form'] . "\n";
      $queryMailCheck .= "   AND row = " . $mailLog['row'] . "\n";
      $queryMailCheck .= "  ORDER BY quando DESC";
      //$campos[intval($formulario['chave'])];
      //if ($isdeveloper)  echo "<PRE>" . $queryMailCheck . "</PRE>";

      if ($_debug) show_query($queryMailCheck, $conn);
      $result = pg_exec ($conn, $queryMailCheck);
      $total  = pg_numrows($result);
      if ($total){
	echo "<DIV class=\"message\">";

      //$queryMailShow .= "  FROM formsemaillog \n";
      //$queryMailShow .= "  WHERE form = " . $mailLog['form'] . "\n";

      //$queryMailShow .= "   AND row = " . $mailLog['row'] . "\n";
      //$queryMailShow .= "  ORDER BY quando DESC";

        echo "<span title=\"";
        //if ($total>=20){
	  $mailLog = pg_fetch_all ($result);
	  foreach($mailLog as $logEntry){
            echo $logEntry['email'] . " - " . $logEntry['data'] . " - " . ($logEntry['success']=='t'?"OK":"ERRO") . " \n";
	  }
	  //}
        echo "\">\n";
	echo $total;
	echo ($total > 1) ? " emails enviados. " : " email enviado. ";
	$last = pg_fetch_array ($result, 0);
	echo "Último e-mail enviado no dia " . $last['data'] . " ";
	echo "para o endereço " . $last['email'] . ".";
        echo "</span>\n";
	echo $closeDIV;
      }
      else{
	echo "<DIV class=\"schedulled\">";
	echo "Nenhum e-mail enviado por este formulário ainda.";
	echo $closeDIV;
      }
      //show_query($queryMailCheck, $conn);
    }
    if ($_POST['botao']==$stringNovo && trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo'])){
      //echo "PASSEI";
      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo']);
    }
    else      
      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query']);    

    if ($queryIncluiLinha){
	  //echo "<PRE>PASSEI" . htmlentities($queryIncluiLinha) . "</PRE>";
      foreach($queryarguments as $queryargument){
		  if (intval($queryargument['key'])){
            $queryIncluiLinha = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $queryIncluiLinha);
            //if ($isdeveloper) echo "Chave: " . $queryargument['key'] . " = " . $queryargument['value'] . "<BR>";

          }
        //echo "Key = "  . $queryargument['key']. ", value = " . $queryargument['value'] . "<BR>";
      }
	  //echo "<PRE>PASSEI" . htmlentities($queryIncluiLinha) . "</PRE>";
      if ($useSessions){
	    if ($_SESSION['matricula']){
          $queryIncluiLinha = str_replace("\$onde_user", $_SESSION['matricula'], $queryIncluiLinha);
	    }
      }

      //reset($_POST['buttonrow']);
      //while (list($key, $val) = each($_POST['buttonrow'])){
	  //if ($isdeveloper) echo "<B>\$_POST['buttonrow']:</B> <PRE>" . print_r($_POST['buttonrow'], true) . "</PRE>";
	  //if ($isdeveloper) echo "<B>\$whereString:</B> <PRE>" . print_r($whereString, true) . "</PRE>";

	  if (!isset($whereString) || trim($whereString) == '')
      foreach($_POST['buttonrow'] as $key => $val){
       $whereString .= $row[0] . " = ";
       if (strpos("_" . $row[1], "int") && $row[1] != "interval")
         $whereString .= intval($key);
       else
         $whereString .= "'" . pg_escape_string($key) . "'";
      }
	  //if ($isdeveloper) echo "<B>\$whereString:</B> <PRE>" . print_r($whereString, true) . "</PRE>";
	  
      if ($_POST['botao']!=$stringNovo)
        $queryIncluiLinha = str_replace("/*where*/", $whereString, $queryIncluiLinha);

      if (isset($_SESSION['referencias']))
        foreach($_SESSION['referencias'] as $variavel => $valor){
          $queryIncluiLinha = str_replace("\$" . $variavel, $valor, $queryIncluiLinha);
        }

      //echo "<PRE>" . htmlentities($whereString) . "</PRE>";
      //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";

      if ($_debug) show_query($queryIncluiLinha, $conn);
      if ($_POST['botao']!=$stringNovo || ($_POST['botao']==$stringNovo && trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo'])) ){
        //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";
		//$queryIncluiLinha = preg_replace('/(\$\d)/i','0',  $queryIncluiLinha);		  
        $resultIncluiLinha = pg_exec($conn, $queryIncluiLinha);
        // if ($isdeveloper && !$resultIncluiLinha){
        //   echo "<B>ERRO: " . pg_last_error() . "</B><BR>";
        //   echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";
        // }
        if ($resultIncluiLinha && pg_numrows($resultIncluiLinha)){
          $rowIncluiLinha = pg_fetch_row ($resultIncluiLinha, 0);
          echo $rowIncluiLinha[0];
          //echo "PASSEI\n";
        }
      }
        //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";		
    }
    $includeCode = trim($formulario['Incluir código (javascript,html)']);
    echo ($includeCode ? $includeCode : "");

    //Codigo para validacao de campos nao nulos ou obrigatorios
    $checkNullable  = "SELECT column_name, column_default, data_type, is_nullable\n";
    $checkNullable .= "  FROM  INFORMATION_SCHEMA.COLUMNS\n";
    $checkNullable .= "  WHERE table_name = '" . $formulario['tabela'] . "'\n";
    $checkNullable .= "    AND table_catalog = '" . $banco . "'\n";
    $checkNullable .= "    AND is_nullable = 'NO'\n";
    $checkNullable .= "    AND column_default IS NULL\n";

    $nullableResult = pg_exec($conn, $checkNullable);
    $nullablesArray = pg_fetch_all($nullableResult);
    foreach($nullablesArray as $nullableColumn){
      $nullableColumns[$nullableColumn['column_name']]['is_nullable'] = $nullableColumn['is_nullable'];
      $nullableColumns[$nullableColumn['column_name']]['data_type'] = $nullableColumn['data_type'];
      $nullableColumns[$nullableColumn['column_name']]['column_default'] = $nullableColumn['column_default'];
    }

   //echo "<script>\n";
   ////echo "console.log('conkey: " . ($linhas + 1) . "\\n');\n";
   //echo "console.log('Nullable: \\n" . tiraQuebrasDeLinha(addslashes($checkNullable), '\n') . "');\n";
   //echo "</script>\n";


    if ($_debug){
      echo "<PRE>" . $checkNullable . "</PRE>";
      echo "<PRE>";
      //var_dump($nullablesArray);
      var_dump($nullableColumns);

      echo "\n";
      echo "HTTP_CLIENT_IP:" . $_SERVER['HTTP_CLIENT_IP'] . "\n";
      echo "HTTP_X_FORWARDED_FOR: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . "\n";
      echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
      echo "header" . $_SERVER[$header] . "\n";
      echo "</PRE>";    
    }

    //echo "<script>console.log('action: " . $form['action'] . "');\n</script>";
?>

  <script>
  $( function() {
  } );
  </script>
                  <?PHP
		//if ($isdeveloper) echo "\$queryarguments[0]['value']: " . $queryarguments[0]['value'] . "<BR>";
		
    echo "<FORM NAME=\"" . fixField($formulario['tabela']) . "\" ACTION=\"" . $form['action'];
    if (intval($queryarguments[0]['value'])
				&& strtoupper(substr($_POST['botao'], 0, 3)) != 'NOV'
		 ) echo "&buttonrow[" . $queryarguments[0]['value'] . "]=detalhesssssss";  
    echo "\" ";

	echo " id=\"sortable\" ";
    
    echo " ENCTYPE=\"multipart/form-data\" ";
    //echo " onsubmit=\"return validateForm()\" "; 
    echo " onsubmit=\"return validateForm()\" "; 
    echo " METHOD=\"POST\">\n";

    //echo "<PRE>";
    //var_dump($queryarguments);
    //echo "</PRE>";
    

    foreach ($obfuscatedEncodedFields as $obfuscatedEncodedField){
      echo "<INPUT TYPE=\"HIDDEN\" NAME=\"encoded_onde_" . fixField($obfuscatedEncodedField) . "\" VALUE=\"\">\n";
    }
    
    /*
      ?>
      <script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
      <script type="text/javascript">
      tinyMCE.init({
      mode : "textareas",
      theme : "simple"
      });
      </script>
      <?PHP
    */

    foreach($NNtables as $NNkey => $NNtable){
      // se for novo e tiver NNlinhas = 2 (relacao N:N), deve pegar o proximo valor da sequence.
      //
      //if ($_debug) echo "<H1>\$NNtable['lines'] = " . intval($NNtable['lines']) . "</H1>\n";
      //echo "\$_POST['botao']: " . $_POST['botao'] . "<BR>\n";
      if ( $NNtable['lines'] == 2  && ($NNtable['size'] == 2 || $NNtable['size'] == 3)  && ($_POST['botao']==$stringNovo || ($formulario['Apenas form, sem tabela'] == 't' && !$_POST['buttonrow'])) && !isset($nextVal)) {        
        $queryNextVal  = "select nextval('\"" . $formulario['tabela'] . "_" . $formulario['campo_chave'] . "_seq\"'::regclass);";
        if ($_debug) echo "<PRE>" . $queryNextVal . "</PRE>\n";
        $NextValResult = pg_exec ($conn, $queryNextVal);
		$NextValRow = [];
		if ($NextValResult)
          $NextValRow = pg_fetch_row ($NextValResult, 0);
	$nextVal = $NextValRow[0];
        if ($_debug) echo "<H3>nextVal = " . $NextValRow[0] . "</H3>\n";
      }
    }
    $jahFoi = false;
    $linhas = -1;
    $ultimo = 0;
    $linhasPercorridas = -1;
    $queryPrepare = "set DateStyle TO 'ISO,MDY'";
    $prepareResult = pg_exec ($conn, $queryPrepare);
    $NNCaptions[] = 'nome';
    //if ($isdeveloper)  echo "NN tables: <BR><PRE>" . print_r($NNtables, true) . "</PRE><BR>";
    if ($formulario['Listar campos na ordem que devem ser exibidos, incluir os N:N']){
      $novaOrdem = explode(',',$formulario['Listar campos na ordem que devem ser exibidos, incluir os N:N']);
      for ($i = 0; $i<($innerTotal + (isset($NNtables)&&is_array($NNtables)?count($NNtables):0));$i++)
        $checkOrdem[$i] = $i;
	  $j = 0;
	  $novaOrdemFixed[] = "";
	  for ($i = 0; $i < (isset($novaOrdem)&&is_array($novaOrdem)?count($novaOrdem):0)+1;$i++){
		if (is_numeric($novaOrdem[$i]) && !in_array($novaOrdem[$i], $novaOrdemFixed)){
          $novaOrdemFixed[$j] = $novaOrdem[$i];
	      $j++;
	    }
  	  }
      $novaOrdem = $novaOrdemFixed;
      $novaOrdemSorted = $novaOrdem;
      sort($novaOrdemSorted, SORT_NUMERIC);
      $diffOrdem = array_diff($checkOrdem, $novaOrdemSorted);
	  $novaOrdem = array_merge($novaOrdem, $diffOrdem);
	}	
    //echo "<PRE>\n"; var_dump($NNtables);echo "</PRE>\n";
	// if ($isdeveloper){
    //   echo "\$linhas: " . $linhas . "<BR>";
  	//   echo "\$innerTotal = " . $innerTotal . "<BR>";
	//   echo "count(\$novaOrdem) = " . (isset($novaOrdem)&&is_array($novaOrdem)?count($novaOrdem):0) . "<BR>";
	//   echo "count(\$NNTables) = " . (isset($NNtables)&&is_array($NNtables)?count($NNtables):0) . "<BR>";
    //   echo "count(\$usados): " . (isset($usados)&&is_array($usados)?count($usados):0) . "<BR>";
	//   echo "count(\$checkOrdem) = " . (isset($checkOrdem)&&is_array($checkOrdem)?count($checkOrdem):0) . "<BR>";
	//   echo "\$innerTotal + count(\$NNTables) = " . $innerTotal + (isset($NNtables)&&is_array($NNtables)?count($NNtables):0) . "<BR>";
    //   echo "\$novaOrdem: <PRE>" . print_r($novaOrdem, true) . "</PRE><BR>";
    //   echo "\$novaOrdemSorted: <PRE>" . print_r($novaOrdemSorted, true) . "</PRE><BR>";
    //   echo "\$checkOrdem: <PRE>" . print_r($checkOrdem, true) . "</PRE><BR>";
    //   echo "\$diffOrdem: <PRE>" . print_r($diffOrdem, true) . "</PRE><BR>";
	// }	
	
    while ($linhasPercorridas<$innerTotal+(isset($NNtables)&&is_array($NNtables)?count($NNtables):0)-1){    
    //while ($linhasPercorridas<$innerTotal -1){    
      if ($ultimo){
        $ultimo = 0;
        $linhas = -1;
      }
      // migracao para php 8.1 [ antes era so o count($novaOrdem) ]
      if (isset($novaOrdem) && is_array($novaOrdem) && count($novaOrdem)){
        $linhas = array_shift($novaOrdem);
        if (!count($novaOrdem)) $ultimo = 1;
      }
      else{
        $linhas++;  
      }
	  //if ($isdeveloper) echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$linhas: " . $linhas . "<BR>";
      $linhasPercorridas++;
      //if ($formulario['Listar campos na ordem que devem ser exibidos, incluir os N:N'])
      //if ($isdeveloper) echo $linhas . "<BR>\n";
      //if ($isdeveloper) echo $linhasPercorridas . "<BR>\n";
      // migracao para php 8.1 [ antes era soh  if (!in_array($linhas, $usados) ){ ]
      if ( (isset($usados) && is_array($usados) && !in_array($linhas, $usados))
	   || !$usados || !isset($usados)  ){	
		  if (!intval(strpos("_" . $row[0], "<span")) && (trim($dicas_formulario[intval($linhas)]))){
        //echo "<PRE>";
        //echo intval(strpos("_" . $row[0], "<span"));
        //echo trim($dicas_formulario[$linhas]);
        //echo "</PRE>";
        $dicaPrefix = "<span title=\"" . $dicas_formulario[$linhas] . "\">";
	$dicaSufix  = "</span>";
      }
      else{
	$dicaPrefix = "";
        $dicaSufix  = "";
      }

      //echo "<script>\n";
      //echo "console.log('tabela " . $formulario['tabela'] . "');\n";
      //echo "console.log('Relacoes: " . $relations['total'] . "');\n";
      //echo "console.log('---------" . $row[0] . "------------\\n');\n";
      //echo "console.log('---------" . $row[4] . "------------\\n');\n";
      //echo "console.log('Relacoes: \\n" . tiraQuebrasDeLinha(addslashes($innerQuery), '\n') . "');\n";
      //echo "</script>\n";

      //echo $linhas . "<BR>";
      ///$relations = checkRelations($row[4]);
	  $row = pg_fetch_row ($innerResult, intval($linhas));
      $relations = checkRelations($row[4]);
      //$row = pg_fetch_row ($innerResult, $row[4]);
      //echo $_theme;
	  if (trim(fixField($row[0]))){
      echo "<DIV id=\"onde_div_" . fixID($row[0]) . "\"";
      echo " class=\"ui-state-default\" style=\"color: " . (strpos($_theme, "Tron")?"lightblue":"black") . "; background: unset; border: unset;\"";
      echo ">";
      echo "<span id=\"onde_span_" . fixID($row[0]) . "\"";
      echo "style=\"display: inline-block\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
      echo "<span id=\"onde_span_index_" . fixID($row[0]) . "\"";
      echo "style=\"display: none\"> (" . $linhas . ") </span>";
	  //}	  
      // Caso o campo seja para selecao de cor, inclui o javascript necessario.
      // o jahFoi impede que inclua multiplos javascripts para o caso de mais
      // de um campo de selecao de cor.
      if (stripos("_" . $row[0], 'rgbcolorof_') && !$jahFoi && intval($row[2])==10){
        $jahFoi = true;
        echo "    <script type=\"text/javascript\" src=\"dependencies/jscolor/jscolor.js\"></script>\n";
      }

      if ($linhas == intval($formulario['chave'])){
        $valorChave = $array[$row[0]];
      }

      if ($linhas == intval($formulario['chave']) && ($_POST['botao']==$stringNovo || ($formulario['Apenas form, sem tabela'] == 't' && !$_POST['buttonrow'])) && isset($nextVal) ){
        echo "<INPUT TYPE=\"HIDDEN\" NAME=\"" . fixField($row[0]) . "\" ";
        echo "id=\"onde_" . fixField($row[0]) . "\" VALUE = \"" . $nextVal . "\">";
		//$_POST['buttonrow'][$nextVal] = "detalhes";
      }
      if ($linhas != intval($formulario['chave']) || $_POST['botao']!=$stringNovo){

        if ( !(($formulario['Esconde primeira coluna'] == "t") && ($linhas == intval($formulario['chave'])) ) &&
	     ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
	     &&  ($row[3] != "t" || $row[1]!='timestamp')
	     //&&  ($row[3] != "t" || $row[1]!='timestamp' || $row[1]!='date')
	     ){
          if (stripos("_" . $row[0], 'rgbcolorof_') && intval($row[2])==10)
	    echo "    <B>" . $dicaPrefix . mb_ucfirst(str_replace("rgbcolorof_", "", $row[0]), $encoding) . ":" . $dicaSufix . "</B>";
          else
	    echo "    <B>" . $dicaPrefix . mb_ucfirst(($row[0]=="codigo"?"Código":$row[0]), $encoding) . ":" . $dicaSufix . "</B>";
	}
	else{
          if ($formulario['Esconde primeira coluna'] != "t"){
	    echo "    <B>" . $dicaPrefix . mb_ucfirst(($row[0]=="usuario"?"Usuário":$row[0]), $encoding) . ":" . $dicaSufix . "</B>";
	  }
	}
        if ($nullableColumns[$row[0]]['is_nullable'] == 'NO' && $row[0] != $formulario['Campo para salvar usuário logado']) echo "<FONT COLOR =\"#FF0000\"><B>(*)</B></FONT>";
        if ($_debug) echo "NULLABLE: " . $nullableColumns[$row[0]]['is_nullable'] . "<BR>\n";
        if ($relations['total']) $row[1] = "references";
	if ($linhas != intval($formulario['chave'])
            &&  ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
            &&  ($row[3] != "t" || $row[1]!='timestamp')
		//&& (trim(fixField($row[0])))
            //&&  ($row[3] != "t" || $row[1]!='timestamp' || $row[1]!='date')
	    ){
	  switch ($row[1]) {
          case 'references':            
            $relations['Array'] = pg_fetch_array ($relations['result'], 0);
            //if ($isdeveloper) echo "<PRE>" . print_r($relations['Array'], true) . "</PRE>";
            //if ($isdeveloper) echo "<PRE>" . print_r($_SESSION['referencias'][$relations['Array']['referenced']], true) . "</PRE>";

	    if (isset($_SESSION['referencias'][$relations['Array']['referenced']])){
              echo "<INPUT TYPE=\"HIDDEN\" NAME=\"" . fixField($row[0]) . "\" ";
              echo "id=\"onde_" . fixField($row[0]) . "\" VALUE = \"" . $_SESSION['referencias'][$relations['Array']['referenced']] . "\">";
              //echo "<PRE>" . print_r($_SESSION['referencias'], true) . "</PRE>";
              //echo "<PRE>" . print_r($relations, true) . "</PRE>";
	    }
            else{
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";

	    $caption = getReferencedCaption($relations, $ReferencedCaptions[$linhas], $array[$row[0]]);
            //echo "<B>CAPTION" . $caption . "</B><BR>";

            /* echo "<script>\n"; */
            /* echo "console.log(\"\$caption: " . $caption . "\")\n;"; */
            /* echo "console.log(\"    \$linhas: " . $linhas . "\")\n;"; */
            /* echo "console.log(\"    \$referenceOnChangeFunctions[\$linhas]: " . $referenceOnChangeFunctions[$linhas] . "\")\n;"; */
            /* echo "</script>\n"; */
		$nullable = 1;
  		if ($nullableColumns[$row[0]]['is_nullable'] == 'NO') $nullable = 0;
	    dbcombo($relations['Array']['referenced'],
                ($relations['Array']['referencedfield'] ? $relations['Array']['referencedfield'] : 'codigo'),
		        ($ReferencedCaptions[$linhas] ? $ReferencedCaptions[$linhas] : 'nome'),
                $conn, fixField($row[0]), 30,
                //(trim($caption) == "" ? "selecione uma opção" : $caption),
                $caption,
		        ($referenceOnChangeFunctions[$linhas] ? $referenceOnChangeFunctions[$linhas] : 0),
		        0, $ReferencedFilters[$linhas], NULL, NULL,
				$ReferencesOrder[$linhas],
                $nullable
				);
	    echo "<BR><BR>\n";
	    //if ($isdeveloper) echo "ARRAY!!!<PRE>" . print_r($array, true) . "</PRE>";
	    //if ($isdeveloper) echo "ARRAY!!!<PRE>" . print_r($row[0], true) . "</PRE>";

	    if ($referenceOnChangeFunctions[$linhas]){
              echo "<script type=\"text/javascript\">\n";
	      //echo "console.log('aqui!!!!!');\n";
              //echo "console.log('    \$(\"select#" . fixField($row[0]) . "\").attr(\"value\", \'" . $array[$row[0]] . "\');');\n";
              //echo "console.log('    \$(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr(\'checked\', \'checked\');');\n";

              echo "    $(\"select#" . fixField($row[0]) . "\").attr(\"value\", '" . $array[$row[0]] . "');\n";      
              echo "    $(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr('checked', 'checked');\n";
	      
              $onChangeFunction =  preg_replace('/(.*)?\((.*?\).*)/i', '${1}', $referenceOnChangeFunctions[$linhas]);
              //$firingChangingFunctions[] = "  " . $onChangeFunction . "(" . $array[$row[0]] . ");\n";
              $firingChangingFunctions[] = "  " . $onChangeFunction . "(" . (is_numeric($array[$row[0]]) ? "" : "'") . $array[$row[0]] . (is_numeric($array[$row[0]]) ? "" : "'") . ");\n";
              echo "</script>\n";
	    }
	    }
	    break;
	  case 'float8':
	  case 'float4':
	  case 'decimal':
	  case 'double':
	  case 'real':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo "id=\"onde_" . fixField($row[0]) . "\" ";

	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 25;
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "200px" : (intval($row[2]/1.1)*8 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" ";

            echo "SIZE=\"9\" MAXLENGTH=\"10\" ";
	    //echo " onKeypress=\"console.log(event.keyCode);\"";
	    
	    echo " onKeypress=\"if( (event.keyCode < 48 || event.keyCode > 57) ";
          // not fora de um parenteses com varias igualdades em lógica OU é como um not in list
          echo " && !(";
          echo "  event.keyCode == 44 "; // virgula
		  if ($formulario['Desabilitar cálculos em campos numéricos'] == 'f'){
            echo " || event.keyCode == 43 || "; // +
            echo "  event.keyCode == 45 || "; // -
            echo "  event.keyCode == 120 || "; // x
            echo "  event.keyCode == 42 || "; // *
            echo "  event.keyCode == 47 || "; // /
            echo "  event.keyCode == 40 || "; // (
            echo "  event.keyCode == 41 || "; // )
            echo "  event.keyCode == 91 || "; // [
            echo "  event.keyCode == 93 || "; // ]
            echo "  event.keyCode == 123 || "; // {
            echo "  event.keyCode == 125 "; // }
		  }
          echo ")";
		  echo ") event.returnValue = false;\"";
	    //echo " onKeypress=\"if( event.keyCode != 44 ) event.returnValue = false;\"";
	    //echo " onKeypress=\"alert(event.keyCode);\"";

	    echo " VALUE = \"" . (( trim($array[$row[0]])=="" )?"":str_replace(".", ",", floatval($array[$row[0]]))) . "\">";
	    echo "<BR><BR>\n";
	    break;
	  case 'int4':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo "id=\"onde_" . fixField($row[0]) . "\" ";

	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 25;
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "200px" : (intval($row[2]/1.1)*8 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" ";

            echo " SIZE=\"6\" MAXLENGTH=\"10\" ";
	    //	    echo " onKeypress=\"if(event.keyCode < 48 || event.keyCode > 57) ";
	    echo " onKeypress=\"if( (event.keyCode < 48 || event.keyCode > 57) ";
            // not fora de um parenteses com varias igualdades em lógica OU é como um not in list
		    if ($formulario['Desabilitar cálculos em campos numéricos'] == 'f'){
            echo " && !(";
            echo "  event.keyCode == 44 || "; // virgula
            echo "  event.keyCode == 43 || "; // +
            echo "  event.keyCode == 45 || "; // -
            echo "  event.keyCode == 120 || "; // x
            echo "  event.keyCode == 42 || "; // *
            echo "  event.keyCode == 47 || "; // /
            echo "  event.keyCode == 40 || "; // (
            echo "  event.keyCode == 41 || "; // )
            echo "  event.keyCode == 91 || "; // [
            echo "  event.keyCode == 93 || "; // ]
            echo "  event.keyCode == 123 || "; // {
            echo "  event.keyCode == 125 "; // }
            echo ")";
			}
			echo ") event.returnValue = false;\"";
            //echo " event.returnValue = false;\"";
	    echo " VALUE = \"" . ((trim($array[$row[0]])=="")?"":intval($array[$row[0]])) . "\">";
	    echo "<BR><BR>\n";
	    break;
	  case 'int8':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo "id=\"onde_" . fixField($row[0]) . "\" ";

	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 25;
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "200px" : (intval($row[2]/1.1)*8 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" ";

            echo " SIZE=\"10\" MAXLENGTH=\"19\" ";
	    echo " onKeypress=\"if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;\"";
	    echo " VALUE = \"" . ((trim($array[$row[0]])=="")?"":intval($array[$row[0]])) . "\">";
	    echo "<BR><BR>\n";
	    break;
	  case 'citext':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    //if ($_debug>1) echo $row[0] . " " . fixField($row[0]) . "<BR>";
	    echo "    <INPUT TYPE=\"TEXT\" ";
            if (stripos("_" . $row[0], 'rgbcolorof_') && intval($row[2])==10)
              echo "CLASS=\"color\" ";
            else{
              echo "CLASS=\"";
              //echo "TEXT";
              echo "ui-input ui-widget ui-corner-all";
              echo "\" ";
	    }
            echo " NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\"";
	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 50;

            // ------------------------------------------------------------------- reduzi de *8 para *5
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "800px" : (intval($row[2]/1.1)*5 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" SIZE=\"";
            echo intval($row[2]/2)>100 ? 200 : intval($row[2]/1.3);
            echo "\"";//  MAXLENGTH=\"" . (intval($row[2]) - 4) . "\"";
	    echo " VALUE = \"" . htmlspecialchars($array[$row[0]], ENT_QUOTES, $encoding) . "\">";
	    echo "<BR><BR>\n";    echo "<script>console.log('isMobile: " . $isMobile . "');\n</script>";
	    break;
	  case 'timestamp':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    //if ($_debug>1) echo $row[0] . " " . fixField($row[0]) . "<BR>";
	    echo "    <INPUT TYPE=\"datetime-local\" ";
            if (stripos("_" . $row[0], 'rgbcolorof_') && intval($row[2])==10)
              echo "CLASS=\"color\" ";
            else{
              echo "CLASS=\"";
              //echo "TEXT";
              echo "ui-input ui-widget ui-corner-all";
              echo "\" ";
	    }
            echo " NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\"";
	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 50;

            // ------------------------------------------------------------------- reduzi de *8 para *5
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "800px" : (intval($row[2]/1.1)*5 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" SIZE=\"";
            echo intval($row[2]/2)>100 ? 200 : intval($row[2]/1.3);
            echo "\"  MAXLENGTH=\"" . (intval($row[2]) - 4) . "\"";
	    //echo " VALUE = \"2023-11-22 17:15\">";
	    $var = htmlspecialchars($array[$row[0]], ENT_QUOTES, $encoding);
	    //if (!$var) $var = date('d/M/Y h:i:s');
	    $date = str_replace('/', '-', $var);	    
	    if ($var) echo " VALUE = \"" . date('Y-m-d H:i', strtotime($date)). "\">"; //min=\"2024-06-01T08:30\" max=\"2024-06-30T16:30\" required
	    echo "<BR><BR>\n";
            //echo date('Y-m-d H:i:s', strtotime($date)) . "<BR><BR>";
	    break;

	  case 'name':
          case 'time':
          case 'interval':
	  case 'varchar':
	  case 'bpchar': // internal name for char()
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    //if ($_debug>1) echo $row[0] . " " . fixField($row[0]) . "<BR>";
	    echo "    <INPUT TYPE=\"TEXT\" ";
            if (stripos("_" . $row[0], 'rgbcolorof_') && intval($row[2])==10)
              echo "CLASS=\"color\" ";
            else{
              echo "CLASS=\"";
              //echo "TEXT";
              echo "ui-input ui-widget ui-corner-all";
              echo "\" ";
	    }
            echo " NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\"";
	    echo " STYLE=\"height: 28px; width: ";
	    if (intval($row[2]) == -1 ) $row[2] = 50;

            // ------------------------------------------------------------------- reduzi de *8 para *5
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>50 ? "70vw" : (intval($row[2]/1.1)*8 ."px"));
            //echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "800px":(intval(log($row[2]/1.1)*5)*10 ."px"));
            //echo ($isMobile) ? "80vw" : (intval( log($row[2]/1.1)*5)*10 ) ."px";
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" SIZE=\"";
            echo intval($row[2]/2)>100 ? 200 : intval($row[2]/1.3);
            echo "\"  MAXLENGTH=\"" . (intval($row[2]) - 4) . "\"";
	    echo " VALUE = \"" . htmlspecialchars($array[$row[0]], ENT_QUOTES, $encoding) . "\">";
            //echo " " . (intval($row[2]/2)>100 ? "800px" : (intval($row[2]/1.1)*5 ."px")) . ", ";
            //echo " " . (intval( log($row[2]/1.1)*5)*20 ) . "px ";
	    echo "<BR><BR>\n";
	    break;
          case 'bytea':
            echo "<BR>\n";
  	    //if ($isdeveloper) echo "passei" . $row[1];
            // verificar se é ou não para enviar esta coluna
	    if (isset($formulario['campo_chave'])&&$formulario['campo_chave'])
              $campo_chave = $formulario['campo_chave'];
            else{
	      $campo_chave = "codigo";
	      $queryOrdinal  = "SELECT column_name, ordinal_position\n";
	      $queryOrdinal .= "FROM information_schema. columns\n";
	      $queryOrdinal .= "  WHERE table_schema = 'public' AND table_name = '" . $formulario['tabela'] . "'\n";
	      $queryOrdinal .= "  and ordinal_position = " . (intval($formulario['chave']) + 1) . ";\n";
              $resOrdinal = pg_query($conn, $queryOrdinal);
	      if ($resOrdinal){
		$campo_chave = pg_fetch_result($resOrdinal, 'column_name');		
	      }
	    }

	    $queryFormAttach  = "SELECT encode(\"" . $row[0] . "\", 'base64') AS field  \n";
	    $queryFormAttach .= "  FROM \"" . $formulario['tabela'] . "\"\n";
	    $queryFormAttach .= "  WHERE \"" . $campo_chave . "\" = " . ($keyIsQuoted ? "'" : '') . intval($valorChave) . ($keyIsQuoted ? "'" : '');
	    /*
	    if ($isdeveloper){
	      echo "</CENTER><PRE>";
	      echo $queryOrdinal;
	      //echo "campo chave: " . $formulario['campo_chave'] . "<br>";
	      //echo "formulario: " . print_r($formulario, true) . "<br>";
	      //echo $queryFormAttach;
	      // formulario['chave'] vem o numero do campo chave do formulario['tabela']
	      echo "</PRE><CENTER>";
	    }/**/
	    
	    $res = pg_query($conn, $queryFormAttach);
	    if ($res){
	      $raw[fixField($row[0])] = pg_fetch_result($res, 'field');
              if ($raw[fixfield($row[0])] && $res){
	        $fileArray[fixField($row[0])] = formsDecodeFile(base64_decode($raw[fixField($row[0])]));
	        //echo "</CENTER>\$fileArray['name']: " . $fileArray['name'] . "<CENTER>";
		//foreach($fileArray as $key => $file)
		//  echo print_r($file['name'], true) . "<BR>";
	      }
	    }
	    //echo $array[$row[0]];

	    //echo "<script>console.log(\"campo: " . $raw[fixField($row[0])] . "\");</script>";
	    /*
            echo "<PRE>";
	    var_dump($raw);
	    echo "</PRE>";/**/
		echo "<script>console.log('arquivo??: " . $linhas . "');</script>";
		echo "<script>console.log('arquivo??: " . $acceptString[$linhas] . "');</script>";
		
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
            
	    //echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000000000\">\n";
	    //echo "    <INPUT NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" TYPE=\"file\">\n";
		//if ($isdeveloper) echo "<PRE>". print_r($raw[fixField($row[0])], true) . "</PRE>";
	    echo "    <INPUT id=\"" . fixField($row[0]) . "\" ";
	    echo " NAME=\"" . fixField($row[0]) . "\" TYPE=\"file\" ";
            echo " style=\"display: none;\" onchange=\"atualizaPlaceholder('" . fixField($row[0]) . "');\" ";
            echo " placeholder=\"Selecione um arquivo\" ";
	    //echo " CLASS=\"";
            //echo "ui-input ui-widget ui-corner-all";
			if (isset($acceptString[$linhas]) && $acceptString[$linhas])
				echo " accept=\"" . $acceptString[$linhas] . "\" ";
            echo ">\n";
            echo "    <label for=\"" . fixField($row[0]) . "\"";
            echo " CLASS=\"modal-label\" ";
	    // echo ">" . ($row[fixField($row[0])]?$fileArray[fixField($row[0])]['name'] . " (clique para trocar de arquivo...)":"Nenhum arquivo selecionado (clique para selecionar um arquivo...)") . "</label>";
            echo ">" . ($raw[fixField($row[0])]?$fileArray[fixField($row[0])]['name'] . " (clique para trocar de arquivo...)":"Nenhum arquivo selecionado (clique para selecionar um arquivo...)") . "</label>";
	    //unset($raw);
	    unset($res);

	    echo "    <BR><BR>\n";
	    break;
	  case 'text':
	  case 'json':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "    <TEXTAREA NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" ROWS=\"10\" COLS=\"80\"";

              echo "CLASS=\"";
              //echo "TEXT";
              echo "ui-input ui-widget ui-corner-all";
              echo "\" ";

	      if ($isMobile)
                echo " STYLE=\"width: 80vw;\"";
              else 
	        echo " STYLE=\"width: 670px;\"";
            echo ">";
	    echo trim(htmlspecialchars($array[$row[0]], ENT_QUOTES, $encoding));
	    //echo $array[$row[0]];
	    echo "</TEXTAREA><BR>\n";
	    echo "    <BR>\n";
	    break;
	  case 'bool':
	    echo "        <INPUT TYPE=\"CHECKBOX\" ";
	    if ($array[$row[0]]=="t") echo "CHECKED";
	    echo " NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" ";
		echo ($referenceOnChangeFunctions[$linhas] ? " onchange=\"" . $referenceOnChangeFunctions[$linhas]  . "\" ": "");
		echo " VALUE=\"true\"><BR>\n";
	    echo "    <BR>\n";


	    if ($referenceOnChangeFunctions[$linhas]){
              echo "<script type=\"text/javascript\">\n";
	      //echo "console.log('aqui!!!!!');\n";
              //echo "console.log('    \$(\"select#" . fixField($row[0]) . "\").attr(\"value\", \'" . $array[$row[0]] . "\');');\n";
              //echo "console.log('    \$(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr(\'checked\', \'checked\');');\n";

              echo "    $(\"select#" . fixField($row[0]) . "\").attr(\"value\", '" . $array[$row[0]] . "');\n";      
              echo "    $(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr('checked', 'checked');\n";
	      
              $onChangeFunction =  preg_replace('/(.*)?\((.*?\).*)/i', '${1}', $referenceOnChangeFunctions[$linhas]);
              $firingChangingFunctions[] = "  " . $onChangeFunction . "(" . $array[$row[0]] . ");\n";
              //$firingChangingFunctions[] = "  " . $onChangeFunction . "(" . (is_numeric($array[$row[0]]) ? "" : "'") . $array[$row[0]] . (is_numeric($array[$row[0]]) ? "" : "'") . ");\n";
              echo "</script>\n";
	    }

		
	    break;
          case 'date':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    ?>
	      <input type="text" class = "ui-input ui-widget ui-corner-all" style="height: 28px; width: 110px;" name="<?PHP echo fixField($row[0]);
                 ?>" id="f_date_<?PHP echo fixField($row[0]); ?>" value="<?PHP echo  $array[$row[0]]; 
                 ?>"><button type="reset" class = "ui-input ui-widget ui-corner-all" style="height: 28px;" id="f_trigger_<?PHP 
                 echo fixField($row[0]); ?>">...</button><script type="text/javascript">
		 Calendar.setup({
		   inputField     :    "f_date_<?PHP echo fixField($row[0]); ?>",      // id of the input field
		       ifFormat       :    "%d/%m/%Y",       // format of the input field
		       showsTime      :    false,            // will display a time selector
		       button         :    "f_trigger_<?PHP echo fixField($row[0]); ?>",   // trigger for the calendar (button ID)
		       singleClick    :    false,           // double-click mode
		       step           :    1                // show all years in drop-down boxes (instead of every other year as default)
		       });
	    </script><BR><BR>
		<?PHP
		break;
	  default:
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";		
	    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\"";
	    echo " SIZE=\"40\"  MAXLENGTH=\"100\"";
	    echo " VALUE = \"" . $array[$row[0]] . "\">";
	    echo "<BR><BR>\n";
	    break;
	  }
	}
	else{
          if ($formulario['Esconde primeira coluna'] != "t"){
	    echo " " . $array[$row[0]] . "<BR><BR>\n";
	    //echo " " . ((isset($caption) && trim($caption)) ? $caption : $array[$row[0]]) . "<BR><BR>\n";
	    //echo "    ???" . getReferencedCaption($relations, $ReferencedCaptions[$linhas], $_POST[fixField($row[0])]) . "???";
	    //echo " conteudo: " . print_r($relations, true). "<BR>";
	    //echo " conteudo: " . print_r($ReferencedCaptions[$linhas], true) . "<BR>";
	    //echo " conteudo: " . print_r($caption, true) . "<BR>";

	  }
          //if ($_POST['botao'] != $stringNovo)
          //if ($linhas == intval($formulario['chave']) && 
	  //     $_POST['botao']==$stringNovo && 
	  //     isset($nextVal) ){
	  if (!isset($nextVal) ){
		  //echo fixField($row[0]) . ": \n";
          //echo $array[$row[0]] . "<BR>\n";

		  //echo "<PRE>buttonsRowKey: " . print_r($buttonRowKey) . "</PRE><BR>";
		  //echo "<PRE>buttonrow_key: " . print_r($buttonrow_key) . "</PRE>";
		  //echo "<PRE>button_row_index: " . print_r($button_row_index) . "</PRE>";
		  //echo "<PRE>POST_buttonrow: " . print_r($_POST['buttonrow']) . "</PRE>";
	    echo "<INPUT TYPE=\"HIDDEN\" NAME=\"" . fixField($row[0]);
            echo "\" id=\"onde_" . fixField($row[0]) . "\" VALUE=\"";
            echo $array[$row[0]] . "\">\n";
	  }
	}
      }
      //if (trim(fixField($row[0])))
		  echo "</DIV>\n";
      $usados[] =  $linhas;
      //$linhas++;
      }
	  }
	  //echo "\$linhas: ". $linhas . "<BR>";

	  if ($linhas >= $innerTotal){
		  //echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$linhas: " . $linhas . "<BR>";
		  $NNtable = $NNtables[$linhas-$innerTotal];
		  $NNkey = $linhas-$innerTotal;
		  //}
		  //}
		  //foreach($NNtables as $NNkey => $NNtable){
		// if ($isdeveloper) {
		// 	echo "\$NNkey: " . $NNkey . "<BR>";
		// 	echo "posicao absoluta: " . $innerTotal + $NNkey . "<BR>";
			
        //     echo "tabela: " . $NNtable['relations'][1]['foreign_table_name'] . "<BR>";
		// }
		//if ($isdeveloper) echo "<PRE>Sortable: " . print_r($NNtables[$NNkey]['sortable'], true) . "</pre>";

      //echo "<H1>-----" . $NNtable['lines'] . "</H1>\n";
      //echo "<H1>-----" . $NNtable['size'] . "</H1>\n";
   if ( ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 2 ) ||
        ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 3)
      ){
		
	   //      if ($NNtable['lines']==2 && $NNtable['size'] == 2){
	//Para pegar valor do campo chave para a tabela do formulario selecionado.
        $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

	/*
	 SELECT codigo, nome,
	 (case when
	 (select
	 case when grupos.codigo = menus_grupos.grupo then true
	 else false end
	 from menus_grupos
	 where menus_grupos.menu = 2) is null then false
	 else
	 (select
	 case when grupos.codigo = menus_grupos.grupo then true
	 else false end
	 from menus_grupos
	 where menus_grupos.menu = 2)
	 end)as checked
	 FROM  grupos
	*/


	// Confere se campos codigo e nome existesm, se nao existirem, pega a chave e o primeiro campo depois da chave
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$checkBoxesResult = 0;
        if ($_debug){
          echo "<PRE>";
          var_dump($NNCaptions);
          echo "</PRE>";
	}
		
	//if ($isdeveloper) echo "passei formulario: <BR><PRE>" . print_r($formulario, true) . "</PRE>";
        if (trim($formulario['Campo(s) para utilizar como caption em relações N:N'])){
          $NNCaptions = explode(',', $formulario['Campo(s) para utilizar como caption em relações N:N']);
          foreach($NNCaptions as $NNCaptionKey => $NNCaption){
            if ($NNCaption) {
	      // caption é o que vai aparecer dentro do combo e label (etiqueta) é o no nome do campo
              //if ($isdeveloper) echo "passei";
              //echo "<PRE>PASSEI</PRE>";
              $NNQueryCaptions[$NNCaptionKey]['caption'] = str_replace(";", ",", $NNCaption);
              $NNQueryCaptions[$NNCaptionKey]['column'] = false;
	    }
            else{
              $NNQueryCaptions[$NNCaptionKey]['caption'] = "nome";
              $NNQueryCaptions[$NNCaptionKey]['column'] = true;
            }
	  }
          if ($_debug){
            echo "<PRE>NNQueryCaptions:\n";
            var_dump($NNQueryCaptions);
            echo "</PRE>";
	  }
        }
	//$NNCaptions[] = 'nome';
	//$NNCaptions[] = "\"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." . $NNtable['relations'][1]['foreign_column_name'];

        //foreach($NNCaptions as $NNCaptionKey => $NNCaption){
	//if ($NNCaptions[$NNkey]){
	  $queryCheckBoxes  = "SELECT \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." . $NNtable['relations'][1]['foreign_column_name'];
          $queryCheckBoxes .= ", ";

          if (isset($NNQueryCaptions[$NNkey]['column'])){
            if ($NNQueryCaptions[$NNkey]['column'] == true){
              $queryCheckBoxes .= "\"" . $NNtable['relations'][1]['foreign_table_name'] . "\".";
              $queryCheckBoxes .= ($NNQueryCaptions[$NNkey]['caption']?$NNQueryCaptions[$NNkey]['caption']:"nome") . " as onde_nn_caption,\n";
	    }else{
              $queryCheckBoxes .= $NNQueryCaptions[$NNkey]['caption'] . " as onde_nn_caption,\n";
	    }
	  }else{
            $queryCheckBoxes .= "\"" . $NNtable['relations'][1]['foreign_table_name'] . "\".";
            $queryCheckBoxes .= ($NNCaptions[$NNkey]?$NNCaptions[$NNkey]:"nome") . " as onde_nn_caption,\n";
	  }
          //$queryCheckBoxes .= $NNCaption . ",\n";
          //$queryCheckBoxes .= $NNCaption . " as onde_nn_caption,\n";
	  //$queryCheckBoxes  = "SELECT " . $NNtable['relations'][1]['foreign_column_name'] . ", " .$NNCaption . ",\n";
		  
	  if (isset($_POST['buttonrow'])){
	    $queryCheckBoxes .= "  (case when\n";
	    $queryCheckBoxes .= "    (select\n";
	    $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then true else false end\n";
	    $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
	    $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";
	    //reset($_POST['buttonrow']);
	    //while (list($key, $val) = each($_POST['buttonrow'])){
            foreach($_POST['buttonrow'] as $key => $val){
	      if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		$queryCheckBoxes  .= intval($key);
	      else
		$queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
	    }

	    $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

	    $queryCheckBoxes .= " limit 1) is null then false\n";
	    $queryCheckBoxes .= "else\n";
	    $queryCheckBoxes .= "    (select\n";
	    $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then true else false end\n";
	    $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
	    $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";

	    //reset($_POST['buttonrow']);
	    //while (list($key, $val) = each($_POST['buttonrow'])){
            foreach($_POST['buttonrow'] as $key => $val){
	      if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		$queryCheckBoxes  .= intval($key);
	      else
		$queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
	    }

	    $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

	    $queryCheckBoxes .= " limit 1)\n";
	    $queryCheckBoxes .= "end) as checked\n";
	  }
	  else
	    $queryCheckBoxes .= "false as checked\n";
	  
      if ($NNtable['sortable']){// inicio do sortable
		  if (isset($_POST['buttonrow'])){
			  $queryCheckBoxes .= ",  (case when\n";
			  $queryCheckBoxes .= "    (select\n";
			  $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then ";
              //echo "coluna de ordenacao: " . $NNtable['sortable_collumn'] . "<BR>";
              $queryCheckBoxes .= "\"" . $NNtable['table_name'] . "\".\"" . (isset($NNtable['sortable_collumn'])?$NNtable['sortable_collumn']:"ordem") . "\" ";
              $queryCheckBoxes .= " else NULL end\n";
			  $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
			  $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";
			  //reset($_POST['buttonrow']);
			  //while (list($key, $val) = each($_POST['buttonrow'])){
			  foreach($_POST['buttonrow'] as $key => $val){
				  if (strpos("_" . $row[1], "int") && $row[1] != "interval")
					  $queryCheckBoxes  .= intval($key);
				  else
					  $queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
			  }

			  $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

			  $queryCheckBoxes .= " limit 1) is null then NULL\n";
			  $queryCheckBoxes .= "else\n";
			  $queryCheckBoxes .= "    (select\n";
			  $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then ";
              //echo "coluna de ordenacao: " . $NNtable['sortable_collumn'] . "<BR>";
              $queryCheckBoxes .= "\"" . $NNtable['table_name'] . "\".\"" . (isset($NNtable['sortable_collumn'])?$NNtable['sortable_collumn']:"ordem") . "\" ";
              //$queryCheckBoxes .= "\"" . $NNtable['table_name'] . "\".ordem ";
              $queryCheckBoxes .= " else NULL end\n";
			  $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
			  $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";

			  //reset($_POST['buttonrow']);
			  //while (list($key, $val) = each($_POST['buttonrow'])){
			  foreach($_POST['buttonrow'] as $key => $val){
				  if (strpos("_" . $row[1], "int") && $row[1] != "interval")
					  $queryCheckBoxes  .= intval($key);
				  else
					  $queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
			  }

			  $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
			  $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

			  $queryCheckBoxes .= " limit 1)\n";			  
			  $queryCheckBoxes .= "end) as ordem\n";
		  }
		  else
			  $queryCheckBoxes .= ",NULL as ordem\n";
      }// fim do sortable
	  
	  $queryCheckBoxes .= "FROM  \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"\n";

	  //if ($isdeveloper) echo "NNCaptionKey: " . $NNCaptionKey . "<BR>\n";
	  //if ($isdeveloper) echo "NNkey: " . $NNkey . "<BR>\n";
          if ($NNFilters[$NNkey]){
    	    $queryCheckBoxes .= " WHERE " . $NNFilters[$NNkey];
          }

	  ////$queryCheckBoxes .= " order by nome\n";
	  ////$queryCheckBoxes .= " order by " .$NNCaption . "\n";

	  // FILTERS NN ( usar o mesmo indice do NNCaption e popular antes, conferir se o indice existe)
	  //if (trim($formulario['Campo(s) para utilizar como caption em relações N:N'])){
          //$NNCaptions = explode(',', $formulario['Campo(s) para utilizar como caption em relações N:N']);
          //foreach($NNCaptions as $NNCaptionKey => $NNCaption){
	  //if (trim($formulario['Condição(ões) para utilizar como filtro em relações N:N'])){
	  //$NNFilters=explode(',', $formulario['Condição(ões) para utilizar como filtro em relações N:N']);
	  //}
	  //if ($isdeveloper) echo "<PRE>NNFILTER: " . print_r($NNFilters, true) . "\n=============</PRE>";
	  //
		  
	  // se for sortable a terceira coluna da or order by 
	  //echo "<PRE>Sortable: " . print_r($NNtables[$NNkey]['sortable'], true) . "</pre>";
	  $queryCheckBoxes .= " order by ";
	  if ($NNtable['sortable'])
	    $queryCheckBoxes .= " ordem, ";
	  $queryCheckBoxes .= " onde_nn_caption";
	  //$queryCheckBoxes .= " order by \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." .$NNCaption . "\n";
      // echo "<B>AQUI??</B><PRE>\n";
      //       echo $queryCheckBoxes;
      //       echo "</PRE>\n";

	  //$_debug = 1;
          if ($_debug){
            echo "<B>AQUI??</B><PRE>\n";
            echo $queryCheckBoxes;
            echo "</PRE>\n";
	  }
	  if ($_debug) show_query($queryCheckBoxes, $conn);
	  $checkBoxesResult = pg_Exec($conn, $queryCheckBoxes);

	  //if ($isdeveloper) echo "<PRE>" . $queryCheckBoxes . "</PRE>";
	  //if ($isdeveloper) echo "Result:" . $checkBoxesResult . "<BR>";
	  //if ($isdeveloper) echo "Error:" . pg_last_error() . "<BR>";

  	  $NNtables[$NNkey]['checkBoxesResult'] = $checkBoxesResult;
	  $NNtables[$NNkey]['caption'] = $NNCaptions[$NNkey];
	  //$NNtables[$NNkey]['caption'] = "onde_nn_caption";
	  if ($checkBoxesResult)
  	    $NNtables[$NNkey]['items'] = pg_numrows($checkBoxesResult);
          //if ($checkBoxesResult) break;
	  //}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$checkBoxes = pg_fetch_all($checkBoxesResult);
	  
    if ($isdeveloper){
		//echo "<B>Etiquetas (" . intval($NNkey) . "):</B><BR>\n<PRE>" . print_r($NNLabels, true) . "</PRE>";
		if (!$checkBoxesResult){
		 echo "<PRE>". $queryCheckBoxes . "</PRE><BR>";
	     echo pg_last_error();
		}
	}
	if ($checkBoxesResult){
          $checkBoxes = pg_fetch_all($checkBoxesResult);
	  //echo "<PRE>\n";
          //var_dump($checkBoxes);
	  //echo "</PRE>";

          // Tentando colocar selecao multipla	  
	  //echo "total de itens: " . intval($NNtables[$NNkey]['items']) . "<BR>\n";
	  //echo "total de itens: " . intval(pg_numrows($checkBoxesResult)) . "<BR>\n";
	  //echo "total de itens: " . count($checkBoxes) . "<BR>\n";
          //echo "<PRE>";
          //var_dump($NNLabels); 
          //echo "</PRE>" . $NNkey;
		  //echo "<PRE>Sortable: " . print_r($NNtables[$NNkey], true) . "</pre>";
      
			echo "<div id=\"onde_div_" .fixID($NNtable['table_name']) . "_";
      echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\"";
      echo " class=\"ui-state-default\" style=\"color: " . (strpos($_theme, "Tron")?"lightblue":"black") . "; background: unset; border: unset;\"";
      echo ">\n";
      echo "<span id=\"onde_span_";
			echo fixID($NNtable['table_name']) . "_";
      echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\"";
      echo "style=\"display: inline-block\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> ";
      echo "<span id=\"onde_span_index_";
			echo fixID($NNtable['table_name']) . "_";
			echo fixID($NNtable['relations'][1]['foreign_table_name']) . "";
      echo "\" style=\"display: none;\">(";
      echo $innerTotal + $NNkey . " ou NN[" . $NNkey . "]) - " . $NNtable['table_name'] . "</span> ";

	  //echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
          $label = mb_ucfirst($NNtable['relations'][1]['foreign_table_name'], $encoding);

          if ($isdeveloper){
            $dicaPrefix = "<span title=\"\$NNkey = " . $NNkey . " (valor inicial = 0)\">";
	    $dicaSufix  = "</span>";
	  }


      if (isset($NNLabels) && isset($NNLabels[intval($NNkey)])){
	    $label = $NNLabels[intval($NNkey)];
        echo "<script>console.log('etiqueta: " . $NNLabels[intval($NNkey)] . "');\n</script>\n";
	  }
	  
	  echo "<B>" . $dicaPrefix . $label . ":" . $dicaSufix ."</B><BR>\n";	  
	  //echo $NNtable['relations'][1]['foreign_column_name'] . "<BR>";
      if ($NNtables[$NNkey]['items'] >= 7 || isset($NNtables[$NNkey]['sortable_collumn'])){
	      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
        echo"<SELECT ";
        if ($isMobile)
          echo "style=\"width: 80vw;\" ";
        else
          echo "width=400 ";
        echo " class=\"chosen-select";
			  if ($NNtables[$NNkey]['sortable']) echo " chosen-sortable";
			  echo "\" ";
        echo " NAME=\"";
	      echo fixField($NNtable['table_name']) . "_";
	      echo fixField($NNtable['relations'][1]['foreign_table_name'] . "_multiple[" . $NNtable['relations'][1]['foreign_column_name'] . "][]\"");
	      echo "\" ID=\"";
	      echo fixID($NNtable['table_name']) . "_";
	      echo fixID($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][]\"");
	      echo "\" SIZE=\"1\" multiple>";
	      foreach($checkBoxes as $checkBox){
	        echo "<option value=\"" . trim($checkBox[$NNtable['relations'][1]['foreign_column_name']]) . "\"";
	        echo " " . ($checkBox['checked'] == 't' ? "SELECTED" : "") . ">";
	        //echo $checkBox[str_replace("\"", "", $NNCaption)] . "<BR>\n";
	        echo strip_tags($checkBox['onde_nn_caption']);// . "<BR>\n";
	      }
	      echo "</select>";
			  if (isset($NNtable['relations'][1]['foreign_table_name']) && trim($NNtable['relations'][1]['foreign_table_name'])){
	        echo "<script>\n";		
		      echo "$(function() {\n";
					echo "// tabela " . fixID($NNtable['table_name']) . "\n";
					echo "// tabela da chave estrangeira: " . $NNtable['relations'][1]['foreign_table_name'] . "\n";
          echo "// nome da coluna: " . $NNtable['relations'][1]['foreign_column_name'] . "\n";
		      echo "console.log(\"passei\");\n";
		      echo "var chosen_";
		      echo fixID($NNtable['table_name']) . "_";
	        echo fixID($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][]");
          echo " = document.getElementById(\"";
		      echo fixID($NNtable['table_name']) . "_";
					echo fixID($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][]") . "_chosen\");\n";
	        echo "chosen_";
		      echo fixID($NNtable['table_name']) . "_";
					echo fixID($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][]") . ".style.width = \"400px\";\n";
          echo "});\n";
	        echo "</script>\n";
        }			
			}
      else{
	    foreach($checkBoxes as $checkBox){
	      //echo "<INPUT TYPE=\"hidden\" NAME=\"";
	      //echo "lastState_" . $NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "]";
	      //echo "[" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"";
	      //echo " VALUE=\"" . $checkBox['checked'] . "\" >"; // antes de checked era codigo

	      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	      echo "<INPUT TYPE=\"checkbox\" NAME=\"";
	      //echo $NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "]";
	      
	      echo fixField($NNtable['table_name']) . "_";	    
	      echo fixField($NNtable['relations'][1]['foreign_table_name'] . "_checkBoxes[" . $NNtable['relations'][1]['foreign_column_name'] . "][" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"");//?D?

	      echo " id=\"";
        echo fixID($NNtable['table_name']) . "_";	      
	      echo fixID($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"");//?D?

	      ///////////////////////////////////////////////// Testar melhor e se nao funcionar inverter estas duas linhas (descomentar uma e comentar a otura)
	      //echo "[" . $checkBox['codigo'] . "]\"";
	      //echo "[" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"";

	      //echo " VALUE=\"" . $checkBox['codigo'] . "\" ";
	      echo " " . ($checkBox['checked'] == 't' ? "CHECKED" : "") . ">";
	      ////echo $NNCaption . "<BR>\n";
	      //echo $checkBox[str_replace("\"", "", $NNCaption)] . "<BR>\n";
	      echo $checkBox['onde_nn_caption'] . "<BR>\n";
	      ////echo $checkBox[trim($NNCaption)] . "<BR>\n";
	      ////echo $checkBox['Nome do arquivo'] . "<BR>\n";

	    }
	  }
	  echo "<BR>";
      echo "<BR>\n";
	  echo "</div>\n";
	}
   }
      //if ($_debug) show_query($queryNN, $conn);
   // Fim no foreach do NNTables
    }////////////////////////////////////////////////////////////
	}

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //echo "<PRE>";var_dump($formulario['Permitir anexos']); echo "</PRE>";
    if ($formulario['Permitir anexos']=='t'){
      //echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000000000\">\n";
      ini_set('upload_max_filesize', '50M'); // Alterei de 10M para 50M.
      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      echo "    <B>Anexar arquivos: </B><BR>\n";
      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      echo "    <INPUT NAME=\"userdoc\" TYPE=\"file\"><BR><BR>\n";
      $teste_query = "SELECT '<a href=\"accessDocument.php?codigo='||trim(to_char(codigo, '999999'))||'\">'||filename||'</a>' as documentos FROM documents where data is not null";
      show_query($teste_query, $conn);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    echo "<DIV id='enviando' style=\"display: none;\">\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <img src=\"images/enviando.gif\" width=\"150\">\n";
    echo "</div>";
    echo "<DIV id='botoes'>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<INPUT TYPE=\"SUBMIT\" ";
    //echo " CLASS=\"SUBMIT\" ";
    echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
    echo " VALUE=\"";
    if ($_POST['buttonrow'])
      echo " Salvar ";
    else
      echo " Inserir ";
    echo "\" ID=salvar_inserir NAME=\"envia\">\n";
    /*
    if ($formulario['Não exibir tabela quando exibir o form']){
      echo "<INPUT TYPE=\"SUBMIT\" ";
      //echo " CLASS=\"SUBMIT\" ";
      echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
      echo " VALUE=\"";
      echo " Cancelar ";
      echo "\" ID=cancelar NAME=\"cancelar\">\n";
    }
    */
    if (isset($formulario['Carregar valor do campo chave da tabela na seção']) &&
        $formulario['Carregar valor do campo chave da tabela na seção'] == 't' &&
        $_POST['buttonrow']){
      echo "<INPUT TYPE=\"SUBMIT\" ";
      //echo " CLASS=\"SUBMIT\" ";
      echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
      echo " VALUE=\"";
      echo " Carregar ";
      echo "\" ID=carregar NAME=\"carregar\">\n";
    }
	//echo print_r($_POST['buttonrow'], true) . "<BR>";
	//echo "Email event: " . print_r($emailEvent, true) . "<BR>";
    if ($_POST['buttonrow'] && strpos($emailEvent, "ENVIAR")){
      echo "<INPUT TYPE=\"SUBMIT\" ";
      //echo " CLASS=\"SUBMIT\" ";
      echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
      echo " VALUE=\"";
      echo " Enviar ";
      echo "\" ID=enviar NAME=\"envia\">\n";
    }
    if (isset($firingChangingFunctions)){
      echo "<script type=\"text/javascript\">\n";
      echo "  $(function() {\n";
      foreach($firingChangingFunctions as $changeFunction){
	//echo "console.log('aqui tambem');\n";	
        //echo "console.log('  -- " . $changeFunction . " --  ');\n";
		  echo "    " . $changeFunction;	
      }
      echo "  });\n";
      echo "</script>\n";
    }
    echo "</DIV>\n";
    echo "</FORM>\n";
    //echo "  <HR WIDTH=\"90%\" SIZE=\"1\">\n";
    
    //show_query($checkNullable, $conn);
    echo "<script type=\"text/javascript\">\n";

    echo " //https://base64.guru/developers/javascript/examples/unicode-strings\n";
    echo "/**\n";
    echo " * ASCII to Unicode (decode Base64 to original data)\n";
    echo " * @param {string} b64\n";
    echo " * @return {string}\n";
    echo " */\n";
    echo "function atou(b64) {\n";
    echo "  return decodeURIComponent(escape(atob(b64)));\n";
    echo "}\n";
    echo "/**\n";
    echo " * Unicode to ASCII (encode data to Base64)\n";
    echo " * @param {string} data\n";
    echo " * @return {string}\n";
    echo " */\n";    
    echo "function utoa(data) {\n";    
    //echo "  console.log('data);\n";
    //echo "  console.log(btoa(unescape(encodeURIComponent(data))));\n";
    echo "  return btoa(unescape(encodeURIComponent(data)));\n";
    echo "}\n";

    echo "    function validateForm() {\n";
    echo "      var error = 0;\n";
    //echo "      console.log('Entrei no validadeForm');\n";
    echo "      var salvar_inserir = document.getElementById(\"botoes\");\n";
    echo "      var enviando = document.getElementById(\"enviando\");\n";
    //echo "      console.log('botao', salvar_inserir);\n";
    echo "      salvar_inserir.style.display = 'none';\n";
    echo "      enviando.style.display = 'block';\n";

    unset($nullabelColumn);
    foreach ($nullableColumns as $index => $nullableColumn){
      echo "      console.log('\$index: " . $index . "');\n";
      //echo "      console.log('\$formularios[\'Campo para salvar usuário logado\']: " . $formulario['Campo para salvar usuário logado'] . "');\n";
      //if ($raw[fixField($index)]) echo "      console.log('\Arquivo: " . $fileArray[fixField($index)]['name'] . "');\n";
       echo "      var " . fixField($index) . "_fileName = '" . $fileArray[fixField($index)]['name'] . "';\n";

      if ($index != $formulario['Campo para salvar usuário logado']){
        //echo "      console.log('\$index: (entrei) " . $index . "');\n";
        echo "      var " . fixField($index) . " = document.forms[\"" . fixField($formulario['tabela']) . "\"][\"" . fixField($index) . "\"].value;\n";
        echo "      console.log('" . fixField($index) . " = ' + " . fixField($index) . ");\n";

        echo "      if ( (" . fixField($index) . ".trim() == \"\" ";
        // Inclui campos zerados pois o INSERT converte para NULL os campos zerados.
        echo " || " . fixField($index) . ".trim() == null ) ";
        echo " && " . fixField($index) . "_fileName.trim() == \"\" ";
        echo " ) {\n";

        // try
        echo "\n        var tinymce_content = \"\";\n";
        echo "        try{\n";
	echo "          var myContent = tinyMCE.get(\"onde_" . strtolower(fixField($index)) . "\").getContent();\n";
        //echo "          console.log('myContent: ' + myContent);\n";
        echo "          tinymce_content = myContent;\n";
        echo "          ";
        echo "        }catch{\n";
        echo "          tinymce_content = \"\";\n";
        //echo "          console.log('Nao eh tinymce (verifica nao nulo)');\n";
        echo "          error++\n";
        echo "        }\n";

        echo "        if (tinymce_content == \"\") {\n";
        //echo "          console.log(\"NULLO: \"+" . fixField($index) . ");\n";
        echo "          error++\n";
        echo "        }\n";
        //echo "        console.log(\"Error: \", error)\n";
        echo "      }\n";
      }
      //echo "      console.log('????" . fixField($index) . " = ' + " . fixField($index) . ");\n";
    }
    echo "      if (error) {\n";
    echo "        alert(\"Os campos indicados com (*) são obrigatórios.!\")\n";
    echo "        salvar_inserir.style.display = 'block';\n";
    echo "        enviando.style.display = 'none';\n";
    echo "      }\n";
    /* echo "      if (x == \"\") {\n"; */
    /* echo "	alert(\"Name must be filled out\");\n"; */
    /* echo "\n"; */
    /* echo "	return false;\n"; */
    /* echo "      }\n"; */
    /* echo "      console.log(\"passei\n\");\n"; */
    //echo "      alert(\"passei\");\n";
    echo "      if (error) return false;\n";

    foreach ($obfuscatedEncodedFields as $obfuscatedEncodedField){
      //echo "      alert(\"passei\");\n";
      $obfuscatedEncodedField = trim($obfuscatedEncodedField);
      
      //echo "console.log('Nome do campo: ]" . $obfuscatedEncodedField . "[');\n";
      ////echo "console.log('encodeURIComponent: ' + encodeURIComponent(document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value));\n";

      ////echo "console.log('Nome do campo: ]" . $obfuscatedEncodedField . "[');\n";
      ////echo "console.log('Nome do campo: onde_" . fixField(trim($obfuscatedEncodedField)) . "');\n";
      ////echo "console.log('Nome do campo: ]" . $obfuscatedEncodedField . "[');\n";

      ////echo "  console.log('escape: ' + document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value );\n";
      //echo "  console.log('encodedURIComponent: ' + encodeURIComponent(document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value));\n";
      ////echo "  console.log('btoa: ' + btoa(encodeURIComponent(document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value)));\n";

        echo "\n        var tinymce_content = \"\";\n";
        echo "        try{\n";
        echo "          console.log(\"campo: ". fixField($obfuscatedEncodedField) . "\");\n";
	echo "          var myContent = tinyMCE.get(\"onde_" . fixField($obfuscatedEncodedField) . "\").getContent();\n";
	echo "          tinyMCE.get(\"onde_" . fixField($obfuscatedEncodedField) . "\").setContent('');\n";
        echo "    document." . fixField($formulario['tabela']) . ".encoded_onde_" . fixField($obfuscatedEncodedField) . ".value = utoa(encodeURIComponent(myContent));\n";
        echo "          console.log('myContent: ' + myContent);\n";
        echo "          tinymce_content = myContent;\n";
        echo "          ";
        echo "        }catch{\n";
        //echo "          document." . fixField($formulario['tabela']) . ".onde_" . fixField($obfuscatedEncodedField) . ".value = '';\n";
        echo "          console.log('Nao eh tinymce obfuscando');\n";
        echo "        }\n";

        //echo "        if (tinymce_content == \"\") {\n";
        //echo "          console.log(\"NULLO: \"+" . fixField($index) . ");\n";
        //echo "          error++\n";
        //echo "        }\n";

	//Try catch se for textarea (value)
	echo "  if (tinymce_content == \"\"){\n";
        echo "    try{\n";
	echo "      if (document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value !== undefined ){\n";
        echo "      document." . fixField($formulario['tabela']) . ".encoded_onde_" . fixField($obfuscatedEncodedField) . ".value = utoa(encodeURIComponent(document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value));\n";
        echo "      document." . fixField($formulario['tabela']) . ".onde_" . fixField($obfuscatedEncodedField) . ".value = '';\n";
        echo "      }\n";
        echo "      console.log('valor do campo (normal) " . $obfuscatedEncodedField . ": ' + document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value);\n";
        echo "    }catch{\n";
        echo "      console.log('Erro obfuscando valor do campo....');\n";
        echo "      //document." . fixField($formulario['tabela']) . ".onde_" . fixField($obfuscatedEncodedField) . ".value = '';\n";
        echo "      //console.log('valor do campo (normal): ' + document.getElementById(\"onde_" . fixField($obfuscatedEncodedField) . "\").value);\n";
        echo "  }\n";

      // Try catch se for editor (code mirror)
        echo "    try{\n";
      // incluir um try catch aqui!
      //echo "      console.log(encodeURIComponent((editor_" . fixField($obfuscatedEncodedField) . ".getValue(''))));\n";
      //echo "      console.log(utoa(editor_" . fixField($obfuscatedEncodedField) . ".getValue('')));\n";
        echo "      document." . fixField($formulario['tabela']) . ".encoded_onde_" . fixField($obfuscatedEncodedField) . ".value = utoa(encodeURIComponent(editor_" . fixField($obfuscatedEncodedField) . ".getValue()));\n";
      //echo "     console.log('Conteudo do campo: ' + editor_" . fixField($obfuscatedEncodedField) . ".getValue());\n";
        echo "      editor_" . fixField($obfuscatedEncodedField) . ".setValue('');\n";
        echo "      console.log('Conteudo do campo [editor]: ' + editor_" . fixField($obfuscatedEncodedField) . ".getValue());\n";
        echo "    }catch{\n";
        echo "    console.log('Erro ao usar editor inexistente.');\n";
        echo "  }\n";
        echo "}\n";
        echo "console.log('CAMPO obfuscado: " . fixField($obfuscatedEncodedField) . "');";
      
    }
    //echo "  alert('passei encode validade');";
    //echo "console.log('btoa: ' + btoa(encodeURIComponent(editor.getValue())));";
  //           fixField($formulario['tabela'])
  //  document.query.encoded_query.value = utoa(editor.getValue());
  /////document.query.encoded_query.value = "";  
  ////console.log(utoa(editor.getValue()));
  //$(editor.getWrapperElement()).hide();
  //editor.setValue('');  
  ////query_field = document.getElementById("query_field");
  ////console.log(query_field);
  ////document.login.encoded_query.value = query;
  //document.query.executar = 1;
  //document.query.submit(); 

    
    echo "    }\n";    
    echo "</script>\n";



    echo "<CENTER>\n";
  }
 }

if ($formulario['Cabeçalho html']){
  if (isset($queryarguments))
    foreach($queryarguments as $queryargument)
		if (intval($queryargument['key']))
      $formulario['Cabeçalho html'] = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $formulario['Cabeçalho html']);

  echo "</CENTER>\n";
  echo $formulario['Cabeçalho html'] . "\n\n";
  echo "<CENTER>\n";
}

      $queryIncluiLinhaCabecalho = trim($formulario['Incluir linha 1 col 1 da query como cabeçalho']);
      if ($queryIncluiLinhaCabecalho){
        $whereString .= $campos[intval($formulario['chave'])] . " = ";
        if (strpos("_" . $row[1], "int") && $row[1] != 'interval')// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<< aqui
          $whereString .= intval($_POST[fixField($row[0])]);
        else
          $whereString .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
        $queryIncluiLinhaCabecalho = str_replace("/*where*/", $whereString, $queryIncluiLinhaCabecalho);

		//echo "</CENTER><PRE>" . print_r($queryarguments) . "</PRE><CENTER>";
        if (isset($queryarguments))
          foreach($queryarguments as $queryargument)
              if (intval($queryargument['key']))
  			    $queryIncluiLinhaCabecalho = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $queryIncluiLinhaCabecalho);

        //echo "</center><PRE>??" . print_r($queryIncluiLinhaCabecalho, true) . "</PRE><center>";
        if (isset($_SESSION['referencias']))
          foreach($_SESSION['referencias'] as $variavel => $valor){
            $queryIncluiLinhaCabecalho = str_replace("\$" . $variavel, $valor, $queryIncluiLinhaCabecalho);
            //echo "</center><PRE>??" . print_r($queryIncluiLinhaCabecalho, true) . "</PRE><center>";
        }
        //echo "<PRE>" . htmlentities($whereString) . "</PRE>";
        //echo "</center><PRE>??" . print_r($queryIncluiLinhaCabecalho, true) . "</PRE><center>";
        //echo "</center><PRE>??" . print_r($queryIncluiLinhaCabecalho, true) . "</PRE><center>";
	  
        if ($_debug) show_query($queryIncluiLinhaCabecalho, $conn);
		//$queryIncluiLinhaCabecalho = preg_replace('/(\$\d)/i','0',  $queryIncluiLinhaCabecalho);
        $resultIncluiLinhaCabecalho = pg_exec($conn, $queryIncluiLinhaCabecalho);
        if ($resultIncluiLinhaCabecalho && pg_numrows($resultIncluiLinhaCabecalho)){
          $rowIncluiLinhaCabecalho = pg_fetch_row ($resultIncluiLinhaCabecalho, 0);
          //echo "<PRE>" . htmlentities($rowIncluiLinha[0]) . "</PRE>"
          // Por que eu queria remover imagens?
          //$pattern = '/<IMG.*?(>)/i';
          //$replacement = '';
          //$rowIncluiLinhaCabecalho[0] =  preg_replace($pattern, $replacement, $rowIncluiLinhaCabecalho[0]);
        }
      }
      //echo "<PRE>" . $queryIncluiLinhaCabecalho . "</PRE>";
      echo $rowIncluiLinhaCabecalho[0];
      //echo pg_last_error() . $rowIncluiLinhaCabecalho[0];

if ($formulario['Apenas form, sem tabela'] == 'f'){
  $extraGet = "&form=" . $codigo;
  //if (isset($_GET['args'])){
  //  foreach($_GET['args'] as $argkey => $argvalue){
  if (isset($arguments) && is_array($arguments)){
    foreach($arguments as $argkey => $argvalue){
      if (!isset($extraGet))
	$extraGet = "?";
      $extraGet .= "&args[" . $argkey . "]=" . $argvalue;
      if ($form){
        if (!isset($form['action']))
          $form['action'] = "?";
        $form['action'] .= "&args[" . $argkey . "]=" . $argvalue;
      }
    }
  }

  //$extraGet = str_replace($extraGet, " ", "%20");
  //$form['action'] = str_replace($form['action']  , " ", "%20");

  /*
   um novo botao para editar a ordem dos campos no form arrastando e soltando
   e por json-get (ou post) salva no campo 
   Listar campos na ordem que devem ser exibidos, incluir os N:N:
   (gerar o css javascript para isso) ??
   */
  if ($formulario['Não exibir tabela quando exibir o form'] == 'f' || (!isset($_POST['buttonrow']) && trim($_POST['botao'])!=trim($stringNovo)) ){
  if ($form){
    $abriuForm = 0;
    //if ($isdeveloper) echo "</center><PRE>FORM:" . print_r($form ,true) . "</pre><center>";

    echo "<style>\n";
    echo "    .flex-container {\n";
    echo "        display: flex;\n";
    echo "          background-color: #f1f1f1;\n";
    echo "        }\n";
    echo "    \n";
    //echo "    table.onde{\n";
    //echo "        width: 100%;\n";
    //echo "        }\n";
    echo "    \n";
    echo "        .flex-container > div {\n";
    echo "          background-color: DodgerBlue;\n";
    echo "        color: white;\n";
    echo "        width: 100px;\n";
    echo "        margin: 10px;\n";
    echo "          text-align: center;\n";
    echo "          line-height: 75px;\n";
    echo "          font-size: 30px;\n";
    echo "        }\n";
    echo "    \n";
    echo "        .container {\n";
    echo "        background: tomato;\n";
    echo "        display: flex;\n";
    echo "          flex-flow: row wrap;\n";
    echo "          align-content: space-between;\n";
    echo "          justify-content: space-between;\n";
    echo "        }\n";
    echo "        .item {\n";
    echo "        width: 100%;\n";
    echo "        background: gold;\n";
    echo "        height: 100px;\n";
    echo "        border: 1px solid black;\n";
    echo "          font-size: 30px;\n";
    echo "          line-height: 100px;\n";
    echo "          text-align: center;\n";
    echo "        margin: 10px\n";
    echo "    	}\n";
    echo "      .item:nth-child(3n) {\n";
    echo "        background: silver;\n";
    echo "        }\n";
    echo "        .container::before, .container::after {\n";
    echo "        content: '';\n";
    echo "        width: 100%;\n";
    echo "        order: 1;\n";
    echo "        }\n";
    echo "      .item:nth-child(n + 4) {\n";
    echo "        order: 1;\n";
    echo "        }\n";
    echo "      .item:nth-child(n + 7) {\n";
    echo "        order: 2;\n";
    echo "        }\n";
    echo "    \n";
    echo "        .item.one {order: 5;}\n";
    echo "        .item.two {order: 4;}\n";
    echo "        .item.three {order: 3;}\n";
    echo "        .item.four {order: 2;}\n";
    echo "        .item.five {order: 1;}\n";
    echo "    \n";
    echo "</style>\n";

    echo "<FORM ACTION=\"" . $form['action'] . "\" METHOD=\"POST\">\n";

    echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringNovo . "\" NAME=\"botao\">\n";
    //if ($form['Cláusula where para ocultar'])
    if ($form['saveForm']){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringSalvar . "\" NAME=\"botao\">\n";
    }
    if ($form['delete']){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringRemover . "\"\n";
      echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
    }
    if ($form['duplicar']){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringDuplicar . "\"\n";
      echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
    }
  }
  
  if ($formulario['Esconde primeira coluna']=="t")
    $form['hideFirstColumn'] = true;

  if (intval(trim($formulario['Coluna com cor de fundo da linha']))){
    $formata['corDeFundo'] = intval(trim($formulario['Coluna com cor de fundo da linha']));
  }

  if (intval(trim($formulario['Coluna com condição de negrido']))){
    $boldCondition['column'] = intval(trim($formulario['Coluna com condição de negrido']));
    $boldCondition['value'] = trim($formulario['Valor para negrito']);
    $boldCondition['hide'] = $formulario['Esconder coluna com condição de negrito']=='t' ? true : false;
  }

  if (trim($formulario['Segunda coluna de ordenação'])){
    $secondOrder = trim($formulario['Segunda coluna de ordenação']);
  }

  if (trim($formulario['limite'])){
    $limite = intval(trim($formulario['limite']));
  }

  if (trim($formulario['String printf para monstrar número de linhas']))
    $showNum = trim($formulario['String printf para monstrar número de linhas']);

  if ($_debug>1) {
    echo "</CENTER>\n";
    echo "<PRE> FORM:\n";
    var_dump($form);
    echo "Campos:\n";
    var_dump($campos);
    echo "Formulario\n";
    var_dump($formulario);
    echo "POST\n";
    var_dump($_POST);
    echo "</PRE>\n";
    echo "<CENTER>\n";
  }

  if ($formulario['Coluna que indica a condição de exclusão'])
    $form['deleteCondition'] = $formulario['Coluna que indica a condição de exclusão'];

  /*
   $references[0]="";
   $references[1]="";
   $references[1]['table'] = "unidades";
   $references[1]['key'] = "apelido";
   $references[1]['value'] = "apelido";
  */
  if (trim($formulario['Dicas (tabela)']))
    $dicas_tabela = json_decode(trim($formulario['Dicas (tabela)']));
  else
    $dicas_tabela = "";

  //echo "<PRE>"; var_dump($dicas_tabela); echo "</PRE>";
  $doNotRemoveOrderBy = true;

  if (isset($formulario['Pivotar esta visualização'])
      && $formulario['Pivotar esta visualização'] == 't')
    $pivotar = true;
  else 
    $pivotar = false;

  //echo $_POST['botao'];
  //echo $stringNovo;
  //echo $_POST['buttonrow'];
  //if ($isdeveloper) echo "<PRE>" . print_r($query, true)  . "</PRE>";
  if (!$orderBy)
    $showQueryResult = show_query($query, $conn, $formulario['ordenarpor'],
	       $desc, $formata,
	       $references, $form, $boolean, $link, $destak,
	       $extraGet, $hideByQuery, $showNum, $boldCondition,
	       $secondOrder, $limite, $totalRowCollum, 
								  $doNotRemoveOrderBy, $dicas_tabela, $pivotar, $feminino, $termo, $termos);
  else
    $showQueryResult = show_query($query, $conn, $orderBy,
	       $desc, $formata,
	       $references, $form, $boolean, $link, $destak,
	       $extraGet, $hideByQuery, $showNum, $boldCondition,
	       $secondOrder, $limite, $totalRowCollum,
								  $doNotRemoveOrderBy, $dicas_tabela, $pivotar, $feminino, $termo, $termos);
  if ($showQueryResult['num_rows'] > 40){
    if ($form["name"]){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringNovo . "\" NAME=\"botao\">\n";
      if ($form['saveForm']){
	echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringSalvar . "\" NAME=\"botao\">\n";
      }
      if ($form['delete']){
	echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringRemover . "\"\n";
	echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
      }
      if ($form['duplicar']){
	echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringDuplicar . "\"\n";
	echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
      }
    }
    echo "    </FORM>\n";
  }
  echo "</CENTER>\n";
 }
 }

if ($innerResult){
//echo "</CENTER><PRE>" . $innerQuery . "</PRE><CENTER>";
$campos = pg_fetch_all($innerResult);
//echo "</CENTER><PRE>" . print_r($campos, true) . "</PRE><CENTER>";
// foreach($campos as $campo){
//   echo fixField($campo['attname']) . "<BR>";
// }
?>
<script>
<?PHP
foreach($campos as $campo){
  echo "var " . fixField($campo['attname']) . "_last_display_value = document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display;\n";
  //  echo "console.log('" . fixField($campo['attname']) . "_last_display_value: ', " . fixField($campo['attname']) . "_last_display_value);\n";
  //  echo "console.log('document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display: ', document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display);\n";
}
foreach($NNtables as $NNkey => $NNtable){
  if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
    echo "var ";
    echo fixID($NNtable['table_name']) . "_";  
    echo fixID($NNtable['relations'][1]['foreign_table_name']) . "_last_display_value = document.getElementById(\"onde_div_";
    echo fixID($NNtable['table_name']) . "_";
    echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display;\n";
  }
}
?>
function reordenar(){
  <?PHP		 
  foreach($campos as $campo){
    echo "" . fixField($campo['attname']) . "_last_display_value = document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display;\n";
    //echo "console.log('" . fixField($campo['attname']) . "_last_display_value: ', " . fixField($campo['attname']) . "_last_display_value);\n";
    echo fixField($campo['attname']) . "_last_display_value = document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display = 'block';\n";
    echo "console.log('document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display: ', document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display);\n";
	}
    foreach($NNtables as $NNkey => $NNtable){
      if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
	echo "console.log(\"onde_div_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\");\n";

	echo fixID($NNtable['table_name']) . "_";      
	echo "" . fixID($NNtable['relations'][1]['foreign_table_name']);
	echo "_last_display_value = document.getElementById(\"onde_div_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display;\n";

	echo "document.getElementById(\"onde_div_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display = 'block';\n";
      }			
    }
?>

$( "#sortable" ).sortable({
		update: function( event, ui ) {
          var sorted = $( "#sortable" ).sortable( "serialize", { key: "sort" } );
          //console.log("sorted: ", sorted);
          //console.log("event: ", event);
          //console.log("ui: ", ui);
        }
    });
<?PHP
  foreach($campos as $campo){
    //echo "console.log('" . fixField($campo['attname']) . "');\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").classList.add(\"ui-icon\");\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").classList.add(\"ui-icon-grip-dotted-vertical\");\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").style.cursor=\"grab\";\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").innerHTML=\"&nbsp;&nbsp;&nbsp;\";\n";
    echo "document.getElementById(\"onde_span_index_" . fixField($campo['attname']) . "\").style.display=\"inline-block\";\n";
  }
    foreach($NNtables as $NNkey => $NNtable){
      if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
	echo "console.log(\"onde_span_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\");";
	echo "document.getElementById(\"onde_span_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").classList.add(\"ui-icon\");\n";
	echo "document.getElementById(\"onde_span_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").classList.add(\"ui-icon-grip-dotted-vertical\");\n";
	echo "document.getElementById(\"onde_span_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name'])  . "\").style.cursor=\"grab\";\n";
	echo "document.getElementById(\"onde_span_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").innerHTML=\"&nbsp;&nbsp;&nbsp;\";\n";
	echo "document.getElementById(\"onde_span_index_";
	echo fixID($NNtable['table_name']) . "_";      
	echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display=\"inline-block\";\n";
      }
    }
?>
}
function salva_ordem(){
  var sorted = $( "#sortable" ).sortable( "toArray" );
  console.log("sorted: ", sorted);
  //$.getJSON("form_save_order.php?callback=?", 
  $.post("form_save_order.php", 
     {codigo: <?PHP echo $formulario['codigo']; ?>,
	    sorted: sorted, 
      action:"save"}, function(data){
       $.each(data, function(i, val){
				 //teste.push(val.query);
         //alert('DENTRO\nId:' + teste + '\nID:' + id);
				 //console.log("i.", i);
				 //console.log("data.", val);
       });
  });

  <?PHP
  foreach($campos as $campo){
    echo "document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display = " . fixField($campo['attname']) . "_last_display_value;\n";
    //    echo "console.log('document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display: ', document.getElementById(\"onde_div_" . fixField($campo['attname']) . "\").style.display);\n";
  }
  foreach($NNtables as $NNkey => $NNtable){
    if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
      echo "document.getElementById(\"onde_div_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display = ";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name']) . "_last_display_value;\n";
    }
  }
?>
		
  $( "#sortable" ).sortable("destroy");
<?PHP
  foreach($campos as $campo){
    //echo "console.log('" . fixField($campo['attname']) . "');\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").classList.remove(\"ui-icon\");\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").classList.remove(\"ui-icon-grip-dotted-vertical\");\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").style.cursor=\"auto\";\n";
    echo "document.getElementById(\"onde_span_" . fixField($campo['attname']) . "\").innerHTML=\"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\";\n";
    echo "document.getElementById(\"onde_span_index_" . fixField($campo['attname']) . "\").style.display=\"none\";\n";
  }
  foreach($NNtables as $NNkey => $NNtable){
    if ($NNtable['lines'] == 2 && $NNtable['size']  >= 2 && $NNtable['size'] <= 3){
      echo "document.getElementById(\"onde_span_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name'])  . "\").classList.remove(\"ui-icon\");\n";
      echo "document.getElementById(\"onde_span_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name'])  . "\").classList.remove(\"ui-icon-grip-dotted-vertical\");\n";
      echo "document.getElementById(\"onde_span_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name'])  . "\").style.cursor=\"auto\";\n";
      echo "document.getElementById(\"onde_span_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name'])  . "\").innerHTML=\"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\";\n";
      echo "document.getElementById(\"onde_span_index_";
      echo fixID($NNtable['table_name']) . "_";      
      echo fixID($NNtable['relations'][1]['foreign_table_name']) . "\").style.display=\"none\";\n";
    }
  }
  
?>
}
</script>
<?PHP
  }
include "page_footer.inc";
//echo "  <button id=novo>Novo</button>\n";

/*  <script> */

/* $(function() { */
/*     $( "input" ) */
/*       .button() */
/*       .click(function( event ) { */
/* 	  event.preventDefault(); */
/* 	}); */
/*   }); */

/* </script> */

  /*
   SQL to list all the tables that reference a particular column in a table
   http://stackoverflow.com/questions/5347050/sql-to-list-all-the-tables-that-reference-a-particular-column-in-a-table
   select R.TABLE_NAME
   from INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE u
   inner join INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS FK
   on U.CONSTRAINT_CATALOG = FK.UNIQUE_CONSTRAINT_CATALOG
   and U.CONSTRAINT_SCHEMA = FK.UNIQUE_CONSTRAINT_SCHEMA
   and U.CONSTRAINT_NAME = FK.UNIQUE_CONSTRAINT_NAME
   inner join INFORMATION_SCHEMA.KEY_COLUMN_USAGE R
   ON R.CONSTRAINT_CATALOG = FK.CONSTRAINT_CATALOG
   AND R.CONSTRAINT_SCHEMA = FK.CONSTRAINT_SCHEMA
   AND R.CONSTRAINT_NAME = FK.CONSTRAINT_NAME
   WHERE U.COLUMN_NAME = 'a'
   AND U.TABLE_CATALOG = 'b'
   AND U.TABLE_SCHEMA = 'c'
   AND U.TABLE_NAME = 'd'


   -- List if a column is nullable
   SELECT column_name, is_nullable
   FROM  INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = 'table'
     AND table_catalog = 'database_name'

   -- Lists the foregin keys of a table
   SELECT
   tc.constraint_name, tc.table_name, kcu.column_name,
   ccu.table_name AS foreign_table_name,
   ccu.column_name AS foreign_column_name
   FROM
   information_schema.table_constraints AS tc
   JOIN information_schema.key_column_usage AS kcu
   ON tc.constraint_name = kcu.constraint_name
   JOIN information_schema.constraint_column_usage AS ccu
   ON ccu.constraint_name = tc.constraint_name
   WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='menus_grupos';

   -- lists the tables which have foregin keys pointing to a table
   SELECT tc.table_schema, tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name
   AS foreign_table_name, ccu.column_name AS foreign_column_name
   FROM information_schema.table_constraints tc
   JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
   JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name
   WHERE constraint_type = 'FOREIGN KEY'
   AND ccu.table_name='menus'


O clearence check tem que inibir incluir ou salvar
pode ser uma variavel
na exibicao e montagem do form, consulta a leitura
nos botoes de insert e save, verifica a escrita
e na hora de executar o update ou insert ou delete, verifica o escrita
as permissoes de escrita tambem controla o duplicar e o excluir

  */
?>
