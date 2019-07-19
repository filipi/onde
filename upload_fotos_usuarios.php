#!/usr/bin/php
<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)

///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 0;
$ehXML = 1;
$headerTitle = "";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario
$query = "SELECT login, avatar FROM usuarios;\n";
$result = pg_query($conn, $query);

$usuarios = pg_fetch_all($result);

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
$uploadPath = $abspath . ($useSessions ? "session_files/" : "/") . $simulation . $uploadDir;

$query = "SELECT codigo FROM albuns WHERE \"Álbum\" = 'Perfil'";
$result = pg_query($conn, $query);
$album = pg_fetch_row($result);
$album = intval(trim($album[0]));
if (!$album) {
    $query = "INSERT INTO albuns(\"Álbum\") VALUES('Perfil')";
    $result = pg_query($conn, $query);
    if ($result) {
        $query = "SELECT codigo FROM albuns WHERE \"Álbum\" = 'Perfil'";
        $result = pg_query($conn, $query);
        $album = pg_fetch_row($result);
        $album = intval(trim($album[0]));
    } else {
        warning("Erro ao inserir álbum Perfil! " . pg_last_error());
    }
}

foreach ($usuarios as $user) {
    $path = "fotos_usuarios/" . $user['login'] . "/";
    $arquivos = scandir($path);
    unset($arquivos[array_search("..", $arquivos)]);
    unset($arquivos[array_search(".", $arquivos)]);    
    unset($arquivos[array_search("CVS", $arquivos)]);    
    //  echo $user['login']." fotos:</br>";
    /* if(!$arquivos){
        $arquivos[0] = "";
        $path = "fotos_usuarios/padrao";
    } */
    foreach ($arquivos as $foto) {
        echo $foto . "\n";
        $filePath = $path . $foto;
        $file_name = $foto;
        //echo "</br></br>nome: ".$file_name."</br>";
        //echo "md5: ".$md5."</br>";
        $type = mime_content_type($filePath);
        $fileArray['name'] = $file_name;
        $fileArray['type'] = $type;
        //echo "tipo: ".$type."</br>";
        $type = explode("/", $type); //Pega tipo do arquivo [0] => tipo [1] =>extensão
        //if($type[0] == 'image'){
            if ($type[1] == 'jpeg' || $type[1] == 'jpg' || $type[1] == 'tiff') { //EXIF da imagem
                $exif = exif_read_data($filePath);
                $exif = json_encode($exif);
                if (!$exif) { // Erro ao ler informações EXIF.
                    //echo json_encode('exif');
                }
            } else {
                $exif = 0;
            }
            echo "Inserindo imagem principal ...";
            $md5 = md5_file($filePath);
            $filedata = file_get_contents($filePath);
            $fileArray['contents'] = $filedata;
            $filedata = formsEncodeFile($fileArray);

	    // Insere a imagem principal no BD
            $mainImage = insertFile($file_name, $filedata, $type, $exif, $md5, $album, null, null, null, $user['login']); 

            if (!$mainImage) {
                echo "[  OK  ]\n";
                $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '" . $md5 . "'";
                $query .= "AND \"Conteúdo\" IS NOT NULL;";
                $result = pg_query($conn, $query);
                $codigoArq = pg_fetch_row($result);
                //$codigoArq = $codigoArq[0];
                echo "codigo arquivo: " . $codigoArq[0] . ($ehXML ? "\n" : "</br>");
            } else {
                //echo $mainImage;
                echo "[ FAIL ]\n";
                echo $mainImage . ": " . uploadErrors($mainImage);
		echo "\n";
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
            
            if($type[1] != 'svg+xml' && $type[1] != 'gif'){
                $size = getimagesize($filePath);
            
                if($size[0] > 1024){
                    $smallProp = 512;
                }
                else if ($size[0] > 255){
                    $smallProp = $size[0] * 0.5;
                }
                else{ 
                    $smallProp = $size[0] * 0.8;
                }

                //if ($_debug) {
                    echo "Reduzindo arquivo....";
                //}

                $smallFile = resizeImage($filePath, $type, $smallProp, "small_".$file_name, $uploadPath);
                if ($smallFile) {
                    if ($_debug) {
                        echo "[  OK  ]" . ($ehXML ? "\n" : "</br>\n");
                    }

                } else {
                    if ($_debug) {
                        echo "[ FAIL ]" . ($ehXML ? "\n" : "\n</br>");
                    }

                }
                $md5Small = md5_file($smallFile); //////////////// Assinatura MD5
                $filedata = file_get_contents($smallFile);
                $fileArray['contents'] = $filedata;
                $filedata = formsEncodeFile($fileArray);
                $smallImage = insertFile($file_name, $filedata, $type, null, $md5Small, $album, $codigoArq[0], 1, null, $user['login']);
                unlink($smallFile);
                if ($smallImage) {
                    echo "[ FAIL ]" . ($ehXML ? "\n" : "\n</br>");
                    echo $smallImage . ": " . uploadErrors($smallImage);
                    exit(0);
                } else {
                    echo "[  OK  ]" . ($ehXML ? "\n" : "\n</br>");
                }
                /**
                 * Gera o nome para a thumbnail da imagem;
                 * Gera thumbnail da imagem e a salva no diretório temporário;
                 *      $thumbProp => proporção para o thumbnail;
                 *
                 * Insere a thumbnail no BD.
                 * Exclui a thumbnail do diretório.
                 */
                
                if($size[0] > 80)
                    $thumbProp = 80;
                else 
                    $thumbProp = $size[0] * 0.9;
                
                //if ($_debug) {
                    echo "Gerando thumbnail....";
                //}

                $thumbFile = resizeImage($filePath, $type, $thumbProp, "thumb_".$file_name, $uploadPath);
                if ($thumbFile) {
                    if ($_debug) {
                        echo "[  OK  ]\n";
                    }

                } else {
                    if ($_debug) {
                        echo "[ FAIL ]\n";
                    }

                }
                $md5Thumb = md5_file($thumbFile); //////////////// Assinatura MD5
                $filedata = file_get_contents($thumbFile);
                //$filedata = bin2hex($filedata); ////////////// Converte conteúdo para hexadecimal
                $fileArray['contents'] = $filedata;
                $filedata = formsEncodeFile($fileArray);
                $thumbImage = insertFile($file_name, $filedata, $type, null, $md5Thumb, $album, $codigoArq[0], null, 1, $user['login']);
                if ($thumbImage) {
                    echo "[ FAIL ]" . ($ehXML ? "\n" : "\n</br>");
                    echo $thumbImage . ": " . uploadErrors($thumbImage);
                    exit(0);
                } else {
                    echo "[  OK  ]". ($ehXML ? "\n" : "\n</br>");
                }
            //unlink($thumbFile);
        }
    }

    echo ($ehXML ? "\n" : "</br>") . "User: ".$user['login'] . ($ehXML ? "\n" : "</br>") . "Relacionando foto de perfil utilizada... ";
    $avatar = $user['avatar'];
    $avatar = explode("/", $avatar);
    $avatar = $avatar[1];
    echo $avatar;
    $query = "SELECT codigo FROM arquivos \n";
    $query.= "          WHERE \"Nome do arquivo\" = '" . $avatar . "'\n"; 
    $query.= "              AND thumb = 'f' AND small = 'f'\n"; 
    $query.= "              AND \"Conteúdo\" IS NOT NULL     \n";
    if($arquivos){
        $query.= "                  AND \"Proprietário\" = '" . $user['login']."'\n";
    }
    $result = pg_query($conn, $query);
    $foto = pg_fetch_row($result);
    echo " = ".$foto[0].($ehXML ? "\n" : "</br>");
    if($foto){
        $query = "UPDATE usuarios SET foto = ".$foto[0]." WHERE login = '" . $user['login'] . "'\n";
        $result = pg_query($conn, $query);
    }
}

include "page_footer.inc";
