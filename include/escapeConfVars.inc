<?PHP
$deps = array("wget", "inkscape", "convert", "gnuplot", "octave", "tex", "php",
              "dvipng", "dvisvgm", "temp_dir", "javac", "java", "enscript", "rsvg-convert",
              "montage", "sed", "mv", "cp", "ssh", "ypcat", "pngquant", "Slic3r", "grep",
			  "cut", "tr", "python", "mosquitto_pub");

$moduleDeps = array ("Core", "date", "ereg", "libxml", "openssl", "pcre", "zlib", "bz2", "calendar",
		     "ctype", "hash", "filter", "ftp", "gettext", "SPL", "iconv", "pcntl", "readline",
		     "Reflection", "session", "standard", "shmop", "SimpleXML", "sockets", "mbstring",
		     "tokenizer", "xml", "imagick", "curl", "dom", "fileinfo", "gd", "gmp", "json",
		     "exif", "pgsql", "Phar", "posix", "pspell", 
		     "sqlite3", "sysvmsg", "sysvsem", "sysvshm", "wddx", "xmlreader", "xmlwriter", 
		     "xsl", "zip", "mhash");

//sort($deps);

 function escapeConfVars(){
   global $conn, $_integracaoSiteLigada,
     $_integracaoLigada, $_voltaMesma, $trocas_minimas,
     $tamanho_senha, $URL, $email, $fone, $developer, $_debug, $_singleQueue,
     $_remoteAssets, $_theme,
     $verificaEmail, $encoding, $mem_limit, $max_execution_time, $deps, $_menu_from_db,
     $logo_image, $organizationWebSiteURL, $_no_check_certificate,
     $system_mail_from, $system_mail_from_name, $system_mail_host, $system_mail_mailer, $debug_mail_recipient;
     
   if (!isset($conn)) return 0;

  foreach($deps as $keydep => $valdep){
    $dep[$keydep] = trim(str_replace("-", "_", $valdep));
    $variable_name = "path_to_" . $valdep;
    global $$variable_name;
  }

  $_integracaoSiteLigada = intval(trim($_integracaoSiteLigada));

  $_integracaoLigada = intval(trim($_integracaoLigada));
  $_voltaMesma = intval(trim($_voltaMesma));
  $trocas_minimas =  intval(trim($trocas_minimas));
  $tamanho_senha =  intval(trim($tamanho_senha));

  $logo_image = ($logo_image != NULL ? pg_escape_string($conn, trim($logo_image)) : NULL);
  $organizationWebSiteURL = pg_escape_string($conn, trim($organizationWebSiteURL));
  $_no_check_certificate = boolval($_no_check_certificate);
  $system_mail_from = pg_escape_string($conn, trim($system_mail_from));
  $system_mail_from_name = pg_escape_string($conn, trim($system_mail_from_name));
  $system_mail_host = pg_escape_string($conn, trim($system_mail_host));
  $system_mail_mailer = pg_escape_string($conn, trim($system_mail_mailer));
  $debug_mail_recipient = pg_escape_string($conn, trim($debug_mail_recipient));

  $URL =  pg_escape_string($conn, trim($URL));
  $email =  pg_escape_string($conn, trim($email));
  $fone =  pg_escape_string($conn, trim($fone));

  foreach($developer as $key => $val){
    $developer[$key] = pg_escape_string($conn, trim($val));
  }
  
  $_debug = intval(trim($_debug));
  $_singleQueue = intval(trim($_singleQueue));
  $_remoteAssets = intval(trim($_remoteAssets));
  $_menu_from_db = intval(trim($_menu_from_db));  
  $_theme =   pg_escape_string($conn, trim($_theme));  
  $verificaEmail = intval(trim($verificaEmail));
  $encoding =  pg_escape_string($conn, trim($encoding));
  $mem_limit =  intval(trim($mem_limit));
  $max_execution_time =  intval(trim($max_execution_time));

  foreach($deps as $keydep => $valdep){
    $dep[$keydep] = trim(str_replace("-", "_", $valdep));
    $variable_name = "path_to_" . $valdep;
    $$variable_name = ($$variable_name != NULL ? pg_escape_string($conn, trim($$variable_name)) : NULL);
  }
 }
?>