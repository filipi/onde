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

$teste =  "teste \"";
echo "<FORM METHOD=POST>";

echo $_POST['teste'] . "<BR>\n";
echo "<PRE>";
echo stripslashes($_POST['teste']) . "\n";

echo htmlentities($_POST['teste']) . "\n";
echo htmlentities(htmlspecialchars($_POST['teste'], ENT_QUOTES, ISO-8859-1)) . "\n";
echo htmlentities(htmlspecialchars_decode($_POST['teste'], ENT_QUOTES)) . "\n";
echo pg_escape_string($_POST['teste']) . "\n";
echo "</PRE>";

echo "<textarea name=teste ROWS=30 COLS=100>\n";

echo $_POST['teste'] . "\n";

echo htmlspecialchars_decode($_POST['teste'], ENT_QUOTES) . "\n";
echo htmlspecialchars($_POST['teste'], ENT_QUOTES, ISO-8859-1) . "\n";
echo pg_escape_string($_POST['teste']) . "\n";

echo "</textarea><BR>\n";

echo "<INPUT TYPE=SUBMIT><BR>";

?>
<input type="text" name="teste2" id="f_date_teste2" value=""><button type="reset" id="f_trigger_teste2; ?>">...</button><script type="text/javascript">
   Calendar.setup({
     inputField     :    "f_date_teste2",      // id of the input field
	 ifFormat       :    "%d/%m/%Y",       // format of the input field
	 showsTime      :    false,            // will display a time selector
	 button         :    "f_trigger_teste2",   // trigger for the calendar (button ID)
	 singleClick    :    false,           // double-click mode
	 step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	 });
</script><BR><BR>
<?PHP

echo "</FORM>";
?>




<?PHP
include "page_footer.inc";
?>
