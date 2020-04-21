<?PHP
  /**
   * Atividade incompleta
   * Acho que OK, tem que testar mais...
   * detectar o tipo de dado da chave quando tem relacao N:N pra inserir (isso eh no
   * procurar "<BR>AQUI"
   * no page_header, livar o count($_POST['toggle']
   *
   * Acho que estah corrigido. (tem que testar se não estragou outras coisas...
   * em algum lugar estah inundando o $_POST['toggle']... tem que limpar..
   * o $_POST['toggle'] só enche quando estah em modo de debug!!
   *
   * forms.php
   * include/lib.inc
   * include/page_header.inc
   *
   */

  /***
   * SUPORTE A ARQUIVOS E A IMAGENS
   * http://www.sumedh.info/articles/store-upload-image-postgres-php.html
   * http://stackoverflow.com/questions/22210612/display-image-from-postgresql-database-in-php

   * Tabela com metadados do arquivo
   - Utilizar a documents
   - alter table documents add column data bytea;
   * Incluir nova flag no forms para
   - alter table forms add column "Permitir anexos" boolean not null default false

   * http://stackoverflow.com/questions/21856137/php-image-upload-to-server-and-save-path-to-the-postgresql-database
   */

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
  */
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);

$mostraForm = true;

include_once("startup.inc"); // Neste include carrega o conf e conecta com o banco.
$codigo = intval($_GET['form']);
$query  = "SELECT * FROM forms WHERE codigo = " . $codigo;
$resultFORMULARIO = pg_exec ($conn, $query);
$formulario  = pg_fetch_array ($resultFORMULARIO, 0);

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

include_once("masterFormStartup.inc");
include "start_sessao.inc";
include "page_header.inc";

$orderBy = pg_escape_string($_GET['orderby']);
$desc = pg_escape_string($_GET['desc']);

if ( $isdeveloper ){
  echo "<div class=\"developerEditToolBar\">\n";
  echo "<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=6&buttonrow[" . $codigo . "]=detalhes";
  foreach ($_GET['toggle'] as $value)
    echo "&toggle[]=" . $value;  
  echo "\">Editar este formulário</a>\n";
  echo "</DIV>\n";
}


$argumentKey = 0;

if(isset($_GET['buttonrow']) && !isset($_POST['buttonrow'])){
  $_POST['buttonrow'] = $_GET['buttonrow'];
 }

if (isset($_POST['buttonrow']) 
    && trim($formulario['formulario'])
    && $formulario['Apenas form, sem tabela'] == 't'
    && $formulario['Não exigir login para este formulário'] == 't'){
  unset($_POST['buttonrow']);
  unset($_GET['buttonrow']);
}

//echo $formulario['formulario'] . "<BR>";
//echo $formulario['Apenas form, sem tabela'] . "<BR>";
//echo $formulario['Não exigir login para este formulário'] . "<BR>";
 

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
  $argumentKey++;
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
    $argumentKey++;
  }
}

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
  case 'varchar':
  case 'timestamp':
  case 'date':
  case 'char':
    $charIndicator = "'";
    break;
  case 'int4':
  case 'int8':
    $array_row_0 = intval($array_row_0);
  default:
    $charIndicator = "";
  }

  $getCaption  = "SELECT " . ($referencedCaption ? $referencedCaption : 'nome') . " FROM \"" . $relations['Array']['referenced'] . "\"";
  $getCaption .= "  WHERE " . ($relations['Array']['referencedfield'] ? $relations['Array']['referencedfield'] : 'codigo') . " = ";
  $getCaption .= $charIndicator;
  $getCaption .= $array_row_0;
  $getCaption .= $charIndicator;
  $getCaptionResult = pg_exec ($conn, $getCaption);
  $getCaptionRow = pg_fetch_row ($getCaptionResult, 0);

  if ($_debug > 1) echo "<PRE>" . $getCaption . "</PRE>\n";
  return $getCaptionRow[0];
}

/// fim do bloco das funcoes.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

if ($formulario['Termo para botões CRUD']){
  $termo = $formulario['Termo para botões CRUD'];
 }
if ($formulario['Termo para botões CRUD (plural)']){
  $termos = $formulario['Termo para botões CRUD (plural)'];
 }
if ($formulario['Tratar termo CRUD no feminino']){
  $feminino = $formulario['Tratar termo CRUD no feminino'];
 }

$stringNovo =  "Nov" . ($feminino == 't' ? "a" : "o") . ($termo ? " " . $termo : "");
$stringRemover = "Remover " . ($termos ? " " . $termos . "  " : " linhas ") . "marcad" . ($feminino == 't' ? "a" : "o") . "s";
$stringDuplicar = "Duplicar " . ($termos ? " " . $termos . "  " : " linhas ") . "marcad" . ($feminino == 't' ? "a" : "o") . "s";

//echo "<PRE>"; var_dump($formulario); echo "</PRE>";
//echo $formulario["Esconde primeira coluna"];

if (trim($formulario['Reference Captions'])){
  $ReferencedCaptions = explode(',', $formulario['Reference Captions']);
  for($i=0;$i<count($ReferencedCaptions);$i++)
    $ReferencedCaptions[$i]=str_replace(";", ",", $ReferencedCaptions[$i]);
 }
if (trim($formulario['Reference filters'])){
  $ReferencedFilters = explode(',', $formulario['Reference filters']);
  for($i=0;$i<count($ReferencedFilters);$i++)
    $ReferencedFilters[$i]=str_replace(";", ",", $ReferencedFilters[$i]);
 }
if (trim($formulario['Reference onChange functions'])){
  $referenceOnChangeFunctions = explode(',', $formulario['Reference onChange functions']);
  //for($i=0;$i<count($referencedOnChangeFunctions);$i++)
  //  $referenceOnChangeFunctions[$i]=str_replace(";", ",", $referenceOnChangeFunctions[$i]);
 }

if (trim($formulario['titulo'])){
  if (isset($queryarguments))
    foreach($queryarguments as $queryargument)
      $formulario['titulo'] = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $formulario['titulo']);
  
  echo "<DIV CLASS=\"titulo\">" . $formulario['titulo'] . "</DIV>\n<BR>\n";
 }

if ($_debug > 1) echo "input_vars = " .     count($_POST) . "<BR>\n";

if ($formulario['Mostra botão para exportar para CSV']=='t'){
  echo "<a href=\"exportToExcell.php?form=" . $codigo;
  if (isset($_GET['args']))
    foreach($_GET['args'] as $argkey => $argvalue)
      echo "&args[" . $argkey . "]=" . $argvalue;

  
  if ($orderBy) echo "&orderby=" . trim($orderBy);
  if ($desc) echo "&desc=" . intval($desc);
  echo "\">";
  echo "<img src=\"images/export_to_excell2.gif\" border=0></a>";
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

  if (isset($queryarguments))
    foreach($queryarguments as $queryargument)
      $query = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $query);

  // echo "<PRE>\$_GET['strvalues']:\n"; var_dump($_GET['strvalues']); echo "</PRE>";
  // echo "<PRE>\$queryarguments:\n"; var_dump($queryarguments); echo "</PRE>";

  //echo "<PRE>" . $query . "</PRE>";
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
  }
}

if (trim($formulario['Campo(s) para utilizar como etiqueta em relações N:N'])){
  $NNCaptions = explode(',', $formulario['Campo(s) para utilizar como etiqueta em relações N:N']);
  for($i=0;$i<count($NNCaptions);$i++)
    $NNCaptions[$i]=str_replace(";", ",", $NNCaptions[$i]);
 }

echo "<CENTER>\n";

if (trim($formulario['formata']))
  $formata = explode(',', $formulario['formata']);

if ($formulario['descendent']=="t" && !is_numeric($desc) )
  $desc = 1;

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
    $form['action']  = call_user_func($formulario['funcao'], $str);
  }
  $form['action'] .= "?form=" . $codigo;
  foreach ($_GET['toggle'] as $value)
    $form['action'] .= "&toggle[]=" . $value;
  foreach ($_GET['args'] as $value)
    $form['action'] .= "&args[]=" . $value;

  if ($orderBy) $form['action'] .= "&orderby=" . $orderBy . "&desc=" . $desc;

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
    $code = round(time() / 10 * rand(1,10));
    foreach($_FILES as $field => $file){
      //echo "<B>" . $field . "</B><BR>\n";
      
      $fileArray['name'] = $file['name'];
      $fileArray['type'] = $file['type'];
      //echo "filename: " . $file['tmp_name'] . "<BR>\n";
      $fileArray['contents'] = file_get_contents($file['tmp_name']); //////// Lê o conteúdo da imagem principal
      if (!$fileArray['contents'] && !$file['error'])
        Warning("Não foi possível carregar o arquivo para o campo " . $field . ".");

      $fileData = formsEncodeFile($fileArray);
      $_POST[$field] = $fileData;
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
  $resultNN = pg_Exec($conn, $queryNN);
  $NNtables = pg_fetch_all($resultNN);
  if ($_debug > 1) show_query($queryNN, $conn);
  //var_dump($NNtables);

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
    if ($_debug > 1) echo "TAMANHO DE " . $NNtable['table_name'] . " = " . $tamanho_linhas[0] . "<BR>\n";
    $NNtables[$NNkey]['size'] = $tamanho_linhas[0];


    //>>>>>>>>>>>>>>>>>>>>>>>      3. A tabela que tiver apenas 2 chaves estrangeiras, eh uma relacao N:N
    if ($NNtables[$NNkey]['lines'] == 2 && $NNtables[$NNkey]['size'] == 2){
      //echo "PASSEI";
      $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

      // "SELECT a.attname, t.typname, a.atttypmod\n"
      // "  FROM pg_attribute as a, pg_type as t\"n
      // "  WHERE attrelid = 78214 AND\n"
      // "        attstattarget<>0 AND t.oid=a.atttypid\n";

      // Confere se campos codigo e nome existesm, se nao existirem, pega a chave e o primeiro campo depois da chave
      $queryCheckBoxes  = "SELECT codigo, nome,\n";
      $queryCheckBoxes .= "  (select case when \"" . $NNrelations[1]['foreign_table_name'] . "\".";
      $queryCheckBoxes .= $NNrelations[1]['foreign_column_name'] . " = " . $NNtable['table_name'] . ".";
      $queryCheckBoxes .= $NNrelations[1]['column_name'] . " then true else false end\n";
      $queryCheckBoxes .= "    from \"" . $NNtable['table_name'] . "\" \n";

      if (isset($_POST['buttonrow'])){
	$queryCheckBoxes .= "    where " . $NNtable['table_name'] . "." . $NNrelations[0]['column_name'] . " = ";
	reset($_POST['buttonrow']);
	foreach($_POST['buttonrow'] as $buttonrow_key => $buttonrow_val){
	  if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	    $queryCheckBoxes  .= intval($buttonrow_key);
	  else
	    $queryCheckBoxes  .= "'" . pg_escape_string($buttonrow_key) . "'";
	}
      }
      $queryCheckBoxes .= ") as checked\n";
      $queryCheckBoxes .= "FROM  \"" . $NNrelations[1]['foreign_table_name'] . "\"\n";

      //$queryCheckBoxes  = "SELECT " . $NNrelation['foreign_column_name'] . "\n";
      //$queryCheckBoxes .= "from " . $NNrelation['foreign_table_name'];
      //var_dump($row);
      //if ($_debug)
      //show_query($queryCheckBoxes, $conn);
      $checkBoxesResult = pg_Exec($conn, $queryCheckBoxes);
      $checkBoxes = pg_fetch_all($checkBoxesResult);
      //echo "<PRE>" . $queryCheckBoxes . "</PRE>\n";
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
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  if ($_POST['envia']==" Salvar "  ||
      $_POST['envia']==" Inserir " ||
      $_POST['envia']==" Enviar " ){

    if ($formulario['Enviar email para notificações']=='t' && $emailEvent && $emailTemplate){
      require("class.phpmailer.php");
      require_once('class.html2text.inc');
      $emailTemplate['Introdução'] = str_replace("\\", "", $emailTemplate['Introdução']);
      $emailTemplate['Rodapé'] = str_replace("\\", "", $emailTemplate['Rodapé']);

      ///////////////////////////////////////////////////////////////////////////////
      $innerQuery = $dataDictionary;
      $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
      if ($_debug) show_query($innerQuery, $conn);
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
      $innerTotal  = pg_numrows($innerResult);
      $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query']);
      if ($queryIncluiLinha){
        $whereString .= $campos[intval($formulario['chave'])] . " = ";
        if (strpos("_" . $row[1], "int") && $row[1] != 'interval')// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<< aqui
          $whereString .= intval($_POST[fixField($row[0])]);
        else
          $whereString .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
        $queryIncluiLinha = str_replace("/*where*/", $whereString, $queryIncluiLinha);
        //echo "<PRE>" . htmlentities($whereString) . "</PRE>";
        //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";

        if (isset($queryarguments))
          foreach($queryarguments as $queryargument)
            $queryIncluiLinha = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $queryIncluiLinha);

        if ($_debug) show_query($queryIncluiLinha, $conn);
        $resultIncluiLinha = pg_exec ($conn, $queryIncluiLinha);
        if (pg_numrows($resultIncluiLinha)){
          $rowIncluiLinha = pg_fetch_row ($resultIncluiLinha, 0);
          //echo "<PRE>" . htmlentities($rowIncluiLinha[0]) . "</PRE>"
          $pattern = '/<IMG.*?(>)/i';
          $replacement = '';
          $rowIncluiLinha[0] =  preg_replace($pattern, $replacement, $rowIncluiLinha[0]);
        }
      }
      $html .= $rowIncluiLinha[0];


      while ($linhas<$innerTotal){
	$row = pg_fetch_row ($innerResult, $linhas);
        $relations = checkRelations($linhas);
        if ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
	  if ($relations['total']){
	    //$row[1] = "string";
	    $relations['Array'] = pg_fetch_array ($relations['result'], 0);
	    $caption = getReferencedCaption($relations, $ReferencedCaptions[$linhas], $_POST[fixField($row[0])]);
	    $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B><BR>";
	    $html .= "" . htmlspecialchars_decode($caption, ENT_QUOTES) . "<BR>\n";
	  }
	  else
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
		    $html .= $row[0] . ":&nbsp;";
		    $html .= str_replace(".", ",", floatval(    str_replace(",", ".", trim($_POST[fixField($row[0])]))    )) . "<BR>\n";
   		    //$html .= "<B>Teste: " . $_POST[fixField($row[0])] . "<B><BR>";

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
		  }
		  else{
                    if ($row[1] != 'bytea'){
		      $html .= "<B>" . mb_ucfirst($row[0], $encoding) . ":</B><BR>";
		      $html .= "" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "<BR>\n";
		    }
                    
		  }
	    }
	$linhas++;
      }
      $html .= "<BR>\n" . $emailTemplate['Rodapé'] . "<BR>\n";

      $h2t = new \Html2Text\Html2Text($html);
      $text = $h2t->get_text();
      /////////////////////////////////////////////////////////////////////////////


      $mail = new PHPMailer();
      $mail->From     = $emailTemplate['Endereço do remetente'] ? $emailTemplate['Endereço do remetente'] : $system_mail_from;
      $mail->FromName = $emailTemplate['Nome do remetente'] ? $emailTemplate['Nome do remetente'] : $system_mail_from_name;
      $mail->Host     = $system_mail_host;
      $mail->Mailer   = $system_mail_mailer;
      $mail->CharSet = $encoding;

      if (trim($emailTemplate['Enviar confirmação de recebimento para']))
        $mail->ConfirmReadingTo = trim($emailTemplate['Enviar confirmação de recebimento para']);

      $mail->Subject  = stripAccents($emailTemplate['assunto'] ? $emailTemplate['assunto'] : "[IDÉIA] Protocolo de protótipo");
      // Plain text body (for mail clients that cannot read HTML)

      $htmlTemp = stripslashes($html);
      $imageFilesToSend = getHtmlImage($htmlTemp);
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

      //$mail->AddEmbeddedImage('images/logo.png', '1272542224.13304.1.camel@brainstorm', 'images/logo.png');

      $imagekey = 0;
      foreach ($imageFilesToSend as $imageFileToSend){
        $imagekey++;
        $mail->AddEmbeddedImage($imageFileToSend, '1272542224.13304.' . $imagekey . '.camel@brainstorm', str_replace("session_files/", "", $imageFileToSend));
        //echo htmlentities($imageFileToSend) . "<BR>\n";
      }

      $queryEmailLog  = "INSERT INTO formsemaillog (tabela, email, form, row) VALUES (";
      $queryEmailLog .= "'" . $formulario['tabela'] . "', ";
      $queryEmailLog .= "'" . $emailTemplate['Endereço do destinatário'] . "', ";
      $queryEmailLog .= intval($codigo) . ", ";
    }
  }

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
	  if ($row[1] == 'bytea' && $_POST[fixField($row[0])])  $queryUPDATE .= "  \"" . $row[0] . "\" = ";
	      
	  ///////////////////////////////////////////////////////////////////////////// <<<< AQUI
	  if ( (strpos("_" . $row[1], "int") && $row[1] != "interval") || strpos("_" . $row[1], "float")){
  	    if (strpos("_" . $row[1], "int"))
              if (trim($_POST[fixField($row[0])])=='')
                $queryUPDATE .= " NULL ";
              else
  	        $queryUPDATE .= intval($_POST[fixField($row[0])]);
            else
              if (strpos("_" . $row[1], "float"))
  	        $queryUPDATE .= floatval(str_replace(",", ".", $_POST[fixField($row[0])]));
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
		$queryUPDATE .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
	  // Se usar o pg_escape_string no update e insert deve se usar o stripslashes no echo
	}
      }
      $linhas++;
    }
    $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

    $queryUPDATE .= "\nWHERE " . $row[0] . " = ";
    if (strpos("_" . $row[1], "int") && $row[1] != "interval")
      $queryUPDATE .= intval($_POST[fixField($row[0])]);
    else
      $queryUPDATE .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";

    if ($_debug) echo "</CENTER><PRE>" . $queryUPDATE . "</PRE><CENTER>\n";
    if ($_POST['envia'] != " Inserir "){
      $result = pg_exec ($conn, $queryUPDATE);
      if (!$result){
        Warning("Erro atualizando " . ($termo ? $termo : "formumlário") . "!\n<PRE>" . pg_last_error(). "</PRE>");      
        //Warning("Erro atualizando formulário!\n<PRE>" . pg_last_error(). "</PRE>");
        include "page_footer.inc";
        exit(1);
      }
      else{
      echo "</CENTER>\n";
        echo "<DIV CLASS=\"message\">" . ($termo ? mb_ucfirst($termo, $encoding) : "Formumlário") . " atualizad" . ($feminino =='t' ? "a" : "o") . " com sucesso.</DIV>\n";
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

      if ($emailTemplate['Destinatários a partir de SQL (campos name, email)']){
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

  if ($_POST['envia']==" Inserir "){
    $queryPrepare = "set DateStyle='DMY'";
    $prepareResult = pg_exec ($conn, $queryPrepare);

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    //$innerQuery.= " AND\n    t.tablename='forms'";

    if ($_debug)  show_query($innerQuery, $conn);
    $innerResult = pg_exec ($conn, $innerQuery);
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
      if ($NNtable['lines'] == 2 && $NNtable['size'] == 2){
	$inserirCampoChave = true;
      }
    }
    while ($linhas<$innerTotal){
      $row = pg_fetch_row ($innerResult, $linhas);
      //echo "fixField(\$row[0]): " . fixField($row[0]) . "<BR>\n";       
      //echo "<B>\$_POST[fixField(\$row[0]): " . $_POST[fixField($row[0])] . " " . $linhas . " " . $formulario['chave'] . "</B><BR>\n";
      if ( $_POST[fixField($row[0])] && (($linhas!=intval($formulario['chave'])) || $inserirCampoChave) ){
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
    //$eq = new eqEOS();    
    while ($linhas<$innerTotal){
      $row = pg_fetch_row ($innerResult, $linhas);
      if ( $_POST[fixField($row[0])] && ($linhas!=intval($formulario['chave']) || $inserirCampoChave) ){
	$campos++;
	if ($campos>1)
	  $queryINSERT .= ",\n";
	if ($row[0]!=trim($formulario['Campo para salvar usuário logado'])){	  
	  if (strpos("_" . $row[1], "int") && $row[1] != "interval"){
	    
	    //$valorCalculado = round($eq->solveIF($_POST[fixField($row[0])]));
	    //echo "<B>" . $valorCalculado . "</B>";
	    $queryINSERT .= intval($_POST[fixField($row[0])]);
	  }
	  else
	    if (strpos("_" . $row[1], "float"))
	      $queryINSERT .= floatval(str_replace(",", ".", $_POST[fixField($row[0])]));
	    else{
	      // $row[0] eh o nome do campo (que tambem é a label que está no form)
	      // $row[1] eh o valor
	      // No caso de existirem arquivos, o que será incluido é o conteúdo deles.
	      // Verificar com a funcao
	      //    if (is_uploaded_file ($_FILES['arquivo']['tmp_name'])){

	      //$queryINSERT .= "'" . htmlspecialchars_decode($_POST[fixField($row[0])], ENT_QUOTES) . "'";
	      //$queryINSERT .= "'" . $_POST[fixField($row[0])] . "'";
	      $valueToInsert  =  "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";
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

    if ($_debug) echo "</CENTER><PRE>" . $queryINSERT . "</PRE><CENTER>\n";
    if ($campos){
      $result = pg_exec ($conn, $queryINSERT);
      echo "</CENTER>\n";
      if (!$result){
	Warning("Erro enviando " . ($termo ? $termo : "formumlário") . "!\n<PRE>" . pg_last_error(). "</PRE>");
	include "page_footer.inc";
	exit(1);
      }
      else{
        echo "</CENTER>\n";
        echo "<DIV CLASS=\"message\">" . ($termo ? mb_ucfirst($termo, $encoding) : "Formumlário") . " enviad" . ($feminino =='t' ? "a" : "o") . " com sucesso.</DIV>\n";	
      //echo "<DIV CLASS=\"message\">Formulário atualizado com sucesso.</DIV>\n";
        $mostraForm = false;
      }
    }
    else{
      echo "</CENTER>\n";
      echo "<DIV CLASS=\"schedulled\">Nenhum campo foi preenchido. Nada foi inserido no bando de dados.</DIV>\n";
    }

    //echo "PASSEI<BR>";
    //echo "\$emailEvent: " . $emailEvent . "<BR>";

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
      $mail->ClearAddresses();
      $mail->ClearAttachments();
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

      if (count($addresses)){
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
    echo "<CENTER>\n";
  }

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
      foreach($NNtables as $NNkey => $NNtable){
	if ($NNtable['lines'] == 2 && $NNtable['size'] == 2){
	  $row = pg_fetch_row ($innerResult, intval($formulario['chave']));

          $result = pg_exec($conn, "BEGIN");
	  if ($_debug){
	    echo "<PRE>PASSEI\n\n";
	    echo "campo: " . fixField($NNtable['relations'][1]['foreign_table_name']) . "\n";
	    var_dump($_POST[ fixField($NNtable['relations'][1]['foreign_table_name']) ] );
	  
	    echo "campo: lastState_" . fixField($NNtable['relations'][1]['foreign_table_name']) . "\n";
	    var_dump($_POST[ "lastState_" . fixField($NNtable['relations'][1]['foreign_table_name']) ] );
	    echo "</PRE>\n";
	  }
	  if ( !is_null($_POST[ fixField($NNtable['relations'][1]['foreign_table_name']) ]) ||
               !is_null($_POST[ "lastState_" . $NNtable['relations'][1]['foreign_table_name'] ])
	       ){
	    //echo "<PRE>PASSEI -- deletando...\n\n";
	    $queryDelete  = "DELETE FROM \"" . $NNtable['table_name'] . "\" WHERE \"";
	    $queryDelete .= $NNtable['relations'][0]['column_name'] . "\" = '" .  intval($_POST[$row[0]]) . "'";
	    $resultDelete = pg_exec($conn, $queryDelete);
	    $erro = 0;
	    if ($_debug) echo "<PRE>" . $queryDelete . "</PRE>";
	    if (!$resultDelete){
	      $resultDelete = pg_exec($conn, "ROLLBACK");
	      $erro++;
	      warning("Erro deletando " . $NNtable['relations'][1]['column_name'] . " do " . $NNtable['relations'][0]['column_name'] .
  	  	      "!<BR>\nOpera&ccedil;&atilde;o desfeita!" . ($_debug ? "<PRE>" . pg_last_error() . "</PRE>" : ""));
  	      //break;
	    }
	  
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
	    //echo "<PRE>"; var_dump($dataType); echo "</PRE>";

	    foreach($_POST[ fixField($NNtable['relations'][1]['foreign_table_name']) ] as $campo => $valores){
	      while (list($key, $val) = each($valores)) {
	        $queryINSERT  = "INSERT INTO \"" . $NNtable['table_name'] . "\"(\"". $NNtable['relations'][0]['column_name'];
	        $queryINSERT .= "\", \"" . $NNtable['relations'][1]['column_name'] . "\") VALUES (";
	        if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		  $queryINSERT .= intval($_POST[fixField($row[0])]);
	        else
		  $queryINSERT .= "'" . pg_escape_string($_POST[fixField($row[0])]) . "'";

	        $queryINSERT .= ", ";
	        if (!(strpos("_" . $dataType[0], "int") && $dataType[0]  != "interval"))
	          $queryINSERT .= "'";
	        $queryINSERT .= $key;
	        if (!(strpos("_" . $dataType[0], "int") && $dataType[0]  != "interval"))
	          $queryINSERT .= "'";
	        $queryINSERT .= ")";

                //echo "<PRE>" . $queryINSERT . "</PRE>";

	        $resultINSERT = pg_exec($conn, $queryINSERT);
	        if ($_debug) echo "<PRE>" . $queryINSERT . "</PRE>\n";
	        if (!$result){
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
	      $result = pg_exec($conn, "ROLLBACK");
	      warning("Erro atualizando " . $NNtable['relations'][1]['column_name'] . " do " . $NNtable['relations'][0]['column_name'] .
		      "!<BR>\nOpera&ccedil;&atilde;o desfeita!" . ($_debug ? "<PRE>" . pg_last_error() . "</PRE>" : ""));
	    }
	    else{
	      $result = pg_exec($conn, "COMMIT");
	      //echo "      <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	      echo "      <DIV CLASS=\"message\">" . trim(mb_ucfirst($NNtable['relations'][1]['column_name'], $encoding));
	      echo " salvo com sucesso!</DIV>\n";
	    }
	  }  
	}
      }
      echo "<CENTER>\n";
  }
  if (isset($_POST['CloneCheckBox']) &&
      substr(trim($_POST['botao']), 0, 8)=="Duplicar"){

    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $formulario['tabela'] . "'";
    $innerResult = pg_exec ($conn, $innerQuery);

    $row = pg_fetch_row ($innerResult, $formulario['chave']);

    //echo "</CENTER><PRE>"; var_dump($row); echo "</PRE><CENTER>";

    $clone = $_POST['CloneCheckBox'];
    pg_Exec ($conn, "BEGIN"); // Inicia a transacao
    if ($_debug) echo "</CENTER><PRE>\n";
    while (list($key, $val) = each($clone)) {
      if ($_debug) echo $key . " = " . $clone[$key] . "\n";
      if  ($clone[$key]){
	$query_liga  = "INSERT INTO \"" . trim($formulario['tabela']) . "\" (\n      ";

	$total  = pg_numrows($innerResult);
	if ($total){
	  $linhas = 0;
	  while ($linhas<$total){
	    if ($linhas <> $formulario['chave']){
	      $clone_row = pg_fetch_row ($innerResult, $linhas);
	      $query_liga .= "\"" . $clone_row[0] . "\"";
	      if ($linhas+1 < $total)
		$query_liga .= ", ";
	      $query_liga .= "\n      ";
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
	      $query_liga .= "\"" . $clone_row[0] . "\"";
	      if ($linhas+1 < $total)
		$query_liga .= ", ";
	      $query_liga .= "\n      ";
	    }
	    $linhas++;
	  }
	}
	$query_liga .= " FROM \"" . trim($formulario['tabela']) . "\" ";

	$query_liga .= "WHERE " . $row[0] . " = ";
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
	pg_Exec ($conn, "ROLLBACK");
	echo "</CENTER>";      
	messageBar("busy", "Falhou duplicando " . ($termos ? $termos : "itens") . " marcad" . ($feminino =='t' ? "as" : "os") . ". Ação desfeita.");
	echo "<CENTER>";
	break;
      }
    }
    pg_Exec ($conn, "COMMIT");
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
    pg_Exec ($conn, "BEGIN"); // Inicia a transacao
    if ($_debug) echo "</CENTER><PRE>\n";
    while (list($key, $val) = each($delete)) {
      if ($_debug) echo $key . " = " . $delete[$key] . "\n";
      if  ($delete[$key]){
	$query_liga  = "DELETE FROM \"" . trim($formulario['tabela']) . "\"\n";
	$query_liga .= "WHERE " . $row[0] . " = ";
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
    pg_Exec ($conn, "COMMIT");
    if ($_debug) echo "</PRE>";
    if (!isset($mensagem_de_erro)){
      echo "</CENTER>";
      messageBar("message", "Exclusão realizada com sucesso.");
      echo "<CENTER>";
    }
    if ($_debug) echo "<CENTER>\n";
  }

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
      reset($_POST['buttonrow']);
      while (list($key, $val) = each($_POST['buttonrow'])){
        //echo $key;
	$innerQuery  = "SELECT * FROM \"" . trim($formulario['tabela']) . "\"\n";
	$campoChave = $campos[intval($formulario['chave'])];
	$innerQuery .= "\nWHERE " . $row[0] . " = ";
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
    $innerTotal  = pg_numrows($innerResult);
    $linhas = 0;
    echo "</CENTER>\n";

    if ($formulario['Enviar email para notificações']=='t' && $_POST['buttonrow']){
      echo "<DIV class=\"message\">";
      echo "Este formulário envia emails!!!";
      echo $closeDIV;
      $queryMailCheck  = "SELECT to_char(quando, 'DD')||'/'||to_char(quando, 'MM')||'/'||to_char(quando, 'YYYY') as data, quando, email\n";
      $queryMailCheck .= "  FROM formsemaillog \n";
      $queryMailCheck .= "  WHERE form = " . $formulario['codigo'] . "\n";

      reset($_POST['buttonrow']);
      while (list($key, $val) = each($_POST['buttonrow']))
	$rowCode = intval($key);

      $queryMailCheck .= "   AND row = " . $rowCode . "\n";
      $queryMailCheck .= "  ORDER BY quando DESC";
      //$campos[intval($formulario['chave'])];

      if ($_debug) show_query($queryMailCheck, $conn);
      $result = pg_exec ($conn, $queryMailCheck);
      $total  = pg_numrows($result);
      if ($total){
	echo "<DIV class=\"message\">";
	echo $total;
	echo ($total > 1) ? " emails enviados. " : " email enviado. ";
	$last = pg_fetch_array ($result, 0);
	echo "Último e-mail enviado no dia " . $last['data'] . " ";
	echo "para o endereço " . $last['email'] . ".";
	echo $closeDIV;
      }
      else{
	echo "<DIV class=\"schedulled\">";
	echo "Nenhum e-mail enviado por este formulário ainda.";
	echo $closeDIV;
      }
    }
    if ($_POST['botao']==$stringNovo && trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo'])){
      //echo "PASSEI";
      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo']);
    }
    else      
      $queryIncluiLinha = trim($formulario['Incluir linha 1 col 1 da query']);    
    if ($queryIncluiLinha){
      if (isset($queryarguments))
        foreach($queryarguments as $queryargument)
          $queryIncluiLinha = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $queryIncluiLinha);

      reset($_POST['buttonrow']);
      while (list($key, $val) = each($_POST['buttonrow'])){
	$whereString .= $row[0] . " = ";
	if (strpos("_" . $row[1], "int") && $row[1] != "interval")
	  $whereString .= intval($key);
	else
	  $whereString .= "'" . pg_escape_string($key) . "'";
      }
      if ($_POST['botao']!=$stringNovo)
        $queryIncluiLinha = str_replace("/*where*/", $whereString, $queryIncluiLinha);

      //echo "<PRE>" . htmlentities($whereString) . "</PRE>";
      //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";


      if ($_debug) show_query($queryIncluiLinha, $conn);
      if ($_POST['botao']!=$stringNovo || ($_POST['botao']==$stringNovo && trim($formulario['Incluir linha 1 col 1 da query ao clicar em novo'])) ){
        //echo "<PRE>" . htmlentities($queryIncluiLinha) . "</PRE>";
        $resultIncluiLinha = pg_exec ($conn, $queryIncluiLinha);
        //echo pg_last_error() . "<BR>";
        if (pg_numrows($resultIncluiLinha)){
          $rowIncluiLinha = pg_fetch_row ($resultIncluiLinha, 0);
          echo $rowIncluiLinha[0];
          //echo "PASSEI\n";
        }
      }
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
  
    echo "<FORM NAME=\"" . fixField($formulario['tabela']) . "\" ACTION=\"" . $form['action'] . "\" ";
    
    echo " ENCTYPE=\"multipart/form-data\" ";

    //echo " onsubmit=\"return validateForm()\" "; 
    echo " onsubmit=\"return validateForm()\" "; 

    echo " METHOD=\"POST\">\n";
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
      if ( $NNtable['lines'] == 2  && $NNtable['size'] == 2  && $_POST['botao']==$stringNovo && !isset($nextVal)) {
        $queryNextVal  = "select nextval('" . $formulario['tabela'] . "_" . $formulario['campo_chave'] . "_seq'::regclass);";
        //echo "<PRE>" . $queryNextVal . "</PRE>\n";
        $NextValResult = pg_exec ($conn, $queryNextVal);
        $NextValRow = pg_fetch_row ($NextValResult, 0);
	$nextVal = $NextValRow[0];
        //echo "<H3>nextVal = " . $NextValRow[0] . "</H3>\n";
      }
    }
    $jahFoi = false;
    while ($linhas<$innerTotal){
      $relations = checkRelations($linhas);
      $row = pg_fetch_row ($innerResult, $linhas);
      echo "<DIV id=\"onde_div_" . fixField($row[0]) . "\">\n";

      // Caso o campo seja para selecao de cor, inclui o javascript necessario.
      // o jahFoi impede que inclua multiplos javascripts para o caso de mais
      // de um campo de selecao de cor.
      if (stripos("_" . $row[0], 'rgbcolorof_') && !$jahFoi && intval($row[2])==10){
        $jahFoi = true;
        echo "    <script type=\"text/javascript\" src=\"dependencies/jscolor/jscolor.js\"></script>\n";
      }

      if ($linhas == intval($formulario['chave']) && $_POST['botao']==$stringNovo && isset($nextVal) ){
        echo "<INPUT TYPE=\"HIDDEN\" NAME=\"" . fixField($row[0]) . "\" ";
        echo "id=\"onde_" . fixField($row[0]) . "\" VALUE = \"" . $nextVal . "\">";
      }

      if ($linhas != intval($formulario['chave']) || $_POST['botao']!=$stringNovo){

        if ( !(($formulario['Esconde primeira coluna'] == "t") && ($linhas == intval($formulario['chave'])) ) &&
	     ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
	     &&  ($row[3] != "t" || $row[1]!='timestamp')
	     //&&  ($row[3] != "t" || $row[1]!='timestamp' || $row[1]!='date')
	     ){
	  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
          if (stripos("_" . $row[0], 'rgbcolorof_') && intval($row[2])==10)
	    echo "    <B>" . mb_ucfirst(str_replace("rgbcolorof_", "", $row[0]), $encoding) . ":</B>";
          else
	    echo "    <B>" . mb_ucfirst($row[0], $encoding) . ":</B>";
	}
	else{
          if ($formulario['Esconde primeira coluna'] != "t"){
  	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "    <B>" . mb_ucfirst($row[0], $encoding) . ":</B>";
	  }
	}
        if ($nullableColumns[$row[0]]['is_nullable'] == 'NO' && $row[0] != $formulario['Campo para salvar usuário logado']) echo "<FONT COLOR =\"#FF0000\"><B>(*)</B></FONT>";
        if ($_debug) echo "NULLABLE: " . $nullableColumns[$row[0]]['is_nullable'] . "<BR>\n";
        if ($relations['total']) $row[1] = "references";

	if ($linhas != intval($formulario['chave'])
            &&  ($row[0]!=trim($formulario['Campo para salvar usuário logado']))
            &&  ($row[3] != "t" || $row[1]!='timestamp')
            //&&  ($row[3] != "t" || $row[1]!='timestamp' || $row[1]!='date')
	    ){
	  switch ($row[1]) {
          case 'references':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";

            $relations['Array'] = pg_fetch_array ($relations['result'], 0);

	    $caption = getReferencedCaption($relations, $ReferencedCaptions[$linhas], $array[$row[0]]);
            //echo "<B>CAPTION" . $caption . "</B><BR>";

	    dbcombo($relations['Array']['referenced'],
                    ($relations['Array']['referencedfield'] ? $relations['Array']['referencedfield'] : 'codigo'),
		    ($ReferencedCaptions[$linhas] ? $ReferencedCaptions[$linhas] : 'nome'),
                    $conn, fixField($row[0]), 30,
                    //(trim($caption) == "" ? "selecione uma opção" : $caption),
                    $caption,
		    ($referenceOnChangeFunctions[$linhas] ? $referenceOnChangeFunctions[$linhas] : 0),
		    0, $ReferencedFilters[$linhas], NULL, NULL);
	    echo "<BR><BR>\n";
	    if ($referenceOnChangeFunctions[$linhas]){
              echo "<script type=\"text/javascript\">\n";
	      //echo "console.log('aqui!!!!!');\n";
              //echo "console.log('    \$(\"select#" . fixField($row[0]) . "\").attr(\"value\", \'" . $array[$row[0]] . "\');');\n";
              //echo "console.log('    \$(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr(\'checked\', \'checked\');');\n";

              echo "    $(\"select#" . fixField($row[0]) . "\").attr(\"value\", '" . $array[$row[0]] . "');\n";      
              echo "    $(\"input[name=" . fixField($row[0]) . "][value=" . $array[$row[0]] . "]\").attr('checked', 'checked');\n";
	      
              $onChangeFunction =  preg_replace('/(.*)?\((.*?\).*)/i', '${1}', $referenceOnChangeFunctions[$linhas]);
              $firingChangingFunctions[] = "  " . $onChangeFunction . "(" . $array[$row[0]] . ");\n";
              echo "</script>\n";
	    }

	    break;
	  case 'float8':
	  case 'float4':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo " STYLE=\"height: 28px; ";	    
	    echo "id=\"onde_" . fixField($row[0]) . "\" SIZE=\"9\" MAXLENGTH=\"10\" ";
	    echo " onKeypress=\"if( (event.keyCode < 48 || event.keyCode > 57) && event.keyCode != 44 ) event.returnValue = false;\"";
	    //echo " onKeypress=\"if( event.keyCode != 44 ) event.returnValue = false;\"";
	    //echo " onKeypress=\"alert(event.keyCode);\"";
	    echo " VALUE = \"" . str_replace(".", ",", floatval($array[$row[0]])) . "\">";
	    echo "<BR><BR>\n";
	    break;
	  case 'int4':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo " STYLE=\"height: 28px; ";	    
	    echo "id=\"onde_" . fixField($row[0]) . "\" SIZE=\"6\" MAXLENGTH=\"10\" ";
	    echo " onKeypress=\"if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;\"";
	    echo " VALUE = \"" . intval($array[$row[0]]) . "\">";
	    echo "<BR><BR>\n";
	    break;
	  case 'int8':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"text\" CLASS=\"";
            //echo "TEXT";
            echo "ui-input ui-widget ui-corner-all";
            echo "\" NAME=\"" . fixField($row[0]) . "\" ";
	    echo " STYLE=\"height: 28px; ";	    
	    echo "id=\"onde_" . fixField($row[0]) . "\" SIZE=\"10\" MAXLENGTH=\"19\" ";
	    echo " onKeypress=\"if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;\"";
	    echo " VALUE = \"" . intval($array[$row[0]]) . "\">";
	    echo "<BR><BR>\n";
	    break;
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
            echo ($isMobile) ? "80vw" : (intval($row[2]/2)>100 ? "200px" : (intval($row[2]/1.1)*8 ."px"));
            //echo intval($row[2]/2)>100 ? 255 : intval($row[2]/1.3)*8;
	    echo ";\" SIZE=\"";
            echo intval($row[2]/2)>100 ? 200 : intval($row[2]/1.3);
            echo "\"  MAXLENGTH=\"" . (intval($row[2]) - 4) . "\"";
	    echo " VALUE = \"" . htmlspecialchars($array[$row[0]], ENT_QUOTES, $encoding) . "\">";
	    echo "<BR><BR>\n";
	    break;
          case 'bytea':
            echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    //echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000000000\">\n";
	    //echo "    <INPUT NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" TYPE=\"file\">\n";
	    echo "    <INPUT NAME=\"" . fixField($row[0]) . "\" TYPE=\"file\">\n";
	    echo "    <BR><BR>\n";
	    break;
	  case 'text':
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
	    echo " NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" VALUE=\"true\"><BR>\n";
	    echo "    <BR>\n";
	    break;
          case 'date':
	    echo "<BR>\n";
	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    ?>
	      <input type="text" name="<?PHP echo fixField($row[0]); ?>" id="f_date_<?PHP echo fixField($row[0]); ?>" value="<?PHP echo  $array[$row[0]]; ?>"><button type="reset" id="f_trigger_<?PHP echo fixField($row[0]); ?>">...</button><script type="text/javascript">
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
	  }

          //if ($_POST['botao'] != $stringNovo)
	  echo "<INPUT TYPE=\"HIDDEN\" NAME=\"" . fixField($row[0]) . "\" id=\"onde_" . fixField($row[0]) . "\" VALUE=\"" . $array[$row[0]] . "\">\n";
	}
      }
      echo "</DIV>\n";
      $linhas++;
    }

    $queryPrepare = "set DateStyle TO 'ISO,MDY'";
    $prepareResult = pg_exec ($conn, $queryPrepare);

    //echo "<PRE>\n"; var_dump($NNtables);echo "</PRE>\n";
    foreach($NNtables as $NNkey => $NNtable){
      //echo "<H1>-----" . $NNtable['lines'] . "</H1>\n";
      //echo "<H1>-----" . $NNtable['size'] . "</H1>\n";
      if ($NNtable['lines']==2 && $NNtable['size'] == 2){
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
	$NNCaptions[] = 'nome';
	$NNCaptions[] = "\"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." . $NNtable['relations'][1]['foreign_column_name'];
        foreach($NNCaptions as $NNCaption){
	$queryCheckBoxes  = "SELECT \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." . $NNtable['relations'][1]['foreign_column_name'] . ", \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." .$NNCaption . ",\n";
	  if (isset($_POST['buttonrow'])){
	    $queryCheckBoxes .= "  (case when\n";
	    $queryCheckBoxes .= "    (select\n";
	    $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then true else false end\n";
	    $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
	    $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";
	    reset($_POST['buttonrow']);
	    while (list($key, $val) = each($_POST['buttonrow'])){
	      if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		$queryCheckBoxes  .= intval($key);
	      else
		$queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
	    }

	    $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

	    $queryCheckBoxes .= ") is null then false\n";
	    $queryCheckBoxes .= "else\n";
	    $queryCheckBoxes .= "    (select\n";
	    $queryCheckBoxes .= "      case when \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" then true else false end\n";
	    $queryCheckBoxes .= "      from \"" . $NNtable['table_name'] . "\" \n";
	    $queryCheckBoxes .= "      where \"" . $NNtable['table_name'] . "\".\"" . $NNtable['relations'][0]['column_name'] . "\" = ";
	    reset($_POST['buttonrow']);
	    while (list($key, $val) = each($_POST['buttonrow'])){
	      if (strpos("_" . $row[1], "int") && $row[1] != "interval")
		$queryCheckBoxes  .= intval($key);
	      else
		$queryCheckBoxes  .= "'" . pg_escape_string($key) . "'";
	    }

	    $queryCheckBoxes .= " and \"" . $NNtable['relations'][1]['foreign_table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['foreign_column_name'] . "\" = \"" . $NNtable['table_name'] . "\".\"";
	    $queryCheckBoxes .= $NNtable['relations'][1]['column_name'] . "\" ";

	    $queryCheckBoxes .= ")\n";
	    $queryCheckBoxes .= "end) as checked\n";

	  }
	  else
	    $queryCheckBoxes .= "false as checked\n";

	  $queryCheckBoxes .= "FROM  \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"\n";
	  $queryCheckBoxes .= " order by \"" . $NNtable['relations'][1]['foreign_table_name'] . "\"." .$NNCaption . "\n";
	  //$_debug = 1;
	  if ($_debug) show_query($queryCheckBoxes, $conn);
	  $checkBoxesResult = pg_Exec($conn, $queryCheckBoxes);
  	  $NNtables[$NNkey]['checkBoxesResult'] = $checkBoxesResult;
	  $NNtables[$NNkey]['caption'] = $NNCaption;
          if ($checkBoxesResult) break;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $checkBoxes = pg_fetch_all($checkBoxesResult);
	if ($checkBoxesResult){
	  //echo "<PRE>\n";
          //var_dump($checkBoxes);
	  //echo "</PRE>";
	  
	  echo "<div id=\"" . fixField($NNtable['relations'][1]['foreign_table_name']) . "\">\n";
	  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	  echo "<B>" . mb_ucfirst($NNtable['relations'][1]['foreign_table_name'], $encoding) . ":</B><BR>\n";	  
	  foreach($checkBoxes as $checkBox){

	    //echo "<INPUT TYPE=\"hidden\" NAME=\"";
	    //echo "lastState_" . $NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "]";
	    //echo "[" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"";
	    //echo " VALUE=\"" . $checkBox['checked'] . "\" >"; // antes de checked era codigo

	    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
	    echo "<INPUT TYPE=\"checkbox\" NAME=\"";
	    //echo $NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "]";
	    
	    echo fixField($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"");//?D?

	    echo " id=\"";
	    echo fixField($NNtable['relations'][1]['foreign_table_name'] . "[" . $NNtable['relations'][1]['foreign_column_name'] . "][" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"");//?D?

	    ///////////////////////////////////////////////// Testar melhor e se nao funcionar inverter estas duas linhas (descomentar uma e comentar a otura)
	    //echo "[" . $checkBox['codigo'] . "]\"";
	    //echo "[" . $checkBox[$NNtable['relations'][1]['foreign_column_name']] . "]\"";

	    //echo " VALUE=\"" . $checkBox['codigo'] . "\" ";
	    echo " " . ($checkBox['checked'] == 't' ? "CHECKED" : "") . ">";
	    //echo $NNCaption . "<BR>\n";
	    echo $checkBox[str_replace("\"", "", $NNCaption)] . "<BR>\n";
	    //echo $checkBox[trim($NNCaption)] . "<BR>\n";
	    //echo $checkBox['Nome do arquivo'] . "<BR>\n";

	  }
        echo "<BR>";
	echo "</div>\n";

	}
      }
      //if ($_debug) show_query($queryNN, $conn);
    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //echo "<PRE>";var_dump($formulario['Permitir anexos']); echo "</PRE>";
    if ($formulario['Permitir anexos']=='t'){
      //echo "    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"100000000000000\">\n";
      ini_set('upload_max_filesize', '10M');
      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      echo "    <B>Anexar arquivos: </B><BR>\n";
      echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      echo "    <INPUT NAME=\"userdoc\" TYPE=\"file\"><BR><BR>\n";
      $teste_query = "SELECT '<a href=\"accessDocument.php?codigo='||trim(to_char(codigo, '999999'))||'\">'||filename||'</a>' as documentos FROM documents where data is not null";
      show_query($teste_query, $conn);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<INPUT TYPE=\"SUBMIT\" ";
    //echo " CLASS=\"SUBMIT\" ";
    echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
    echo " VALUE=\"";
    if ($_POST['buttonrow'])
      echo " Salvar ";
    else
      echo " Inserir ";
    echo "\" NAME=\"envia\">\n";

    if ($_POST['buttonrow'] && strpos($emailEvent, "ENVIAR")){
      echo "<INPUT TYPE=\"SUBMIT\" ";
      //echo " CLASS=\"SUBMIT\" ";
      echo " CLASS=\"ui-button ui-widget ui-corner-all\" ";
      echo " VALUE=\"";
      echo " Enviar ";
      echo "\" NAME=\"envia\">\n";
    }
    if (isset($firingChangingFunctions)){
      echo "<script type=\"text/javascript\">\n";
      echo "  $(function() {\n";
      foreach($firingChangingFunctions as $changeFunction){
	echo "console.log('aqui tambem');\n";	
        //echo "console.log('  -- " . $changeFunction . " --  ');\n";
	echo $changeFunction;	
      }
      echo "  });\n";
      echo "</script>\n";
    }
    echo "</FORM>\n";
    //show_query($checkNullable, $conn);
    echo "<script type=\"text/javascript\">\n";

    echo "    function validateForm() {\n";
    echo "      var error = 0;\n";
    unset($nullabelColumn);
    foreach ($nullableColumns as $index => $nullableColumn){
      if ($index != $formulario['Campo para salvar usuário logado']){
        echo "      var " . fixField($index) . " = document.forms[\"" . fixField($formulario['tabela']) . "\"][\"" . fixField($index) . "\"].value;\n";
        echo "      console.log(" . fixField($index) . ");\n";
        echo "      if (" . fixField($index) . ".trim() == \"\") {\n";
        echo "        console.log(\"NULLO: \"+" . fixField($index) . ");\n";
        echo "        error++\n";
        echo "      }\n";
      }
      echo "      console.log('\$index: " . $index . "');\n";
      echo "      console.log('\$formularios[\'Campo para salvar usuário logado\']: " . $formulario['Campo para salvar usuário logado'] . "');\n";
    }
    echo "      if (error) alert(\"Os campos indicados com (*) são obrigatórios.!\")\n";
  
    /* echo "      if (x == \"\") {\n"; */
    /* echo "	alert(\"Name must be filled out\");\n"; */
    /* echo "\n"; */
    /* echo "	return false;\n"; */
    /* echo "      }\n"; */
    /* echo "      console.log(\"passei\n\");\n"; */
    //echo "      alert(\"passei\");\n";
    echo "      if (error) return false;\n";
    echo "    }\n";
    echo "</script>\n";



    echo "<CENTER>\n";
  }
 }

if ($formulario['Cabeçalho html']){
  echo "</CENTER>\n";
  echo $formulario['Cabeçalho html'] . "\n\n";
  echo "<CENTER>\n";
}

if ($formulario['Apenas form, sem tabela'] == 'f'){

  $extraGet = "&form=" . $codigo;
  if (isset($_GET['args'])){
    foreach($_GET['args'] as $argkey => $argvalue){
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

  // echo $extraGet . "<BR>\n";
  // echo $form['action'] . "<BR>\n";

  if ($form){
    $abriuForm = 0;
    echo "<FORM ACTION=\"" . $form['action'] . "\" METHOD=\"POST\">\n";

    echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringNovo . "\" NAME=\"botao\">\n";
    //if ($form['Cláusula where para ocultar'])

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

  if (!$orderBy)
    show_query($query, $conn, $formulario['ordenarpor'],
	       $desc, $formata,
	       $references, $form, $boolean, $link, $destak,
	       $extraGet, $hideByQuery, $showNum, $boldCondition,
	       $secondOrder, $limite, $totalRowCollum);
  else
    show_query($query, $conn, $orderBy,
	       $desc, $formata,
	       $references, $form, $boolean, $link, $destak,
	       $extraGet, $hideByQuery, $showNum, $boldCondition,
	       $secondOrder, $limite, $totalRowCollum);

  if ($form["name"]){
    echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringNovo . "\" NAME=\"botao\">\n";
    if ($form['delete']){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringRemover . "\"\n";
      echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
    }
    if ($form['duplicar']){
      echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"" . $stringDuplicar . "\"\n";
      echo "       onClick=\"return confirmSubmit()\" NAME=\"botao\">\n";
    }
    echo "    </FORM>\n";
  }
  echo "</CENTER>\n";
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

?>

