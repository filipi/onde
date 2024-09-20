<?PHP
if ($isMobile){
  ?>
<SCRIPT LANGUAGE='javascript'>
   var menu_mouse;
</SCRIPT>
<A STYLE="display:block;"
  onclick="document.getElementById('right-wrap').style.display = 'none';"
  onmouseover="menu_mouse = document.body.style.cursor; document.body.style.cursor = 'pointer'; "
  onmouseout="document.body.style.cursor =  menu_mouse;"
  style="text-decoration: none; cursor: pointer; margin: 0px; padding: 0px; border: 0px;  width: 100%; display:block;"
  border="0"
  ><img src="images/Ic_menu_48px<?PHP if(stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo "Glow";?>.png"></A>
<?PHP
}

if (isset($_POST['t']))
  $toggle = $_POST['t'];
else
  if (isset($_GET['t']))
    $toggle = $_GET['t'];
  else
    if (isset($_POST['toggle']))
      $toggle = $_POST['toggle'];
    else
      $toggle = $_GET['toggle'];
?>
    <STYLE type="text/css">
<?PHP
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')){
?>
        A:link {text-decoration: none; color: lightblue}
        A:visited {text-decoration: none; color: lightblue}
        A:active {text-decoration: none; color: lightblue}
        A:hover { text-decoration: none; color: lightblue}
<?PHP
					   }else{?>
        A:link {text-decoration: none; color: #0069B3}
        A:visited {text-decoration: none; color: #0069B3}
        A:active {text-decoration: none; color: #0069B3}
        A:hover { text-decoration: none; color: #0069B3}
		    <?PHP } ?>

td.onde_menu{
  font-size: 10px;
  font-family: "Verdana","Trebuchet MS", Arial, sans-serif;
}


table.calendar {
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
empty-cells: show;
border-collapse: collapse;
margin: 0px;
}

th.calendar {
text-align: left;
font-weight: bold;
font-size: 11pt;
padding-left: 5px;
padding-right: 5px;
background-color: #fff6d5;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
margin: 0px;
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
}

td.calendar {
text-align: center;
font-size: 10pt;
padding-left: 5px;
padding-right: 5px;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
margin: 0px;
background-color: white;
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
}

</STYLE>
    <?PHP
function submenu($menus, $menupai, $toggleString){
  global $PHPSESSID, $menuDivider, $_theme, $closeDIV, $conn, $day, $month, $year;
  //echo "<B>SESSID" . $PHPSESSID . "</B><BR>\n";
 
  foreach ($menus as $key => $menu) {
    if ($menu['Menu pai'] == $menupai) {
      switch ($menu['tipo']) {
      case 6:
	$menu_pai2 = $menu['codigo'];	
        menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de identação'], $menu['codigo']);
	$toggleString2 = $toggleString."&toggle[]=M".$menu_pai2;
	//echo $toggleString2;
        //echo "<PRE>"; var_dump($menu); echo "</PRE>";	
        $queryShares  = $menu['Consulta (SQL)'];
        $resultShares = pg_exec ($conn, $queryShares);
        $shares = pg_fetch_all ($resultShares);	
        foreach($shares as $share){
          menuItem("compartilhamento.php?codigo=" . $share['codigo'] . "&PHPSESSID=" . $PHPSESSID . $toggleString2,
		   mb_substr($share['nome'], 0, 19) . "...", $menu['Nível de identação'] + 1, 0, $share['nome'], NULL);
        }
        echo $closeDIV;
	break;	
      case 5: //Pasta
	$menu_pai2 = $menu['codigo'];
	menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de identação'], $menu['codigo']);
	$toggleString2 = $toggleString."&toggle[]=M".$menu_pai2;
	//echo $toggleString2;
	submenu($menus, $menu_pai2, $toggleString2);
        echo $closeDIV;
	break;
      case 1: //Link
	menuItem($menu['Link'] . "?PHPSESSID="  . $PHPSESSID . $toggleString, $menu['Etiqueta'],
		 $menu['Nível de identação'], false, NULL, $menu['codigo']);
	break;
      case 2: // Formulário
	menuItem("forms.php?form=". $menu['Formulário'] . "&PHPSESSID=" . $PHPSESSID . $toggleString . "&" . $menu['argumentos'],
		 $menu['Etiqueta'], $menu['Nível de identação'], false, NULL, $menu['codigo']);
	break;
      case 4: // Calendário
	//$day = 19; $month = 10; $year = 2018;
        echo "    <div style=\"\" id=\"calendar-container" . fixField($menu['Etiqueta']) . "\"></div>\n";
    ?>
      <script type="text/javascript">
      function dateChanged(calendar) {
      // Beware that this function is called even if the end-user only
      // changed the month/year.  In order to determine if a date was
      // clicked you can use the dateClicked property of the calendar:
	   
      if (calendar.dateClicked) {
	// OK, a date was clicked, redirect to /yyyy/mm/dd/index.php
	var y = calendar.date.getFullYear();
	var m = calendar.date.getMonth();     // integer, 0..11
	var d = calendar.date.getDate();      // integer, 1..31
	m++;
	console.log('passei');
	// redirect...
	//window.location = "/" + y + "/" + m + "/" + d + "/index.php";
<?PHP
  if (stripos("_" . $_theme, "frameless"))
    echo "      window.location = \"" . $menu['Link'] . "?day=\" + d + \"&month=\" + m + \"&year=\" + y + \"&" . $toggleString . "\";\n";  // Sem frames
  else
    echo "      parent.frames['centro'].location.href = \"" . $menu['Link'] . "?day=\" + d + \"&month=\" + m + \"&year=\" + y;\n"; // com frames
?>
	//window.location = "teste.php?data=" + y + m + d;
      }
    };
    Calendar.setup(
		   {
		   flat         : <?PHP echo "\"calendar-container" . fixField($menu['Etiqueta']) . "\""; ?>, // ID of the parent element
      <?PHP if ($day && $month && $year) echo "    date: \"" . $month . "/" . $day . "/" . $year . "\",\n";?>
		   flatCallback : dateChanged,          // our callback function
		   flatTheme        : "winter"
                   }
		   );
    </script>
	<?PHP
      //echo "data: " . $_GET['day'] . "/" . $month . "/". $year . "<BR>";


	echo $menuDivider;	
	break;	
      }
    }
  }
}

?>
    <STYLE type="text/css">
<?PHP
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')){
?>
        A:link {text-decoration: none; color: lightblue}
        A:visited {text-decoration: none; color: lightblue}
        A:active {text-decoration: none; color: lightblue}
        A:hover { text-decoration: none; color: lightblue}
<?PHP
					   }else{?>
        A:link {text-decoration: none; color: #0069B3}
        A:visited {text-decoration: none; color: #0069B3}
        A:active {text-decoration: none; color: #0069B3}
        A:hover { text-decoration: none; color: #0069B3}
		    <?PHP } ?>

td.onde_menu{
  font-size: 10px;
  font-family: "Verdana","Trebuchet MS", Arial, sans-serif;
}


table.calendar {
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
empty-cells: show;
border-collapse: collapse;
margin: 0px;
}

th.calendar {
text-align: left;
font-weight: bold;
font-size: 11pt;
padding-left: 5px;
padding-right: 5px;
background-color: #fff6d5;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
margin: 0px;
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
}

td.calendar {
text-align: center;
font-size: 10pt;
padding-left: 5px;
padding-right: 5px;
border-width: 1px;
border-color: DarkGray;
border-style: solid;
margin: 0px;
background-color: white;
font-family: 'Trebuchet MS' , arial, sans-serif;
color: black;
font-size: 10pt;
}

</STYLE>

<SCRIPT LANGUAGE='javascript'>
function toggleMenu(id1){
  var field1 = document.getElementById(id1);
  if ((field1 == null)){
  }
  else
    if (field1.style.display == "none"){
      field1.style.display = "block";
    }
    else{
      field1.style.display = "none";
    }
}
</SCRIPT>
<DIV ALIGN="left">
  <TABLE WIDTH="100%" HEIGHT="" CELLSPACING=0 CELLPADDING=0 BORDER=0>
  <TR>
  <TD ALIGN="left" VALIGN="top" class="onde_menu">
  <HR SIZE=1 WIDTH="95%">
<?PHP
    // carrega os grupos do usuario logado para carregar os menus que
    // este usuario pode visualizar
    $usuario = $_SESSION['matricula'];
$sql = "SELECT grupo \n";
$sql .= "  FROM usuarios_grupos\n";
$sql .= "  WHERE usuario = '" . $usuario . "' \n";
$sql .= "ORDER BY grupo ASC";
$result = pg_query($conn, $sql);
$result = pg_fetch_all($result);
foreach ($result as $res) {
  $grupos[] = $res['grupo'];
}

//echo "<PRE>" . $sql . "</PRE>";

$grupos = implode(",", $grupos);
//$sql = "SELECT DISTINCT m.*\n";
$sql  = "SELECT DISTINCT m.ordem as realordem, m.ordem, m.codigo, \n";
$sql .= "   m.\"Menu pai\", m.\"Etiqueta\", m.\"contar no fim para o início\", \n";
$sql .= "   m.\"Nível de identação\", m.\"Formulário\", m.\"Link\", m.tipo,  \n";
$sql .= "   m.\"Consulta (SQL)\", m.argumentos\n";
$sql .= "   FROM menus AS m, menus_grupos AS mg\n";
$sql .= "   WHERE mg.grupo IN (" . $grupos . ") \n";
$sql .= "     AND mg.menu = m.codigo\n";
$sql .= "     AND m.\"contar no fim para o início\"  = false\n";
$sql .= "UNION \n";

//$sql .= "SELECT DISTINCT  ( (ordem*-1) + (select count(codigo) from menus) + 1 ) as realordem,\n";
//$sql .= "SELECT DISTINCT  ( m.ordem + (select count(codigo) from menus) + 1 ) * -1 as realordem,\n";
$sql .= "SELECT DISTINCT  ( m.ordem + (select max(ordem) from menus) + 1 ) as realordem,\n";

$sql .= "   m.ordem, m.codigo, \n";
$sql .= "   m.\"Menu pai\", m.\"Etiqueta\", m.\"contar no fim para o início\", \n";
$sql .= "   m.\"Nível de identação\", m.\"Formulário\", m.\"Link\", m.tipo,  \n";
$sql .= "   m.\"Consulta (SQL)\", m.argumentos\n";
$sql .= "   FROM menus AS m, menus_grupos AS mg\n";
$sql .= "   WHERE mg.grupo IN (" . $grupos . ") \n";
$sql .= "     AND mg.menu = m.codigo\n";
$sql .= "     AND m.\"contar no fim para o início\"  = true\n";
$sql .= "\n"; 

 
// $sql = "SELECT DISTINCT m.*\n";
// $sql .= "   FROM menus AS m, menus_grupos AS mg\n";
// $sql .= "   WHERE mg.grupo IN (" . $grupos . ") AND mg.menu = m.codigo\n";
// $sql .= "ORDER BY m.tipo, realordem;";

$sql .= "ORDER BY realordem;";

//echo "PASSEI";
// echo "<PRE>" . $sql . "</PRE>";
 
$result = pg_query($conn, $sql);
$menus = pg_fetch_all($result);
//$debug = 1;
if ($debug) {
  echo "<PRE>";
  print_r($menus);
  echo "</PRE>";
}
$sentido = 'f';
foreach ($menus as $key => $menu) {
  //echo intval($key) . ",";
  //echo $menu['contar no fim para o início'];
  //echo "Sentido: " . $sentido . "<BR>\n";

  if ($menu['contar no fim para o início'] != $sentido && $isdeveloper){  
    menuTogglePoint("hardcoded_developer_menu", "Debug", 0, NULL);
    $runningBots = trim(`ps ax | grep php | grep Bot | grep -v ps`);
     
    if ($runningBots){
      $runningBots = tiraQuebrasDeLinha($runningBots, ";");
      $runningBots = explode(";", $runningBots);
      //echo count($runningBots) . " bots rodando<BR>\n";
      foreach($runningBots as $bot){
	if (strpos($bot, "editaisBot")){
	  $bot = explode("?", $bot);
	  $editaisBotPid = intval(trim($bot[0]));
	}
	if (strpos($bot, "demandasBot")){
	  $bot = explode("?", $bot);
	  $demandasBotPid = intval(trim($bot[0]));
	}
	if (strpos($bot, "camerasBot")){
	  $bot = explode("?", $bot);
	  $camerasBotPid = intval(trim($bot[0]));
	}
      }
    }
    echo $menuMargin;
    if ($editaisBotPid)
      echo "editaisBot OK [" . $editaisBotPid . "]<BR>\n";
    else
      echo "<FONT COLOR=\"#FF0000\">editaisBot parado</FONT><BR>\n";
    echo $menuMargin;
    echo "<a id=\"demandasBotDisplay\" href=\"demandasBot.php?toggle[]=hardcoded_developer_menu\" ";
    if ( !stripos("_" . $_theme, "frameless")) echo "target=\"centro\"";
    echo ">";
    if ($demandasBotPid)
      echo "demandasBot OK[" . $demandasBotPid . "]<BR>\n";
    else
      echo "<FONT COLOR=\"#FF0000\">demandasBot parado</FONT><BR>\n";
    echo "</a>\n";
    echo $menuMargin;
    if ($camerasBotPid)
      echo "camerasBot OK [" . $camerasBotPid . "]<BR>\n";
    else
      echo "<FONT COLOR=\"#FF0000\">camerasBot parado</FONT><BR>\n";
    echo $menuDivider;
    menuItem("configura.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Op&ccedil;&otilde;es", 1, false, NULL, NULL);
    menuItem("sessionManager.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Sessões", 1, false, NULL, NULL);
    menuItem("query.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Consulta [SQL]", 1, false, NULL, NULL);
?>
    <A HREF="./cleanUp.php?PHPSESSID=<?PHP echo $PHPSESSID; ?>"
       BORDER=0 TARGET="_parent">
       <B><FONT COLOR='#FF0000'>Limpar sess&otilde;es orf&atilde;s</FONT></B></A>
								   <?PHP
								   echo $menuDivider;

    
    echo $closeDIV;
  }
  $sentido = $menu['contar no fim para o início'];
 
  //echo $sentido;

  if ($menu['tipo'] == 1 && is_null($menu['Menu pai'])) {
    menuItem($menu['Link'] . "?PHPSESSID=" . $PHPSESSID, $menu['Etiqueta'], $menu['Nível de identação'], false, NULL, $menu['codigo']);
  }

  if ($menu['tipo'] == 2 && is_null($menu['Menu pai'])) {
    menuItem("forms.php?PHPSESSID=" . $PHPSESSID . "&form=" . $menu['Formulário'],
	     $menu['Etiqueta'], $menu['Nível de identação'], false, NULL, $menu['codigo']);
  }

  if ($menu['tipo'] == 3 && is_null($menu['Menu pai'])) {
    echo "  <STYLE>\n";
    echo "   input.text {\n";
    if (!stripos("_" . $_theme, 'frameless')){
      if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo '
        border: 1px solid #000;
        background-color : black;
        border-color : lightblue;
        color : lightblue;';
    else
        echo '  border: 1px solid #A3A4B2;';
     }
    echo "    }\n";
    echo "</STYLE>\n";    
    echo "<INPUT style=\"width: 150px;\" TYPE=\"TEXT\" CLASS=\"TEXT\" ";
    echo " NAME=\"nome\" id=\"query\" SIZE=10 ";
    if ($isMobile) echo " STYLE=\"width: 250; height: 50; font-size: 32;\" ";
    echo " MAXLENGTH=80 onkeypress=\"if (window.event.keyCode==13) busca(window.event.keyCode);\" >&nbsp&nbsp&nbsp";
    echo "<a id=\"ancora\"";
    if (!stripos("_" . $_theme, "frameless")) {echo "target=\"centro\"";}
    echo "onClick=\"(busca(0));\"><IMG SRC=\"images/mag-glass_10x10.gif\" BORDER=0 ALT=\"`o\"></a></br>";
    echo $menuDivider;
    ?>
<script>
function busca(teste){
    //alert(teste);
  // document.getElementById("ancora").innerHTML="";
  // setar o attributo href do a
  var ancora = document.getElementById("ancora");
  var query =  document.getElementById("query");
  ancora.href="pesquisa.php?nome=" + query.value;
  if (teste==13) {
    //location.href="pesquisa.php?nome=" + query.value;
    window.open("pesquisa.php?nome=" + query.value, <?PHP
		if ( !stripos("_" . $_theme, "frameless") ) echo "\"centro\""; else echo "\"_top\""; ?>);
  }
  //query.value = "";
  return true;
  }
</script>  
<?PHP

    
  }
  if ($menu['tipo'] == 5 && is_null($menu['Menu pai'])) {
    menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de identação'], $menu['codigo']);
    $menu_pai = $menu['codigo'];
    $toggleString = "&toggle[]=M".$menu_pai;
    submenu($menus, $menu_pai, $toggleString);
    echo $closeDIV;
  }
 
}

 
  /*
 echo $menuDivider;
 foreach ($menus as $key => $menu) {
   if ($menu['tipo'] == 5 && is_null($menu['Menu pai'])) {
     menuTogglePoint($menu['codigo'], $menu['Etiqueta'], $menu['Nível de identação'], $menu['codigo']);
     $menu_pai = $menu['codigo'];
     $toggleString = "&toggle[]=".$menu_pai;
     submenu($menus, $menu_pai, $toggleString);
     echo $closeDIV;
   }
 }
  */
 
 ///////////////////////////////////////////////////////////////////////  adds developer part of the menu.
 /*


 */ 
?>
</TD>
</TR>
</TABLE>
</DIV>
