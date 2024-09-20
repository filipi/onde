<?PHP
if($useSessions){
  error_reporting(1);

  if (!isset($PHPSESSID))
    if (isset($_GET['PHPSESSID']))
      $PHPSESSID = $_GET['PHPSESSID'];
    else
      if (isset($_POST['PHPSESSID']))
	$PHPSESSID = $_POST['PHPSESSID'];
      else
	if (isset($_SESSION['PHPSESSID']))
	  $PHPSESSID = $_SESSION['PHPSESSID'];
  //session_save_path("./session_files");

  ini_set('session.save_path',"./session_files");
  //session_save_path('./session_files');
  session_name('onde');
  session_start();


  if (isset($PHPSESSID) && trim($PHPSESSID)){
    $_SESSION['PHPSESSID'] = $PHPSESSID;
  }
  else
    $PHPSESSID = $_SESSION['PHPSESSID'];


  //echo $_SESSION['matricula'] ;
  //echo "PASSEI";
  //exit(0);

  //if(!(session_is_registered("h_log") && 
  //     session_is_registered("matricula") && 
  //     session_is_registered("senha"))){
  if(!($_SESSION['h_log'] && 
       $_SESSION['matricula'])){
    if ($demanda || $form){
      $withoutMenu[] = "f-main.php";
      $withoutMenu[] = "inicio.php";    
      include_once "frm_login.php";
    }
    else{

      $erro_sessao = true;
      include_once "erro_sessao.inc";
    }
    exit(0);
  }
 }
?>
