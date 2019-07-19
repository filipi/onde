<?PHP
 /**
  * $Id: sobre.php,v 1.30 2018/06/05 15:34:26 filipi Exp $
  */
  //$headerTitle = "Sobre";
$useSessions = 1; $ehXML = 0;

if ($useSessions){
  ini_set('session.save_path',"./session_files");
  session_name('onde');
  session_start();
  if(!(isset($_SESSION['h_log']) && 
       isset($_SESSION['matricula']))){
    $useSessions = 0;
    session_destroy();
    $withoutMenu[] = "sobre.php";
  }
  else
    $useSessions = 1; 
}
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
echo "<BR>\n";
?>
<CENTER>
<H1><?PHP echo $SYSTEM_NAME; ?> <?PHP echo $SYSTEM_VERSION; ?></H1>
<DIV class=coment>
  Desenvolvido por Gustavo Leal<BR>
  de Janeiro de 2018 à Junho de 2018 (5 meses)<?PHP /*<img src = "images/cake.png">*/ ?>
  <BR>
  e por Filipi Vianna<BR>
  de Fevereiro de 2008 à Junho de 2018 (10 anos)<?PHP /*<img src = "images/cake.png">*/ ?>
</DIV>
<BR>
  O <?PHP echo $SYSTEM_NAME; ?> utiliza como base a <I>fLameWork</I> - O.N.D.E.<BR>
<DIV class=coment>O.N.D.E.: ONDE Não é Desenvolvida por Experts<BR>
<BR>
Versão da <I>fLameWork</I> - O.N.D.E. <B><?PHP echo $ONDE_VERSION; ?></B><BR>
de Janeiro de 2003 à Junho de 2018 (15 anos)<BR><BR>
Contribuiram para a criação desta <I>fLameWork</I><BR>
Bruno Cortopassi Trindade (<I>documentação</I>)<BR>
Felipe Schiefferdecker Karpouzas (<I>documentação</I>)<BR>
Henrique Damasceno Vianna (<I>mecanismos para prevenir SQL injection</I>)<BR>
Guilherme Reschke (<I>mecanismo de login e geração de PDFs</I>)<BR>
Marcelo Rodrigues Schmitz<BR>
Gustavo Henrique Leal<BR>
Filipi Damasceno Vianna</DIV>
</CENTER>
<BR>
<?PHP
  //Versão do Banco de Dados - <B><?PHP echo $DATABASE_VERSION; </B>
include "page_footer.inc";
?>
