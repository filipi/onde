<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
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
if(isset($_POST['nome'])) {
  foreach ($_POST as $key => $value){
    $_escaped[$key] = pg_escape_string(trim($value));

  }
  $data = file_get_contents($_FILES['images']['tmp_name']);
  $foto = bin2hex($data);
  $query = "SELECT * FROM images WHERE \"filename\" = ". intval($_escaped['nome']).";";
  $result = pg_query($conn, $query);
  $exists = pg_NumRows($result);
  $usuario = pg_fetch_row($result);
  if($exists){
    $sql = "UPDATE images\n";
    $sql.= " SET \"filename\" = '".$_escaped['nome']."',\n";
    $sql.= "     \"images\" = decode ('{$foto}', 'hex'), \n";
    $sql.= "WHERE \"codigo\" = ".$usuario[0].";";
  }else {
    $sql = "INSERT INTO images(filename, images)\n";
    $sql.= " VALUES (";
    $sql.= "     '".$_escaped['nome']."',\n";
    $sql.= "     decode('{$foto}', 'hex') \n";
    $sql.= "         );";
  }
  //echo "<PRE>\n";
  //echo $sql;
  //echo "</PRE>\n";
  pg_query($conn, $sql);

} else {
  echo "Preencha todos os campos.";
}


//<img src=\"loadFiles.php?id=".$profileImage[0]."&image=1&field=images\" height = 255
?>
    <form method="POST" enctype="multipart/form-data">
      Nome:
      <input type="text" name="nome"></br></br>

      Imagem:
      <input type="file" name="images"></br></br>
      <input type="submit" value="Send">

    </form>
  </br></br>
  <a href = "listImages.php"> Lista de imagens </a>

<?PHP
include "page_footer.inc";
?>
