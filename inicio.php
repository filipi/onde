<?PHP
$headerTitle = "";
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);

include "page_header.inc";

$mes = date("m", time());
$dia = date("d", time());
$ano = date("Y", time());
$query  = "SELECT nome, login, to_char(aniversario, 'dd/MM') as data,\n";
$query .= "       to_char(aniversario, 'dd') as dia, \n";
$query .= "       to_char(aniversario, 'MM') as mes, \n";
$query .= "       aniversario - TIMESTAMP '2009-" . $mes . "-" . $dia . " 00:00:00' as offset\n ";
$query .= "  FROM usuarios \n";
$query .= "  WHERE aniversario < TIMESTAMP'2009-" . $mes . "-" . $dia . " 00:00:00' + interval '17 days'\n";
$query .= "    AND aniversario >= TIMESTAMP'2009-" . $mes . "-" . $dia . " 00:00:00' - interval '3 days'\n";
$query .= "    AND ativo=true \n";
$query .= "  ORDER BY aniversario \n";

$result = pg_exec ($conn, $query); // Executa a consulta.
if ($_debug){
  togglePoint("queries_aniversariantes", "Consulta SQL para buscar aniversariantes", 0, NULL, NULL); 
  echo "<PRE>\n";
  echo $query . "\n";
  if ($_debug>2) show_query($query, $conn);
  echo "</PRE>\n";
  echo $closeDIV;
}

$totalAniversariantes  = pg_numrows($result);
$linhas = 0; $felicitacoes = 0;
if ($totalAniversariantes){
  while ($linhas<=$totalAniversariantes){
    $aniversariantes[$linhas] = pg_fetch_array ($result, $linhas);
    if ($_debug>2){
      echo "MES: " . $aniversariantes[$linhas]['mes'] . "<BR>\n";
      echo "DIA: " . $aniversariantes[$linhas]['dia'] . "<BR>\n";
      echo "ANO: " . $ano . "<BR>\n";
    }
    $aniversariantes[$linhas]['dia'] = date("N", mktime(0, 0, 0, $aniversariantes[$linhas]['mes'], $aniversariantes[$linhas]['dia'], $ano)) ;
    $offset = explode(" ", $aniversariantes[$linhas]['offset']);
    $aniversariantes[$linhas]['offset'] = intval($offset[0]);
    if ($aniversariantes[$linhas]['nome'] == $_SESSION['nome'] && $aniversariantes[$linhas]['data'] == ($dia . "/" . $mes))
      $felicitacoes = 1;
    $linhas++;
  }
  //echo "</div>\n";  // Nao Achei onde abre esse DIV
 }
?>
<DIV CLASS=titulo><?PHP 
  if ($felicitacoes)
    echo "Feliz anivers&aacute;rio";
  else{
    echo "Bem vind";
    if ($_SESSION['genero']=="masculino")
      echo "o";
    else
      if ($_SESSION['genero']=="feminino")
        echo "a";
      else
        echo "o(a)";
  }
  echo ", <B>" . $_SESSION[nome] . "</B>!</DIV>\n";

if (!strpos("_" . $_SESSION['grupos'], 'diretor') || 
    strpos("_" . $_SESSION['grupos'], 'root') ){
  $query  = "SELECT naofinalizadas('" . $_SESSION['matricula'] . "'),\n";
  $query .= "       naodefinidas('" . $_SESSION['matricula'] . "')\n";


foreach($tipos as $esteTipo) if ($esteTipo['demandas']) $empty = false;

  $result = pg_exec ($conn, $query); // Executa a consulta.

if ($_debug){
  togglePoint("query_pendencias", "Consulta SQL para mostrar pendencias", 0, NULL, NULL); 
  echo "<PRE>\n";
  echo $query . "\n";
  if (pg_last_error()) echo "\n-----------------------------------------\n" . pg_last_error() . "\n";
  echo "</PRE>\n";

  showPgFunctionDefinition($conn, "naofinalizadas");
  showPgFunctionDefinition($conn, "naodefinidas");
  echo $closeDIV;
}

  $row = pg_fetch_row ($result, 0);
  if ($row[0]){
    echo "<DIV class=\"schedulled\">";
    echo "Voc&ecirc; tem <A href=\"masterForm.php?PHPSESSID=" . $PHPSESSID . "&formID=" . ATIVIDADES . "\">" . $row[0];
    if ($row[0]>1)
      echo " atividades n&atilde;o finalizadas.\n";
    else
      echo " atividade n&atilde;o finalizada.\n";
    echo "</A></DIV>\n";
  }
  if ($row[1]){
    echo "<DIV class=\"busy\">";
    echo "Voc&ecirc; tem <A href=\"masterForm.php?PHPSESSID=" . $PHPSESSID . "&formID=" . ATIVIDADES . "\">" . $row[1];
    if ($row[1]>1)
      echo " atividades ";
    else
      echo " atividade ";
    echo "</A>com situa&ccedil;&atilde;o n&atilde;o definida.\n";
    echo "</DIV>\n";
  }
}
if (strpos("_" . $_SESSION['grupos'], 'diretor') ||
    strpos("_" . $_SESSION['grupos'], 'root') ){
   $query  = "SELECT codigo, nome, demandasporsituacao(codigo) from situacoes";
   if ($_debug) echo "<PRE>" . $query . "</PRE><BR>\n";
   $result = pg_exec ($conn, $query); // Executa a consulta.
   $total  = pg_numrows($result);
   $linhas = 0;
   while ($linhas<$total){
     $row = pg_fetch_row($result, $linhas);
     if ($_debug>1){
       echo "<PRE>\n";
       var_dump($row);
       echo "\n</PRE>\n";
     }     
     $demandas[stripAccents($row[1])] = $row[2];
     $linhas++;
   }
   if ($_debug){
     echo "<PRE>\n";
     var_dump($demandas);
     echo "\n</PRE>\n";
   }

  if ($demandas['em andamento'] + $demandas['nao definida']){
    echo "<DIV class=\"schedulled\">";
    echo "O Instituto tem <A href=\"masterForm.php?PHPSESSID=" . $PHPSESSID . "&formID=" . SOLICITACOES . "\">";
    echo $demandas['em andamento'] + $demandas['nao definida'];
    if (($demandas['em andamento'] + $demandas['nao definida'])>1)
      echo " solicita&ccedil;&otilde;es n&atilde;o finalizadas.\n";
    else
      echo " solicita&ccedil;&atilde;o n&atilde;o finalizada.\n";
    echo "</A></DIV>\n";
  }
  if ($demandas['nao definida']){
    echo "<DIV class=\"busy\">";
    echo "O Instituto tem <A href=\"masterForm.php?PHPSESSID=" . $PHPSESSID . "&formID=" . SOLICITACOES . "\">";
    echo $demandas['nao definida'];
    if ($demandas['nao definida'] > 1)
      echo " solicita&ccedil;&otilde;es ";
    else
      echo " solicita&ccedil;&atilde;o ";
    echo "</A>com situa&ccedil;&atilde;o n&atilde;o definida.\n";
    echo "</DIV>\n";
  }

}

$linhas = 0;
if ($totalAniversariantes){
  echo "<div class=\"birthday\">Pr&oacute;ximo";
  echo (($totalAniversariantes>1) ? "s" : "");
  echo " aniversariante";
  echo (($totalAniversariantes>1) ? "s" : "");
  echo ":<BR>\n";
  while ($linhas<=$totalAniversariantes){    
    if ($aniversariantes[$linhas]['nome']){
      if ($aniversariantes[$linhas]['nome'] != $_SESSION['nome'])
        echo "<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=310#" . $aniversariantes[$linhas]['login'] . "\">" . $aniversariantes[$linhas]['nome'] . "</A>";
      else
        echo "voc&ecirc;";
      switch (intval($aniversariantes[$linhas]['offset'])){
      case -4:
      case -3:
        if (intval($aniversariantes[$linhas]['dia'])>=6)
  	  echo ", no &uacute;ltimo ";
        else
	  echo ", na &uacute;ltima ";
        echo $semana[$aniversariantes[$linhas]['dia']]['longo'];
	echo " (" . $aniversariantes[$linhas]['data'] . ")<BR>\n";
	break;
      case -2:
	echo ", anteontem<BR>\n";
	break;
      case -1:
	echo ", ontem<BR>\n";
	break;
      case 0:
	echo ", <B>HOJE</B><BR>\n";
	break;
      case 1:
	echo ", amanh&atilde;<BR>\n";
	break;
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
      case 7:
        if (intval($aniversariantes[$linhas]['dia'])>=6)
  	  echo ", no pr&oacute;ximo ";
        else
	  echo ", na pr&oacute;xima ";
        echo $semana[$aniversariantes[$linhas]['dia']]['longo'];
	echo " (" . $aniversariantes[$linhas]['data'] . ")<BR>\n";
	break;
      default:
	echo ", dia " . $aniversariantes[$linhas]['data'] . "<BR>\n";
	break;
      }
    }
    $linhas++;
  }
  echo "</div>\n";
 }

if ($_debug>1){
  echo "<PRE>\n";
  var_dump($aniversariantes);
  echo "\n</PRE>\n";
 }

echo "  <CENTER>\n";

$query  = "select apelido as unidade, solicitacoesPorUnidade('" . $ano . "', apelido) as demandas,\n";
$query .= "       to_char((case \n";
$query .= "                  when horasTrabalhadasAno('" . $ano . "', apelido) is null \n";
$query .= "                    then 0 else horasTrabalhadasAno('" . $ano . "', apelido) \n";
$query .= "                end) + horasAtividadesAno('" . $ano . "', apelido), '999999') as horas";
$query .= "\nfrom unidades\n";
$query .= "where solicitacoesPorUnidade('" . $ano . "', apelido) >0 \n";
if ($orientation==1)
  $query .= "order by demandas desc \n";
else
  $query .= "order by unidade \n";

$result = pg_exec ($conn, $query);
$total  = pg_numrows($result);
$linhas = 0;

if (!$total){
  if ($_debug>1){
    echo "Ano: " . intval($_GET['ano']) . "\n";
    echo $query . "\n";
    echo "Linhas resultantes: " . intval($total) . "\n";
  }
  $ano--;
}

if (strpos("_" . $_SESSION['grupos'], 'diretor') ||
    strpos("_" . $_SESSION['grupos'], 'root') ){
  echo "  <A HREF=\"relatorioAnual.php?PHPSESSID=" .  $PHPSESSID . "&ano=" . $ano . "\">\n";
}

$query = "select count(codigo) from solicitacoes where to_char(quando, 'YYYY') = '" . $ano . "'";
$result = pg_exec ($conn, $query);
$total  = pg_numrows($result);
if ($total){
  echo "  <IMG WIDTH=" . ($isMobile ? "321" : "800") . " HEIGHT=" . ($isMobile ? "535" : "480") . " BORDER=0 SRC=\"plot.php?PHPSESSID=" . $PHPSESSID . "&ano=" . $ano . ($isMobile ? "&orientation=1&mobile=1" : "") . "\"></A><BR>\n";
  echo "  <DIV class=coment><A HREF=\"plot.php?PHPSESSID=" .  $PHPSESSID . "&ano=" . $ano . "&type=svg\">\n";
  echo "  [clique aqui para baixar este gráfico no formato editável SVG]</DIV>\n";
}

?>
  <TABLE class=onde>
  <TR>
  <TD class=onde>
  <DIV CLASS=coment>
  <B>HORA DE LOGIN:</B><?PHP echo $_SESSION['h_log']; ?> &nbsp; &nbsp;<BR>
  <B>IP:</B> <?PHP echo $_SESSION['ip']; ?> &nbsp; &nbsp;<BR>
  <B>MATRICULA:</B><?PHP echo $_SESSION['matricula']; ?> &nbsp; &nbsp;<BR>
  </DIV>
  </TD>
  </TR>
  </TABLE>
  </CENTER>
<?PHP
include "page_footer.inc";
?>
