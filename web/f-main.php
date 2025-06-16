<?PHP
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;

//echo $_SERVER[REQUEST_URI];

if (isset($_GET['alvo'])){
  $alvo = intval($_GET['alvo']);
  if ($alvo)
    while (strlen($alvo)<6) $alvo = "0" . $alvo;
 }

$useSessions = 1; $ehXML = 1;
include "iniset.php";
include_once("include/php_backwards_compatibility.inc");
include_once("include/escapeConfVars.inc");
include("conf.inc");escapeConfVars();
if (!stripos("_" . $_theme, "frameless")){
  include "page_header.inc";
}
//include_once("masterFormStartup.inc");

if (!$_debug)
  ini_set ( "error_reporting", "E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR" );
if (isset($_GET['PHPSESSID']))
  $PHPSESSID = $_GET['PHPSESSID'];

$isdeveloper = 0;
reset($developer);
while (list($key, $val) = each($developer)){
  if (isset($_SESSION)){
    if ($developer[$key] == $_SESSION['matricula'])
      $isdeveloper=1;
   }
 }

if (!stripos("_" . $_theme, "frameless")){
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\">\n";
  echo "<HTML>\n";
  echo "  <HEAD>\n";
  echo "  <TITLE>ONDE fLame Work ";
  echo "&nbsp;isdeveloper=" . $isdeveloper; 
  echo "&nbsp;matricula=" . $_SESSION['matricula']; 
  echo "  </TITLE>\n";
  echo "  <META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html;  charset=" . $encoding . "\">\n";
  echo "  </HEAD>\n";
 }

if (stripos("_" . $_theme, "frameless")){

  if ($demanda){
    $formID = SOLICITACOES_MINHAS_DETALHES;
    $codigo = $demanda;
    unset($form);
    //include("masterForm.php");
  }else
    if ($form){
      unset($form);
      include("forms.php");
    }
    else{
      include("inicio.php");
    }
   }
   else{
   ?>
  <FRAMESET COLS="20%,*" BORDER=0 MARGINWIDTH="0" MARGINHEIGHT="0"
            FRAMEBORDER="0" NORESIZE>
    <FRAME NAME="menu" SRC="menu.php?PHPSESSID=<?PHP echo $PHPSESSID;?>"
           BORDER=0 MARGINWIDTH="0" MARGINHEIGHT="0" FRAMEBORDER="0"
           NORESIZE  SCROLLING="auto">
    <FRAME NAME="centro" SRC="<?PHP
  if ($demanda)
    echo "masterForm.php?PHPSESSID=". $PHPSESSID . "&formID=" . SOLICITACOES_MINHAS_DETALHES . "&codigo=" . $demanda;
  if ($form)
    echo "forms.php?PHPSESSID=" . $PHPSESSID . "&form=" . $form . ($alvo ? "#" . $alvo : ""); else echo "inicio.php?PHPSESSID=" . $PHPSESSID;?>" 
           BORDER=0 MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="auto" 
    FRAMEBORDER="0" NORESIZE>
  </FRAMESET>
  <NOFRAMES>
  </NOFRAMES>
<?PHP
  }
?>
