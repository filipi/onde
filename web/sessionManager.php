<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 0;
$headerTitle = "Debug > Gerenciador de sessões";
include "iniset.php";
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////

if ($isdeveloper){
  $dir = scandir($path_to_temp_dir);
  echo "<PRE>\n";
  $indice = 0;
  foreach($dir as $file){
    if (strpos(" " . $file, "sess_")){
      //echo $file . "\n";
      $sessao = carrega($path_to_temp_dir . "/" . $file, false);
      $campos = explode(";", $sessao);
      foreach($campos as $campo){
	$variaveis = explode("|", $campo);

	//echo "\$variaves[0]: " . $variaveis[0] . "\n";
	//echo "\$variaves[1]: " . $variaveis[1] . "\n";
	//echo "posicao: " . strpos($variaveis[1], '"') . "\n";
	//echo "tamanho: " . (strlen($variaveis[1]) - strpos($variaveis[1], '"') )  . "\n";
	//echo substr( $variaveis[1], strpos($variaveis[1], '"') , strlen($variaveis[1])) . "\n";
	
	$sessoes[$indice][$variaveis[0]] = substr( $variaveis[1], strpos($variaveis[1], '"') + 1, (strlen($variaveis[1]) - strpos($variaveis[1], '"')) - 2 );
	
        //echo "-----------------------\n";
      }

      //echo $sessao . "\n";
    }
    $indice++;
  }
  foreach($sessoes as $sessao){
    echo $sessao['h_log'] . " - " . $sessao['matricula'] . " - " . $sessao['nome'] . " - " . $sessao['ip'] . "\n";
  }
  echo "</PRE>\n";
 
 }
?>




<?PHP
include "page_footer.inc";
?>
