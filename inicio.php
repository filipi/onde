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
  echo ", <B>" . $_SESSION['nome'] . "</B>!</DIV>\n";



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
