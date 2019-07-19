<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
// $field = pg_escape_string(trim($_GET['field']));
$id = intval($_GET['id']);
//$image = intval($_GET['image']);
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 1;
//$headerTitle = "Página de gabarito";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "light_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario
 
  $sql = "SELECT encode(\"Conteúdo\", 'base64') AS field FROM arquivos WHERE \"codigo\" = ".$id;
  $res = pg_query($conn, $sql);
  $raw = pg_fetch_result($res, 'field');
  ////////////////////////////////////////////
  
  $fileArray = formsDecodeFile(base64_decode($raw));
  
  header("Content-Type: ". $fileArray['type']);
  header('Content-Disposition: attachment; filename="' . $fileArray['name'] . '"');
  echo $fileArray['contents'];

  /* if($image){
      header('Content-type: image');
  }else{
    // We'll be outputting a PDF
    header('Content-Type: application/pdf');
    // It will be called downloaded.pdf
    header('Content-Disposition: attachment; filename="' . $name . '_s_' . strtoupper($field) . '.pdf"');
  }
  echo base64_decode($raw); */
  
  pg_close($conn);
  
  



?>

<?PHP
include "page_footer.inc";
?>
