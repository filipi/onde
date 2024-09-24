<?PHP

/**
 * Pagina de listagem de ementas [TESTE]
 * $Id: perfil.php,v 1.26 2019/01/10 18:48:08 filipi Exp $
 */

$useSessions = 1;
$ehXML = 0;
$headerTitle = "Configura&ccedil;&otilde;es pessoais";
include "iniset.php";
include "page_header.inc";
global $PHPSESSID;

echo "<BR>\n";
if ($_debug) {
    echo "<B>Conection handle=" . $conn . "</B><BR>\n";
}

if ($_debug) {
    echo "<B>Genero: " . $_POST['genero'] . "</B><BR>\n";
}

$_POST['buttonrow'][$_SESSION['matricula']] = "Detalhes...";

if ($_POST['envia'] == 'Inserir' || $_POST['salvar']) {
    if (htmlentities(trim($_POST['senha'])) != htmlentities(trim($_POST['confirma']))) {
        $_POST['buttonrow'][$_POST['login']] = "Detalhes...";
        $messagem = "Senhas n&atilde;o conferem.<BR><BR>\n";
        $messagem .= "As informa&ccedil;&otilde;es ser&atilde;o atualizadas<BR>\n";
        $messagem .= "por&eacute;m a senha ser&aacute; mantida a mesma.<BR>\n";
        warning($messagem);
    }
}

if ($_debug) {
    echo "<PRE>";
    var_dump($_POST);
    echo "</PRE>";
}

############################################--Versão antiga--############################################################
/**
    * if ($_FILES['userfile']['name']) {
    *    
    *    if ($_debug){
    *        echo "<B>VARDUMP(FILES)</B>:<BR>\n<PRE>\n";
    *          var_dump($_FILES);
    *          echo "</PRE>\n";
    *        }
    *        //$code = round(time() / 10 * rand(1,10));
    *        //$filename = "oct" . $code;
    *        if ($_debug) echo "\n<BR><B>MD5:</B>\n";
    *        $md5 = md5_file($_FILES['userfile']['tmp_name']);
    *        if ($_debug) echo $md5 . "<BR>\n";
    *        //if (!(move_uploaded_file($_FILES['userfile']['tmp_name'], $path_to_temp_dir . $filename . ".dat")))
    *        $type = explode("/", $_FILES['userfile']['type']);
    *        if ($type[0]=="image"){
    *          $serverImageFile = "session_files/image_" . $md5 . "." . $type[1];
    *          if (!(move_uploaded_file($_FILES['userfile']['tmp_name'], $serverImageFile))){
    *            echo "<DIV class=\"schedulled\">";
    *            echo "Erro ao carregar arquivo.</DIV";
    *          }
    *        }
    *        else{
    *          echo "<DIV class=\"schedulled\">";
    *          echo "Arquivo carregado n&atilde;o foi reconhecido como imagem v&aacute;lida!</DIV>";
    *        }
    * } 
*/
###############################################--Inserir imagem no banco de dados--######################################
 /**
  * Insere a imagem de perfil do usuário no banco de dados
  * Cria thumbnail e versão reduzida
  * @author: Gustavo Leal;
  *  
  */

  $query  = "SELECT codigo FROM albuns WHERE \"Álbum\" = 'Perfil'";
  $result = pg_query($conn, $query);
  $album  = pg_fetch_row($result);
  $album = intval(trim($album[0]));
  if(!$album){
    $query = "INSERT INTO albuns(\"Álbum\", sistema) VALUES('Perfil', 't')";
    $result = pg_query($conn, $query);
    if($result){
      $query  = "SELECT codigo FROM albuns WHERE \"Álbum\" = 'Perfil'";
      $result = pg_query($conn, $query);
      $album  = pg_fetch_row($result);
      $album = intval(trim($album[0]));
    } else {
      warning("Erro ao inserir álbum Perfil! ".pg_last_error());
    }
  }

  if ($_FILES['userfile']['name']) {              
    $type = explode("/", $_FILES['userfile']['type']);                    //Pega tipo do arquivo [0] => tipo [1] =>extensão

    if ($type[0] == "image") {
        $file_name = $_FILES['userfile']['name'];
        $fileArray['name'] = $_FILES['userfile']['name'];
        $fileArray['type'] = $_FILES['userfile']['type'];
        /**
         * Se não existir o diretório simulation para a sessão do usuário, ele é criado.
         * Se não existir o diretório para arquivos temporários, ele é criado.
         * @author: Filipi Viana e Gustavo Leal;
         */
        if ($type[1] == 'jpeg' || $type[1] == 'jpg' || $type[1] == 'tiff'){  //EXIF da imagem
          $exif = exif_read_data($_FILES['userfile']['tmp_name']);
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

        $simulation = "simulation" . $PHPSESSID;
        $uploadDir = "/upload/";
        if (!file_exists(($useSessions ? "session_files/" : "") . $simulation)) {
        mkdir("./" . ($useSessions ? "session_files/" : "") . $simulation, 0777);
        }
        if (!file_exists(($useSessions ? "session_files/" : "") . $simulation . $uploadDir)) {
        mkdir("./" . ($useSessions ? "session_files/" : "") . $simulation . $uploadDir, 0777);
        }

        /**
         * Gera string com o diretório para armazenar as imagens temporariamente;
         * Move o arquivo temporário para o diretório gerado;
         */
        $abspath = realpath(dirname(__FILE__));
        $uploadPath = $abspath . "/session_files/" . $simulation . $uploadDir;
        $file_tmp = $_FILES['userfile']['tmp_name']; //// Nome do arquivo temporário salvo no computador para processamento;
        if ($_debug){
            echo "<PRE>\n";
            var_dump($_FILES);
            echo "</PRE>\n";
        }
        $file = $uploadPath . $file_name;
        if ($_debug) echo "\$file: " . $file . "<BR>\n";
        if (move_uploaded_file($file_tmp, $file)){
        if ($_debug) echo "SUCESSO ... movido<BR>\n";
        }
    
        if ($_debug) { ///////////////////////////////DEBUG
            echo "<PRE>";
            print_r($file);
            echo "</PRE>";
        }
            
        if ($_debug) ///////////////////////////////// DEBUG
          echo "\n<BR><B>MD5:</B>\n";

        $md5 = md5_file($file); ////////////////////// Assinatura MD5 da imagem original
        if ($_debug) ///////////////////////////////// DEBUG
            echo $md5 . "<BR>\n";
        
        $filedata = file_get_contents($file); //////// Lê o conteúdo da imagem principal
        $fileArray['contents'] = $filedata;
        $filedata = formsEncodeFile($fileArray);
        if ($_debug) ///////////////////////////////// DEBUG
        echo "<pre>" . $filedata . "</pre>";

        /* $query  = "SELECT codigo FROM albuns WHERE \"Álbum\" = 'Perfil'";
        $result = pg_query($conn, $query);
        $album  = pg_fetch_row($result);
        
        $album = intval(trim($album[0])); */
        
        
        if ($_debug) ///////////////////////////////// DEBUG
            echo "album = ".$album;
            
        $mainImage = insertFile($file_name, $filedata, $type, $exif, $md5, $album, null, null, null, $_SESSION['matricula']); // Insere a imagem principal no BD
        if(!$mainImage){
          $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '".$md5."'";
          $query.= "AND \"Conteúdo\" IS NOT NULL;";
          $result = pg_query($conn, $query);
          $codigoArq = pg_fetch_row($result);
            //$codigoArq = $codigoArq[0]; 
        }else{
           //echo $mainImage;
          echo $mainImage.": ".uploadErrors($mainImage);
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
        if ($_debug) echo "Reduzindo arquivo.... (" . $file . ")";
	$smallName = $md5 . "_pequeno";
        $smallFile = resizeImage($file, $type, $smallProp, $smallName, $uploadPath);
        if ($smallFile){
            if ($_debug) echo "[  OK  ]\n";
        }
        else{
            if ($_debug) echo "[ FAIL ]\n";      
        }
        $md5Small = md5_file($smallFile); //////////////// Assinatura MD5
        $filedata = file_get_contents($smallFile);
        $fileArray['contents'] = $filedata;
        $filedata = formsEncodeFile($fileArray);
        $smallImage = insertFile($file_name, $filedata, $type, null, $md5Small, $album, $codigoArq[0], 1, null, $_SESSION['matricula']);
        unlink($smallFile);
        if($smallImage){
            echo $smallImage.": ".uploadErrors($smallImage);
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
	$thumbName = $md5 . "_miniatura";
        $thumbFile = resizeImage($file, $type, $thumbProp, $thumbName, $uploadPath);
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
        unlink($thumbFile);
        if($thumbImage){
            echo $thumbImage.": ".uploadErrors($thumbImage);
            echo json_encode($return, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);;
            exit(0);
        }
        unlink($file);
        $imagemPerfil = codigoImagem($_POST['login'], $album, $md5);
    } else {
        echo "<DIV class=\"schedulled\">";
        echo "Arquivo carregado n&atilde;o foi reconhecido como imagem v&aacute;lida!</DIV>";
    }
}

if ($_POST['salvar']) {
    //$imagemPerfil = getUserProfilePictureCode($disciplina['login']);

    $query = "UPDATE usuarios\n";
    $query .= "  SET nome = '" . $_POST['nome'] . "' ";
    
    if (isset($_POST['genero'])) {
        $_SESSION['genero'] = $_POST['genero'];
        if ($_POST['genero'] == "masculino") {
            $query .= ",\n      masculino = true";
        } else
        if ($_POST['genero'] == "feminino") {
            $query .= ",\n      masculino = false";
        }

    }
    if (trim($_POST['aniversario'])) {
        $diaMes = explode("/", $_POST['aniversario']);
        $query .= ",\n      aniversario = '2009-" . $diaMes[1] . "-" . $diaMes[0] . "'";
    }
    if (isset($_POST['email'])) {
        $query .= ",\n      email = '" . $_POST['email'] . "'";
    }

    if (isset($_POST['ramal'])) {
        $query .= ",\n      ramal = '" . $_POST['ramal'] . "'";
    }

    if (isset($_POST['celular'])) {
        $query .= ",\n      celular = '" . $_POST['celular'] . "'";
    }

    if (isset($_POST['endereco'])) {
        $query .= ",\n      endereco = '" . $_POST['endereco'] . "'";
    }

    if (isset($_POST['cep'])) {
        $query .= ",\n      cep = '" . $_POST['cep'] . "'";
    }

    if (isset($_POST['cidade'])) {
        $query .= ",\n      cidade = '" . $_POST['cidade'] . "'";
    }

    if (isset($_POST['senha2'])) {
        $query .= ",\n      senha2 = '" . $_POST['senha2'] . "'";
    }

    if (isset($_POST['senha3'])) {
        $query .= ",\n      senha3 = '" . $_POST['senha3'] . "'";
    }

    if ($_POST['senha']) {
        $query .= ",\n      senha = '" . crypt(trim($_POST['senha']), '9$') . "'";
    }

    if ($_POST['horas']) {
        $query .= ",\n      horas = " . intval($_POST['horas']);
    }

    if ($imagemPerfil) {
        $query .= ",\n      avatar = NULL";
        $query .= ",\n      foto = ".$imagemPerfil;
    }
    $query .= "\n  WHERE login = '" . $_POST['login'] . "'";
    $result = pg_exec($conn, $query);
    if ($_debug) {
        echo "<PRE>" . $query . "</PRE><BR>\n";
        if (!$result) {
            $error++;
            echo "Erro na execucao da consulta.\n";
            echo "<PRE>\n";
            echo pg_last_error();
            echo "</PRE>\n";
        }
    }
    if ($erro) {
        $result = pg_exec($conn, "ROLLBACK");
        warning("Erro!");
    } else {
        $result = pg_exec($conn, "COMMIT");
        echo "      <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
        echo "      <DIV CLASS=\"message\">Usu&aacute;rio";
        echo "      salvo com sucesso!</DIV><BR>\n";
    }
    $_SESSION['nome'] = $_POST['nome'];
    $_SESSION['email'] = $_POST['email'];
}

if ($_POST['buttonrow'] ||
    substr(trim($_POST['botao']), 0, 4) == "Novo") {

    while (list($key, $val) = each($_POST['buttonrow'])) {
        if ($_debug) {
            echo "<B>login: " . $key;
            echo " - " . $_POST['buttonrow'][$key] . "</B><BR>\n";
        }
        $query = "select * from usuarios where login='" . $key . "'";
        if ($_debug) {
            echo "<PRE>" . $query . "</PRE><BR>\n";
        }

        $result = pg_exec($conn, $query);
        $disciplina = pg_fetch_array($result, 0);
        if ($_debug) {
            echo "<B>login: " . $disciplina['login'] . "</B><BR>\n";
        }

        if ($_debug) {
            echo "<B>nome: " . $disciplina['nome'] . "</B><BR>\n";
        }

    }
/*######################################################VERSÃO ANTERIOR####################################################
    /* if ($disciplina['avatar'] && !$serverImageFile) {
        $serverImageFile = $disciplina['avatar'];
    }

    if ($serverImageFile) {
        $size = getimagesize($serverImageFile);
//     echo "<PRE>\n";
        //     var_dump($size);
        //     echo "</PRE>\n";
        echo "<DIV class=\"codigoDeDemanda\">\n";
        echo "<IMG src=\"" . $serverImageFile . "\" ALT=\"ideia_image\" ";
        if ($size[0] > 255) {
            echo " width=255 ";
        }

        echo "ALIGN=\"bottom\" BORDER=\"0\">\n";
        echo "</DIV>\n";
    } */

##########################################################################################################################*/
################################################--Buscar imagem no banco de dados--######################################
/** 
 *
 *  Seleciona as imagens do banco de dados para apresentar na imagem de perfil;
 *  
 *  @author: Gustavo Leal
 */

    //$imagemPerfil = codigoImagem($disciplina['login'], $album, $codigo);
    $imagemPerfil = getUserProfilePictureCode($disciplina['login']);
    
    if($imagemPerfil){
 
    echo "<DIV class=\"codigoDeDemanda\">\n";
    echo "<img src=\"loadFiles.php?id=".$imagemPerfil."\" height = 220 \n";
    //    if ($size[0] > 255) {
    //        echo " width=220 ";
    //    }
    echo "ALIGN=\"bottom\" BORDER=\"0\">\n";
    echo "</DIV><BR>";// . $imagemPerfil . "\n"; 
}

/*************************************************************************************************************************/
    //echo "  <FORM ACTION=\"" . basename( $_SERVER['PHP_SELF']) . "\" ";
    echo "  <FORM ACTION=\"\" ";
    echo "METHOD=\"POST\" ENCTYPE=\"multipart/form-data\">\n";

    echo "    <INPUT TYPE=\"HIDDEN\" NAME=\"salvar\" VALUE=1\>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    if (substr(trim($_POST['botao']), 0, 4) == "Novo") {
        echo "    <B>login: </B><BR>\n";
        echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
        echo "    <INPUT TYPE=\"";
        echo "TEXT";
        echo "\" NAME=\"login\" ";
        echo "VALUE=\"" . $disciplina['login'] . "\"><BR>\n";
    } else {
        echo "    <B>login: </B>" . $disciplina['login'] . "<BR><BR>\n";
        echo "    <INPUT TYPE=\"";
        echo "HIDDEN";
        echo "\" NAME=\"login\" ";
        echo "VALUE=\"" . $disciplina['login'] . "\">\n";
    }


    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Nome:</B> " . $disciplina['nome'] . "<BR><BR>";
    echo "    <INPUT TYPE=\"HIDDEN\" CLASS=\"TEXT\" NAME=\"nome\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"100\"";
    echo "     VALUE=\"" . $disciplina['nome'] . "\">";


    /* echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n"; */
    /* echo "    <B>Nome:</B><BR>\n"; */
    /* echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n"; */
    /* echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"nome\" SIZE=\"40\"\n"; */
    /* echo "    MAXLENGTH=\"100\""; */
    /* echo "     VALUE=\"" . $disciplina['nome'] . "\"><BR><BR>"; */


    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>G&ecirc;nero:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"RADIO\" NAME=\"genero\" ";
    if ($disciplina['masculino'] == "f") {
        echo " CHECKED ";
    }

    echo "VALUE=\"feminino\">feminino<BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"RADIO\" NAME=\"genero\" ";
    if ($disciplina['masculino'] == "t") {
        echo " CHECKED ";
    }

    echo "VALUE=\"masculino\">masculino<BR><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>e-mail:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"email\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"200\"";
    echo "     VALUE=\"" . $disciplina['email'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Ramal:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"ramal\" SIZE=\"10\"\n";
    echo "    MAXLENGTH=\"200\"";
    echo "     VALUE=\"" . $disciplina['ramal'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Celular:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"celular\" SIZE=\"20\"\n";
    echo "    MAXLENGTH=\"20\"";
    echo "     VALUE=\"" . $disciplina['celular'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Endereço:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"endereco\" SIZE=\"50\"\n";
    echo "    MAXLENGTH=\"200\"";
    echo "     VALUE=\"" . $disciplina['endereco'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>CEP:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"cep\" SIZE=\"10\"\n";
    echo "    MAXLENGTH=\"9\"";
    echo "     VALUE=\"" . $disciplina['cep'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Cidade:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"cidade\" SIZE=\"50\"\n";
    echo "    MAXLENGTH=\"200\"";
    echo "     VALUE=\"" . $disciplina['cidade'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Senha:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"PASSWORD\" CLASS=\"PASSWORD\" NAME=\"senha\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"200\"";
    $disciplina['senha'] = "";
    echo "     VALUE=\"" . $disciplina['senha'] . "\"><BR><BR>";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Confirma senha:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"PASSWORD\" CLASS=\"PASSWORD\" NAME=\"confirma\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"200\"";
    echo "     VALUE=\"" . $disciplina['confirma'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Senha principal no Sistema IDEIA (GTiT):</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"senha2\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"6\"";
    echo "     VALUE=\"" . $disciplina['senha2'] . "\"><BR><BR>";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Senha alternativa no Sistema IDEIA (GTiT):</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"senha3\" SIZE=\"40\"\n";
    echo "    MAXLENGTH=\"6\"";
    echo "     VALUE=\"" . $disciplina['senha3'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>N&uacute;mero de horas semanais:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"horas\" SIZE=\"6\"\n";
    echo "    MAXLENGTH=\"10\"";
    echo "    onKeypress=\"if(event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;\"";
    echo "     VALUE=\"" . $disciplina['horas'] . "\"><BR><BR>";

    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "    <B>Data de anivers&aacute;rio:</B><BR>\n";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    if ($disciplina['aniversario']) {
        $aniversario = date("d/m", strtotime($disciplina['aniversario']));
    } else {
        $aniversario = "";
    }

    ?>
<input type="text" name="aniversario" id="f_date_b" value="<?php echo $aniversario; ?>"><button type="reset" id="f_trigger_b">...</button>
<script type="text/javascript">
   Calendar.setup({
     inputField     :    "f_date_b",      // id of the input field
	 ifFormat       :    "%d/%m",       // format of the input field
	 showsTime      :    false,            // will display a time selector
	 button         :    "f_trigger_b",   // trigger for the calendar (button ID)
	 singleClick    :    false,           // double-click mode
	 step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	 });
</script><BR><BR>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="hidden" name="MAX_FILE_SIZE" value="100000000000000">
    <B>Upload de imagens: </B><BR>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT NAME="userfile" TYPE="file">
    <BR>
<?PHP
echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    if (substr(trim($_POST['botao']), 0, 4) == "Novo") {
        echo "    <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"Inserir\" NAME=\"envia\">\n";
    } else {
        echo "    <INPUT TYPE=\"SUBMIT\" CLASS=\"SUBMIT\" VALUE=\"Salvar\" NAME=\"envia\">\n";
    }
    echo "  </FORM>\n";
}
include "page_footer.inc";
?>
