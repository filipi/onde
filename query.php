<?PHP
  //create table consultas( codigo serial primary key, nome varchar(100), consulta text, data timestamp default current_timestamp, usuario varchar(6) references usuarios(login) on delete cascade);
 /**
  * Shell para execucao de consultas ao banco
  * $Id: query.php,v 1.36 2018/12/17 13:03:49 filipi Exp $
  */
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
if (isset($_POST['nome'])) $headerTitle = pg_escape_string($_POST['nome']);
include "page_header.inc";
?>
<script  type='text/javascript' src='dependencies/codemirror/lib/codemirror.js'></script>
<script  type='text/javascript' src='dependencies/codemirror/addon/hint/show-hint.js'></script>
<script  type='text/javascript' src='dependencies/codemirror//addon/hint/sql-hint.js'></script>
<script  type='text/javascript' src='dependencies/codemirror/addon/edit/matchbrackets.js'></script>
<script  type='text/javascript' src='dependencies/codemirror/mode/sql/sql.js'></script>

<?PHP if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
  $borderColor="lightblue"; else $borderColor="black" ?>
<style type="text/css">
      .CodeMirror {
        border-top: 1px solid <?PHP echo $borderColor;?>;
        border-bottom: 1px solid <?PHP echo $borderColor;?>;
        //font-family: "Lucida Console", "Monospace", monospace;
	font-family: "Liberation Mono", "Monaco", Menlo, monospace, "Lucida Console", "Consolas", "DejaVu Sans Mono", "Bitstream Vera Sans Mono";
        src: local('Menlo');
        line-height: 1.3em;
        height: 500px;
      }
</style>
<?PHP
echo "<BR>\n";
//echo "    <CENTER>\n";
echo "      <BR>\n      <BR>\n";

if (isset($_POST['salvar'])) $_POST['salvar'] = true;
if (isset($_POST['executar'])) $_POST['executar'] = true;
if (isset($_POST['remover'])) $_POST['remover'] = true;
if (isset($_POST['novo'])) $_POST['novo'] = true;
if (isset($_POST['nome'])) $_POST['nome'] = pg_escape_string(trim($_POST['nome']));
if (isset($_POST['codigo'])) $_POST['codigo'] = intval($_POST['codigo']);

//$_debug=1;
if (isset($_POST['DeleteCheckBox']) && $_POST['remover']){
  $delete = $_POST['DeleteCheckBox'];
  pg_Exec ($conn, "BEGIN"); // Inicia a transacao
  if ($_debug) echo "<PRE>\n";
  while (list($key, $val) = each($delete)) {
    if ($_debug) echo $key . " = " . $delete[$key] . "\n";
    if  ($delete[$key]){
      $query_liga  = "DELETE FROM consultas\n";
      $query_liga .= "  WHERE codigo = " . $key . "\n";
    }
    if ($_debug) echo $query_liga;
    $result = pg_Exec ($conn, $query_liga);
    if (!$result){
      echo "FALHOU, executando roll back\n";
      pg_Exec ($conn, "ROLLBACK");
      break;
    }
  }
  pg_Exec ($conn, "COMMIT");
  if ($_debug) echo "</PRE>\n";
}

if (isset($_POST['query_field'])){
  $query_field = $_POST['query_field'];
  $query_field = str_replace("\'", "'", $query_field);
  $query_field = str_replace('\"', '"', $query_field);
  $query_field = trim($query_field);
  $last_debug = $_debug;
  $_debug=1;
}

if($_POST['buttonrow']){

  while (list($key, $val) = each($_POST['buttonrow'])){
    $_POST['codigo'] = $key;
    if ($_debug){
      echo "<B>codigo: " . $key;
      echo " - " .$_POST['buttonrow'][$key] . "</B><BR>\n";
    }    
    $query = "select * from consultas where codigo=" . $key;
    if ($_debug) echo "<PRE>" . $query . "</PRE><BR>\n";
    $result=pg_exec($conn, $query);
    $consulta=pg_fetch_array($result, 0);
    $query_field = trim($consulta['consulta']);
    $_POST['nome'] = trim($consulta['nome']);
    $_POST['tempo'] = trim($consulta['tempo']);
  }
 }
?>
|<a href="http://www.w3schools.com/sql/default.asp" target="_blank">Tutorial de SQL</a>|
 <a href="http://sqlzoo.net/wiki/SELECT_basics" target="_blank">Tutorial interativo</a>|
<br>

  <?PHP /* <FORM ACTION="<?PHP echo $_SERVER['PHP_SELF']; ?>" METHOD="POST" */ ?>
<FORM ACTION="" METHOD="POST"
 NAME="query">

  <INPUT TYPE="HIDDEN" NAME="tempo" VALUE=<?php if (isset($_POST['tempo'])) echo floatval($_POST['tempo']); else echo "0";?> id="tempo">
  
  
<TEXTAREA COLS="80" ROWS="20" NAME="query_field" id="query_field"><?PHP
  if (isset($query_field) && !$_POST['novo'])  
      echo $query_field;?></TEXTAREA>
      <?PHP /*echo str_replace(trim('\\\ '), trim('\ '), $query_field);?></TEXTAREA> */?>
<INPUT TYPE="HIDDEN" NAME="buttonrow" VALUE=1><BR>
  
<?PHP
  if (!$_POST['novo'] && $_POST['codigo']){?>
<INPUT TYPE="HIDDEN" NAME="codigo" VALUE="<?PHP echo $_POST['codigo']; ?>"><BR>
		       <?PHP }?>


<INPUT TYPE="SUBMIT" CLASS="SUBMIT" VALUE='Novo &#128462;' NAME='novo'>
<INPUT TYPE="SUBMIT" CLASS="SUBMIT" VALUE='Executar &#9654; ...' NAME='executar'>
  <?PHP /*
<input class=onde size=20 type=text id=nome style="font-size: 14;" name="nome" value="<?PHP echo ($_POST['salvar'] ? $_POST['nome'] : $consulta['nome']); ?>">
<input class=onde size=20 type=text id=nome style="font-size: 14;" name="nome" value="<?PHP echo $_POST['nome']; ?>">
	*/?>
<input class=onde size=20 type=text id=nome style="font-size: 14;" name="nome" value="<?PHP echo ($_POST['novo'] ? '' : $_POST['nome']); ?>">
<INPUT TYPE="SUBMIT" CLASS="SUBMIT" VALUE='Salvar...' NAME='salvar'>
</form>
    <script>
    var editor = CodeMirror.fromTextArea(document.getElementById("query_field"), {
	//mode: "text/x-sql",
	mode: {name: "text/x-sql",globalVars: true},
        extraKeys: {"Ctrl-Space": "autocomplete"},
        lineNumbers: true,
	tabMode: "indent",
	<?PHP if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo "	    theme: \"night\", \n"; ?>
	matchBrackets: true,
        hint: CodeMirror.hint.sql,
        hintOptions: {tables: {<?PHP

        // $query  = "SELECT distinct p.proname as \"Name\"\n";
        // $query .= "FROM pg_catalog.pg_proc p\n";
        // $query .= "     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace\n";
        // $query .= "WHERE pg_catalog.pg_function_is_visible(p.oid)\n";
        // $query .= "      AND n.nspname <> 'pg_catalog'\n";
        // $query .= "      AND n.nspname <> 'information_schema' order by \"Name\"\n";

        // $query  = "SELECT c.oid, t.tablename\n";
        // $query .= "  FROM pg_tables as t, pg_class as c\n";
        // $query .= "  WHERE tableowner<>'postgres' AND c.relname=t.tablename";
        // $result = pg_exec($conn, $query);
        // $tabelas = pg_fetch_all($result);
        // $linhas = 0;
        // foreach($tabelas as $tabela){
        //   $linhas++;
        //   echo "\"" . $tabela['tablename'] . "\": {";
        //   $query  = "SELECT a.attname, t.typname, a.atttypmod\n";
        //   $query .= "  FROM pg_attribute as a, pg_type as t\n";
        //   $query .= "  WHERE attrelid = " .  $tabela['oid'] . " AND\n";
        //   $query .= "        attstattarget<>0 AND t.oid=a.atttypid";
        //   $result = pg_exec($conn, $query);
        //   $campos = pg_fetch_all($result);
        //   $colunas = 0;
        //   foreach($campos as $campo){
	//     $colunas++;
        //     echo "\"" . $campo['attname'] . "\": null";
        //     if ($colunas < sizeof($campos)) echo ", ";            
	//   }
	  
        $query  = "SELECT c.oid, t.tablename\n";
        $query .= "  FROM pg_tables as t, pg_class as c\n";
        $query .= "  WHERE tableowner<>'postgres' AND c.relname=t.tablename";
        $result = pg_exec($conn, $query);
        $tabelas = pg_fetch_all($result);
        $linhas = 0;
        foreach($tabelas as $tabela){
          $linhas++;
          echo "\"" . $tabela['tablename'] . "\": {";
          $query  = "SELECT a.attname, t.typname, a.atttypmod\n";
          $query .= "  FROM pg_attribute as a, pg_type as t\n";
          $query .= "  WHERE attrelid = " .  $tabela['oid'] . " AND\n";
          $query .= "        attstattarget<>0 AND t.oid=a.atttypid";
          $result = pg_exec($conn, $query);
          $campos = pg_fetch_all($result);
          $colunas = 0;
          foreach($campos as $campo){
	    $colunas++;
            echo "\"" . $campo['attname'] . "\": null";
            if ($colunas < sizeof($campos)) echo ", ";            
	  }	  
          echo "}";
	  if ($linhas<sizeof($tabelas)) echo ",\n        ";
	}
	echo ",\n        ";
        $query  = "SELECT distinct UPPER(p.proname) as \"tablename\"\n";
        $query .= "FROM pg_catalog.pg_proc p\n";
        $query .= "     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace\n";
        $query .= "WHERE pg_catalog.pg_function_is_visible(p.oid)\n";
        $query .= "      AND n.nspname <> 'pg_catalog'\n";
        $query .= "      AND n.nspname <> 'information_schema'\n";
        $result = pg_exec($conn, $query);
        $tabelas = pg_fetch_all($result);
        $linhas = 0;
        foreach($tabelas as $tabela){
          $linhas++;
          echo "\"" . $tabela['tablename'] . "\": {null:null}";
	  if ($linhas<sizeof($tabelas)) echo ",\n        ";
	}	
        echo "\n";
        //  users: {name: null, score: null, birthDate: null},
        //  countries: {name: null, population: null, size: null}
?>
        }}
    });
</script>
	<?PHP
	
/*
select u.nome, u.email
  from usuarios as u, usuarios_grupos as ug
  where ug.usuario = u.login and ug.grupo = 19 and u.ativo = true
*/
if($_POST['buttonrow'] &&  $query_field && $_POST['executar']){
  //show_query($query_field, $conn);
  $startTime = microtime(true);

  show_query($query_field, $conn, $orderby, $desc, $formata, 
             $references, $form, $boolean, $link, $destak,
             $extraGet, $hideByQuery, 1, $boldCondition,
             $secondOrder, $limite, $appendTotalRow, true);

  // $error = pg_last_error($conn);
  // echo $error;
  // echo "PASSEI";
      

  
  $tempo =  (microtime(true) - $startTime);
  echo "<BR>Tempo de execução da consulta = " . $tempo . " segundos.<BR>\n";
  //echo "<INPUT TYPE=\"HIDDEN\" NAME=\"tempo\" VALUE=\"" . $tempo . "\">";
  echo"<SCRIPT>  document.getElementById(\"tempo\").value = " . $tempo . "; </SCRIPT>";
}

?>
<?PHP
        $_debug = $last_debug;
	if ($_POST['salvar'] && $query_field){
	  if ($_POST['codigo']){
	    $query  = "UPDATE consultas\n";
	    $query .= "set nome     = '" . pg_escape_string($_POST['nome']) . "',\n";
	    $query .= "    consulta = '" . pg_escape_string($query_field) . "',\n";
	    $query .= "    usuario  = '" . pg_escape_string($_SESSION['matricula']) . "'\n";
	    if ($_POST['tempo']) $query .= ",    tempo = " . floatval($_POST['tempo']) . "\n";	    
	    
            $query .= "  WHERE codigo = " . intval($_POST['codigo']);
	  }
	  else{
	    $query  = "INSERT INTO consultas (nome, consulta, usuario";
	    if ($_POST['tempo']) $query .= ", tempo";
	    $query .=") VALUES (\n";
	    $query .= "  '" . pg_escape_string($_POST['nome']) . "',\n";
	    $query .= "  '" . pg_escape_string($query_field) . "',\n";
	    $query .= "  '" . pg_escape_string($_SESSION['matricula']) . "'";
	    if ($_POST['tempo']) $query .= ", " . floatval($_POST['tempo']);	    
	    $query .= ")\n";
	  }
	  echo "<PRE>";
	  if ($_debug) echo $query;
	  $result = pg_exec($conn, $query);
	  if (!$result) { 
	    echo "Erro na execucao da consulta.\n";
	    echo pg_last_error();
	    echo "</PRE>";
	    echo $closeDIV;
	    echo "<CENTER>\n";
	    include("page_footer.inc");
	    exit(1);
	  }
	  echo "</PRE>";

	}



$query  = "SELECT codigo, nome, tempo as \"tempo da<BR>última execução\", data\n";
$query .= "  FROM consultas\n";

echo "    <CENTER>\n";
if (!(isset($_GET['orderby'])) &&
    (isset($_POST['orderby'])))
  $_GET['orderby'] = $_POST['orderby'];

$references[0]="";
$references[1]="";
$references[2]="";
$references[3]="";

$form['field']="codigo";
//$form['action']=basename( $_SERVER['PHP_SELF']);
$form['action']="";
$form['name']="detalhes";
$form['delete']=1;
$form['hideFirstColumn'] = 1;
$formata[2] = 10;
$formata[3] = true;

//echo "    <FORM ACTION=\""  . $_SERVER['PHP_SELF'] . "\"";
echo "    <FORM ACTION=\"\"";
echo " METHOD=\"POST\">\n";
if (!(isset($_GET['orderby'])) && (isset($_POST['orderby'])))
  $_GET['orderby'] = $_POST['orderby'];
  echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"remover consultas marcadas\"\n";
  echo "       onClick=\"return confirmSubmit()\" NAME=\"remover\">\n";

if (!(isset($_GET['orderby'])) && (isset($_POST['orderby'])))
  $_GET['orderby'] = $_POST['orderby'];
echo "      <BR>\n      <BR>\n";

if (isset($_GET['orderby']))
  show_query($query, $conn, 
             $_GET['orderby'],
             ($_GET['desc'] || isset($_POST['desc'])),
             $formata, $references,$form, $boolean,
             "");
else
  show_query($query, $conn, "data", 1, $formata, $references,
             $form, $boolean,"");
  echo "      <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"remover consultas marcadas\"\n";
  echo "       onClick=\"return confirmSubmit()\" NAME=\"remover\">\n";
echo "    </FORM>\n";
echo "    </CENTER>\n";

echo "<BR>\n";

echo "<BR>\n";

include "page_footer.inc";
?>
