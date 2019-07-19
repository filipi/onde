<?PHP
///////////////////////////////////////////////////////////////////////////////
/**
 * Biblioteca de funcoes PHP
 * @author Filipi Vianna <filipi@pucrs.br>
 * $Id: lib.inc,v 1.256 2019/01/18 19:35:29 filipi Exp $
 *
 * Contribuiram para a criacao dessa biblioteca:
 * Marcelo Rodrigues Schmitz
 * Guilherme Reschke
 * Filipi Damasceno Vianna
 * Gustavo Henrique leal
 */
///////////////////////////////////////////////////////////////////////////////

require('Encoding.php');

use ForceUTF8 \Encoding as ForceEncoding;

$menuDivider = "    <HR SIZE=1 WIDTH=\"95%\">\n";
$menuMargin = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$closeDIV = "        </DIV>\n";

$dataDictionary  = "SELECT a.attname, tt.typname, a.atttypmod, a.atthasdef\n";
$dataDictionary .= "  FROM pg_tables as t, pg_class as c, pg_attribute as a, pg_type as tt\n";
$dataDictionary .= "  WHERE\n";
$dataDictionary .= "    tableowner <> 'postgres' AND\n";
$dataDictionary .= "    c.relname = t.tablename AND\n";
$dataDictionary .= "    attrelid = c.oid AND\n";
$dataDictionary .= "    attstattarget<>0 AND \n";
$dataDictionary .= "    tt.oid=a.atttypid";

$foreginKeys  = "select\n";
$foreginKeys .= "  class.relname,\n";
$foreginKeys .= "  (select class.relname \n";
$foreginKeys .= "   from pg_class as class\n";
$foreginKeys .= "     where class.oid=cons.confrelid\n";
$foreginKeys .= "  ) as referenced,\n";
$foreginKeys .= "  cons.conkey,\n";
$foreginKeys .= "  cons.confkey[1],\n";
$foreginKeys .= "  (select pg_attribute.attname\n";
$foreginKeys .= "  from pg_attribute, pg_class\n";
$foreginKeys .= "  where pg_class.oid = pg_attribute.attrelid\n";
$foreginKeys .="     and pg_class.relname=(select class.relname \n";
$foreginKeys .= "                            from pg_class as class\n";
$foreginKeys .= "                            where class.oid=cons.confrelid)\n";
$foreginKeys .= "    and pg_attribute.attnum=cons.confkey[1]) as referencedfield\n";
$foreginKeys .= "from pg_constraint as cons, pg_class as class\n";
$foreginKeys .= "  where cons.conrelid = class.oid\n";
$foreginKeys .= "  and cons.confkey is not null\n";

$withoutMenu[] = "index.php";
$withoutMenu[] = "frm_login.php";
$withoutMenu[] = "auth.php";
$withoutMenu[] = "logoff.php";
$withoutMenu[] = "cleanUp.php";
$withoutMenu[] = "chgpasswd.php";
$withoutMenu[] = "f_cronograma.php";
//$withoutMenu[] = "wireit.php";

// Ver mais itens em $withoutMenu no start_sessao.inc

function fixField($fieldName){
  return stripAccents(strtr($fieldName, ' <>?=.\\-', '________'));
}

/*
 * http://itman.in/en/how-to-get-client-ip-address-in-php/
 */
function getRealIpAddr(){
  if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  else
    $ip = $_SERVER['REMOTE_ADDR'];
  return $ip;
}

/**
 * Retorna string com uma mensagem adequada ao usuários referente ao upload de um arquivo
 * @param integer $error_code - Código de erro informado do $_FILES['error']
 */
// http://php.net/manual/en/features.file-upload.errors.php
function checkUploadError($error_code){
  switch($error_code){
  case UPLOAD_ERR_INI_SIZE:  //Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
  case UPLOAD_ERR_FORM_SIZE: //Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
    $message = "Arquivo acima do tamanho máximo permitido.";
    break;
  case UPLOAD_ERR_PARTIAL:   //Value: 3; The uploaded file was only partially uploaded.
    $message = "Upload incompleto.";
    break;
  case UPLOAD_ERR_CANT_WRITE: //Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
    $message = "Problemas ao salvar arquivo no servidor.";
    break;
  case UPLOAD_ERR_NO_FILE:    //Value: 4; No file was uploaded.
  case UPLOAD_ERR_NO_TMP_DIR: //Value: 6; Missing a temporary folder. Introduced in PHP 5.0.3.
  case UPLOAD_ERR_EXTENSION:  //Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.
    $message = "Erro ao fazer o upload (" . $file['error'] . ")";
    break;
  }
  return $message;
}

/**
 * Retorna uma string contendo os dados para insercao nos campos bytea do forms.php
 * @param Array $fileArray - array com os dados do arquivo
 * string $fileArray['name'] = nome do arquivo
 * string $fileArray['type'] = string com o tipo do arquivo
 * string $fileArray['contents'] = conteúdo do arquivo
 */
function formsEncodeFile($fileArray){
  //echo "<B>\$fileArray:</B><PRE>";
  //var_dump($fileArray);
  //echo "</PRE>";
  if (!(trim($fileArray['name'])) || !(trim($fileArray['type'])) || !(trim($fileArray['contents'])) )
    return false;
  $package = $fileArray['name'] . "/" . $fileArray['type'] . "]" . $fileArray['contents'];
  return bin2hex($package);
}

function formsDecodeFile($contents){
  $fileData = hex2bin($contents);
  $fileMarkerPos = strpos($fileData, '/');
  $fileTypePos = strpos($fileData, ']');  
  $fileArray['name'] = substr($fileData, 0, $fileMarkerPos);
  $fileArray['type'] = substr($fileData, $fileMarkerPos + 1, $fileTypePos - ($fileMarkerPos + 1));
  $fileContentsPos = strlen($fileArray['name'] . '/' . $fileArray['type'] . ']');
  $fileArray['contents'] = substr($fileData, $fileContentsPos, strlen($fileData) - $fileContentsPos);

  return $fileArray;
}

function safe_feof($fp, &$start = NULL) {
  $start = microtime(true);
  return feof($fp);
}

function mb_ucfirst($string, $encoding){
  $strlen = mb_strlen($string, $encoding);
  $firstChar = mb_substr($string, 0, 1, $encoding);
  $then = mb_substr($string, 1, $strlen - 1, $encoding);
  return mb_strtoupper($firstChar, $encoding) . $then;
}


function fixEncoding($string){
  global $encoding, $_debug;

  switch ($encoding){
  case 'ISO-8859-1':
    $retorno = ForceEncoding::toLatin1($string);
    break;
  case 'UTF-8':
    $retorno = ForceEncoding::toUTF8($string);
    break;
  default: 
  $retorno = ForceEncoding::toUTF8($string);
 }

  return $retorno;
}

/**
 * Detect IE6 on Server Side Using PHP
 * http://www.zoopable.com/how-to-detect-ie6-on-server-side-using-php/
 */
function is_it_ie6(){
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== FALSE;
}

function printTimeStamp($resetCounter){
  global $printCount;

  if (isset($resetCounter) && ($resetCounter))
    $printCount = 0;
  else
    $printCount++;
  
  $timestamp = date("YYYY", time());
  echo "[" . date("Y-m-d H:i:s", time());
  echo "] ";
}

function menuItem($baseName, $caption, $indentLevel, $notDivide, $hint, $codigo){
  global $menuDivider, $menuMargin, $_theme, $_menu_from_db, $isdeveloper, $isMobile, $PHPSESSID;
  //echo "<PRE>CAPTION: " . $caption . "</PRE>";
  $caption = tiraQuebrasDeLinha($caption, "<|>");
  $caption = explode("<|>", $caption);  
  //echo "<PRE>CAPTION: ";$caption . "</PRE>";
  
  if (isset($hint))  echo "<SPAN TITLE=\"" . trim($hint) . "\">";
  echo "        <A ";
  if ($isMobile) echo " STYLE=\" width: 100%; display:block; height: 48px; font-size: 18px;  vertical-align: middle;\" ";
  echo " HREF=\"" . $baseName . "\" BORDER=0 ";
  if (!stripos("_" . $_theme, "frameless")){    
    echo "TARGET=\"";
    if (strpos("_" . $baseName, "logoff.php") ||  strpos("_" . $baseName, "cleanUp.php"))
      echo "_parent";
    else
      echo "centro";
    echo "\"";
  }
  echo ">";
  foreach($caption as $value){
    indent($indentLevel);
    echo $value;
  }
  echo "</A>\n";  
  if (isset($hint)) echo "</SPAN>";

  if ($_menu_from_db && $isdeveloper && $codigo != NULL && !$isMobile){
  echo "<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=194&buttonrow[" . $codigo . "]=detalhes";
  foreach ($_GET['toggle'] as $value)
    echo "&toggle[]=" . $value;  
  echo "\">[E]</a>\n";
  }    
  
  if (!isset($notDivide) || !$notDivide )echo $menuDivider;
  }

function indent($indentLevel){
  global $menuMargin;
  for ($i=0;$i<$indentLevel;$i++)
    echo $menuMargin;
}

function menuTogglePoint($idString, $label, $indentLevel, $codigo){
  global $menuDivider, $toggle, $_menu_from_db, $isdeveloper, $isMobile, $PHPSESSID;
  //indent($indentLevel);
  echo "<SCRIPT LANGUAGE='javascript'>\n";
  echo "   var M_" . $idString . "_mouse;\n";
  echo "</SCRIPT>\n";
  echo "    <A onClick=\"toggleMenu('" . $idString . "');\"\n";
  echo "    onmouseover=\"M_" . $idString . "_mouse = document.body.style.cursor;";
  echo "    document.body.style.cursor = 'pointer'; \"";
  echo "    onmouseout=\"document.body.style.cursor =  M_" . $idString . "_mouse;\"";
  echo "  STYLE=\"text-decoration: none; cursor: pointer; margin: 0px; padding: 0px; border: 0px;";
  if ($isMobile) echo "  width: 100%; display:block; height: 48px; font-size: 18px;  vertical-align: middle;";  
  echo "\" ";
  echo " BORDER=0";
  echo ">\n";
  indent($indentLevel);
  echo "<B>.::" . $label . "::.</B></A>\n";
  
  if ($_menu_from_db && $isdeveloper && $codigo != NULL && !$isMobile){
  echo "<a href=\"forms.php?PHPSESSID=" . $PHPSESSID . "&form=194&buttonrow[" . $codigo . "]=detalhes";
  foreach ($_GET['toggle'] as $value)
    echo "&toggle[]=" . $value;  
  echo "\">[E]</a>\n";
  }    
  
  
  echo $menuDivider;
  echo "    <DIV ID=\"" . $idString . "\"  style=\"display:";
  if (in_array($idString, $toggle)){
    echo "block";
  }
  else{
    echo "none";
  }
  echo "\">\n";
}

function wordHeader($title, $filename, $landscape){
  global $ehXML, $_debug, $encoding;
  header("Cache-Control: ");// leave blank to avoid IE errors
  header("Pragma: ");// leave blank to avoid IE errors
  header("Content-type: application/octet-stream");
  //header("Content-type: application/vnd.ms-word");
  header("Expires: 0");
  header("Content-Disposition: attachment;Filename=" . $filename);

//
//http://stackoverflow.com/questions/11313098/display-mode-of-word-document-generated-in-php
?>
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo $encoding; ?>">
   <meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 9">
<meta name=Originator content="Microsoft Word 9">
<!--[if !mso]>
<style>
v\:* {behavior:url(#default#VML);}
o\:* {behavior:url(#default#VML);}
w\:* {behavior:url(#default#VML);}
.shape {behavior:url(#default#VML);}
</style>
<![endif]-->
<title><?php if ($title) echo $title;?></title>
<!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:DoNotHyphenateCaps/>
  <w:PunctuationKerning/>
  <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
  <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
 </w:WordDocument>
</xml><![endif]-->
<style>
<?PHP
if ($landscape){
  echo "         <STYLE TYPE=\"text/css\">\n";
  //echo "         <!--\n";
  echo "                 @page { size: landscape }\n";
  //echo "         -->\n";
  echo "         </STYLE>\n";
}
?>
</head>
<body>
<?PHP
}


function checkRelations($linhas){
  global $foreginKeys, $formulario, $_debug, $conn;
  $relationsQuery  = $foreginKeys;
  $relationsQuery .= "  and class.relname = '" . $formulario['tabela'] . "'\n";
  $relationsQuery .= "  and cons.confkey is not null\n";
  $relationsQuery .= "  and cons.conkey = '{" . ( $linhas + 1 ) . "}'\n";
  if ($_debug>2) show_query($relationsQuery, $conn);
  //echo "</CENTER><PRE>" . $relationsQuery . "</PRE><CENTER>\n";
  //show_query($relationsQuery, $conn);
  $relations['result'] = pg_exec ($conn, $relationsQuery);
  $relations['total'] = pg_numrows($relations['result']);
  return $relations;
}


//http://us2.php.net/function.fsockopen
// utilizada funcao isOnline($url) exemplo no help do fsockopen do php.net
function timeOutCheck($url){
  global $ehXML, $_debug, $_no_check_certificate;
  
  if (!$url_info = parse_url($url)) {
    return true;    
  }
  switch ($url_info['scheme']) {
  case 'https':
    $scheme = 'ssl://';
    $port = 443;
    break;
  case 'http':
  default:
    $scheme = '';
    $port = 80;    
  }
  $data = "";

  //$context =  stream_context_create(['verify_peer' => false]);
  //error_reporting( E_ALL );  
  $context = stream_context_create(array(
                  'http' => array(
                      'method' => 'POST',
                      'header' => 'Content-Type: application/json',
                      //'content' => $encryptedEncodedData,
                      ),
                  'ssl' => array(
		      'verify_peer'      => ($_no_check_certificate ? false : true),
                      'verify_peer_name' => ($_no_check_certificate ? false : true),
                      ),
                  )
              );  
  $fid = stream_socket_client($scheme . $url_info['host'] . ":" . $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
  //$fid = @fsockopen($scheme . $url_info['host'], $port, $errno, $errstr, 30);

  if ($_debug > 1){
    echo $scheme . $url_info['host'] . ":" . $port . "\n";
    var_dump($errno);
    echo "Error number: " . $errno . "\n";
    var_dump($errstr);
    echo "Error string: " . $errstr . "\n";
    var_dump($fid);
  }

  if (!$fid && !$errno && !$errstr){
    error_reporting( E_ALL );      
    $fid = stream_socket_client($scheme . $url_info['host'] . ":" . $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    printTimeStamp(false);
    echo "Error number: " . $errno . "\n";
    printTimeStamp(false);
    echo "Error string: " . $errstr . "\n";
    paraBot(14);
  }
  
  if ($fid) {
    fputs($fid, 'HEAD ' . (isset($url_info['path'])? $url_info['path']: '/') . (isset($url_info['query'])? '?' . $url_info['query']: '') . " HTTP/1.0\r\n" . 
	  "Connection: close\r\n" . 
	  'Host: ' . $url_info['host'] . "\r\n\r\n");
    $start = NULL;
    $timeout = ini_get('default_socket_timeout');
    while(!safe_feof($fid, $start) && (microtime(true) - $start) < $timeout){
      //while(!feof($fid)){
      $START_gets = microtime(true);
      stream_set_timeout($fid, intval($timeout/10));            
      $data .= @fgets($fid, 128);
      if ((microtime(true) - $START_gets) >= intval($timeout/10)){
        //echo "TEMPO do fgets: " . (microtime(true) - $START_gets)	. "\n";
	//echo "DESISTINDO...\n";
        fclose($fid);
         empty($data);
         return "tempo de espera excedido";		
      }
    }
    if ($_debug > 1) echo $data . "\n";
    fclose($fid);
    //exit();
    //echo "PASSEI dentro do timeoutcheck (depois do while)\n";          
    //echo "///////////////////////////////////////////////////////\n\n";
    //echo "DATA: " .  $data . "\n\n";
    
    if (strpos($data, "503")){
      if ($ehXML && $_debug){
        printTimeStamp();
	echo "Erro 503 - Serviço temporariamente indisponível\n";
	if ($_debug > 2) echo $data;
      }
      //echo "Erro 503 - Serviço temporariamente indisponível\n";
      return "Erro 503";
    }
    empty($data);
    return false;//!empty($data);
  } else {
    return "tempo de espera excedido";
  }
}

function changeFormID($string, $new){
 $pattern = '/(.*)?(formID=)(\d+)(.*)/i';
 $replacement = '${1}${2}' . $new . '${4}';
 return preg_replace($pattern, $replacement, $string);
}

function changeHtmlImage($string, $images){
  $imagekey = 0;
  if (is_array($images)){
    foreach($images as $image){
      $imagekey++;
      $string = str_replace($image, "cid:1272542224.13304." . $imagekey . ".camel@brainstorm", $string);
    }
  }
  else{
    $string = str_replace($images, "cid:1272542224.13304camel@brainstorm", $string);
  }
  return $string;
}

function getHtmlImage($string, $id){
   global $_debug;
   $array_strings = explode("img", $string);
   $linhas = 0;
   //var_dump($array_strings);
   foreach ($array_strings as $string){
     $linhas++;
     if ($linhas>1){
       $string = "img" . $string;

      //var_dump($string);
      $string = tiraQuebrasDeLinha($string, "<QUEBRADELINHA>");
      if ($_debug>3){
        echo "<PRE>\n";
        echo htmlentities($string);
        echo "</PRE>\n";
      }
     
      if (isset($id))
        $pattern = '/.*?img(.*)?(id="image_' . $id . ')?src="(.*)?(loadFiles.php\?id=' . $id . ')(.*)/i';
        
      else
        $pattern = '/(.*)?img(.*)?(src)="(.*)?(ideia_image)(.*)/i';
            
      //$pattern = '/(.*)?img(.*)?(src)="(.*)?(border)(.*)/i';
      //echo $pattern."</br>";
      $replacement = '${4}';
      $result = preg_replace($pattern, $replacement, $string); 
      
      $result = str_replace("\" border=\"0\" alt=\"", "", $result);
      $resultID = explode("id=", $result); 

      //$result = str_replace("\"", "", $result);
      //echo "<PRE>" . htmlentities($result) . "<BR><BR>\n";
      if(isset($id)){
        
        if($resultID[1] == $id){
          if($_debug>1){
            echo "Teste:";
            var_dump($resultID[1]);
            echo "<PRE>Result: " . htmlentities($result) . "<BR><BR>\n";
          }
          
          $images = $result;
        }
      } else {
        $images[] = trim($result);
        
      }
      //$images[] = trim($replacement);
    }
  }
  return $images;
}


function depsOK($deps){
  global $_debug, $ehXML, $closeDIV;
  foreach ($deps as $dep){
    $dep = trim(str_replace("-", "_", $dep));  
    $variable = "path_to_" . $dep;
    global $$variable;
    if (trim($$variable)==''){
      if ($_debug > ($ehXML ? 1 : 0)	  ){
	if (!$ehXML) echo "<DIV class=\"busy\">";
	echo "Faltam dependencias!!! Variavel \$" . $variable . " não setada.\n";
	if (!$ehXML) echo $closeDIV;      
      }
      return 0;
    }
    else{
      if ($_debug > ($ehXML ? 1 : 0)){
	if (!$ehXML) echo "<DIV class=\"message\">";
	echo "Variavel \$" . $variable . " = " . $$variable . " - OK.\n";
	if (!$ehXML) echo $closeDIV;      
      }
    }
  }
  return 1;
}


/**
 * possible statuses: busy (red), schedulled (blue), message (green)
 */
function messageBar($status, $message){
  echo "<DIV class=\"";
  echo $status;
  echo "\">";
  echo $message;
  echo "</DIV>\n";
}

function hideRest($classID, $content, $indentLevel, $labelPlus, $labelMinus, $size){
  global $_theme, $imagePath, $menuMargin, $encoding;
  for ($i=0;$i<$indentLevel;$i++) echo $menuMargin;
  echo "    <DIV id=\"" . $classID . "_REST_Escondida\" style=\"display: block;\">\n";
  echo mb_substr($content, 0, $size, $encoding);
  echo "      <A onClick=\"toggleShowHide('" . $classID . "_REST_Mostrada', '" . $classID . "_REST_Escondida');\"\n";
  echo "       STYLE=\"cursor: hand\">";
  echo $labelPlus . "</A><BR>\n";
  echo "    </DIV>\n";
  echo "    <DIV id=\"" . $classID . "_REST_Mostrada\" style=\"display: none;\">\n";
  echo $content;
  for ($i=0;$i<$indentLevel;$i++) echo $menuMargin;
  echo "      <A onClick=\"toggleShowHide('" . $classID . "_REST_Escondida', '" . $classID . "_REST_Mostrada');\"\n";
  echo "       STYLE=\"cursor: hand\">";
  echo "<BR>\n" . $labelMinus . "</A><BR>\n";
  echo "    </DIV>\n";
}


 function togglePoint($classID, $label, $indentLevel, $checkBox, $value){
   global $_theme, $imagePath, $menuMargin;
   echo "<SCRIPT LANGUAGE='javascript'>\n";
   echo "   var M_" . $classID . "_mouse;\n";
   echo "</SCRIPT>\n";

   if ($checkBox){
     for ($i=0;$i<$indentLevel;$i++) echo $menuMargin;
     echo "\n      <INPUT TYPE=\"CHECKBOX\" \n";
     echo "      onClick=\"toggleShowHide('" . $classID . "_Mostrada', '" . $classID . "_Escondida');\" ";
     echo " NAME=\"" . $classID . "\" ";
     if ($value) echo " CHECKED ";
     echo ">" . $label . "</INPUT>\n";
     //echo intval($value);
   }
   
   echo "    <DIV id=\"" . $classID . "_Escondida\" style=\"display: block;\">\n";
   if (!$checkBox){
     for ($i=0;$i<$indentLevel;$i++) echo $menuMargin;
     echo "    <A onClick=\"toggleShowHide('" . $classID . "_Mostrada', '" . $classID . "_Escondida');\"\n";
     echo "    onmouseover=\"M_" . $classID . "_mouse = document.body.style.cursor;";
     echo "    document.body.style.cursor = 'pointer'; \"";
     echo "    onmouseout=\"document.body.style.cursor =  M_" . $classID . "_mouse;\"";
     echo "  STYLE=\"text-decoration: none; cursor: pointer; margin: 0px; padding: 0px; border: 0px;\" ";
     echo ">";
     echo "<IMG SRC=\"" . $imagePath;
     if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
       echo "rightGlow.png";
     else
       echo "right.png";
     echo "\" BORDER=0 ALT=\"[>]\">";
   }
   if (!$checkBox)
     echo $label . ":</A><BR>\n";  
   echo "    </DIV>\n";
   echo "    <DIV id=\"" . $classID . "_Mostrada\" style=\"display: none;  background-color: ";
   if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
     echo "#000000";
   else
     echo "#F3F4B2";
   echo ";\n	border: 1px solid #A3A4B2;\">\n";

   if (!$checkBox){
     for ($i=0;$i<$indentLevel;$i++) echo $menuMargin;
     echo "      <A onClick=\"toggleShowHide('" . $classID . "_Escondida', '" . $classID . "_Mostrada');\"\n";
     echo "    onmouseover=\"M_" . $classID . "_mouse = document.body.style.cursor;";
     echo "    document.body.style.cursor = 'pointer'; \"";
     echo "    onmouseout=\"document.body.style.cursor =  M_" . $classID . "_mouse;\"";
     echo "  STYLE=\"text-decoration: none; cursor: pointer; margin: 0px; padding: 0px; border: 0px;\" ";
     echo "  >";
     echo "<IMG SRC=\"" . $imagePath;
     if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
       echo "downGlow.png";
     else
       echo "down.png";
     echo "\" BORDER=0 ALT=\"[>]\">";
   }
   if (!$checkBox)
     echo "<B>" . $label . ":</B></A><BR>\n";   
 }

/**
 * Retorna um array com os grupos a que um usuario pertence
 * @param string $user - matricula do usuario
 * @param int $conn    - PostgreSQL database connection ID
 */
function getGroups($user, $conn){
  $query  = "SELECT g.nome \n";
  $query .= "  FROM usuarios_grupos as ug, grupos as g\n";
  $query .= "  WHERE ug.usuario = '" . $user . "'";
  $query .= "    AND ug.grupo = g.codigo";
  $result = pg_exec ($conn, $query);
  $total  = pg_numrows($result);
  if ($total){
    $linhas = 0;
    while ($linhas<$total){
      $row = pg_fetch_row ($result, $linhas);
      $memberOf[] = $row[0];
      $linhas++;
    }
    return $memberOf;
  }
  else
    return false;
}  

/**
 * Retorna true se o usuario pertence a um dos grupos passados como arguento.
 * @param araray string $groups - grupos para testar
 * @param string $user - matricula do usuario
 * @param int $conn    - PostgreSQL database connection ID
 */
function checkMembership($groups, $user, $conn){
  $memberOf = getGroups($user, $conn);
  $returnValue = false;
  foreach ($groups as $group)
    if (in_array($group, $memberOf))
      $returnValue = true;
  return $returnValue;  
}


function legendaCronograma($conn){
  $query = "SELECT * FROM fases ORDER BY codigo";
  $result = pg_exec($conn, $query);
  $fases = pg_fetch_all($result);
  echo "<TABLE class=onde><TR class=onde>\n";
  foreach ($fases as $fase){
    echo "<TD class=onde BGCOLOR=\"" . $fase['bg_color'] . "\">\n";
    echo "<FONT COLOR=\"" . $fase['fg_color'] . "\">";
    echo $fase['nome'] . "</FONT>\n";
    echo "</TD>\n";
  }
  echo "</TR></TABLE>\n";
}

/**
 * Renderiza uma tabela html mostrando um cronograma cadastrado no sistema.
 * @param int $scale  - escala de dias (7 - mostra por semana)
 * @param bool $open  - se true separa as atividades em linhas diferentes
 * @param int $solicitacao - codigo da solicitacao no Grande IDEIA
 * @param int atividade    - codigo da atividade no Grande IDEIA
 * @param int $conn    - PostgreSQL database connection ID

@example:
$scale = intval($_GET['scale']);
$open = intval($_GET['open']);
$solicitacao = intval($_GET['solicitacao']);
$atividade = intval($_GET['atividade']);

cronograma($scale, $open, $solicitacao, $atividade, $conn);

or

cronograma(7, false, $codigo_solicitacao, 0, $conn);

 */ 
function cronograma($scale, $open, $solicitacao, $atividade, $conn){
  global $_debug;
  if ($_debug){
    if ($scale){
      if ($scale < 1) $scale = 1;
      echo "<A HREF=\"" . basename( $_SERVER['PHP_SELF']) . "?PHPSESSID=" . $PHPSESSID . "&atividade=" . $atividade;
      echo "&scale=" . ($scale - 1);
      if ($open)
	echo "&open=" . $open;
      echo "\"><IMG SRC=\"images/zoom_in.png\" BORDER=0></A>";
      echo "<A HREF=\"" . basename( $_SERVER['PHP_SELF']) . "?PHPSESSID=" . $PHPSESSID . "&atividade=" . $atividade;
      echo "&scale=" . ($scale + 1);
      if ($open)
	echo "&open=" . $open;
      echo "\"><IMG SRC=\"images/zoom_out.png\" BORDER=0></A>";
    }
    else
      $scale = 1;
  }
  else{
    if (!$scale) $scale = 1;
  }

  echo "<BR>\n";
  if ($_debug>1){
    echo "<PRE>\n";
    var_dump($_POST);
    echo "</PRE>\n";
  }
  if ($_debug>1) echo "<B>Conection handle=" . $conn . "</B><BR>\n";

  if ($solicitacao){
    if ($_debug>1) echo "Solicita&ccedil;&atilde;o: " . $solicitacao . "<BR>\n";
    $query  = "SELECT min(af.inicio) \n";
    $query .= "  FROM atividadesfases AS af, fases, atividades\n";
    $query .= "  WHERE af.atividade = atividades.codigo\n";
    $query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
    //$query .= "   AND atividades.codigo = " . $solicitacao . "\n";
    $query .= "   AND af.fase = fases.codigo\n";
    if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
    $result = pg_exec ($conn, $query);
    $dados = pg_fetch_row ($result, 0);
    $min = $dados[0];
    if ($_debug>1) echo "<B>MIN = </B>". $min . "<BR>\n";


    $query  = "SELECT max(af.fim) \n";
    $query .= "  FROM atividadesfases AS af, fases, atividades\n";
    $query .= "  WHERE af.atividade = atividades.codigo\n";
    $query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
    //$query .= "   AND atividades.codigo = " . $solicitacao . "\n";
    $query .= "   AND af.fase = fases.codigo\n";
    if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
    $result = pg_exec ($conn, $query);
    $dados = pg_fetch_row ($result, 0);
    $max = $dados[0];
  }
  else{
    if ($_debug>1) echo "Atividade: " . $atividade . "<BR>\n";
    $query  = "SELECT min(af.inicio) \n";
    $query .= "  FROM atividadesfases AS af, fases \n";
    $query .= "  WHERE af.atividade = " . $atividade . "\n";
    $query .= "   AND af.fase = fases.codigo\n";
    if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
    $result = pg_exec ($conn, $query);
    $dados = pg_fetch_row ($result, 0);
    $min = $dados[0];

    $query  = "SELECT max(af.fim) \n";
    $query .= "  FROM atividadesfases AS af, fases \n";
    $query .= "  WHERE af.atividade = " . $atividade . "\n";
    $query .= "   AND af.fase = fases.codigo\n";
    if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
    $result = pg_exec ($conn, $query);
    $dados = pg_fetch_row ($result, 0);
    $max = $dados[0];
  }

  $query = "SELECT TIMESTAMP '" . $max . "' - TIMESTAMP '" . $min . "'";
  if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
  $result = pg_exec ($conn, $query);
  $dados = pg_fetch_row ($result, 0);

  $query  = "SELECT \n";
  $query .= "  to_char(TIMESTAMP '" . $min . "', 'dd') as dia,\n";
  $query .= "  to_char(TIMESTAMP '" . $min . "', 'MM') as mes,\n";
  $query .= "  to_char(TIMESTAMP '" . $min . "', 'YYYY') as ano\n";
  if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
  $result = pg_exec ($conn, $query);
  $primeiraData = pg_fetch_array ($result, 0);

  $query  = "SELECT \n";
  $query .= "  to_char(TIMESTAMP '" . $max . "', 'dd') as dia,\n";
  $query .= "  to_char(TIMESTAMP '" . $max . "', 'MM') as mes,\n";
  $query .= "  to_char(TIMESTAMP '" . $max . "', 'YYYY') as ano\n";
  if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";
  $result = pg_exec ($conn, $query);
  $ultimaData = pg_fetch_array ($result, 0);

  $primeiroDia = date("N", mktime(0, 0, 0, $primeiraData['mes'], $primeiraData['dia'], $primeiraData['ano'])) ;
  $ultimoDiaMes = strftime("%d", mktime(0, 0, 0, $primeiraData['mes'] + 1, 0, $primeiraData['ano']));
  if ($_debug > 1) echo "primeiro dia: " . $primeiroDia . "<BR>\n";

  if ($_debug>1) {
    echo "Primeiro dia: " . $primeiroDia . "<BR>\n";
    echo "Ultimo dia do primeiro mes: " . $ultimoDiaMes . "<BR>\n";
  }
  if (strpos($dados[0], "days"))
    $intervalo = explode(" ", $dados[0]);
  else {
    $intervalo[0] = 0;
    $intervalo[1] = "days";
  }
  $intervalo[0]++;
  if ($_debug>1) echo "intervalo: " .  $intervalo[0] . " dias<BR>\n";
  if ($_debug>1) echo "intervalo: " .  round($intervalo[0]/7,0) . " semanas<BR>\n";


  if ($solicitacao){
    $query  = "SELECT distinct atividades.codigo";
    $query .= ",\n    SUBSTRING(upper(grupos.nome) FROM  'LABORATÓRIO\s\DE\s(.*)')";
    $query .= "||'('||trim(SUBSTRING(u.nome, '(.*?(\s))'))||')' as responsavel \n";
    $query .= "  FROM atividadesfases AS af, atividades, usuarios_grupos, grupos, usuarios as u\n";
    $query .= "  WHERE af.atividade = atividades.codigo\n";
    $query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
    //$query .= "   AND atividades.codigo = " . $solicitacao . "\n";
    $query .= "   AND atividades.usuario = usuarios_grupos.usuario\n";
    $query .= "   AND grupos.codigo = usuarios_grupos.grupo\n";
    $query .= "   AND grupos.centrodecusto<>''\n";
    $query .= "   AND grupos.centrodecusto IS NOT NULL\n";
    $query .= "   AND atividades.usuario = u.login\n";
    $query .= " ORDER BY atividades.codigo";
    if ($_debug)  show_query($query, $conn);
    $result = pg_exec ($conn, $query);
    $total  = pg_numrows($result);
    $linhas = 0;

    while ($linhas<$total){
      $row = pg_fetch_row ($result, $linhas);
      $atividades[$linhas]['codigo'] = $row[0];
      $atividades[$linhas]['responsavel'] = $row[1]; 
      $linhas++;
    }
  }

  $query  = "SELECT af.fase, af.inicio, af.fim, fases.bg_color, fases.fg_color, \n";
  $query .= "    to_char(af.inicio, 'YYYY') as anoinicio,\n";
  $query .= "    to_char(af.inicio, 'MM') as mesinicio,\n";
  $query .= "    to_char(af.inicio, 'dd') as diainicio,\n";
  $query .= "    to_char(af.fim, 'YYYY') as anofim,\n";
  $query .= "    to_char(af.fim, 'MM') as meafim,\n";
  $query .= "    to_char(af.fim, 'dd') as diafim,\n";
  $query .= "    (fim - inicio) + INTERVAL '1 day' as duracao,\n";
  $query .= "    inicio - TIMESTAMP '" . $min . "'  + INTERVAL '1 day' as offset";

  if ($solicitacao){
    $query .= ",\n    SUBSTRING(upper(grupos.nome) FROM  'LABORATÓRIO\s\DE\s(.*)')";
    $query .= "||'('||trim(SUBSTRING(u.nome, '(.*?(\s))'))||')' as responsavel, \n";
    $query .= "atividades.codigo\n";

    $query .= "  FROM atividadesfases AS af, fases, atividades, usuarios_grupos, grupos, usuarios as u\n";
    $query .= "  WHERE af.atividade = atividades.codigo\n";
    $query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
    //$query .= "   AND atividades.codigo = " . $solicitacao . "\n";
    $query .= "   AND atividades.usuario = usuarios_grupos.usuario\n";
    $query .= "   AND grupos.codigo = usuarios_grupos.grupo\n";
    $query .= "   AND grupos.centrodecusto<>''\n";
    $query .= "   AND grupos.centrodecusto IS NOT NULL\n";
    $query .= "   AND atividades.usuario = u.login\n";
  }
  else{
    $query .= "  FROM atividadesfases AS af, fases \n";
    $query .= "  WHERE af.atividade = " . $atividade . "\n";
  }

  $query .= "   AND af.fase = fases.codigo\n";
  $query .= " ORDER BY af.inicio";
  if ($_debug>1) echo "<PRE>\n" . $query . "\n</PRE>\n";

  if ($_debug){
    echo "<BR>\n";
    show_query($query, $conn);
  }
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      echo "<div style=\"border:none; border-width: 0px;\">";
      if ($solicitacao){
      echo "<div style=\"float: left; border:none; border-width: 0px;\">";
  
  echo "<table class=onde>\n";
  echo "<TR>\n";
    echo "<TH class=onde style=\"height:";
    if ($scale>1)
      if ($_debug) echo "50px"; else echo "38px";
     else 
       if ($_debug) echo "50px"; else echo "45px";

     echo "\" >Laborat&oacute;rio</TH>\n";
  echo "</TR>\n";

      for ($j=0;$j<count($atividades);$j++){
	$query  = "SELECT fases.bg_color, fases.fg_color, \n";
	$query .= "    inicio - TIMESTAMP '" . $min . "'  + INTERVAL '1 day' as offset,\n";
	$query .= "    (fim - inicio) + INTERVAL '1 day' as duracao,\n";
	$query .= "    SUBSTRING(upper(grupos.nome) FROM  'LABORATÓRIO\s\DE\s(.*)')";
	$query .= "||' ('||trim(SUBSTRING(u.nome, '(.*?(\s))'))||')' as responsavel, \n";
	$query .= "atividades.codigo\n";
	$query .= "  FROM atividadesfases AS af, fases, atividades, usuarios_grupos, grupos, usuarios as u\n";
	$query .= "  WHERE atividades.codigo = " . $atividades[$j]['codigo'] . "\n";
	$query .= "   AND af.atividade = atividades.codigo\n";
	$query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
	$query .= "   AND af.inicio >= inicioCronograma(" . $solicitacao . ")\n";
	$query .= "   AND af.fim <= fimCronograma(" . $solicitacao . ")\n";
	$query .= "   AND atividades.usuario = usuarios_grupos.usuario\n";
	$query .= "   AND grupos.codigo = usuarios_grupos.grupo\n";
	$query .= "   AND grupos.centrodecusto<>''\n";
	$query .= "   AND grupos.centrodecusto IS NOT NULL\n";
	$query .= "   AND atividades.usuario = u.login\n";
	$query .= "   AND af.fase = fases.codigo\n";
	$query .= " ORDER BY af.inicio";

	$result = pg_exec ($conn, $query);
	$total  = pg_numrows($result);
	$linhas = 0;
	while ($linhas<$total){
	  $cronograma[$linhas] = pg_fetch_array ($result, $linhas);

	  $offset = explode(" ", $cronograma[$linhas]['offset']);
	  $offset = ( intval($offset[0]) );
	  $cronograma[$linhas]['offset'] = $offset;

	  $duracao = explode(" ", $cronograma[$linhas]['duracao']);
	  $duracao = (intval($duracao[0]) - 0);
	  $cronograma[$linhas]['duracao'] = $duracao;

	  $linhas++;
	}
	if ($_debug>1){
	  echo "</TABLE><BR>";
	  echo "<PRE>\n";
	  echo $query;
	  echo "</PRE>\n";
	  show_query($query, $conn);
	  echo "<BR>\n";
	  echo "Primeiro dia: " . $primeiroDia . "<BR>\n"; 
	  echo "Intervalo: " . $intervalo[0] . "<BR>\n";
	  //echo "<PRE>\n";
	  //echo var_dump($cronograma);
	  //echo "</PRE>\n";
	  echo "<TABLE class=onde>";
	}
	echo "  <TR>\n";
	echo "<TD class=onde>" . $atividades[$j]['responsavel'] . "</TD>\n";
	echo "  </TR>\n";
      }

      echo "</table>";  
  echo "</DIV>";
      }
      //////////////////////////////////////////////////////////////////////////////////////////////////////////////  
      //echo "<div style=\"width:600px; border:solid; border-width: 1px; overflow-y:scroll;\">";
      echo "<div style=\"border:none; border-width: 0px; overflow-x:auto;\">";

  
  echo "<TABLE CLASS=\"onde\">\n";
  // echo "  <TR>\n";
  // for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
  //   //if ((((($i - 2) % 7)+1) != 6) && (((($i - 2) % 7)+1) != 7) )
  //   echo "    <TH class=onde>" . $semana[(($i - 1) % 7)+1]['curto'] . "</TH>\n";
  // }
  // echo "  </TR>\n";
  // echo "  <TR>\n";
  // for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
  //   //if ((((($i - 2) % 7)+1) != 6) && (((($i - 2) % 7)+1) != 7) )
  //   echo "    <TH class=onde>" . ((($i-1) % 7)+1) . "</TH>\n";
  // }

  $indexAno = 0;    $anosColSpan = 0;
  $indexMes = 0;    $mesesColSpan = 0;
  for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
    $ano = date("Y", mktime(0, 0, 0, $primeiraData['mes'], $primeiraData['dia'] + $i -$primeiroDia, $primeiraData['ano'])) ; 
    if ($indexAno > 0  && $anos[($indexAno-1)]['ano'] == $ano) 
      $anosColSpan++;
    else{
      $anosColSpan++;
      if (($indexMes - 1)>=0)
	$anos[($indexAno-1)]['colspan'] = $anosColSpan;
      $anos[$indexAno]['ano'] = $ano;
      $anosColSpan = 0;
      $indexAno++;
    }
    $mes = date("M", mktime(0, 0, 0, $primeiraData['mes'], $primeiraData['dia'] + $i -$primeiroDia  , $primeiraData['mes'])) ; 
    if ($indexMes > 0 && $mess[($indexMes-1)]['mes'] == $mes) 
      $mesesColSpan++;
    else{
      $mesesColSpan++;
      if (($indexMes - 1)>=0)
	$mess[($indexMes - 1)]['colspan'] = $mesesColSpan;
      $mess[$indexMes]['mes'] = $mes;
      $mesesColSpan = 0;
      $indexMes++;
    }
    if ($_debug>1) echo "ano col span: " . $anosColSpan . " -- ano: " . $ano . " -- ano anterior: " . $anos[($indexAno-1)]['ano'] . "<BR>\n";
  }
  $anosColSpan++;
  $anos[($indexAno-1)]['colspan'] = $anosColSpan;
  $mesesColSpan++;
  $mess[($indexMes - 1)]['colspan'] = $mesesColSpan;

  if ($_debug>1){
    echo "<PRE>\n";
    var_dump($anos);
    echo "\n</PRE>\n";
    echo "<PRE>\n";
    var_dump($mess);
    echo "\n</PRE>\n";
  }

  if ($solicitacao){
    echo "<TR>\n";
    // echo "<TH class=onde ROWSPAN=";
    // if ($scale>1)
    //   if ($_debug) echo "4"; else echo "2";
    // else 
    //   if ($_debug) echo "4"; else echo "3";

    // echo ">Laborat&oacute;rio</TH>\n";
  }
  for ($i=0;$i<count($anos);$i++){
    echo "<TH class=onde COLSPAN=\"" . $anos[$i]['colspan'] . "\">" . $anos[$i]['ano'] . "</TH>";
  }
  echo "</TR>\n";
  echo "<TR>\n";
  for ($i=0;$i<count($mess);$i++){
    echo "<TH class=onde COLSPAN=\"" . $mess[$i]['colspan'] . "\">" . $mess[$i]['mes'] . "</TH>";
  }
  echo "</TR>\n";
  echo "  <TR>\n";
  if ($scale == 1)
    for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale)
      echo "    <TH class=onde>" . $semana[(($i + 6) % 7)+1]['curto'] . "</TH>\n";
  echo "  </TR>\n";
  if ($_debug){
    echo "  <TR>\n";
    for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
      echo "    <TH class=onde>" . ((($i-1) % 7)+1) . "</TH>\n";
    }
    echo "  </TR>\n";
  }


  $result = pg_exec ($conn, $query);
  $total  = pg_numrows($result);
  $linhas = 0;

  //if ($open || !$open){
  if ($open){
    while ($linhas<$total){
      $cronograma[$linhas] = pg_fetch_array ($result, $linhas);
      $offset = explode(" ", $cronograma[$linhas]['offset']);
      $offset = ( intval($offset[0]) );
      $duracao = explode(" ", $cronograma[$linhas]['duracao']);
      $duracao = (intval($duracao[0]) - 0);
      if ($_debug>1) echo "offset = " . $offset . "  duracao = " . $duracao . "  ";
      echo "  <TR>\n";
      if ($solicitacao)
	echo "<TD class=onde>" . $cronograma[$linhas]['responsavel'] . "</TD>\n";
      for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
	echo "    <TD class=onde HEIGHT=10 ";
	if ( (($i - $primeiroDia+1)>=$offset) && (($i-$primeiroDia+1)<($offset+$duracao)) ){
	  echo " BGCOLOR=\"#" . $cronograma[$linhas]['bg_color'] . "\" ";
	}
	echo ">";
	echo "<FONT ";
	if ( (($i - $primeiroDia+1)>=$offset) && (($i- $primeiroDia+1)<($offset+$duracao)) )
	  echo "COLOR=\"#" . $cronograma[$linhas]['fg_color'] . "\"";
	echo ">";
	if ($_debug) echo $i;
	echo "&nbsp;";
	echo "</FONT></TD>\n";
      }
      echo "  </TR>\n";
      $linhas++;
    }
  }
  else{
    if ($solicitacao){
      if ($_debug) {
	echo "<PRE>\n";
	var_dump($atividades);
	echo "\n</PRE>\n";
      }
      for ($j=0;$j<count($atividades);$j++){
	$query  = "SELECT fases.bg_color, fases.fg_color, \n";
	$query .= "    inicio - TIMESTAMP '" . $min . "'  + INTERVAL '1 day' as offset,\n";
	$query .= "    (fim - inicio) + INTERVAL '1 day' as duracao,\n";
	$query .= "    SUBSTRING(upper(grupos.nome) FROM  'LABORATÓRIO\s\DE\s(.*)')";
	$query .= "||' ('||trim(SUBSTRING(u.nome, '(.*?(\s))'))||')' as responsavel, \n";
	$query .= "atividades.codigo\n";
	$query .= "  FROM atividadesfases AS af, fases, atividades, usuarios_grupos, grupos, usuarios as u\n";
	$query .= "  WHERE atividades.codigo = " . $atividades[$j]['codigo'] . "\n";
	$query .= "   AND af.atividade = atividades.codigo\n";
	$query .= "   AND atividades.solicitacao = " . $solicitacao . "\n";
	$query .= "   AND af.inicio >= inicioCronograma(" . $solicitacao . ")\n";
	$query .= "   AND af.fim <= fimCronograma(" . $solicitacao . ")\n";
	$query .= "   AND atividades.usuario = usuarios_grupos.usuario\n";
	$query .= "   AND grupos.codigo = usuarios_grupos.grupo\n";
	$query .= "   AND grupos.centrodecusto<>''\n";
	$query .= "   AND grupos.centrodecusto IS NOT NULL\n";
	$query .= "   AND atividades.usuario = u.login\n";
	$query .= "   AND af.fase = fases.codigo\n";
	$query .= " ORDER BY af.inicio";

	$result = pg_exec ($conn, $query);
	$total  = pg_numrows($result);
	$linhas = 0;
	while ($linhas<$total){
	  $cronograma[$linhas] = pg_fetch_array ($result, $linhas);

	  $offset = explode(" ", $cronograma[$linhas]['offset']);
	  $offset = ( intval($offset[0]) );
	  $cronograma[$linhas]['offset'] = $offset;

	  $duracao = explode(" ", $cronograma[$linhas]['duracao']);
	  $duracao = (intval($duracao[0]) - 0);
	  $cronograma[$linhas]['duracao'] = $duracao;

	  $linhas++;
	}
	if ($_debug>1){
	  echo "</TABLE><BR>";
	  echo "<PRE>\n";
	  echo $query;
	  echo "</PRE>\n";
	  show_query($query, $conn);
	  echo "<BR>\n";
	  echo "Primeiro dia: " . $primeiroDia . "<BR>\n"; 
	  echo "Intervalo: " . $intervalo[0] . "<BR>\n";
	  //echo "<PRE>\n";
	  //echo var_dump($cronograma);
	  //echo "</PRE>\n";
	  echo "<TABLE class=onde>";
	}

	echo "  <TR>\n";
	//	echo "<TD class=onde>" . $atividades[$j]['responsavel'] . "</TD>\n";
	$linhas = 0;
	for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
	  $tripa[$i]['check']=0;
	}
	while($linhas<$total){
	  for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){         
	    if ($i>=$cronograma[$linhas]['offset']+$primeiroDia-1 && $i<$cronograma[$linhas]['offset']+$cronograma[$linhas]['duracao']+$primeiroDia - 1){
	      $tripa[$i]['check']=1;
	      $tripa[$i]['bg_color']=$cronograma[$linhas]['bg_color'];
	      $tripa[$i]['fg_color']=$cronograma[$linhas]['fg_color'];
	    }
	  }
	  $linhas++;
	}
	for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
	  if ($tripa[$i]['check']){
	    echo "<TD class=onde BGCOLOR=\"#";
	    echo $tripa[$i]['bg_color'];
	    echo "\"><FONT COLOR=\"#";
	    echo $tripa[$i]['fg_color'];
	    echo "\">&nbsp;</FONT></TD>";
	  }
	  else
	    echo "<TD class=onde>&nbsp;</TD>";
	}
	echo "  </TR>\n";


      }
    }
    else{
      $result = pg_exec ($conn, $query);
      $total  = pg_numrows($result);
      $linhas = 0;
      while ($linhas<$total){
	$cronograma[$linhas] = pg_fetch_array ($result, $linhas);

	$offset = explode(" ", $cronograma[$linhas]['offset']);
	$offset = ( intval($offset[0]) );
	$cronograma[$linhas]['offset'] = $offset;

	$duracao = explode(" ", $cronograma[$linhas]['duracao']);
	$duracao = (intval($duracao[0]) - 0);
	$cronograma[$linhas]['duracao'] = $duracao;

	$linhas++;
      }

      echo "  <TR>\n";
      for ($i=$primeiroDia;$i<$primeiroDia + $intervalo[0];$i = $i + $scale){
	$linhas = 0;
	while ($linhas<$total){

	  if ( (($i - $primeiroDia + 1)>=$cronograma[$linhas]['offset']) && (($i -$primeiroDia + 1)<($cronograma[$linhas]['offset']+$cronograma[$linhas]['duracao'])) ){
	    echo "    <TD class=onde HEIGHT=10 ";
	    echo " BGCOLOR=\"#" . $cronograma[$linhas]['bg_color'] . "\" ";
	    echo ">";
	    echo "<FONT ";
	    echo "COLOR=\"#" . $cronograma[$linhas]['fg_color'];
	    echo "\">";
	    if ($_debug) echo $i;
	    echo "&nbsp;";
	    echo "</FONT></TD>\n";
	  }
	  else{
	    if ( ($linhas+1<=$total) && (($i - $primeiroDia+1)>=($cronograma[$linhas]['offset']+$cronograma[$linhas]['duracao'])) && (($i - $primeiroDia+1)<($cronograma[$linhas+1]['offset']))){
	      echo "    <TD class=onde HEIGHT=10>";
	      if ($_debug) echo $i;
	      echo "&nbsp;</TD>";
	    }
	  }
	  $linhas++;
	}
      }
      echo "  </TR>\n";
    }
  }
  echo "</TABLE>\n";
  echo "</DIV>";
  echo "</DIV>";
  if ($_debug>1){
    echo "<PRE>\n";
    var_dump($cronograma);
    echo "</PRE>\n";
  }

  $dataset = pg_fetch_array ($result, 0);
  $timestamp = $dataset['inicio'];
  $firstDay = date("N", $dataset['inicio']);
  if ($_debug > 1){
    echo "First day: " . $firstDay . "<BR>\n";
    echo "timestamp: " . $timestamp  . "<BR>\n";
    echo date("M-d-Y",  $timestamp) .  "<BR>\n";
  }
}


 function getAnosMinMax($conn){
  $query  = "SELECT min(to_number(to_char(quando, 'YYYY'), '9999')) as ano from solicitacoes\n";
  $query .= "UNION\n";
  $query .= "SELECT min(to_number(to_char(quando, 'YYYY'), '9999')) as ano from checkpoints\n";
  $query .= "UNION\n";
  $query .= "SELECT min(to_number(to_char(quando, 'YYYY'), '9999')) as ano from horasdetrabalho\n";
  $query .= "UNION\n";
  $query .= "SELECT min(to_number(to_char(\"início\", 'YYYY'), '9999')) as ano from incubados\n";
  $query .= "UNION\n";
  $query .= "SELECT min(to_number(to_char(\"término\", 'YYYY'), '9999')) as ano from incubados order by ano";
  $result = pg_exec ($conn, $query);
  if (pg_numrows($result))
    $row = pg_fetch_row ($result, 0);
  $minimo = $row[0];
  $query  = "SELECT max(to_number(to_char(quando, 'YYYY'), '9999')) as ano from solicitacoes\n";
  $query .= "UNION\n";
  $query .= "SELECT max(to_number(to_char(quando, 'YYYY'), '9999')) as ano from checkpoints\n";
  $query .= "UNION\n";
  $query .= "SELECT max(to_number(to_char(quando, 'YYYY'), '9999')) as ano from horasdetrabalho\n";
  $query .= "UNION\n";
  $query .= "SELECT max(to_number(to_char(\"início\", 'YYYY'), '9999')) as ano from incubados\n";
  $query .= "UNION\n";
  $query .= "SELECT max(to_number(to_char(\"término\", 'YYYY'), '9999')) as ano from incubados order by ano desc";
  $result = pg_exec ($conn, $query);
  if (pg_numrows($result))
    $row = pg_fetch_row ($result, 0);
  $maximo = $row[0];
  return array('minimo' => $minimo, 'maximo' => $maximo);
 }
			
 
/**
 * Mostra um cabecalho de selecao de ano
 */
function selectAno($conn, $ano, $orderBy, $desc, $PHPSESSID, $extraGET){
  $trailling = "";
  if (isset($orderBy) && trim($orderBy)) $trailling .= "&orderby=" . $orderBy;
  if (isset($desc) ) $trailling .= "&desc=" . intval($desc);
  if (isset($PHPSESSID) && trim($PHPSESSID) ) $trailling .= "&PHPSESSID=" . $PHPSESSID;
  if (isset($extraGET)) $trailling .= $extraGET;

  echo "<BR>\n";
  $limites = getAnosMinMax($conn);

  for ($i=$limites['minimo']; $i<=$limites['maximo']; $i++) {
    echo "[";
    echo "<A HREF=\"" . basename($_SERVER['PHP_SELF']);
    echo "?PHPSESSID=" . $PHPSESSID;
    echo "&ano=" . $i;
    echo $trailling;
    echo "\">";
    echo $i . "</A>";
    echo "]&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "\n";
  }
  echo "<BR>";

  if ( ($ano - 1) >= $limites['minimo']){
    echo "<A HREF=\"" . basename($_SERVER['PHP_SELF']);
    echo "?PHPSESSID=" . $PHPSESSID;
    echo "&ano=" . ($ano - 1);
    echo $trailling;
    echo "\">";
  }
  echo "&lt;";
  echo ($ano - 1);
  if ( ($ano - 1) >= $limites['minimo']) echo "</A>\n";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "\n";

  echo "<A HREF=\"" . basename($_SERVER['PHP_SELF']);
  echo "?PHPSESSID=" . $PHPSESSID;
  echo "&ano=" . date("Y", time());
  echo $trailling;
  echo "\">";
  echo "ano atual</A>\n";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "\n";

  if ( ($ano + 1) <= $limites['maximo']){
    echo "<A HREF=\"" . basename($_SERVER['PHP_SELF']);
    echo "?PHPSESSID=" . $PHPSESSID;
    echo "&ano=" . ($ano + 1);
    echo $trailling;
    echo "\">";
  }
  echo ($ano + 1) . "&gt;";
  if ( ($ano + 1) <= $limites['maximo']) echo "</A>\n";

  return $limites;
}


/**
 * Retorna uma string com formatacao em timestamp (postgresql timestamp)
 * a partir de uma string contendo uma data no formato dd/mm/aaaa, passada
 * como argumento.
 */
function toPgTimeStamp($pureDate){
  if (strpos($pureDate, "/")){
    $result = explode("/", $pureDate);
    if (count($result>2)){
      $result['dia'] = $result[0];
      $result['mes'] = $result[1];
      $result['ano'] = $result[2];
      $result['pg_ts'] = trim($result['ano']) . "-" . trim($result['mes'])  . "-" . trim($result['dia']) . " 00:00:00";
      return $result['pg_ts'];
    }
  }
  return false;
}

/**
 * Retorna a string passada como argumento sem os acentos e cedilha
 */
function stripAccents($string){
//  return strtr($string, "áéíóúàèìòäëëïöüâêîôûãõçñÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÇÑ", 
//                        "aeiouaeiouaeiouaeiouaocnAEIOUAEIOUAEIOUAEIOUAOCN");
/*
Since strtr (like PHP's other string functions) treats strings as a sequence of bytes, and since UTF-8 and other multibyte encodings use - by definition - more than one byte for at least some characters, the three-string form is likely to have problems. Use the associative array form to specify the mapping.

Assuming UTF-8
$str = 'Äbc Äbc'; // strtr() sees this as nine bytes (including two for each Ä)
echo strtr($str, 'Ä', 'a'); // The second argument is equivalent to the string "\xc3\x84" so "\xc3" gets replaced by "a" and the "\x84" is ignored

echo strtr($str, array('Ä' => 'a')); // Works much better
*/
  return strtr($string, array ('á' => 'a',
			       'é' => 'e',
			       'í' => 'i',
			       'ó' => 'o',
			       'ú' => 'u',
			       'à' => 'a',
			       'è' => 'e',
			       'ì' => 'i',
			       'ò' => 'o',
			       'ä' => 'u',
			       'ë' => 'a',
			       'ë' => 'e',
			       'ï' => 'i',
			       'ö' => 'o',
			       'ü' => 'u',
			       'â' => 'a',
			       'ê' => 'e',
			       'î' => 'i',
			       'ô' => 'o',
			       'û' => 'u',
			       'ã' => 'a',
			       'õ' => 'o',
			       'ç' => 'c',
			       'ñ' => 'n',
			       'Á' => 'A',
			       'É' => 'E',
			       'Í' => 'I',
			       'Ó' => 'O',
			       'Ú' => 'U',
			       'À' => 'A',
			       'È' => 'E',
			       'Ì' => 'I',
			       'Ò' => 'O',
			       'Ù' => 'U',
			       'Ä' => 'A',
			       'Ë' => 'E',
			       'Ï' => 'I',
			       'Ö' => 'O',
			       'Ü' => 'U',
			       'Â' => 'A',
			       'Ê' => 'E',
			       'Î' => 'I',
			       'Ô' => 'O',
			       'Û' => 'U',
			       'Ã' => 'A',
			       'Õ' => 'O',
			       'Ç' => 'C',
			       'Ñ' => 'N'));
}

/**
 * Retorna uma string sem as quebras de linha
 */
function stripLineBreaks($str1, $marker){
  //$str1=stripAccents($str1);
  if (!$marker)
    return preg_replace("/(\r|\n)/u", ', ', $str1);
  else
    return preg_replace("/(\r|\n)/u", $marker, $str1);
}

/**
 * Tira todas as quebras de linha
 */
function tiraQuebrasDeLinha($str1, $marcador){
  //$str1=stripAccents($str1);
  if (!$marcador)
    //return preg_replace("/(\r|\n)/u", ', ', $str1);
    return mb_ereg_replace ("/(\r|\n)/u", ', ', $str1);
  else
    return preg_replace("/(\r|\n)/u", $marcador, $str1);  
}

/**
 * recoloca das quebras, quando trocadas por marcadores
 */
function poeQuebrasDeLinha($str1, $marcador){
  return preg_replace("/" . $marcador . "/", "\n", $str1);
}

/**
 * Retorna uma string contendo o que estiver entre as tags <body> </body> 
 * da string passada como arguemnto da funcao.
 */
function isolaBody($conteudo){
  //$conteudo = bin2hex($conteudo);
  
  //$conteudo = tiraQuebrasDeLinha($conteudo, "<QUEBRA DE LINHAS>");
  
  //echo "Listagem:<BR><PRE>" . $conteudo . "</PRE>\n";
  
  //$pattern = '/<([B]|[b])([O]|[o])([D]|[d])([Y]|[y]).*?(>).*?(<\/([B]|[b])([O]|[o])([D]|[d])([Y]|[y])>)/';
  //$pattern = '/<([B]|[b])([O]|[o])([D]|[d])([Y]|[y])>.*?(<\/([B]|[b])([O]|[o])([D]|[d])([Y]|[y])>)/';
  $pattern = '/(<([B]|[b])([O]|[o])([D]|[d])([Y]|[y]).*?(>)(.*)?(<\/([B]|[b])([O]|[o])([D]|[d])([Y]|[y])))/';
  $resultado = preg_match($pattern, $conteudo, $matches);

  //echo "<PRE>" . trim($conteudo) . "</PRE><BR>";
  //echo "<PRE>";
  //var_dump($matches);
  //echo "</PRE>";

  // usando indice 0, inclui as tags body e /body
  //$conteudo = poeQuebrasDeLinha(trim($matches[0]), "<QUEBRA DE LINHAS>");
  // usando indice 7, soh coloca o que estah dentro do body
  $conteudo = poeQuebrasDeLinha(trim($matches[7]), "<QUEBRA DE LINHAS>");

  //echo "<PRE>";
  //echo htmlentities($conteudo);
  //echo "</PRE>";

  return $conteudo;
}


/*
htmlFormField($_POST, formName, 
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <INPUT TYPE="TEXT" CLASS="TEXT" NAME="RG" id="RG"
       SIZE=10 MAXLENGTH=10 VALUE="<?PHP echo $_POST['RG']; ?>"><BR>
      <BR>
*/
function show_week($date, $db, $type){
  global $_debug;

  $query  = "SELECT " .  $db['table'] . "." . $db['field'] . $db['select'] . "\n";
  $query .= " FROM " . $db['table']  . $db['from'] . "\n";
  $query .= " WHERE " . $db['table'] . "." . $db['field'] . ">'" . $date['year'];
  $query .= "-" . $date['month'] . "-01' AND \n       " .  $db['table'] . "." . $db['field'];
  $query .= "<'"  . $date['year'] . "-" . $date['month'] . "-31' " . $db['where'] . "\n";
  if ($_debug>1){
    echo "<PRE>\n";
    echo var_dump($db);
    echo "\n\n";
    echo $query;
    echo "</PRE>\n";
  }
  $result = pg_exec ($conn, $query);
  $total  = pg_numrows($result);
  $linhas = 0;
  while ($linhas<$total){
    $calendar[$linhas] = pg_fetch_array ($result, $linhas);
    $linhas++;
  }
  if ($_debug>1){
    echo "<PRE>\n";
    echo var_dump($calendar);
    echo "</PRE>\n";
  }
  $first_day = mktime(0,0,0,$date['month'], 1, $date['year']);
  $title = date('F', $first_day);
  $day_of_week = date('D', $first_day);
  switch($day_of_week){
    case "Sun": $blank = 0; break;
    case "Mon": $blank = 1; break;
    case "Tue": $blank = 2; break;
    case "Wed": $blank = 3; break;
    case "Thu": $blank = 4; break;
    case "Fri": $blank = 5; break;
    case "Sat": $blank = 6; break;
  }
  $days_in_month =  date('d', mktime(0, 0, 0, $date['month'] + 1, 0, $date['year'])); 
  echo "<table class=smallcallendar>";
  echo "<TR class=smallcallendar><TH class=smallcallendar colspan=60>" . $title . " " . $date['year'] . "</th></tr>";
  echo "<TR  class=smallcallendar><td class=smallcallendar>Domingo</td>";
  echo "<td  class=smallcallendar>Segunda</td><td class=smallcallendar>Ter&ccedil;a</td>";
  echo "<td  class=smallcallendar>Quarta</td><td  class=smallcallendar>Quinta</td>";
  echo "<td  class=smallcallendar>Sexta</td><td  class=smallcallendar>S&aacute;bado</td></tr>";
  $day_count = 1;
  echo "<TR class=smallcallendar>";
  while ( $blank > 0 ){
    echo "<td class=smallcallendar></td>";
    $blank = $blank - 1;
    $day_count++;
  }
  $day_num = 1;
  echo "<B>" . intval($day_count)  . "</B>\n";
  while ( $day_num <= ($day_count)){
    //if ($blank >= 1 && $blank <05) break;

    echo "<td class=smallcallendar> $day_num";
    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<DIV CLASS=\"messagecal\">8h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<BR>
                                    10h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">10h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    12h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">13h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    15h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">15h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    17h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">18h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    20h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">20h&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    22h</DIV>\n";

    //echo "<BR><BR><BR><BR></td>";
    $day_num++;
    $day_count++;
    if ($day_count > 7){
      echo "</tr><tr class=smallcallendar>";
      $day_count = 1;
    }
  }
  while( $day_count > 1 && $day_count < 7 ){
    echo "<td class=smallcallendar> </td>";
    $day_count++;
  }
  echo "<tr class=smallcallendar></table>";
}

function show_calendar_table($date, $db, $type){
  $query  = "SELECT " .  $db['table'] . "." . $db['field'] . $db['select'] . "\n";
  $query .= " FROM " . $db['table']  . $db['from'] . "\n";
  $query .= " WHERE " . $db['table'] . "." . $db['field'] . ">'" . $date['year'];
  $query .= "-" . $date['month'] . "-01' AND \n       " .  $db['table'] . "." . $db['field'];
  $query .= "<'"  . $date['year'] . "-" . $date['month'] . "-31' " . $db['where'] . "\n";
//    echo "<PRE>\n";
//    echo $query;
//    echo "</PRE>\n";
  $result = pg_exec ($conn, $query);
  $total  = pg_numrows($result);
  $linhas = 0;
  while ($linhas<$total){
    $calendar[$linhas] = pg_fetch_array ($result, $linhas);
    $linhas++;
  }
//    echo "<PRE>\n";
//    echo var_dump($calendar);
//    echo "</PRE>\n";

  $first_day = mktime(0,0,0,$date['month'], 1, $date['year']);
  $title = date('F', $first_day);
  $day_of_week = date('D', $first_day);
  switch($day_of_week){
    case "Sun": $blank = 0; break;
    case "Mon": $blank = 1; break;
    case "Tue": $blank = 2; break;
    case "Wed": $blank = 3; break;
    case "Thu": $blank = 4; break;
    case "Fri": $blank = 5; break;
    case "Sat": $blank = 6; break;
  }
  $days_in_month =  date('d', mktime(0, 0, 0, $date['month'] + 1, 0, $date['year'])); 
  echo "<table class=smallcallendar>";
  echo "<TR class=smallcallendar><TH  class=smallcallendar colspan=60>" . $title . " " . $date['year'] . "</th></tr>";
  echo "<TR  class=smallcallendar><td  class=smallcallendar>S</td>";
  echo "<td  class=smallcallendar>M</td><td  class=smallcallendar>T</td>";
  echo "<td  class=smallcallendar>W</td><td  class=smallcallendar>T</td>";
  echo "<td  class=smallcallendar>F</td><td  class=smallcallendar>S</td></tr>";
  $day_count = 1;
  echo "<TR class=smallcallendar>";
  while ( $blank > 0 ){
    echo "<td class=smallcallendar></td>";
    $blank = $blank - 1;
    $day_count++;
  }
  $day_num = 1;
  while ( $day_num <= $days_in_month ){
    echo "<td class=smallcallendar> $day_num";
    //    echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<DIV CLASS=\"messagecal\">8h - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<BR>
                                    12h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">13h - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    18h</DIV>\n";
    echo "<DIV CLASS=\"messagecal\">18h - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<BR>
                                    22h</DIV>\n";

    //echo "<BR><BR><BR><BR></td>";
    $day_num++;
    $day_count++;
    if ($day_count > 7){
      echo "</tr><tr class=smallcallendar>";
      $day_count = 1;
    }
  }
  while( $day_count > 1 && $day_count < 7 ){
    echo "<td class=smallcallendar> </td>";
    $day_count++;
  }
  echo "<tr class=smallcallendar></table>";
}


function show_calendar($day, $month, $year){
  $first_day = mktime(0,0,0,$month, 1, $year);
  $title = date('F', $first_day);
  $day_of_week = date('D', $first_day);
  switch($day_of_week){
    case "Sun": $blank = 0; break;
    case "Mon": $blank = 1; break;
    case "Tue": $blank = 2; break;
    case "Wed": $blank = 3; break;
    case "Thu": $blank = 4; break;
    case "Fri": $blank = 5; break;
    case "Sat": $blank = 6; break;
  }
  //echo "<B>PASSEI!!</B>\n";
  $days_in_month =  date('d', mktime(0, 0, 0, $month + 1, 0, $year)); 
  //cal_days_in_month(0, $month, $year);
  // Estranhamente a funcao cal_days_in_month apesar de ser suportada
  // pelo php 5, nao funciona no PHP 5.2.6 da diana
  //echo "<table border=6 width=394>";
  echo "<table class=calendar>";
  echo "<TR class=calendar><TH  class=calendar colspan=60>$title $year </th></tr>";
  //echo "<TR><TH>$title $year </th></tr>";
  //echo "<TR><td width=62>S</td><td with=62>M</td><td with=62>T</td><td with=62>W</td><td with=62>T</td><td with=62>F</td><td with=62>S</td></tr>";
  echo "<TR  class=calendar><td  class=calendar>S</td>";
  echo "<td  class=calendar>M</td><td  class=calendar>T</td>";
  echo "<td  class=calendar>W</td><td  class=calendar>T</td>";
  echo "<td  class=calendar>F</td><td  class=calendar>S</td></tr>";
  $day_count = 1;
  echo "<TR class=calendar>";
  while ( $blank > 0 ){
    echo "<td class=calendar></td>";
    $blank = $blank - 1;
    $day_count++;
  }
  $day_num = 1;
  while ( $day_num <= $days_in_month ){
    echo "<td class=calendar> $day_num";
  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "<BR><BR> </td>";
    $day_num++;
    $day_count++;
    if ($day_count > 7){
      echo "</tr><tr class=calendar>";
      $day_count = 1;
    }
  }
  while( $day_count > 1 && $day_count < 7 ){
    echo "<td class=calendar> </td>";
    $day_count++;
  }
  echo "<tr class=calendar></table>";
}


if(file_exists("./images/warning.gif")){
  $imagePath = "./images/";
}
else{
  if(file_exists("../images/warning.gif")){
    $imagePath = "../images/";
  }
}

function htmlDropBox($classe, $classID, $PHPSESSID, $language){
  global $path_to_enscript, $imagePath, $_debug, $useSessions, $ehXML, $_theme;
  if ($_debug > 1) echo "<B>PHPSESSID = " . $PHPSESSID . "</B>\n";
  if (!$path_to_enscript || !$imagePath) {
    warning("Alguma das
    vari&aacute;veis do sistema n&atilde;o est&aacute; corretamente
    setada.");
    exit();
  }
  if (!$language) $language = "java";
  if ($language!="html")
    $extension = $language;
  else
    $extension = "xml";

  //$classeExplodida = explode("\n", $classe);
  //for($i=0;$i<(sizeof($classeExplodida))-1; $i++)
  //  $classeExplodida[$i] = $i . ": " . $classeExplodida[$i];
  //$classe = implode("\n", $classeExplodida);


  //$classFileName  = "./session_files/simulation" .  $PHPSESSID;
  $classFileName = "./" . ($useSessions?"session_files/":"") . "simulation" .  $PHPSESSID;
  if (!file_exists($classFileName)){
    $success = mkdir($classFileName, 0777);
    if (!$success && $_debug>1) echo "<B>ERRO!! criando diretorio</B>: " . $classFileName . "\n";
  }
  
  $classFileName .= "/" . $classID;
  $classFileHandle = fopen($classFileName . "." . $extension, "w");

  fputs($classFileHandle, $classe);  
  fclose($classFileHandle);
  $commandLine  = $path_to_enscript . " --line-numbers -E" . $language;
  $commandLine .= " -w html --color -p " . $classFileName;
  $commandLine .=  ".html " . $classFileName . "." . $extension;
  if ($_debug>1)
    echo "<PRE>" . $commandLine . "</PRE>\n";
  passthru($commandLine, $erro);
  if ($erro){ 
    $erro = 0;
    $commandLine  = $path_to_enscript . " --line-numbers -E";
    $commandLine .= $language . " -w html --color -p ";
    $commandLine .= $classFileName .  ".html " . $classFileName;
    $commandLine .= "." . $extension;
    if ($_debug>1)
      echo "<PRE>" . $commandLine . "</PRE>\n";
    passthru($commandLine, $erro);
    if ($erro && $_debug)
      echo "<B>ERRO gerando HTML!!!</B><BR>\n";    
  }
  $classeHtmlEd = carrega($classFileName . ".html", 1);
  echo "    <DIV id=\"" . $classID . "_Escondida\" style=\"display: block;\">\n";
  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "      <A onClick=\"toggleShowHide('" . $classID . "_Mostrada', '" . $classID . "_Escondida');\"";
  echo "       STYLE=\"cursor: hand\">";
  //echo "      onmouseover=\"document.body.style.cursor='pointer'\">";
  echo "<IMG SRC=\"" . $imagePath;
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
    echo "rightGlow.png";
  else
    echo "right.png";
  echo "\" BORDER=0 ALT=\"[>]\">";
  echo "Mostrar c&oacute;digo " . $extension . ".</A><BR>\n";
  echo "    </DIV>\n";
  //  echo "    <DIV id=\"" . $classID . "_Mostrada\" style=\"display: none;  background-color: #F3F4B2;
  //	border: 1px solid #A3A4B2;\">\n";
  
  echo "    <DIV id=\"" . $classID . "_Mostrada\" style=\"display: none;  background-color: ";
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
    echo "#000000";
  else
    echo "#F3F4B2";
  echo ";\n	border: 1px solid #A3A4B2;\">\n";  
  echo "    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  echo "      <A onClick=\"toggleShowHide('" . $classID . "_Escondida', '" . $classID . "_Mostrada');\"";
  echo "       STYLE=\"cursor: hand\">";
  //echo "      onmouseover=\"document.body.style.cursor='pointer'\">";
  echo "<IMG SRC=\"" . $imagePath;
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
    echo "downGlow.png";
  else
    echo "down.png";
  echo "\" BORDER=0 ALT=\"[>]\">";
  echo "Esconder c&oacute;digo " . $extension . ".</A><BR>\n";
  echo $classeHtmlEd;
  echo "    </DIV>\n";
  echo "<BR>\n";
  //echo $classFileName . ".html";
  if ($_debug < 2){
    unlink($classFileName . ".html");
    unlink($classFileName . "." . $extension);
  }
}

function getParametros($conn, $tabela, $projeto, $orderBy){
  global $_debug;
  $querySELECT  = "SELECT b.codigo, b.distribuicao, b.parametros, a.streamarjuna, b.entidade, b.local\n";
  $querySELECT .= "  FROM distribuicoes as a, ";
  $queryWHERE   = " as b\n  WHERE b.projeto=" . $_SESSION['projetoCodigo'];
  $queryWHERE  .= "\n  AND a.codigo=b.distribuicao";
  if ($orderBy)
    $queryWHERE  .= "\n  ORDER BY \"" . $orderBy . "\"";
  $queryParametros  = $querySELECT . $tabela . $queryWHERE;
  if ($_debug>1) echo "<PRE>" . $queryParametros . "</PRE>\n";

  $result = pg_exec ($conn, $queryParametros);
  $total  = pg_numrows($result);
  $linhas = 0;
  while ($linhas<$total){
    $row = pg_fetch_array ($result, $linhas);
    $retorno[$linhas]['codigo'] = $row[0]; // Pega o codigo da chegada para instanciar
    $retorno[$linhas]['distribuicao'] = $row[1];
    $retorno[$linhas]['string'] = $row[2];
    $retorno[$linhas]['streamarjuna'] = $row[3];
    $retorno[$linhas]['entidade'] = $row[4];
    $retorno[$linhas]['local'] = $row[5];

    $parametros1 = explode(";", $retorno[$linhas]['string']);
    $argumento = "";
    while (list($key, $val) = each($parametros1)){
      $parametros2 = explode("=", $parametros1[$key]);
      if (trim($parametros2[0])){ 
        $retorno[$linhas]['parametros'][$key]['variavel'] = trim($parametros2[0]);
        $retorno[$linhas]['parametros'][$key]['valor'] = floatval(trim($parametros2[1]));
        if ($argumento)
          $argumento .= ", " .  floatval(trim($parametros2[1]));
        else
          $argumento .= floatval(trim($parametros2[1]));
      }
    }
  
    $retorno[$linhas]['argumento'] = $argumento;

    $linhas++;
  }
  if ($_debug>1){
    echo "<PRE>\n";
    var_dump($retorno);
    echo "</PRE>\n";
  }
  return $retorno;
}

function queryToArray($conn, $query){
  $result = pg_exec ($conn, $query); // Executa a consulta.
  $total = pg_numrows($result); // Verifica o numero de linhas resultante.
  $linhas=0;
  while ($linhas<$total){
    $row = pg_fetch_array ($result, $linhas);
    $retorno[$row[0]] = $row[1];
    $linhas++;
  }
  return $retorno;
}

function full_copy( $source, $target ){
  if ( is_dir( $source ) ){
    @mkdir( $target, 0777 );
    $d = dir( $source );
    while ( FALSE !== ( $entry = $d->read() ) ){
      if ( $entry == '.' || $entry == '..' ){
        continue;
      }
      $Entry = $source . '/' . $entry;           
      if ( is_dir( $Entry ) ){
        full_copy( $Entry, $target . '/' . $entry );
        continue;
      }
      copy( $Entry, $target . '/' . $entry );
      //chmod($target . '/' . $entry, 0666);
    }
    $d->close();
  }
  else{
    copy( $source, $target );
  }
}

function carrega($arquivo, $tagged){
  global $_debug;
  if ($tagged) $grava=0;
  if (!file_exists($arquivo))
    return 0;
  $handle = @fopen($arquivo, "r");
  
  $retorno = "";
  $linhas = 0;
  if ($handle) {
    while (!feof($handle)) {
      $buffer = fgets($handle);
      $buffer = fixEncoding($buffer);
      //if ($_debug>1) echo "<PRE>" . $buffer . "</PRE><BR>\n";
      if ($tagged){
        if (trim($buffer)=="<PRE>") $grava=1;
        if ($grava) $retorno .= $buffer;// . "\n";
        if (preg_match_all('/<\/PRE>/', $buffer, $deu)) $grava=0;
      }
      else
        if (trim($buffer)) $retorno .= $buffer;// . "\n";	
      $linhas++;
      //echo $linhas . ": " . $buffer . "\n";
    }
    fclose($handle);
  }
  //$texto = new ForceEncoding();
  return $retorno;
}

function spellcheck ( $string ) {
  $words = split(' ',$string);
  $misspelled = $return = array();
  pspell_config_create("pt_BR",PSPELL_NORMAL);
  $int = pspell_new('pt_BR');
  foreach ($words as $value) {
    $check = preg_split('/[\W]+?/',$value);
    if (($check[1] != '') and (strpos("'",$value) > 0) ){
      $check[0] = $value;
    }
    if (($check[0] + 1 == 1) and (!pspell_check($int, $check[0]) )) {
      $res  .= '<SPAN class="misspelled" style="color:#FF0000; font-weight:bold;">' . $value . ' </SPAN> ';
      $poss = pspell_suggest($int,$value);
      $orig = metaphone($value);
      foreach ($poss as $suggested){
        $ranked[metaphone($suggested)] = $suggested;
      }
      if ($ranked[$orig] <> '') {
        $poss[1] = $ranked[$orig];
      }
      $res2  .= '<SPAN style="color:#CC8800; font-weight:bold">' . $poss[1] . ' </SPAN> ';
    } else {
      $res .= $value . ' ';
      $res2 .= $value . ' ';
    }
  }
  $n[1] = $res;
  $n[2] = $res2;
  return $n;
}

function urlGet($url){
  global $_debug;

  $saida = "";
  ini_set ( "user_agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36" );

  //$url = preg_replace('/(\s{1,})/', "+", $url);
  
  $handle = fopen($url, "r");
  if ($handle){
    while (!feof($handle)) {
      //if ($_debug>1) 
      //echo "<PRE>" . htmlentities($teste) . "</PRE>\n";
      $teste = fgets($handle, 4096);
      $saida .= $teste;
    }
    fclose($handle);
  }  
  //echo $saida;
  return $saida;
}

/**
 * Retorna o numero de resultados encontrados no scholargoogle para
 * uma consulta passada por parametro.
 * @param string $nome consulta a ser enviada por get no scholargoogle
 */
function scholarRank($nome){
  //http://php.net/manual/pt_BR/context.http.php
  //http://php.net/manual/pt_BR/reserved.variables.httpresponseheader.php
  //http://php.net/manual/pt_BR/wrappers.http.php

  $saida = "";
  $contador = 0;
  ini_set ( "user_agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36" );
  $nome = preg_replace('/(\s{1,})/', "+", $nome);
  $handle = fopen("http://scholar.google.com.br/scholar?hl=en&lr=&q=\"" . $nome . "\"&btnG=Search", "r");
  echo "\n\nhttp://scholar.google.com.br/scholar?hl=en&lr=&q=\"" . $nome . "\"&btnG=Search\n\n";
  while (!feof($handle)) {
    $teste = fgets($handle, 4096);
    if ($teste == false) break;
    //$resultado = trim(preg_replace('/(.*?Results)\s<b>\d{1,}<\/b>\s*-\s*<b>\d{1,}<\/b>\s*of.*?\w{1,}\s*<b>(.*?)<\/b>.*/', "$2", $teste));
    if (strpos($teste, "result")) {
      //echo $teste . "\n";
      $resultado = trim(preg_replace('/.*?">(About\s|)(\d.*?\s)result.*/', "$2", $teste));
      echo "\n\nResultados: " . $resultado . "\n\n";
    }
    if (is_numeric($resultado)){
      $saida = $resultado;
    }
    else{
      $resultado = trim(preg_replace('/(.*?Results)\s<b>\d{1,}<\/b>\s*-\s*<b>\d{1,}<\/b>\s*of\s*<b>(.*?)<\/b>.*/', "$2", $teste));
      if (is_numeric($resultado)){
        $saida = $resultado;
      }
    }
  }
  fclose($handle);
  return $saida;
}

/**
 * echoa messagem usando a formatacao de subtitulo
 * @param string $texto Texto a ser echoado no subtitulo.
 */
function subtitulo($texto){
  echo "  <DIV ID=subtitulo>" . $texto . "</DIV>\n";
}

/**
 * Preenche a esquerda de uma string com zeros
 * @param string $valor string a ser preenchida com zeros
 * @param int $tamanho  tamanho total da string com os zeros.
 *       * 1 - De 4 em 4 anos é ano bissexto.
 *       * 2 - De 100 em 100 anos não é ano bissexto.
 *       * 3 - De 400 em 400 anos é ano bissexto.
 *       * 4 - Prevalece as últimas regras sobre as primeiras.
 *
 */
function leftZero($valor, $tamanho){
  while((strlen($valor))<$tamanho){
    $valor = "0" . $valor;
  }
  return $valor;
}

/**
 * Converte entidades HTML para seus correspondentes ISO
 * @param string $string String a ser convertida
 */
function unhtmlentities ($string){  
  $trans_tbl = get_html_translation_table (HTML_ENTITIES);
  $trans_tbl = array_flip ($trans_tbl);
  return strtr ($string, $trans_tbl);
}    

/**
 * Gera um XML para mostrar o dataset resultante de uma dada consulta
 * (query) SQL
 * @param int    $conn      Handle de conexao com banco
 * @param string $query     string com a consulta (query) SQL
 * @param string $tableName nome da tabela em xml
 * @param string $rowName   nome da linha em xml
 * @access public
 */
function query2xml($conn, $query, $tableName, $rowName){
  global $_debug, $encoding;
  if ($_debug>1) echo "<PRE>" . $query . "</PRE>\n";
  $result = pg_exec ($conn, $query);/* Executa a consulta */
  if ($result){
    $xml  = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n";
    $xml .= "  <" . $tableName . ">\n";
    $totalDeCampos = pg_numfields($result);
    $totalDeLinhas = pg_numrows($result);
    $linhas = 0;
    if ($_debug>1) echo "<B>Total de linhas: " . $totalDeLinhas . "</B><BR>\n";
    if ($_debug>1) echo "<B>Total de campos: " . $totalDeCampos . "</B><BR>\n";
    while ($linhas<$totalDeLinhas){
      $xml .= "    <" . $rowName . ">\n";
      $row = pg_fetch_row ($result, $linhas);
      $colunas = 0;
      while ($colunas<$totalDeCampos){ 
        $xml .= "      <" . pg_fieldname($result, $colunas) . ">";
        $xml .= unhtmlentities ($row[$colunas]);
        $xml .= "</" . pg_fieldname($result, $colunas) . ">\n";
        $colunas++;
      }
      $xml .= "    </" . $rowName . ">\n";
      $linhas++;
    }
    $xml .= "  </" . $tableName . ">\n";
    if (!$_debug<2)
      Header("Content-type: application/xml; charset=" . $encoding); 
  }
  if ($_debug>1)
    echo "<PRE>\n" . $xml . "\n</PRE>\n";
  else
    echo $xml;            
}

///////////////////////////////////////////////////////////////////////////////
/**
 * resizeImage() redimensiona uma imagem e a salva em um diretório especificado
 * 
 * @param string file => Caminho para a imagem original a ser dimensionada;
 * @param string type => Extensão da imagem;
 * @param int    prop => Tamanho desejado para uma das dimensões. A função calcula o tamanho
 * original da imagem. A menor dimensão assume o valor de $prop e a maior é
 * calculada em relação ao $prop;
 * @param string newName => Nome desejado para o novo arquivo de imagem;
 * @param string uploadPath => Local para armazenar a nova imagem.
 * Retorno:
 * @return     $newFile => Caminho para a nova imagem.
 * 
 * @author: Gustavo Leal
 * Uso de Imagick
 *  src: https://jqueryajaxphp.com/image-conversion-using-php-imagemagick/
 */
function resizeImage($file, $type, $prop, $newName, $uploadPath){
  global  $_debug, $conn, $PHPSESSID, $_SESSION, $ehXML;

// Verificar orientacao ao salvar...
//http://sylvana.net/jpegcrop/exif_orientation.html
//     1        2       3      4         5            6           7          8

// 888888  888888      88  88      8888888888  88                  88  8888888888
// 88          88      88  88      88  88      88  88          88  88      88  88
// 8888      8888    8888  8888    88          8888888888  8888888888          88
// 88          88      88  88
// 88          88  888888  888888

    
  $img = new Imagick(); //Define imagick function
  //echo "abrindo..." . $file . "\n";;
  $img->readImage($file); //Read the file
  //echo "ABRIU!!!\n";
  $imageprops = $img->getImageGeometry();
  $width = $imageprops['width'];
  $height = $imageprops['height'];

  if ($width > $height && $height >  prop) {
    $newHeight = $prop;
    $newWidth = ($prop / $height) * $width;
  } else if ($width < $height && $width >  prop){
    $newWidth = $prop;
    $newHeight = ($prop / $width) * $height;
  } else {
    $newWidth = $prop;
    $newHeight = $prop;
  }
  if ($_debug > 1) {
    echo ($ehXML ? "\n" : "</br>") . "Width: " . $width . ($ehXML ? "\n" : "</br>");
    echo "New Width: " . $newWidth . ($ehXML ? "\n" : "</br>");
    echo "Heigh: " . $height . ($ehXML ? "\n" : "</br>");
    echo "New Height: " . $newHeight . ($ehXML ? "\n" : "</br>");
  }
  $img->resizeImage($newWidth, $newHeight, imagick::FILTER_LANCZOS, 0.9, true);
  $newFile = $uploadPath . $newName;
  //echo "<BR>" . $newFile . "<BR>\n";
  $create = $img->writeImages($newFile, false);
  if ($create) {
    return $newFile;
  } else {
    if ($_debug) echo "Houve um erro no redimensionamento!";
    return 0; // retorna alguma coisa para quem chamou a funcao saber que nao deu!
  }

  if ($_debug > 1) {
    echo "<PRE>";
    echo $newFile;
    echo "</PRE>";
    echo "<PRE>";
    echo $create;
    echo "</PRE>";
  }
  return $newFile;
}

///////////////////////////////////////////////////////////////////////////////
/**
 * insertFile() insere um arquivo no banco de dados, na tabela arquivos.
 * @param string  $nome           => nome do arquivo a ser inserido;
 * @param string  $filedata       => conteúdo do arquivo encriptado em base64;
 * @param string  $type           => array que contém o tipo de arquivo. type[0] = tipo, type[1] = extensão;
 * @param string  $exif           => string json contendo informações EXIF da imagem;
 * @param string  $md5            => assinatura MD5 do arquivo;
 * @param int     $album          => ID do album ao qual o arquivo pertence;
 * @param bool    $imagemOriginal => se necessario referir a versão anterior, definir como 1;
 * @param bool    $small          => 1 caso a imagem seja versão reduzida;
 * @param bool    $thumb          => 1 caso a imagem seja thumbnail.
 * @param string  $usuario        => Login do usuário conectado. 
 * 
 * @author: Gustavo Leal
 * Erros:
 *    1 => Extensão de arquivo inválida.
 *    2 => Versão anterior não encontrada.
 *    3 => Erro inserindo o arquivo.
 *    4 => Erro deletando permissoes do arquivo.
 *    5 => Erro atualizando permissões de usuário. Operação desfeita!
 *    6 => Erro inserindo relação com o álbum.
 *    0 => Arquivo inserido com sucesso!
 */
function insertFile($nome, $filedata, $type = array(), $exif, $md5, $album, $imagemOriginal, $small, $thumb, $usuario){
  global $_debug, $conn, $PHPSESSID, $_SESSION;

  if (!isset($usuario))
    $usuario = $_SESSION['matricula'];
  
  //////////////////////////////////////////////////////////////////Verifica se a imagem adicionada já existe no banco
  $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '" . $md5 . "' ";
  $query .= "AND \"Conteúdo\" IS NOT NULL;";
  $result = pg_query($conn, $query);
  $md5Exist = pg_NumRows($result);

  //////////////////////////////////////////////////////////////////Seleciona o tipo correto de arquivo
  $query = "SELECT codigo FROM tipos_de_arquivos \n";
  $query .= "WHERE \"MIME Type / Internet Media Type\" = '" . $type[0] . "/" . $type[1] . "';";
  //echo $query;
  $result = pg_query($conn, $query);
  $exist = pg_NumRows($result);
  //echo $type[0];
  if ($exist) {
    $extensao = pg_fetch_row($result);
  } else {
    return 1;   // Extensão inválida;
  }
  
  // Query para inserção do arquivo no banco de dados
  // Se já existe um arquivo com a mesma assinatura MD5 o conteúdo não é inserido.
  // Se a imagem possuir informações EXIF, insere-as no banco caso não haja outra igual.
  

  $sql = "INSERT INTO arquivos(\"Nome do arquivo\", \"Assinatura MD5\", \"Tipo\", \"Proprietário\", \"Grupo\"";
  if (!$md5Exist) {
    $sql .= ", \"Conteúdo\" \n";
    if ($type[1] == 'jpeg' || $type[1] == 'jpg') //EXIF da imagem
      {
	$sql .= ", \"EXIF\" \n";
      }

  }
  if ($imagemOriginal) {
    $sql .= ", \"Imagem original\" \n";
  }
  if ($small) {
    $sql .= ", small \n";
  }
  if ($thumb) {
    $sql .= ", thumb \n";
  }
  $sql .= " ) VALUES (";
  $sql .= "            '" . $nome . "',\n"; //Nome
  $sql .= "            '" . $md5 . "',\n"; //Assinatura MD5
  $sql .= "            '" . $extensao[0] . "',\n"; //Tipo
  $sql .= "            '" . $usuario . "',\n"; //Proprietário
  $sql .= "            '4' \n"; //Grupo -- Usuarios
  if (!$md5Exist) {
    //$sql .= "     , decode('{$filedata}', 'hex') \n"; //Conteudo do arquivo
      $sql .= "     , '".$filedata."'\n"; //Conteudo do arquivo
    if ($type[1] == 'jpeg' || $type[1] == 'jpg' || $type[1] == 'tiff') { //EXIF da imagem
       $sql .= "     , '" . $exif . "' \n";
    }
  }
  if ($imagemOriginal) {
    $sql .= "     ,'" . $imagemOriginal . "' \n";
  }
  if ($small) {
    $sql .= ", '" .$small. "'  \n";
  }
  if ($thumb) {
    $sql .= ", '" .$thumb. "'  \n";
  }
  $sql .= "          );";

  if ($_debug > 1)  echo $sql . "<\br>";

  $result = pg_query($conn, $sql); //Executa a query
  if ($_debug) echo "Inserindo arquivo....";
  if(!$result){
    $result = pg_exec($conn, "ROLLBACK");
    return 3; // Erro inserindo o arquivo. Operação desfeita!

    if ($_debug)
      echo "[ FAIL ]\n";
    else
      warning( "Erro inserindo arquivo!");
  }
  else
    if ($_debug) echo "[  OK  ]\n";
  
  // Coleta o código dos arquivos inseridos anteriormente e insere as permissões
  // adequadas para eles

  $permissoesPerfil = [0 => 1, 1 => 2, 2 => 4];

  $query = "SELECT codigo FROM arquivos where \"Proprietário\" = '" . $usuario . "' ";
  $query .= "AND (select MAX(codigo) from arquivos where \"Proprietário\" = '" . $usuario . "') = codigo";
  $result = pg_query($conn, $query);
  $arquivo = pg_fetch_row($result);
  if ($_debug > 1) {
    echo $query;
    echo "<PRE>";
    print_r($arquivo);
    echo "</PRE>\n";
  }

  foreach ($permissoesPerfil as $permissao) { //Insere todas as permissões.

    $query = "INSERT INTO permissoes_arquivos(arquivo, permissao) VALUES (";
    $query .= $arquivo[0] . ", ";
    $query .= $permissao . "); \n";
    $result = pg_query($conn, $query);
    if (!$result) {
      //echo "passei";
      $result = pg_exec($conn, "ROLLBACK");
      return 5; // Erro atualizando permissões de usuário. Operação desfeita!
      //$erro++;
      //warning("Erro atualizando permissão de usuario!<BR>\nOpera&ccedil;&atilde;o desfeita!");
      //break;
    }    
    
  }
 
  if ($_debug > 1) {
    echo "<PRE>" . $query . "</PRE>\n";
  }

  
  if($album > 0){
    
    $query = "INSERT INTO arquivos_albuns(arquivo, album) VALUES (";
    $query .= $arquivo[0] . ", ";
    $query .= $album . "); \n";
    $result = pg_query($conn, $query);
  } 
  
  if ($_debug > 1) {
    echo "<PRE>Album: " . $query . "</PRE>\n";
    echo "<PRE>Album: " . $album . "</PRE>\n";
  }
  
  if (!$result) {
    //echo "passei";
    $result = pg_exec($conn, "ROLLBACK");
    return 6; // Erro inserindo relação com o álbum.
    //$erro++;
    //warning("Erro atualizando albuns!<BR>\nOpera&ccedil;&atilde;o desfeita!");
    //break;
  }
  return 0;
}
///////////////////////////////////////////////////////////////////////////////

/**
 * uploadErrors() retorna uma string contendo uma mensagem de erro relacionada a função insertFile().
 * @param int   $error  Codigo do erro ocorrido na função insertFile().
 * Mensagens:
 *    1 => Extensão de arquivo inválida!
 *    2 => Versão anterior não encontrada!
 *    3 => Erro inserindo o arquivo!
 *    4 => Erro deletando permissoes do arquivo!
 *    5 => Erro atualizando permissões de usuário! Operação desfeita!
 *    6 => Erro inserindo relação com o álbum!
 *    0 => Arquivo inserido com sucesso!
*/
function uploadErrors($error){
  switch($error){
    case 1:
      return "Extensão de arquivo inválida!";
      break;
    case 2:
      return "Versão anterior não encontrada!";
      break;
    case 3:
      return "Erro inserindo o arquivo!";
      break;
    case 4:
      return "Erro deletando permissoes do arquivo!";
      break;
    case 5:
      return "Erro atualizando permissões de usuário! Operação desfeita!";
      break;
    case 6:
      return "Erro inserindo relação com o álbum!";
      break;
    default:
      return "Arquivo inserido com sucesso!";
      break;
  }
}


function getUserProfilePictureCode($login){
  global $conn, $_debug;
  $query  = "SELECT codigo from arquivos\n";
  $query .= "  where \"Imagem original\" = (SELECT arquivos.codigo \n";
  $query .= "                                 from arquivos, usuarios \n";
  $query .= "                                  where usuarios.foto = arquivos.codigo\n";
  $query .= "                                  and usuarios.login = '" . pg_escape_string($login) . "')\n";
  $query .= "  and small = 't'";
    
  $result = pg_query($conn, $query);
  $profileImage = pg_fetch_row($result);
  return $profileImage[0];
}

 
/**
 * codigoImagem() retorna o código de uma imagem de um usuário.
 * @param string $login => string contendo a matrícula do usuario logado.
 * @param int    $album => album ao qual pertence a imagem;
 * 
 * @author: Gustavo Leal
 */
 function codigoImagem($login, $album, $md5){
   global $conn, $_debug;
   $query = " SELECT codigo, \"Assinatura MD5\" FROM arquivos where \"Proprietário\" = '".$login."' 	\n";
   $query.= " 	AND (select MAX(a.codigo) 	\n";
   $query.= "          from arquivos as a, albuns as ab, arquivos_albuns as aa  	\n";
   $query.= "          where aa.arquivo = a.codigo 	\n";
   $query.= "         	AND aa.album = ab.codigo 	\n";
   $query.= " 			AND a.\"Proprietário\" = '".$login."' 	\n";
   $query.= " 			AND a.thumb = FALSE 	\n";
   $query.= " 			AND a.small = FALSE 	\n";
   $query.= "         	AND aa.album = ".$album."	\n";
   $query.= "         ) = codigo	\n";
   $query.= "  AND \"Assinatura MD5\" = '" . $md5 . "';\n";

   //echo "<PRE>" . $query . "</PRE>";
   
   $result = pg_query($conn, $query);
   $existFile = pg_NumRows($result);
   $profileImage = pg_fetch_row($result);
    
   if($existFile){
     if(is_null($profileImage[6])){
       $query = "SELECT codigo FROM arquivos WHERE \"Assinatura MD5\" = '" . $profileImage[1] . "' ";
       $query .= "AND \"Conteúdo\" IS NOT NULL;";
       $result = pg_query($conn, $query);
       $image = pg_fetch_row($result);
       $profileImage[0] = $image[0];        
     }
     if($_debug){
       echo "<PRE>";
       print_r($profileImage);
       echo "</PRE>";
     }
     return $profileImage[0];
   }
 }   


///////////////////////////////////////////////////////////////////////////////
/*
         _______________
        /               \           C U I D A D O !!!
       /                 \
      /                   \       Esta funcao ainda esta implementada de
      |   XXXX     XXXX   |       forma muito rebuscada. Caso voce tenha
      |   XXXX     XXXX   |       analisado a mesma profundamente e julgue
      |   XXX       XXX   |       que a tenha compreendido completamente,
      |         X         |       muito provavelmente voce nao a
      \__      XXX      __/       compreendeu!
        |\     XXX     /|
        | |           | |
        | I I I I I I I |         PS.: Alem de rebuscada, existem muitas
        |  I I I I I I  |              gambiarras e muito codigo redundante.
        \_             _/              Como o codigo redundante estah muito
          \_         _/                misturado com o codigo otimizado,
            \_______/                  NAO saia deletando coisas aqui
    XXX                    XXX         dentro!!
   XXXXX                  XXXXX
   XXXXXXXXX         XXXXXXXXXX
          XXXXX   XXXXX
             XXXXXXX
          XXXXX   XXXXX
   XXXXXXXXX         XXXXXXXXXX
   XXXXX                  XXXXX
    XXX                    XXX

 */
/**
 * Mostra uma tabela com linhas de cores intercaladas, mostrando o resultado
 * da consulta passada como argumento.
 * @param string $query  string com a consulta (query) SQL
 * @param int    $conn   Handle de conexao com banco
 * @param string $orderby    nome do campo para ordenar
 * @param int    $desc       1 para descendente, 0 para ascendente
 * @param array  $formata    vetor com o ou para que o campo seja formatado
 *                           com 9 posicoes
 * @param array  $references vetor, caso tenha algum valor, eh a string com o 
 *                           nome da tabela cujo campo nome deve estar no
 *                           caption e o campo codigo deve estar no valor de
 *                           uma combo box.
 * @param array  $form
 * @param bool   $boolean
 * @param string $link
 * @param string $destak         Termo a ser destacado no resultado da
 *                               consulta (italico, negrito e vermelho)
 * @param string $extraGe t
 * @param string $hideByQuery    Esconde algumas linhas por javascript
 *                               conforme a query passada.
 * @param string $showNum        String estilo printf com a mensagem
 *                               que mostra o numero de linhas do
 *                               resultado. (se for 1, monta um texto
 *                               padrão)
 * @param int    $boldCondition  Coluna com a condição para uma linha
 *                               ficar em negrito
 * @param string $secondOrder    Uma segunda coluna para ordenação
 *                               (trocar para um vetor de ordenação)
 * @param int    $limite         Limite do mumero de linhas do resultado
 *                               Na pratica da um apende de um LIMIT $limite
 *                               no final da $query.
 * @param array  $appendTotalRow Inclui no final da tabela uma linha com 
 *                               os totais dos campos numéricos e strings
 *                               nas colunas presentes no array $appendToRow
 *                           type=text [NAO IMPLEMENTADO]
 * @param string  $downloads['maxgeo'] Largura x Altura
 * @param string  $downloads['urldestino'] URL para download  default formFileDownloa.php
 * @param string  $downloads['tabela']  Tabela que contém o arquivo para download
 * @param string  $downloads['campo'] Campo do arquivo para download 
 * @param string  $downloads['campoChave'] default codigo
 * @param string  $downloads['valorChave'] 
 * @param boolean  $downloads['chaveTemAspas'] default false
 * @access public
 */
//"<TD bgcolor=\"" . $row[$formata['corDeFundo']]
//<TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " 
//<TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " 

function show_query(){

  /*
     This block is necessary after PHP 7.2 checks the number of parameters passed.
     show_query has calls with different amount of parameters passed.
     It would be a larger effort to change every call to a fixed number of parameters.
  */
  if (func_get_arg(0) != NULL)
   $query = func_get_arg(0);
  if (func_get_arg(1) != NULL)
   $conn = func_get_arg(1);
  if (func_get_arg(2) != NULL)
   $orderby = func_get_arg(2);
  if (func_get_arg(3) != NULL)
   $desc = func_get_arg(3);
  if (func_get_arg(4) != NULL)
   $formata = func_get_arg(4);
  if (func_get_arg(5) != NULL)
   $references = func_get_arg(5);
  if (func_get_arg(6) != NULL)
   $form = func_get_arg(6);
  if (func_get_arg(7) != NULL)
   $boolean = func_get_arg(7);
  if (func_get_arg(8) != NULL)
   $link = func_get_arg(8);
  if (func_get_arg(9) != NULL)
   $destak = func_get_arg(9);
  if (func_get_arg(10) != NULL)
   $extraGet = func_get_arg(10);
  if (func_get_arg(11) != NULL)
   $hideByQuery = func_get_arg(11);
  if (func_get_arg(12) != NULL)
   $showNum = func_get_arg(12);   
  if (func_get_arg(13) != NULL)
   $boldCondition = func_get_arg(13);
  if (func_get_arg(14) != NULL)
   $secondOrder = func_get_arg(14);
  if (func_get_arg(15) != NULL)
   $limite = func_get_arg(15);
  if (func_get_arg(16) != NULL)
   $appendTotalRow = func_get_arg(16);
  $doNotRemoveOrderBy = false;
  if (func_get_arg(17) != NULL)
    $doNotRemoveOrderBy = func_get_arg(17);


  global $_debug, $imagePath, $SYSTEM_VERSION, $ONDE_VERSION,
    $DATABASE_VERSION, $email, $fone, $_theme, $encoding, $closeDIV,
    $toggle, $queryCount, $PHPSESSID, $abriuForm;
  $queryCount++; // Incrementa numero de chamadas a estah funcao para modo de debug.

  if (!$abriuForm){
    foreach($toggle as $togglePoint){
      $extraGet .= "&amp;toggle[]=" . $togglePoint;
      echo "      <INPUT TYPE=\"HIDDEN\" NAME=\"toggle[]\" VALUE=\"" . $togglePoint . "\">\n";
      //echo "<script> console.log('passei'); </script>\n"; 
    }
    $abriuForm = 1;
  }
   
  if ($_debug>2){
    echo "<B>Field: </B>" . $form['field'] . "<BR>\n";
    echo "<B>Action: </B>" . $form['action'] . "<BR>\n";
    echo "<B>Name: </B>" . $form['name'] . "<BR>\n";
    echo "<B>Delete: </B>" . $form['delete'] . "<BR>\n";
  }
  if (!$query) exit; // Abandona caso nenhuma consulta seja passada

  $colunas=0;
  if ($form['hideFirstColumn']){
    $columnsToHide[$colunas] = 0;
    $colunas++;
  }
  if ($formata['corDeFundo']){
    $columnsToHide[$colunas] = intval($formata['corDeFundo']);
    $colunas++;
  }
  if ($form['deleteCondition']){
    $columnsToHide[$colunas] = intval($form['deleteCondition']);
    $colunas++;
  }
  if ($boldCondition['hide']){
    $columnsToHide[$colunas] = intval($boldCondition['column']);
    $colunas++;
  }
  if (is_array($link)){
    while (is_array(current($link))) {
      $columnsToHide[$colunas] = intval($link[key($link)]['urlAppendColumn']);
      $colunas++;    
      next($link);
    }
  }
  if($hideByQuery){?>
    <SCRIPT LANGUAGE='javascript'>
      function toggleColor(id){
      //alert(id);
      var field = document.getElementById(id);
      field.style.backgroundColor = "#FF0000";
    }
    function toggleMenuTabela(id1){
      //alert (id1);
      var field1 = document.getElementById(id1);
      if ((field1 == null)){
      }
      else
	if (field1.style.display == "none"){
	  field1.style.display = "";
	}
	else{
	  field1.style.display = "none";
	}
    }
    </SCRIPT>
	<?PHP
	}
  if ($_debug>2){
    echo "<PRE>hideByQuery:\n ";
    var_dump($hideByQuery);
    echo "</PRE>\n";
  }
  if ($hideByQuery['queryColuna']){// Usado para ocultar linhas da tabela
    $onClick['consulta'] = $hideByQuery['onClick'];
    $onClick['delimiter'] = $hideByQuery['delimiter'];
    queryCheckList($hideByQuery['queryColuna'], $conn, "hide", "unchecked",
                   1, $onClick);
  }
  if (isset($_SESSION['PHPSESSID'])){
    $_POST['PHPSESSID'] = $_SESSION['PHPSESSID'];    
  }  
  if (isset($_POST['PHPSESSID']))
    $_GET['PHPSESSID'] = $_POST['PHPSESSID'];
  if (isset($_GET['PHPSESSID']))
    echo "      <INPUT TYPE=\"HIDDEN\" NAME=\"PHPSESSID\" VALUE=\"" . $_GET['PHPSESSID'] . "\">\n";
  if ($orderby){
    $query = $query . "\nORDER BY \"" . $orderby . "\"";
    if ($desc) $query = $query . " desc";
    if ($secondOrder && ($orderby != $secondOrder) )
      $query .= ", " . $secondOrder;
  }
  if (($form['field'])&&(!$form['delete'])){
    echo "      <FORM NAME=\"" . $form['name'] . "\"";
    echo " METHOD=\"POST\" ACTION=\"" . $form['action'] . "\">\n";
  }

  if ($_debug){
    echo "</CENTER>\n";
    $toggleID = md5($query) . intval($queryCount);
    htmlDropBox($query, "enscript_" . $toggleID, $PHPSESSID, 'sql');

    
    togglePoint($toggleID, "Informa&ccedil;&otilde;es de debug [show_query]", 1, false, NULL);
    echo "<PRE>\n";
  }

  //echo "SecondOrder " . $secondOrder . "<BR>\n";
  //echo "<PRE>" . $query . "</PRE>\n";
  if ($limite) $query .= "\nlimit " . intval($limite);


  // Antes de executar, dar um explain, ver se o tempo é grande.
  // $result = pg_exec("EXPLAIN " . $query );
  // Se for grande, consulta o cache de consultas, se está lá
  // $result = pg_exec("SELECT codigo from cacha_de_consultas where query = '" . $query . "';
  // Verifica se o bot do cache está rodando
  // Se estiver no cache, ver se o timestamp da ultima execucao
  // é não é muito maior que o tempo para executar.

  
  $result = pg_exec ($conn, $query);/* Executa a consulta */

  
  if (!$result and !$doNotRemoveOrderBy){
    //echo "<PRE>" . $query . "</PRE>\n";
    //$query = tiraQuebrasDeLinha($query, "<MARCADOR>");
    $query = preg_replace("/(\r\n)/", "<MARCADOR>", $query);
    //echo "<PRE>" . htmlentities($query) . "</PRE>\n";
    $pattern = '/ORDER BY.*/i';
    $replacement = "";
    $query = preg_replace($pattern, $replacement, $query);
    $query = poeQuebrasDeLinha($query, "<MARCADOR>");
    //echo "<PRE>" . $query . "</PRE>\n";
    $orderBy = "";
    $desc = "";
    $result = pg_exec ($conn, $query);/* Executa a consulta */
    if ($_debug) echo "[Warning] - Tentando sem o order by.\n";
  }
  if ($_debug){
    //echo htmlentities($query). "\n\n";
    echo htmlentities($query, ENT_COMPAT | ENT_HTML401, $encoding). "\n\n";
    foreach ($formata as $campoFormatado){
      echo "-> " . $campoFormatado . "\n";
    }
  }  
  if (!$result) { 
    echo "Erro na execucao da consulta.\n";
    $lastError = pg_last_error();
    echo $lastError;
    echo "</PRE>";
    echo $closeDIV;
    echo "<CENTER>\n";
    if ($_debug > 3){
      include("page_footer.inc");
      exit(1);
    }
  }
  if (!$result and $doNotRemoveOrderBy){    
    if (!$ehXML) echo "</CENTER><DIV class=\"busy\">";
    echo "<BR><PRE style=\"color: black;\">" . $lastError . "</PRE>";
    if (!$ehXML) echo $closeDIV . "<CENTER>";          
  }

  if ($form['delete']){
    echo "<script>\n";?>
      var delete_selected = false;;
      function selectAllDelete(total){
	console.log(delete_selected);	
	for (var i = 0; i<total; i++){
	  //console.log(i);
	  //console.log(i + ') ' + $("#delete_" + i).attr('disabled'));	   
	  if (!delete_selected && !$("#delete_" + i).attr('disabled') )  
	    $("#delete_" + i).attr('checked', true);
	  else
	    $("#delete_" + i).attr('checked', false);
	}
        delete_selected = !delete_selected;
      }
    <?PHP echo "</script>\n";
  }  
  if ($form['duplicar']){
  }
  
  if ($_debug){
    echo "</PRE>";
    echo $closeDIV;
    echo "<CENTER>\n";
  }
  $total = pg_numrows($result);
  if ($showNum){
    if (strpos('%', $showNum)>1	){
      printf($showNum, pg_numrows($result));
    }
    else
      if (strlen($showNum)>1)
        echo "<BR>\n" . pg_numrows($result) . " " . $showNum . "<BR>\n";
      else
        echo "<BR>\nSua consulta resultou em " . pg_numrows($result) . " linhas<BR>\n";
  }
  if ($limite){
    if ( (strlen($showNum)>1) && !(strpos('%', $showNum)>1))
      echo "<BR>\nResultado limitado em " . $limite . " " . $showNum . "<BR><BR>\n";
    else
      echo "<BR>\nO resultado de sua consulta foi limitado em " . $lmite . " linhas<BR><BR>\n";
  }
  printf("      <TABLE id=\"cTable\" class=onde>\n");
  printf("        <TR>\n");
  $colunas=0;
  $totalDeCampos=pg_numfields($result);
  while ($colunas<$totalDeCampos){ /* Nomes dos campos no cabecalho.    */
    //echo "<B>" . pg_field_type($result, $colunas) . "</B><BR>\n";

    if (!in_array($colunas, $columnsToHide)){
      printf("          <TH class=onde >");
      if ($orderby){
	$nome_do_campo = pg_fieldname($result, $colunas);
	if ($nome_do_campo == $orderby)
	  if (!$desc){
	    echo "<A HREF=\"" . $_SERVER['PHP_SELF'];
	    echo "?orderby=" . $nome_do_campo . "&amp;desc=1";
	    if (trim($extraGet))
	      echo $extraGet;
	    if (isset($_GET['PHPSESSID']))
	      echo "&PHPSESSID=" . $_GET['PHPSESSID'] . "\">";
	    else
	      echo "\">";
	    echo $nome_do_campo  . "<IMG SRC=\"" . $imagePath . "/down.png\"";
	    echo " BORDER=0 ALT=\"[v]\"></A>";
	  }
	  else{
	    echo "<A HREF=\"" . $_SERVER['PHP_SELF'];
	    echo "?orderby=" . $nome_do_campo . "&amp;desc=0";
	    if (trim($extraGet))
	      echo $extraGet;
	    if (isset($_GET['PHPSESSID']))
	      echo "&amp;PHPSESSID=" . $_GET['PHPSESSID'] . "\">";
	    else
	      echo "\">";
	    echo $nome_do_campo  . "<IMG SRC=\"" . $imagePath . "/up.png\"";
	    echo " BORDER=0 ALT=\"[^]\"></A>";
	  }
	else{
	  echo "<A HREF=\"" . $_SERVER['PHP_SELF'];
	  echo "?orderby=" . $nome_do_campo . "&amp;desc=0";
	  if (trim($extraGet))
	    echo $extraGet;
	  if (isset($_GET['PHPSESSID']))
	    echo "&amp;PHPSESSID=" . $_GET['PHPSESSID'] . "\">";
	  else
	    echo "\">";
	  echo $nome_do_campo  . "</A>";
	}
      }
      else{
	echo ($_debug ? "<span title=\"" .  pg_fieldtype($result, $colunas) . "\">" : "");
	echo pg_fieldname($result, $colunas);
	echo ($_debug ? "</span>" : "");
      }
      if ($_debug>2) echo "Coluna: " . $colunas . " " .pg_fieldtype($result, $colunas);
      printf("</TH>\n");

    }    
    $colunas++;    
  }

  if ($form['delete']){
    echo "<TH class=onde ><IMG onclick=\"javascript:selectAllDelete(" . $total . ");\" SRC=\"" . $imagePath . "/trash-x.png\"";
    echo " BORDER=0 ALT=\"[DEL]\"></A></TH>\n";
  }  
  if ($form['duplicar']){
    echo "<TH class=onde ><IMG SRC=\"" . $imagePath . "/clone_2.gif\"";
    echo " BORDER=0 ALT=\"[DEL]\"></A></TH>\n";
  }
  
  if ($form['field']){
    printf("<TH class=onde >&nbsp;</TH>\n");
  }
  $linhas=0;
  //$hideByQuery = 1; // para testar o hideByQuery
  while ($linhas<pg_numrows($result)){// Preenche a tabela.
    $colunas=0;
    $row = pg_fetch_row ($result, $linhas);
    if ($linhas % 2){ // Verifica se a linha eh par ou impar.
      echo "        </TR>\n";
      echo "        <TR ";
      if ($hideByQuery){
        echo " id=\"" . $row[0] . "\"";
        echo " style=\"display:none\" ";
      }
      echo ">\n  ";
    }
    else{
      echo "        </TR>\n";
      echo "        <TR";
      if ($hideByQuery){
        echo " id=\"" . $row[0] . "\"";
        echo " style=\"display:none\" ";
      }
      echo ">\n  ";
    }
    while ($colunas<$totalDeCampos){ // Laco das colunas
      //echo pg_fieldtype($result, $colunas);
      if (!in_array($colunas, $columnsToHide)){
	  //echo "<script> console.log('" . pg_field_type($result, $colunas) . "');\n</script>";
	  echo "</TD>\n";	
	// if (pg_field_type($result, $colunas)=="text" && !$formata[$colunas]){
        //   echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">\n          ";
        //   $fileArray = formsDecodeFile(base64_decode($row[$colunas]));
	//   if (strpos("_" . $fileArray['type'], "image"))
	//     echo  "<img src=\"data:" . $fileArray['type'] . ";base64, " . base64_encode($fileArray['contents']) . "\">";
	//   else
	//     echo $row[$colunas];
	//   echo "</TD>\n";
	// }else	  
	if (pg_field_type($result, $colunas)=="bytea"){
          echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">\n          ";
	  $base64Query = "SELECT encode('" . $row[$colunas] . "', 'base64')";
	  $base64Result = pg_exec($conn, $base64Query);
	  //echo "\$base64Result: " . intval($base64Result) . "\n";
	  //echo "Last error: " . pg_last_error() . "\n";	  
	  $base64Row = pg_fetch_row($base64Result, 0);
	  //var_dump(formsDecodeFile(base64_decode($base64Row[0])));
	  $fileArray = formsDecodeFile(base64_decode($base64Row[0]));
	  if (strpos("_" . $fileArray['type'], "image"))
	    echo  "<img src=\"data:" . $fileArray['type'] . ";base64, " . base64_encode($fileArray['contents']) . "\">";
	  else
	    echo "&nbsp;";
	  echo "</TD>\n";
	}else	  
	if (pg_field_type($result, $colunas)=="int8" ||
	    $row[$colunas] ||
	    pg_field_type($result, $colunas)=="int4" ||
	    pg_field_type($result, $colunas)=="float4"||
	    pg_field_type($result, $colunas)=="float8"||
	    pg_field_type($result, $colunas)=="numeric"){

	  if ( (pg_field_type($result, $colunas)=="int8" ||
	    pg_field_type($result, $colunas)=="int4" ||
	    pg_field_type($result, $colunas)=="float4"||
	    pg_field_type($result, $colunas)=="float8"||
		pg_field_type($result, $colunas)=="numeric") &&  
	       (isset($appendTotalRow) && $appendTotalRow) )  $totalRow[$colunas] += $row[$colunas];;
	    
	  if (pg_field_type($result, $colunas)=="numeric" ||
	    pg_field_type($result, $colunas)=="float4"||
	    pg_field_type($result, $colunas)=="float8"){

	    if ( (pg_field_type($result, $colunas)=="float4"||
		  pg_field_type($result, $colunas)=="float8") &&
		!(strpos($row[$colunas], ".")) ){
              if ($row[$colunas]=='') $row[$colunas]= "0";
	      $row[$colunas] .= ".00";
	    }
            if (strpos($row[$colunas], ".")){
              $workNumber = explode(".", $row[$colunas]);
              if ($formata[$colunas]=='$'){
                $row[$colunas] = "R\$ " . number_format($row[$colunas], 2, ",", ".");
	      }
              else{                
                $workNumber[1] = mb_substr($workNumber[1], 0, (isset($formata[$colunas]) ? $formata[$colunas] : ($_debug ? 11 : 1)), $encoding);
                $row[$colunas] = $workNumber[0] . "," . $workNumber[1];
	      }
	    }              
	  }
	  if (!$row[$colunas]) $row[$colunas]=0;//Se nulo troca por ZERO
	  if ($references[$colunas]){
	    echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">\n          ";
	    dbcombo($references[$colunas]['table'], 
                    $references[$colunas]['key'],
                    $references[$colunas]['value'], $conn, 
		    pg_fieldname($result, $colunas) .  "[" . $row[0] . "]", 
		    40, nl2br(htmlentities($row[$colunas])), $submit);
	    echo "          </TD>\n";	    
	  }
	  else{
	    if (pg_field_type($result, $colunas)=="bool")
	      if ($boolean[$colunas])
		if ($row[$colunas]=="t"){
  	          if (isset($appendTotalRow) && $appendTotalRow)  $totalRow[$colunas] += 1;		  
		  echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
		  echo $boolean[$colunas][1] . "</TD>\n";
		}
		else{
		  echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
		  echo $boolean[$colunas][0] . "</TD>\n";           
		}
	      else
		if ($row[$colunas]=="t"){
		  echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
		  echo "<CENTER><INPUT TYPE=\"checkbox\" DISABLED name=\"";
		  echo pg_fieldname($result, $colunas);
		  echo "[" . $row[0] ."]\" CHECKED></CENTER></TD>\n";
		}
		else{
		  echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
		  echo "<CENTER><INPUT TYPE=\"checkbox\" DISABLED name=\"";
		  echo pg_fieldname($result, $colunas);
		  echo "[" . $row[0] . "]\">";
                  echo "</CENTER></TD>\n";	    	      
		}
	    else		
	      if (pg_field_type($result, $colunas)=="int4" || 
		  pg_field_type($result, $colunas)=="float4" ||
		  pg_field_type($result, $colunas)=="int8" ||
		  pg_field_type($result, $colunas)=="numeric" ){
		$numero = $row[$colunas];		
		if ($formata[$colunas]){
		  while((strlen($numero))<$formata[$colunas]){
		    $numero = "0" . $numero;
		  }
		}
		echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\"><CENTER>";
                if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "<B>";
		echo $numero;
                if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "</B>";
                echo "</CENTER></TD>\n  ";	    
	      }
	      else // GAMBIARRA, isso deve ser feito no select
		if (((pg_fieldtype($result, $colunas)=="text")
		     ||(pg_fieldtype($result, $colunas)=="varchar"))
		    && ($formata[$colunas])){
		  echo "    <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "");
                  echo " class=onde VALIGN=\"top\">";	  
		  
                  echo "<SPAN TITLE=\"" . $row[$colunas] . "\">";
                  if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "<B>";

		  if ($formata['url']==$colunas)
		    echo "<A HREF=\"" . $row[$colunas] . "\" target=new>";
                  

  		  $row[$colunas] =  nl2br(htmlentities(mb_substr(tiraQuebrasDeLinha($row[$colunas], NULL), 0, 
								 $formata[$colunas], $encoding), ENT_COMPAT, $encoding));
		  if ($destak)
                    $detak = html_entity_decode($destak, ENT_COMPAT, $encoding);
                    $row[$colunas] = html_entity_decode($row[$colunas], ENT_COMPAT, $encoding);
                    if (is_array($destak))                      
                      foreach($destak as $term){
		        $row[$colunas] = str_ireplace($term, //htmlentities($term, ENT_COMPAT, $encoding),
			    		     "<FONT COLOR=\"#FF0000\"><I><BF>" . $term . "</BF></I></FONT>", 
				  	     $row[$colunas]);		  
		      }
                    else
		      $row[$colunas] = str_ireplace($destak,
			  		   "<FONT COLOR=\"#FF0000\"><I><BF>" . $destak . "</BF></I></FONT>", 
				  	   $row[$colunas]);
		    if (!$link){
		      echo nl2br($row[$colunas]);
		    }
		    else{
		      if (!is_array($link)){
			echo "<A HREF=" . $link . "?codcred=" . $row[1];
			echo "&PHPSESSID=" . $_GET['PHPSESSID'] .">";
			echo nl2br($row[$colunas]) . "</A>";
		      }
		      else{
			if (isset($link['URL'])){
			  echo "<A HREF=" . $link['URL'] . "?" . $row[1];
			  echo "&PHPSESSID=" . $_GET['PHPSESSID'] .">";
			  echo nl2br($row[$colunas]) . "</A>";
			}
			else{
			  if (isset($link[$colunas]['urlbase']) && trim($row[intval($link[$colunas]['urlAppendColumn'])])){                            
			    echo "<A HREF=" . $link[$colunas]['urlbase'];
			    echo $row[intval($link[$colunas]['urlAppendColumn'])];
			    if (!$link[$colunas]['external'])
			      echo "&PHPSESSID=" . $_GET['PHPSESSID'];
			    echo ">";
                            //if ($formata[$colunas])
                            //  echo nl2br(htmlentities(substr(tiraQuebrasDeLinha($row[$colunas]), 0, $formata[$colunas])));
                            //else
			    echo nl2br($row[$colunas]);
			    if (trim($link[$colunas]['iconImage']))
			      echo $link[$colunas]['iconImage'];
			    echo "</A>";
			  }
			  else{
                            echo $row[$colunas];
			  }
			}
		      }
		    }

		  if ($formata['url']==$colunas) echo "</A>";

		  if ((strlen($row[$colunas]))>=$formata[$colunas]){
		    echo "...";
                    if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "</B>";
                    echo "</SPAN></TD>\n  ";
                  }
		}
	    
		else
		  if ((pg_fieldtype($result, $colunas)=="interval")){
		    if (strpos($row[$colunas], ":")){
		      $timeString = "";
		      $plural = "";
		      $singular = "";
		      $lookFor = "";
		    }
		    if (strpos($row[$colunas], "day")){
		      $timeString = "dia";
		      $plural = "s";
		      $singular = "";
		      $lookFor = "day";
		    }
		    if (strpos($row[$colunas], "mon")){
		      $timeString = "";
		      $plural = "meses";
		      $singular = "mês";
		      $lookFor = "mon";
		    }
		    if (strpos($row[$colunas], "year")){
		      $timeString = "ano";
		      $plural = "s";
		      $singular = "";
		      $lookFor = "year";
		    }
                    $MTBR = intval(trim(mb_substr($row[$colunas], 0, strpos($row[$colunas], $lookFor))));
		    if (!$MTBR){
		      if (preg_match('/[0-9][0-9]:[0-9][0-9]:[0-9][0-9]/', $row[$colunas], $matches))
			$MTBR = $matches[0];
		      else
		        $MTBR = $row[$colunas];
		    }
		    echo "<TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">" . $MTBR . " " . $timeString . ($MTBR>1 ? $plural : $singular) . "</TD>";
		}	    
		else // GAMBIARRA, isso tambem deve ser feito no select
		  if ((pg_fieldtype($result, $colunas)=="date" ||
		       pg_fieldtype($result, $colunas)=="timestamptz" ||
		       pg_fieldtype($result, $colunas)=="timestamp")
		      && ($formata[$colunas])){
		    echo "    <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">";
                    if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "<B>";
		    echo date("d/m/Y",strtotime($row[$colunas]));
                    if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "</B>";
		    echo "</TD>\n  ";
		  }
		  else{
                    echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\">";
                    if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "<B>";

		    if (!$link){
		      echo $row[$colunas];
		      //echo nl2br($row[$colunas]);
		    }
		    else{
		      if (!is_array($link)){
			echo "<A HREF=" . $link . "?codcred=" . $row[1];
			echo "&PHPSESSID=" . $_GET['PHPSESSID'] .">";
			echo nl2br($row[$colunas]) . "</A>";
		      }
		      else{
			if (isset($link['URL'])){
			  echo "<A HREF=" . $link['URL'] . "?" . $row[1];
			  echo "&PHPSESSID=" . $_GET['PHPSESSID'] .">";
			  echo nl2br($row[$colunas]) . "</A>";
			}
			else{
			  if (isset($link[$colunas]['urlbase']) && trim($row[intval($link[$colunas]['urlAppendColumn'])])){                            
			    echo "<A HREF=" . $link[$colunas]['urlbase'];
			    echo $row[intval($link[$colunas]['urlAppendColumn'])];
			    if (!$link[$colunas]['external'])
			      echo "&PHPSESSID=" . $_GET['PHPSESSID'];
			    echo ">";
                            //if ($formata[$colunas])
                            //  echo nl2br(htmlentities(substr(tiraQuebrasDeLinha($row[$colunas]), 0, $formata[$colunas])));
                            //else
			      echo nl2br($row[$colunas]);
			    if (trim($link[$colunas]['iconImage']))
			      echo $link[$colunas]['iconImage'];
			    echo "</A>";
			  }
			  else{
 		            echo nl2br($row[$colunas]);
			  }
			}
		      }
		    }
                    if ($row[$boldCondition['column']]==$boldCondition['value'] && isset($boldCondition)) echo "</B>";
                    echo "</TD>\n  ";
		  }
                
	  }
	}
	else // Caso o campo esteja vazio, preenche a tabela com um espaco
	  echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde  valign=\"TOP\">&nbsp;</TD>\n  ";
      }
      
      $colunas++;
    }
    if ($form['delete']){
      echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
      echo "<INPUT TYPE=\"checkbox\" name=\"DeleteCheckBox[" . $row[0] ."]\"";
      echo "id = delete_" . $linhas ." ";      
      if (pg_field_type($result,intval($form['deleteCondition']))=="bool"){
        if ($form['deleteCondition'] && $row[intval($form['deleteCondition'])]=="t") echo " DISABLED ";
      }
      else
        if ($form['deleteCondition'] && $row[intval($form['deleteCondition'])]) echo " DISABLED ";
      echo ">";
      echo "</TD>\n";
    }
    if ($form['duplicar']){
      echo "        <TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde VALIGN=\"TOP\" ALIGN=\"CENTER\">";
      echo "<INPUT TYPE=\"checkbox\" name=\"CloneCheckBox[" . $row[0] ."]\">";
      echo "</TD>\n";
    }
    if ($form['field']){
      echo "<TD " . ($row[$formata['corDeFundo']] ? " bgcolor=\"" . $row[$formata['corDeFundo']] . "\" ": "") . " class=onde><INPUT TYPE=\"SUBMIT\" CLASS=\"tablesubmit\"";
      echo " id=\"buttonrow_" . $row[0] . "\" ";
      echo " NAME=\"buttonrow[" . $row[0] . "]\"";
      if(!$form['buttonCaption']) $form['buttonCaption']="Detalhes...";
      echo " value=\"" . $form['buttonCaption'] . "\"></TD>\n";
    }
    $linhas++;
  }
  echo "         </TR>";
  if (isset($appendTotalRow) && $appendTotalRow){
    echo "<TR>";
    for ($colunas = 0; $colunas < $totalDeCampos; $colunas++){
      
      if (isset($appendTotalRow[$colunas]) && trim($appendTotalRow[$colunas])) {
        echo "<TD class=onde>";
	
        //echo "\$coluna: " . $colunas . "<BR>\n";
        //echo "pf_field_type: " . pg_field_type($result, $colunas) . "<BR>\n";	
	
	echo $appendTotalRow[$colunas];
	echo "</TD>"; // $appendTotalRow -> array com strings para colocar onde não tem soma
      }
      else{
	// caso seja float e não tem o ponto, concatena '.00'
	if ( (pg_field_type($result, $colunas)=="float4"||
	      pg_field_type($result, $colunas)=="float8") &&
	     !(strpos($totalRow[$colunas], ".")) ){
	  // caso seja vazio, coloca '0'
	  if ($totalRow[$colunas]=='') $totalRow[$colunas]= "0";
	  $totalRow[$colunas] .= ".00";
	}
	//echo "PASSEI";  
	if (strpos($totalRow[$colunas], ".")){
	  $workNumber = explode(".", $totalRow[$colunas]);	  
	  if ($formata[$colunas]=='$'){
	    $totalRow[$colunas] = "R\$ " . number_format($totalRow[$colunas], 2, ",", ".");
	  }	  
	  else{                
	    $workNumber[1] = mb_substr($workNumber[1], 0, (isset($formata[$colunas]) ? $formata[$colunas] : 1), $encoding);
	    $totalRow[$colunas] = $workNumber[0] . "," . $workNumber[1];
	  }
	}              
	
	if (!in_array($colunas, $columnsToHide)){    
	  echo "<TD class=onde><CENTER>";
          //echo "\$coluna: " . $colunas . "<BR>\n";
          //echo "pf_field_type: " . pg_field_type($result, $colunas) . "<BR>\n";	
	  
	  echo $totalRow[$colunas];
	  echo "</CENTER></TD>"; //$totalRow -> array com os totais numéricos
	}
	
      }
    }
    if (isset($form)){
      if ($form['delete'])
        echo "<TD class=onde>&nbsp;</TD>";
      if ($form['duplicar'])
        echo "<TD class=onde>&nbsp;</TD>";	  
      echo "<TD class=onde>&nbsp;</TD>";
    }    
    echo "</TR>";
  }
    
  echo "\n      </TABLE>\n";
  
  //      <BR>\n";
  /*  
  echo "</CENTER>";
  echo "<PRE>";
  var_dump($form);
  echo "</PRE>";
  echo "<CENTER>";
  */
  
  // echo "<PRE>\$appendTotalRow:\n";
  // var_dump($appendTotalRow);
  // echo "</PRE>";
  
  if (($form['field'])&&(!$form['delete'])){
    //echo "      </FORM>\n"; // Se descomentar os botoes de baixo para NOVO nao funcionam quando nao pode deletar
  }
  if ($showNum &&  pg_numrows($result)>80 )
    if (strlen($showNum)>1)
      echo "<BR>\n" . pg_numrows($result) . " " . $showNum . "<BR><BR>\n";
    else
      echo "<BR>\nSua consulta resultou em " . pg_numrows($result) . " linhas<BR><BR>\n";
}
///////////////////////////////////////////////////////////////////////////////


/**
 * Preenche um combo-box com o conteudo de uma coluna de
 * uma tabela especifica.
 * @param string $tablename     Nome da tabela do banco de dados
 * @param string $fieldvalue    Campo da tabela para o valor do combo
 * @param string $fieldcaption  Campo para o caption do combo
 * @param int    $conn          Handle de conexao com banco
 * @param string $comboname     Nome do combo para o formulario HTML
 * @param int    $width         Largura do combo
 * @param string $default       Valor default
 * @param int    $submit        Se o combo envia um sinal de submit
 *                              para ao form
 * @param bool   $forceSelect   Forçar SELECT mesmo para listas grandes
 * @param int    $selectLimit   Tamanho limite da lista para usar SELECT
 * @param mixed  $multiple      Permitir multiplas seleções
 *                              Se estiver setado, deve ser um array
 *                              com os valores selecionados 
 * @access public
 */
function dbcombo($tablename, $fieldvalue, $fieldcaption, $conn,
                 $comboname, $width, $default, $submit, $echoCaption,
                 $filter, $desc){//, $forceSelect, $selectLimit, $multiple){
  if (!$fieldvalue && $fieldcaption) $fieldvalue=$fieldcaption;
  if ($fieldvalue && !$fieldcaption) $fieldcaption=$fieldvalue;
  if (!$fieldvalue && !$fieldcaption) exit;
  global $_debug;
  /*Cria uma consulta.*/
  $query  = "SELECT " . $fieldvalue . ", " . $fieldcaption . "\n";
  $query .= "  FROM \"" . $tablename . "\"\n";
  if (trim($filter))
    $query .= "  WHERE " . $filter . "\n";
  $query .= "ORDER BY " . $fieldcaption;
  if ($desc) $query .= " DESC";
  if ($_debug>1){
    echo "<PRE>\n";
    echo $query;
    echo "\n</PRE>\n";
  }
  querycombo($query, $conn, $comboname, $width, $default, $submit, $echoCaption, $forceSelect, $selectLimit, $multiple);
}

/**
 * Preenche um combo-box com a primeira coluna do resultado de uma query.
 * @param string $query         string com a consulta (query) SQL
 * @param int    $conn          Handle de conexao com banco
 * @param string $comboname     Nome do combo para o formulario HTML
 * @param int    $width         Largura do combo
 * @param string $default       Valor default
 * @param int    $onchange      Se o combo envia um sinal de submit
 *                              para ao form
 * @param bool   $forceSelect   Forçar SELECT mesmo para listas grandes
 * @param int    $selectLimit   Tamanho limite da lista para usar SELECT
 * @param mixed  $multiple      Permitir multiplas seleções
 *                              Se estiver setado, deve ser um array
 *                              com os valores selecionados 
 * @access public
 */
function querycombo($query, $conn, $comboname, $width, $default, 
                    $onchange, $echoCaption, $forceSelect, $selectLimit, $multiple){
  global $_debug;
  $result = pg_exec ($conn, $query); // Executa a consulta.
  $total = pg_numrows($result); // Verifica o numero de linhas resultante.
  $linhas=0;
  while ($linhas<$total){
    $comboarray[$linhas] = pg_fetch_row ($result, $linhas);
    $linhas++;
  }
  arraycombo($comboarray, $comboname, $with, $default, $onchange, $echoCaption, $forceSelect, $selectLimit, $multiple);
}

/**
 * Preenche um combo-box com a primeira coluna do do array passado
 * @param array  $comoboarray   Vetor 2 x n com os valores para o combo
 * @param string $comboname     Nome do combo para o formulario HTML
 * @param int    $width         Largura do combo
 * @param string $default       Valor default
 * @param int    $onchange      Se o combo envia um sinal de submit
 *                              para ao form
 * @param bool   $forceSelect   Forçar SELECT mesmo para listas grandes
 * @param int    $selectLimit   Tamanho limite da lista para usar SELECT
 * @param mixed  $multiple      Permitir multiplas seleções
 *                              Se estiver setado, deve ser um array
 *                              com os valores selecionados 
 * @access public
 * Exemplo: 
  //echo ":&nbsp;<INPUT TYPE=\"TEXT\" CLASS=\"TEXT\" NAME=\"tag[" . $key;
  //echo "][campo] SIZE=\"40\" MAXLENGTH=\"100\"\n";
  //echo "      VALUE=\"" . $resultadoConsulta['nome'] . "\"><BR>\n";
  //SELECT a.attname, t.typname, a.atttypmod
  //FROM pg_attribute as a, pg_type as t
  //WHERE attrelid = 18230 AND // 18230 = artigosglobo
  //  attstattarget<>0 AND t.oid=a.atttypid
  $query  = "SELECT pg_attribute.attname, pg_attribute.attname\n";
  $query .= "  FROM pg_attribute, pg_class\n";
  $query .= "  WHERE attstattarget<>0 AND \n";
  $query .= "        pg_class.oid=pg_attribute.attrelid AND\n";
  $query .= "         pg_class.relname='artigosglobo'\n";
  if ($_debug>1){
    echo "<PRE>\n";
    echo $query;
    echo "\n</PRE>\n";
  }
  $result = pg_exec ($conn, $query); // Executa a consulta.
  $total  = pg_numrows($result); // Verifica o numero de linhas resultante.
  $linhas = 0;
  while ($linhas<$total){
    $comboarray[$linhas] = pg_fetch_row ($result, $linhas);
    $linhas++;
  }
  $comboarray[$linhas][0] = "descritor";
  $comboarray[$linhas][1] = "descritor";
  $comboarray[$linhas + 1][0] = "autor citado";
  $comboarray[$linhas + 1][1] = "autor citado";
  $comboarray[$linhas + 2][0] = "obra citada";
  $comboarray[$linhas + 2][1] = "obra citada";
  arraycombo($comboarray, "tag[" . $key . "][campo]", 10, "selecione", 0);
 */
 function arraycombo($comboarray, $comboname, $with, $default, $onchange, $echoCaption, $forceSelect, $selectLimit, $multiple){
  global $_debug;  

  if ($_debug>1){ echo "<PRE>\n"; var_dump($comboarray); echo "\n</PRE>\n"; }
  
  $total = count($comboarray);// Verifica o numero de linhas resultante.
  if ($_debug>1) echo "<B>total=" . $total . "</B><BR>\n";

  $linhas=0;
  $default_index=0;
  while ($linhas<$total){
    $row = $comboarray[$linhas];
    if ($_debug>1){
      echo "<PRE>\n";
      var_dump($row);
      echo "\n</PRE>\n";
    }
    if ($default==$row[1]) $default_index = $linhas + 1;
    $linhas++;
  }
  if ($echoCaption && $default_index){
    echo "&nbsp;";
    if ($echoCaption['URL']) echo "<A HREF=" . $echoCaption['URL'] . $comboarray[$default_index-1][0] . ">";
    echo $default;
    if ($echoCaption['URL']) echo "</A>";
    echo "<BR>\n";
    echo"         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  }
  if ($_debug>1) echo "<B>default=" . htmlentities($default) . "</B><BR>\n";

  if (count($comboarray)> ($selectLimit ? $selectLimit : 7)  || $forceSelect){
    $linhas=0;
    if ($onchange){
      if ($onchange==1){
        echo "<SELECT NAME=\"" . $comboname . "\" ID=\"" . $comboname . "\" SIZE=\"1\"";
        echo " onchange=\"submit()\" ";
      }
      else{
        echo "<SELECT NAME=\"" . $comboname . "\" ID=\"" . $comboname . "\" SIZE=\"1\"";
        echo " onchange=\"" . $onchange . "\" ";
      }
    }
    else // 
      echo"<SELECT class=\"chosen-select\" NAME=\"" . $comboname . "\" ID=\"" . $comboname . "\" SIZE=\"1\" ";

    echo " " .  ((isset($multiple) && $multiple) ? "CHECKBOX" : "radio") . " >\n";

    
    if (!$default_index){
      echo "            <OPTION SELECTED VALUE=\"" . $default;
      echo "\">" . $default . "</OPTION>\n" ;
    }
    while ($linhas<$total){//Preenche o combo com o resultado da consulta.
      $row = $comboarray[$linhas];
      if (($default_index) && ($default_index == ($linhas + 1))){
        echo "            <OPTION SELECTED VALUE=\"" . $row[0] . "\">";
        echo $row[1] . "</OPTION>\n";
      }
      else{
        echo "            <OPTION VALUE=\"" . $row[0] . "\">";
        echo $row[1] . "</OPTION>\n";
      }
      $linhas++; 
    }
    echo "          </SELECT>\n";
  }
  else{
    $linhas = 0;
    while ($linhas<$total){//Preenche o combo com o resultado da consulta.
      $row = $comboarray[$linhas];      
      echo "<input type=\"" . ((isset($multiple) && $multiple) ? "CHECKBOX" : "radio") . "\" name=\"" . $comboname . "\" ID=\"" . $comboname . "\" value=\"" . $row[0] . "\"";
      if (($default_index) && ($default_index == ($linhas + 1)))
	echo " CHECKED ";
      else
        if (isset($multiple) && in_array($linhas + 1, $multiple))  echo " CHECKED ";	
      if ($onchange)
        if ($onchange==1)
        echo " onchange=\"submit()\" ";
      else
        echo " onchange=\"" . $onchange . "\" ";
      echo ">" . $row[1];
      if ($linhas < $total - 1){
        echo "<br>\n";
        echo"         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
      }
      $linhas++;
    }
  }   
}

/**
 * Preenche uma "radio list" com o conteudo de uma coluna de uma tabela
 * especifica.
 * @param string $tablename     Nome da tabela do banco de dados
 * @param string $fieldvalue    Campo da tabela para o valor do combo
 * @param string $fieldcaption  Campo para o caption do combo
 * @param int    $conn          Handle de conexao com banco
 * @param string $radioname     Nome do radio para o formulario HTML
 * @param string $default       Valor default
 */
function dbradio($tablename, $fieldvalue, $fieldcaption, $conn,
                 $radioname, $default){
  global $_debug;
  if ($_debug>1){
    echo "<PRE>\n";
    echo "  <B>tableName:</B> " . $tablename . "\n";
    echo "  <B>fieldvalue:</B> " .$fieldvalue  . "\n";
    echo "  <B>fieldcaption:</B> " . $fieldcaption . "\n";
    echo "  <B>conn:</B> " .$conn  . "\n";
    echo "  <B>radioName:</B> " . $checkListName . "\n";
    echo "  <B>default:</B> " . $default . "\n";
    echo "</PRE>\n";
  }
  if (!$fieldvalue && $fieldcaption) $fieldvalue=$fieldcaption;
  if ($fieldvalue && !$fieldcaption) $fieldcaption=$fieldvalue;
  if (!$fieldvalue && !$fieldcaption) exit;
  /*Cria uma consulta.*/
  $query  = "SELECT " . $fieldvalue . " , " . $fieldcaption . "\n";
  $query .= "  FROM " . $tablename;
  queryradio($query, $conn, $radioname, $default);
}

/**
 * Preenche uma "radio-list" com a primeira coluna do resultado
 * de uma query.
 * @param string $query         string com a consulta (query) SQL
 * @param int    $conn          Handle de conexao com banco
 * @param string $radioname     Nome do radio para o formulario HTML
 * @param int    $width         Largura do combo
 * @param string $default       Valor default
 */
function queryradio($query, $conn, $radioname, $default){
  global $_debug;
  if ($_debug>1) echo "<PRE>1:\n" . $query . "</PRE>";
  $result = pg_exec ($conn, $query); // Executa a consulta.
  $total = pg_numrows($result);// Verifica o numero de linhas resultante.
  $linhas=0;
  while ($linhas<$total){// Preenche o combo com o resultado da consulta.
    $row = pg_fetch_row ($result, $linhas);
    echo"         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "        <INPUT TYPE=\"RADIO\" ";
    if ($row[1]==$default) echo "CHECKED";
    echo " NAME=\"" . $radioname . "\" VALUE=\"" . $row[0] . "\">";
    echo $row[1] . "<BR>\n";
    $linhas++; 
  }
}

/**
 * Preenche uma "check-list" com o conteudo de uma coluna de uma tabela
 * especifica.
 * @param string $tablename     Nome da tabela do banco de dados
 * @param string $fieldvalue    Campo da tabela para o valor do combo
 * @param string $fieldcaption  Campo para o caption do combo
 * @param int    $conn          Handle de conexao com banco
 * @param string $radioname     Nome do radio para o formulario HTML
 * @param string $default       Valor default
 */
function dbCheckList($tablename, $fieldvalue, $fieldcaption, $conn,
                     $checkListName, $default){
  global $_debug;
  if ($_debug>1){
    echo "<PRE>\n";
    echo "  <B>tableName:</B> " . $tablename . "\n";
    echo "  <B>fieldvalue:</B> " .$fieldvalue  . "\n";
    echo "  <B>fieldcaption:</B> " . $fieldcaption . "\n";
    echo "  <B>conn:</B> " .$conn  . "\n";
    echo "  <B>checkListName:</B> " . $checkListName . "\n";
    echo "  <B>default:</B> " . $default . "\n";
    echo "</PRE>\n";
  }
  if (!$fieldvalue && $fieldcaption) $fieldvalue=$fieldcaption;
  if ($fieldvalue && !$fieldcaption) $fieldcaption=$fieldvalue;
  if (!$fieldvalue && !$fieldcaption) exit;
  /*Cria uma consulta.*/
  $query  = "SELECT " . $fieldvalue . ", " . $fieldcaption;
  $query .= ", " . $default . "\n";
  $query .= "  FROM " . $tablename;
  queryCheckList($query, $conn, $checkListName, $default, 0, NULL);
}

/**
 * Preenche uma "check-list" com a primeira coluna do resultado
 * de uma query.
 * @param string $query         string com a consulta (query) SQL
 * @param int    $conn          Handle de conexao com banco
 * @param string $radioname     Nome do radio para o formulario HTML
 * @param int    $width         Largura do combo
 * @param string $default       Valor default
 */
function queryCheckList($query, $conn, $checkListName, $default, 
                        $noBreakLine, $onClick){
  global $_debug;
  if ($_debug>1) echo "<PRE>1:\n" . $query . "</PRE>";
  $result = pg_exec ($conn, $query); // Executa a consulta.
  $total = pg_numrows($result);// Verifica o numero de linhas resultante.
  $linhas = 0;
  while ($linhas<$total){// Preenche o combo com o resultado da consulta.
    $row = pg_fetch_row ($result, $linhas);
    echo"         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
    echo "        <INPUT TYPE=\"CHECKBOX\" ";
    if ($row[1]==$default) echo "CHECKED"; //Compatibilidade reversa
    if ($row[2] && $default!="unchecked") echo "CHECKED";
    echo " NAME=\"" . $checkListName . "[" . $linhas . "]";
    echo "\" VALUE=\"" . $row[0] . "\"";
    if ($onClick){
      echo " onClick=\"";
      $consulta  = $onClick['consulta'] . $onClick['delimiter'];
      $consulta .= $row[0] . $onClick['delimiter']; // Executa a consulta.
      if ($_debug>1) echo "<PRE>Consulta:\n" . $consulta . "</PRE>\n";
      $result2 = pg_exec ($conn, $consulta);
      $total2 = pg_numrows($result2);// Verifica o numero de linhas resultante.
      $linhas2=0;
      while ($linhas2<$total2){// Preenche o combo com o resultado da consulta.
        $row2 = pg_fetch_row ($result2, $linhas2);
        echo "toggleMenuTabela('" . $row2[0] . "');";
        $linhas2++;
      }
      echo "\"";
    }
    echo ">";
    echo $row[1];
    if ($_debug>1) echo "<B>noBreakLine: " . $noBreakLine . "</B><BR>\n";
    if (!$noBreakLine) echo "<BR>\n";
    $linhas++; 
  }
}

/**
 * Mostra uma lista com detalhes identados.
 * @param string $tablename     Nome da tabela do banco de dados
 * @param string $titulo        Titulo da lista
 * @param boolan $detalhes      Se mostra detalhes
 * @param int    $conn          Handle de conexao com banco
 */
function dbListaDetalhada($tablename, $titulo, $detalhes, $conn){
  if (!$titulo || !$detalhes) exit;
  /*Cria uma consulta.*/
  $query = "select " . $titulo. " , " . $detalhes . " from " . $tablename;
  queryListaDetalhada($conn, $query);
}

/**
 * Mostra uma lista com detalhes identados para uma query especifica.
 * @param string $conn Handle da conexao com o banco.
 * @param string $query Query de consulta ao banco.
 */
function queryListaDetalhada($conn, $query){
  $result = pg_exec ($conn, $query);// Executa a consulta.
  $total = pg_numrows($result);// Verifica o numero de linhas resultante.
  $linhas=0;
  while ($linhas<$total){// Preenche o combo com o resultado da consulta.
    $row = pg_fetch_row ($result, $linhas);
    echo "        <div id=\"destacado\">" . $row[0] . "</div>\n";
    echo "        <BLOCKQUOTE>\n";
    echo "        " . $row[1] . "\n";
    echo "        </BLOCKQUOTE>\n";
    $linhas++; 
  }
}

/**
 * Forca uma solicitacao de autenticacao do usuario.
 * ALERTA DE GAMBIARRA!!!
 * (muito gambiarrada - nao uso mais, estah aqui apenas pra compatibilidade)
 */
function  authenticate() {
  Header( "WWW-authenticate:  basic  realm='Test  Authentication  System'");
  Header( "HTTP/1.0  401  Unauthorized");
  echo "You  must  enter  a  valid  login  ID  and  password  to";
  echo "  access  this  resource\n";
  exit;
}
/**
 * Mostra um aviso de alerta ao usuario!
 * @param string $Messagem Messagem a ser mostrada no alerta.
 */
function Warning($Messagem){
  global $imagePath;
  global $ehXML;
  if (!$ehXML){
?>
    <TABLE WIDTH="540" CLASS="warning">
      <TR><TH class=onde ><B>Alerta!</B></TH></TR>
      <TR>
        <TD class=onde ALIGN="LEFT">
<?PHP
      echo "          <IMG SRC=\"" . $imagePath . "/warning.gif\" HEIGHT=61 WIDTH=64";
      echo " ALT=\"/!\ \" ALIGN=\"LEFT\">\n";
?>	  
          <FONT SIZE=2 FACE="Arial, Helvetica">
	   <?php echo nl2br($Messagem) ?><BR>
        </TD>
      </TR>
    </TABLE>
<?PHP
  }
  else{
    echo "/!\ ALERTA!\n";
    echo $Messagem . "\n";
  }
}

/**
 * Mostra o conteudo de uma tabela e, caso $conn seja 0 (zero), mostra
 * o conteudo de uma tabela dbase
 * @param string $tabela     tabela a ser mostrada
 * @param int    $conn       Handle de conexao com banco
 * @param string $orderby    nome do campo para ordenar
 * @param int    $desc       1 para descendente, 0 para ascendente
 * @param array  $formata    vetor com o ou para que o campo seja formatado
 *                           com 9 posicoes
 * @param array  $references vetor, caso tenha algum valor, eh a string com o 
 *                           nome da tabela cujo campo nome deve estar no
 *                           caption e o campo codigo deve estar no valor de
 *                           uma combo box.
 * @param array edit         vetor cujo campo da tabela deve ser um input
 *                           type=text [NAO IMPLEMENTADO]
 * @access public
 */
function show_table($tablename, $conn, $orderby, $desc, $formata, 
                    $references, $form, $boolean, $link){
  global $_debug, $imagePath;
  if ($_debug>1){
    echo "<B>Field: </B>" . $form['field'] . "<BR>\n";
    echo "<B>Action: </B>" . $form['action'] . "<BR>\n";
    echo "<B>Name: </B>" . $form['name'] . "<BR>\n";
    echo "<B>Delete: </B>" . $form['delete'] . "<BR>\n";
  }

  if (!$table) exit; // Abandona caso nenhuma consulta seja passada

  if (!$conn) {
    show_dbase_table($tablename);
  }
  else{
    $query = "SELECT * FROM " . $tablename; /* Cria a consulta */
    show_query($query, $conn, $orderby, $desc, $formata, $references,
               $form, $boolean, $link);
  }
}

/**
 * Mostra as tabelas do banco de dados aberto pela conexao passada como
 * argumento. 
 * @param integer handler da conexcao com o banco.
 */
function show_tables($conn){
  $query = "SELECT tablename FROM pg_tables WHERE tableowner<>'postgres'";
  $result = pg_Exec ($conn, $query);/* Executa a consulta */
  if (!$result) { 
    echo "Erro na execucao da consulta.\n";
    if (!pg_close($conn))
      echo"Conex&atilde;o " . $conn . " n&atilde;o fechou";
    else
      echo"Conex&atilde;o " . $conn . " encerrada com sucesso!!!";
    exit;
  }
  $linhas=0;
  while ($linhas<pg_NumRows($result)){   /* Preenche a tabela.                 */
    $row = pg_fetch_row ($result, $linhas);
    if ($row[0]){
      echo "<H2>" . $row[0] . "</H2>\n";
      show_table($row[0], $conn, 0, 0, 0, 0, 0, 0, 0);
    }
    $linhas++;
  } 
  echo "<BR>\n";
}

/**
 * Funcao para validar um e-mail
 * Funcao retirada do site:
 * http://www.zend.com/zend/spotlight/ev12apr.php
 * @param string $Email Endereco de email a ser testado.
 */
function ValidateMail($email_to_validate) {
  global $_debug, $HTTP_HOST, $email;
  $result = array();

  /* Verifica se o e-mail é valido, por expressão regular */
  
  if (!eregi(
    "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",
    $email_to_validate)){
    $result[0]  = false;
    $result[1]  = "$email_to_validate n&atilde;o possui o formato apropriado, ";
    $result[1] .= "verifique a digita&ccedil;&atilde;o.";
    return $result;
  }

  /* Verifica qual é o servidor de e-mail do endereco fornecido */
  list ( $Username, $Domain ) = split ("@",$email_to_validate);
  if (getmxrr($Domain, $MXHost)){
    $ConnectAddress = $MXHost[0];
  }
  else{
    $ConnectAddress = $Domain;
  }
    
  if ($_debug>1){
    echo"<B>ConnectAddress: " . $ConnectAddress . "</B><BR>\n";
    echo"<B>HTTP_HOST: " . $HTTP_HOST . "</B><BR>\n";
    echo"<B>SERVER[HTTP_HOST]: " . $_SERVER['HTTP_HOST'] . "</B><BR>\n";
  }
  if (!$HTTP_HOST) $HTTP_HOST = $_SERVER['HTTP_HOST'];
  if (!$HTTP_HOST) $HTTP_HOST = "localhost";
  if ($_debug>1) echo"<B>HTTP_HOST: " . $HTTP_HOST . "</B><BR>\n";

  /* Conecta no servidor e verifica o enderco fornecido */
  echo $ConnectAddress;
  $Connect = fsockopen( $ConnectAddress, 25, $errno, $errstr, 300);
  if ($Connect){
    if (ereg("^220", $Out = fgets($Connect, 1024))) {
      fputs ($Connect, "HELO $HTTP_HOST\r\n");
      $Out = fgets ( $Connect, 1024 );
      fputs ($Connect, "MAIL FROM: <" . $email . ">\r\n");
      $From = fgets ( $Connect, 1024 );
      fputs ($Connect, "RCPT TO: <" . $email_to_validate . ">\r\n");
      $To = fgets ($Connect, 1024);
      fputs ($Connect, "QUIT\r\n");
      fclose($Connect);
      if ($_debug>1){
	echo "<PRE>\n";
        echo "HELO $HTTP_HOST\r\n";
        echo $Out . "\n";
        echo "MAIL FROM: &lt;" . $email . "&gt;\r\n";
        echo $From . "\n";
        echo "RCPT TO: &lt;" . $email_to_validate . "&gt;\r\n";
        echo $To . "\n";
        echo "</PRE>\n";
      }
      if (!ereg ("^250", $From) || !ereg ( "^250", $To )) {
        $result[0]  = false;
        $result[1]  = "Servidor de destino deste endere&ccedil;o rejeitou";
        $result[1] .= " o endere&ccedil;o fornecido.";
        return $result;
      }
    } else {
      $result[0]  = false;
      $result[1]  = "O servidor de destino do endere&ccedil;o ";
      $result[1] .= "fornecido n&atilde;o responde.";
      return $result;
    }
  }  else {
    $result[0] = false;
    $result[1] = "Imposs&iacute;vel conectar com o servidor de e-mail!";
    $result[1] = $result[1] . " - " . $errstr;
    return $result;
  }    
  $result[0]=true;
  $result[1]="$email_to_validate parece ser v&aacute;lido.";
  return $result;
} // end of function     


$semana[1]['longo'] = "Segunda";
$semana[2]['longo'] = "Terça";
$semana[3]['longo'] = "Quarta";
$semana[4]['longo'] = "Quinta";
$semana[5]['longo'] = "Sexta";
$semana[6]['longo'] = "Sábado";
$semana[7]['longo'] = "Domingo";

$semana[1]['curto'] = "Seg";
$semana[2]['curto'] = "Ter";
$semana[3]['curto'] = "Qua";
$semana[4]['curto'] = "Qui";
$semana[5]['curto'] = "Sex";
$semana[6]['curto'] = "Sáb";
$semana[7]['curto'] = "Dom";

/**
 * Necessario para as funcoes criadas pelo greschke
 * @author: Guilherme Reschke <reschke@pucrs.br>
 */
$p = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$meses['01'] = "Janeiro";
$meses['02'] = "Fevereiro";
$meses['03'] = "Março";
$meses['04'] = "Abril";
$meses['05'] = "Maio";
$meses['06'] = "Junho";
$meses['07'] = "Julho";
$meses['08'] = "Agosto";
$meses['09'] = "Setembro";
$meses['10'] = "Outubro";
$meses['11'] = "Novembro";
$meses['12'] = "Dezembro";

$curso['EC'] = "Engenharia Civil";
$curso['ECP'] = "Engenharia de Computação";
$curso['EE'] = "Engenharia Elétrica";
$curso['EM'] = "Engenharia Mecânica";
$curso['ECA'] = "Engenharia Mecatrônica";
$curso['EP'] = "Engenharia de Produção";
$curso['EQ'] = "Engenharia Química";

/**
 * Calcula media de 10 valores.
 * @author: Guilherme Reschke <reschke@pucrs.br>
 */
function calc_media_individual($nota1,$nota2,$nota3,$nota4,$nota5,$nota6,
                               $nota7,$nota8,$nota9,$nota10){
  global $encoding, $_debug;
  $nota1 = str_replace(",",".",$nota1);
  $nota2 = str_replace(",",".",$nota2);
  $nota3 = str_replace(",",".",$nota3);
  $nota4 = str_replace(",",".",$nota4);
  $nota5 = str_replace(",",".",$nota5);
  $nota6 = str_replace(",",".",$nota6);
  $nota7 = str_replace(",",".",$nota7);
  $nota8 = str_replace(",",".",$nota8);
  $nota9 = str_replace(",",".",$nota9);
  $nota10 = str_replace(",",".",$nota10);
  
  $media = ($nota1+$nota2+$nota3+$nota4+$nota5+$nota6+$nota7+$nota8+
            $nota9+$nota10)/10;
  
  if ($media < 10) $media = mb_substr($media,0,3, $encoding);
  
  return number_format($media,1);
}

/**
 * Calcula media de 4 valores
 * @author: Guilherme Reschke <reschke@pucrs.br>
 */
function calc_media_final($nr_membros,$media1,$media2,$media3,$media4){
  global $encoding, $_debug;

  $media1 = str_replace(",",".",$media1);
  $media2 = str_replace(",",".",$media2);
  $media3 = str_replace(",",".",$media3);
  $media4 = str_replace(",",".",$media4);
  
  $media = ($media1+$media2+$media3+$media4)/$nr_membros;

  if ($media < 10) $media = mb_substr($media,0,3, $encoding);
  
  return $media;
}

/**
 * Mostra uma mensagem adequada ao tipo de saida utilizada.
 * Verifica a saida pela variavel glocal ehXML.
 */
function echoMessage($message){
  global $ehXML;
  if (!$ehXML)    
    echo "      <DIV CLASS=\"message\">" . $message . "</DIV><BR>\n";
  else{
    printTimeStamp(false);
    echo " " . $message . "\n";
  }
}

/**
 * Mostra uma mensagem adequada ao tipo de saida utilizada.
 * Verifica a saida pela variavel glocal ehXML.
 */
function echoTT($message){
  global $ehXML;
  if (!$ehXML)    
    echo "      <PRE>" . $message . "</PRE>\n";
  else
    echo $message . "\n";
}
 
/**
 * Retorna o numero de linhas de um arquivo sem carrega-lo em memoria
 */
function linesCount($fileName){
  $linecount = 0;
  if (file_exists($fileName)){
    $handle = fopen($fileName, "r");
    while(!feof($handle)){
      $line = fgets($handle, 4096);
      $linecount = $linecount + substr_count($line, PHP_EOL);    
    }
    fclose($handle);
    return $linecount;
  }
}


 function tailFile($fileName, $offset){
  $handle = fopen($fileName, 'r');
  if (isset($offset)) {
    $data = stream_get_contents($handle, -1, $offset);
    return $data;
  }
  else {
    fseek($handle, 0, SEEK_END);
  }
  return 0;  
}

 
/**
 * Mostra um valor de um campo a partir de seu relacionamento
 * em uma outra tabela
 * GAMBIARRA - Alterar trecho de codigo que usa isso!
 */
function show_field($conn, $value, $knowfield, $wantedfield, $table){
  global $_debug;
  $query  = "SELECT " . $wantedfield . "\n";
  $query .= "  FROM " . $table . " WHERE \n";
  $query .= "  WHERE " . $knowfield . " = " . $value . "";
  $result = pg_Exec ($conn, $query);
  if ($_debug>1) echo "<PRE>" . $query . "</PRE>";
  $row = pg_fetch_row ($result, 0);
  return $row[0];
}


function showPgFunctionDefinition($conn, $functionName){
  $query  = "  SELECT -- n.nspname as \"Schema\",\n";
  $query .= "  p.proname as \"Name\",\n";
  $query .= "  pg_catalog.pg_get_function_result(p.oid) as \"Result data type\",\n";
  $query .= "  pg_catalog.pg_get_function_arguments(p.oid) as \"Argument data types\",\n";
  $query .= "  '<PRE>'||p.prosrc||'</PRE>',\n";
  $query .= " CASE\n";
  $query .= "  WHEN p.proisagg THEN 'agg'\n";
  $query .= "  WHEN p.proiswindow THEN 'window'\n";
  $query .= "  WHEN p.prorettype = 'pg_catalog.trigger'::pg_catalog.regtype THEN 'trigger'\n";
  $query .= "  ELSE 'normal'\n";
  $query .= " END as \"Type\"\n";
  $query .= "FROM pg_catalog.pg_proc p\n";
  $query .= "     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace\n";
  $query .= "WHERE pg_catalog.pg_function_is_visible(p.oid)\n";
  $query .= "      AND n.nspname <> 'pg_catalog'\n";
  $query .= "      AND n.nspname <> 'information_schema'\n";
  $query .= "and p.proname = '" . $functionName . "'\n";
   // $query .= "and pg_catalog.pg_get_function_arguments(p.oid)='integer, character, character'\n";
  show_query($query, $conn);
}

 
 
/* http://www.appservnetwork.com/modules.php?name=News&file=article&sid=8 */


function xlsBOF() { 
  echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
  return; 
} 

function xlsEOF() { 
  echo pack("ss", 0x0A, 0x00); 
  return; 
} 

function xlsWriteNumber($Row, $Col, $Value) { 
  echo pack("sssss", 0x203, 14, $Row, $Col, 0x0); 
  echo pack("d", $Value); 
  return; 
} 

function xlsWriteLabel($Row, $Col, $Value ) { 
  $L = strlen($Value); 
  echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L); 
  echo $Value; 
return; 
}

function xlsHeader(){
  // Query Database
  $result=mysql_db_query($dbname,"select id,prename,name,sname,grade from appdata where course='$courseid' and sec='$section'");

  // Send Header
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");;
  header("Content-Disposition: attachment;filename=$courseid-$sec.xls ");
  header("Content-Transfer-Encoding: binary ");

  // XLS Data Cell

  xlsBOF(); 
  xlsWriteLabel(1,0,"Student Register $semester/$year");
  xlsWriteLabel(2,0,"COURSENO : ");
  xlsWriteLabel(2,1,"$courseid");
  xlsWriteLabel(3,0,"TITLE : ");
  xlsWriteLabel(3,1,"$title");
  xlsWriteLabel(4,0,"SETION : ");
  xlsWriteLabel(4,1,"$sec");
  xlsWriteLabel(6,0,"NO");
  xlsWriteLabel(6,1,"ID");
  xlsWriteLabel(6,2,"Gender");
  xlsWriteLabel(6,3,"Name");
  xlsWriteLabel(6,4,"Lastname");
  $xlsRow = 7;
  while(list($id,$prename,$name,$sname,$grade)=mysql_fetch_row($result)) {
    ++$i;
    xlsWriteNumber($xlsRow,0,"$i");
    xlsWriteNumber($xlsRow,1,"$id");
    xlsWriteLabel($xlsRow,2,"$prename");
    xlsWriteLabel($xlsRow,3,"$name");
    xlsWriteLabel($xlsRow,4,"$sname");
    $xlsRow++;
  }
  xlsEOF();
  exit();
}

function changeConf($variable, $value){
  global $ehXML, $_debug, $path_to_cp;
  $readingFile = "conf.inc";
  $writingFile = "conf.new.buffer.inc";

  if (file_exists("./include/" . $readingFile)){
    $readingFile = "./include/" . $readingFile;
    $writingFile = "./include/" . $writingFile;
    echo "\n";
  }
  else
  if (file_exists("../include/" . $readingFile)){
    $readingFile = "../include/" . $readingFile;
    $writingFile = "../include/" . $writingFile;
  }
  else{
    echo "Can't find configuration file!\n";
    return 0;
  }
  
   $reading = fopen($readingFile, 'r');
   $writing = fopen($writingFile, 'w');

   $replaced = false;
  
   while (!feof($reading)) {
     $line = fgets($reading);
     //echo $line . "<BR>";
     if (strpos('_' . $line, $variable)) {
       $line = "  " . $variable . " = " . $value . ";\n";
       $replaced = true;
     }
     fputs($writing, $line);
   }
   fclose($reading); 
   // might as well not overwrite the file if we didn't replace anything
   if (fclose($writing) && $replaced){
      $command_to_copy_files = $path_to_cp . " " . $writingFile . " " . $readingFile;
      $error = `$command_to_copy_files`;
      if (!$ehXML) echo "<PRE>\n";
      echo htmlentities($error);
      if (!$ehXML) echo "\n</PRE>\n";
      if (!$ehXML) echo "<DIV class=\"message\">";
      echo "Variável de configuração ";
      if ($_debug) echo "(" . $variable ." = " . $value . ") ";
      echo "alterada com sucesso.\n";
      if (!$ehXML) echo "</DIV>\n";
   }
   else{
     Warning("Erro alterando variável " . ($_debug ? " ( " . $variable . " = " . $value . ") " : "") . "de configuração.");
   }    
 }

/**
 *
 * https://stackoverflow.com/questions/16181551/ical-decoder-function-returning-blank-array-in-php
 */
function icsToArray($paramUrl) {
  $icsFile = file_get_contents($paramUrl);

  $icsData = explode("BEGIN:", $icsFile);

  foreach($icsData as $key => $value) {
    $icsDatesMeta[$key] = explode("\n", $value);
  }

  foreach($icsDatesMeta as $key => $value) {
    foreach($value as $subKey => $subValue) {
      if ($subValue != "") {
	if ($key != 0 && $subKey == 0) {
	  $icsDates[$key]["BEGIN"] = $subValue;
	} else {
	  $subValueArr = explode(":", $subValue, 2);
	  $icsDates[$key][$subValueArr[0]] = $subValueArr[1];
	}
      }
    }
  }

  return $icsDates;
}
?>
