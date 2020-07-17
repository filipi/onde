<?PHP
  /*
  Outra forma de exportar para excell
  http://stackoverflow.com/questions/3968973/how-can-i-write-data-into-an-excel-using-php
  http://www.the-art-of-web.com/php/dataexport/
  https://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.intro.php
  https://phpexcel.codeplex.com/
  https://gist.github.com/ihumanable/929039
  */
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
if (isset($_GET['form']))
  $form = intval($_GET['form']);
else
  $form = 0;
if (isset($_GET['orderby']))
  $orderby = pg_escape_string($_GET['orderby']);
if (isset($_GET['desc']))
  if (intval($_GET['desc']) == 1)
    $desc = true;
  else
    $desc = false;

if (isset($_GET['nh'])) // nh for no header
  $no_header = True;
else
  $no_header = False;

if (isset($_GET['d']) && $_GET['d'] == ',')
  $delimiter = ',';
else
  $delimiter = ';';


///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 1;
$headerTitle = "";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
//$_debug = 1;
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario

if (isset($_GET['args'])){
  foreach($_GET['args'] as $argkey => $argvalue){
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

if ($form) {
  $query = "SELECT tabela, campos, consulta, nome, ordenarpor, titulo, descendent  from forms where codigo =  ". $form;
  $result = pg_exec($conn, $query);
  $total  = pg_numrows($result);
  if ($_debug){
    echo "Total de linhas: " . $total . "<BR>\n";
    echo "<PRE>" . $query . "</PRE>";
  }
  if ($total){
    $row = pg_fetch_row ($result, 0);
  }
  if ($_debug){
    echo "\$row[0]: " . $row[0] . "<BR>\n"; // tabela
    echo "\$row[1]: " . $row[1] . "<BR>\n"; // campos
    echo "\$row[2]: " . $row[2] . "<BR>\n"; // consulta
    echo "\$row[3]: " . $row[3] . "<BR>\n"; // nome da tabela
    echo "\$row[4]: " . $row[4] . "<BR>\n"; // orderby
    echo "\$row[5]: " . $row[5] . "<BR>\n"; // titulo
    echo "\$row[6]: " . $row[6] . "<BR>\n"; // descendent
  }



if ($_debug<1) {
  header("Content-type: application/vnd.ms-excel");
  header("Content-Disposition: attachment;" . ($row[3] ? "Filename=" . $row[3] . ".csv" : ""));
}

  if ($row[2])
    $query = $row[2];
  else
    if ($row[0]){
      $query = "SELECT ";
      if ($row[1]) $query .= $row[1]; else $query .= "*";
      $query .= " FROM \"". $row[0] ."\"";
    }

  if (!isset($_GET['orderby']))
    $orderby = trim($row[4]);
  if (!isset($_GET['desc']))
    $desc = trim($row[6]);

  if (isset($orderby) && $orderby){
    $query .= "\n ORDER BY \"" . $orderby . "\"";
    if (isset($desc) && $desc)
      $query .= " desc";
  }

  if (isset($queryarguments))
    foreach($queryarguments as $queryargument)
      $query = str_replace("\$" . $queryargument['key'], trim($queryargument['value']), $query);

  
  if ($_debug) echo "<PRE>" . $query . "</PRE>";
  $result = pg_exec($conn, $query);
  $total  = pg_numrows($result);
  if ($_debug){
    if (!$total){
      echo "<PRE>" . pg_last_error() . "</PRE>";
      echo "<PRE>\n";
    }
    echo "Total de linhas: " . $total . "<BR>\n";
    echo "<PRE>" . $query . "</PRE>";
    echo "<PRE>\n";
  }
  if ($total){
    $linhas = 0;
    $row = pg_fetch_assoc ($result, 0);
    if($_debug) {
      echo "<PRE>";
      var_dump($row);
      echo "</PRE>";
    }

    if (!$no_header){
      foreach($row as $key => $value){
        //if($_debug) echo "Chave: ".$value;
        $key = trim(preg_replace('/<[^>]\*>/', '', utf8_decode($key) )) . $delimiter;
        //echo trim(stripAccents($key)).";";
	
	if (strpos($key, ":")){
	  $range = explode(":", $key);
	  for ($i = $range[0]; $i < $range[1]; $i++){
	    echo $i;
	    if ($i<$range[1]-1) echo $delimiter;
	  }
	}
	else
	  echo $key;
	
      }
      echo "\n";
    }
    while ($linhas<$total){
      $columns = pg_fetch_row ($result, $linhas);
      foreach($columns  as $key => $column){      
        //if (!is_numeric($column)) echo "'";
        if($_debug){
         /*  echo "<PRE>Column";
          var_dump(html_entity_decode($column));
          echo "</PRE>";  */
        }
        $column = html_entity_decode($column);
        $column = tiraQuebrasDeLinha($column, ' ');
        $valor = trim(preg_replace('/<[^>]*>/', '', utf8_decode($column) ));
        //$valor =  trim(stripAccents($column));
	if (floatval($valor) && $delimiter != ','){
	  $valor = str_replace(".", ",", $valor);
	}
	echo trim($valor);	
	//if (!is_numeric($column)) echo "'";
	if ($key < count($columns)-1) echo $delimiter;	
      }
      echo "\n";
      $linhas++;
    }
  }
  if ($_debug) echo "<PRE>\n";
}

include "page_footer.inc";
?>
