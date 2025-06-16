<?PHP
  //////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
  ///////////////////////////////////////////////////////////////// Tratando POST
  /////////////////////////////////////////////// GET passado para links (action)

  ///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1;
$ehXML = 1;
$headerTitle = "";
include "iniset.php";
include "page_header.inc";
//$_debug = 1;
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////// Funcoes locais

///////////////////////////////////////////////////////////////////////////////

/**
 * Erros:
 *  => 'Erro ao inserir album newsletter.'
 *  => 'Erro ao ler informações EXIF.'
 *  => 'Erro ao criar diretório da sessão.'
 *  => 'Erro ao criar diretório para arquivos temporários.'
 *  => 'Erro ao realizar upload para arquivo temporário.'
 *  => 'Erro redimensionando imagem.'
 *  => 'Erro gerando thumbnail.'
 *
 */



global $_debug, $filename, $conn, $ehXML, $closeDIV, $useSessions, $PHPSESSID, $_theme, $_SESSION;
$PHPSESSID = trim($PHPSESSID);
//var_dump($_SESSION);


if (isset($_FILES['files']['name']) && empty($_FILES['files']['name']) == false) {

  if ($_debug > 1) {  
    $return['FILES'] = array($_FILES);
  }
  $fileArray['name'] = $_FILES['files']['name'];
  $fileArray['type'] = $_FILES['files']['type'];

  $type = explode("/", $_FILES['files']['type']); //Pega tipo do arquivo [0] => type[1] = extensão

  $file_name = $_FILES['files']['name']; //Pega o nome do arquivo com extensão.

  $md5 = md5_file($_FILES['files']['tmp_name']);
    if ($_debug > 1) {///////////////////////////////// DEBUG
            $return['md5'] = array($md5);
  }
  $query = "SELECT codigo, \"Nome do arquivo\" FROM arquivos WHERE \"Assinatura MD5\" = '".$md5."'";
  $query.= "AND \"Conteúdo\" IS NOT NULL;";
  $result = pg_query($conn, $query);
  $codigoArq = pg_fetch_row($result);

  if ($type[0] == 'image') {
    if(!$codigoArq){
      if ($type[1] == 'jpeg' || $type[1] == 'jpg' || $type[1] == 'tiff'){  //EXIF da imagem
        $exif = exif_read_data($_FILES['files']['tmp_name']);
        $exif = json_encode($exif);
        if(!$exif){     // Erro ao ler informações EXIF.
            //echo json_encode('exif');
        }
      } else {
        $exif = 0;
      }

      if($_debug > 1){
        $return['exif'] = array($exif);
       }
      /**
       * Se não existir o diretório simulation para a sessão do usuário, ele é criado.
       * Se não existir o diretório para arquivos temporários, ele é criado.
       * @author: Filipi Viana e Gustavo Leal;
       */
      
      $simulation = "simulation" . $PHPSESSID;
      $uploadDir = "/upload/";
      if($_debug) echo "Verificando existência de diretórios...\n";
      if (!file_exists(($useSessions ? "session_files/" : "") . $simulation)) {
        if($_debug) echo "Criando diretório de sessão...\n";
        if(mkdir("./" . ($useSessions ? "session_files/" : "") . $simulation, 0777)){
            if($_debug) echo "Diretório de sessão criado.\n";
        } else {    // Erro ao criar diretório da sessão.
          if($_debug) echo "Erro ao criar diretório de sessão\n";
        }
      }
      if (!file_exists(($useSessions ? "session_files/" : "") . $simulation . $uploadDir)) {
        if($_debug) echo "Criando diretório de upload...\n";
        if(mkdir("./" . ($useSessions ? "session_files/" : "") . $simulation . $uploadDir, 0777)){
          if($_debug) echo "Diretório de upload criado.\n";
        } else {    // Erro ao criar diretório para arquivos temporários.
          if($_debug) echo "Erro ao criar diretório de upload\n";
        }
      }

      /**
       * Gera string com o diretório para armazenar as imagens temporariamente;
       * Move o arquivo temporário para o diretório gerado;
       */

      $abspath = realpath(dirname(__FILE__));
      $uploadPath = $abspath . "/session_files/" . $simulation . $uploadDir;
      $file_tmp = $_FILES['files']['tmp_name']; //// Nome do arquivo temporário salvo no computador para processamento;
      if ($_debug > 1) {
        $return['path'] = array($uploadPath);
      }
      $file = $uploadPath . $file_name;
      if ($_debug > 1) {
        $return['file'] = array($file);
      }

      if ($_debug) echo "\$file: " . $file . "<BR></br>\n";
      if (move_uploaded_file($file_tmp, $file)){
        if ($_debug) echo "SUCESSO ... movido<BR></br>\n";
      } else {    // Erro ao realizar upload para arquivo temporário.
        if ($_debug) echo "FALHA<BR></br>\n";
        //echo json_encode('');
      }
  
      $filedata = file_get_contents($file); //////// Lê o conteúdo da imagem principal
      //$filedata = bin2hex($filedata); ////////////// Converte conteúdo da imagem principal para hexadecimal
      $fileArray['contents'] = $filedata;
      $filedata = formsEncodeFile($fileArray);
      if ($_debug > 1) {  ///////////////////////////////// DEBUG
        $return['filedata'] = array($filedata);
      }
      
      $album = intval(trim($_GET['album']));

      if ($_debug > 1) {  ///////////////////////////////// DEBUG
          $return['album'] = array($album);
      } 
      
      $mainImage = insertFile($file_name, $filedata, $type, $exif, $md5, $album, null, null, null, $_SESSION['matricula']); // Insere a imagem principal no BD
      if(!$mainImage){
        $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '".$md5."'";
        $query.= "AND \"Conteúdo\" IS NOT NULL;";
        $result = pg_query($conn, $query);
        $codigoArq = pg_fetch_row($result);
        //$codigoArq = $codigoArq[0]; 
      }else{
          //echo $mainImage;
          $return['error'] = array($mainImage, uploadErrors($mainImage));
          echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);;
          exit(0);
      }
      /**
       * Gera o nome para versão reduzida da imagem;
       * Gera versão reduzida da imagem e a salva no diretório temporário;
       *      $smallProp => proporção para a imagem reduzida;
       * 
       * Insere imagem reduzida no BD.
       * Exclui a imagem do diretório.
       */
    
      $smallProp = 512;
      if ($_debug) echo "Reduzindo arquivo....";
      $smallFile = resizeImage($file, $type, $smallProp, "small_".$file_name, $uploadPath);
      if ($smallFile){
        if ($_debug) echo "[  OK  ]\n";
      }
      else{
        if ($_debug) echo "[ FAIL ]\n";      
      }
      $md5Small = md5_file($smallFile); //////////////// Assinatura MD5
      $filedata = file_get_contents($smallFile);
      //$filedata = bin2hex($filedata); ////////////// Converte conteúdo para hexadecimal
      $fileArray['contents'] = $filedata;
      $filedata = formsEncodeFile($fileArray);
      $smallImage = insertFile($file_name, $filedata, $type, null, $md5Small, $album, $codigoArq[0], 1, null, $_SESSION['matricula']);
      unlink($smallFile);
      if($smallImage){
        $return['error'] = array($smallImage, uploadErrors($smallImage));
        echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);;
        exit(0);
      }
      /**
       * Gera o nome para a thumbnail da imagem;
       * Gera thumbnail da imagem e a salva no diretório temporário;
       *      $thumbProp => proporção para o thumbnail;
       * 
       * Insere a thumbnail no BD.
       * Exclui a thumbnail do diretório.
       */
       
      $thumbProp = 80;
      if ($_debug) echo "Gerando thumbnail....";
      $thumbFile = resizeImage($file, $type, $thumbProp, "thumb_".$file_name, $uploadPath);
      echo $thumbFile;
      if ($thumbFile){
        if ($_debug) echo "[  OK  ]\n";
      }
      else{
        if ($_debug) echo "[ FAIL ]\n";      
      }
      $md5Thumb = md5_file($thumbFile); //////////////// Assinatura MD5
      $filedata = file_get_contents($thumbFile);
      //$filedata = bin2hex($filedata); ////////////// Converte conteúdo para hexadecimal
      $fileArray['contents'] = $filedata;
      $filedata = formsEncodeFile($fileArray);
      $thumbImage = insertFile($file_name, $filedata, $type, null, $md5Thumb, $album, $codigoArq[0], null, 1, $_SESSION['matricula']);
      //unlink($thumbFile);
      if($thumbImage){
        
        $return['error'] = array($thumbImage, uploadErrors($thumbImage));
        echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);;
        exit(0);
      }
      unlink($file);
    } else{
         //Verifica se a imagem já é relacionada ao album newsletter
            //echo "passei";
            $query = "SELECT * FROM arquivos_albuns WHERE arquivo = '".$codigoArq[0]."' AND album= '".$album."';";
            $result = pg_query($conn, $query);
            if(!pg_fetch_all($result)){ // Caso não haja relação entre o arquivo e o album, insere ela
                $query = "SELECT codigo FROM arquivos WHERE \"Nome do arquivo\" = '".$codigoArq[1]."'\n";
                $query.= "      AND \"Conteúdo\" IS NOT NULL \n";
                $query.= "      AND small = TRUE;";
                if($_debug > 1){
                    $return['querySmallExist'] = $query;
                }
                $result = pg_query($conn, $query);
                $codigoSmall = pg_fetch_row($result);
                $query = "SELECT codigo FROM arquivos WHERE \"Nome do arquivo\" = '".$codigoArq[1]."'\n";
                $query.= "      AND \"Conteúdo\" IS NOT NULL \n";
                $query.= "      AND thumb = TRUE;";
                if($_debug > 1){
                    $return['queryThumbExist'] = $query;
                }
                $result = pg_query($conn, $query);
                $codigoThumb = pg_fetch_row($result);
                if($album > 0){
                    $query = "INSERT INTO arquivos_albuns(arquivo, album) VALUES (";
                    $query .= $codigoArq[0] . ", ";
                    $query .= $album . "); \n";
                    $query .= "INSERT INTO arquivos_albuns(arquivo, album) VALUES (";
                    $query .= $codigoSmall[0] . ", ";
                    $query .= $album . "); \n";
                    $query .= "INSERT INTO arquivos_albuns(arquivo, album) VALUES (";
                    $query .= $codigoThumb[0] . ", ";
                    $query .= $album . "); \n";
                    if($_debug > 1){
                        $return['queryInsertAlbuns'] = $query;
                    }
                    $result = pg_query($conn, $query);
                } 
            }
        }
    
  } else {
    if(!$codigoArq){
      $filedata = file_get_contents($_FILES['files']["tmp_name"]); /////////// Pega conteúdo do arquivo em binário
      //$filedata = bin2hex($filedata); ////////////// Converte conteúdo para hexadecimal
      $fileArray['contents'] = $filedata;
      $filedata = formsEncodeFile($fileArray);
      if ($_debug) {///////////////////////////////// DEBUG
          $return['filedata'] = array($filedata);
      }
      $file = insertFile($file_name, $filedata, $type, null, $md5, null, null, null, null, $_SESSION['matricula']);
      if(!$file){
          $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '".$md5."'";
          $query.= "AND \"Conteúdo\" IS NOT NULL;";
          $result = pg_query($conn, $query);
          $codigoArq = pg_fetch_row($result);
          //$codigoArq = $codigoArq[0]; 
      }
      if($file){
          $return['error'] = array($file, uploadErrors($file));
          echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
          exit(0);
     }
    }
  }
}
$return['arq'] = 1;
echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
include "page_footer.inc";
if ($_debug) echo "\n________________________________________ FIM ________________________________________\n";
?>