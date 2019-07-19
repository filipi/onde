<?PHP
include_once("include/php_backwards_compatibility.inc");
include_once("include/escapeConfVars.inc");
include("include/conf.inc");escapeConfVars();
$remoteDomain = "http://remotedomain";
if (isset($_GET['demanda'])) $demanda = intval($_GET['demanda']); else $demanda = 0;
if (isset($_GET['form'])) $form = $form = intval($_GET['form']); else $form = 0;
if ($_theme == "plain" || stripos("_" . $_theme, 'fancy')  || stripos("_" . $_theme, 'tron') || stripos("_" . $_theme, 'frameless')){
  include("frm_login.php");
  exit;
 }
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<HTML>
  <HEAD>
    <TITLE>ONDE</TITLE>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
  </HEAD>
  <FRAMESET COLS="*,810,*" noresize FRAMEBORDER=0 BORDER=0 FRAMESPACING=0
            MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING="No">
    <FRAME NAME="dummy" SRC="<?PHP
if (!$_remoteAssets)
  if ($_theme)
    echo "themeAssets/" . $_theme;
  else
    echo "themeAssets/framelessPlain";    
else
  echo $remoteDomain;?>/system/dummy.htm"
           MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING="No">
    <FRAME NAME="desk" SRC="f-desk.php<?PHP if ($demanda) echo "?demanda=" . $demanda; else if ($form) echo "?form=" . $form; ?>" scrolling="NO" marginwidth="0"
           marginheight="0" frameborder=0>
    <FRAME NAME="dummy" SRC="<?PHP
if (!$_remoteAssets)
  if ($_theme)
    echo "themeAssets/" . $_theme;
  else
    echo "themeAssets/framelessPlain";    
else
  echo $remoteDomain;?>/system/dummy.htm"
           MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING="No">
  </FRAMESET>
</HTML>
