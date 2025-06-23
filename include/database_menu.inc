<?PHP
    //<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    //<link href="themeAssets/framelessbootstrap/css/styleMenu.css" rel="stylesheet">
    //<link href="themeAssets/framelessbootstrap/css/bootstrap.css" rel="stylesheet">
    //<script type="text/javascript" src="themeAssets/framelessbootstrap/js/jquery-1.8.min.js"></script>
    //<script type="text/javascript" src="themeAssets/framelessbootstrap/js/bootstrap.js"></script>
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
//else{
// Caso não seja mobile, 

//}

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
        A:link {text-decoration: none; color: }
        A:visited {text-decoration: none; color: #ACD6F3}
        A:active {text-decoration: none; color: #ACD6F3}
        A:hover { text-decoration: none; color: #ACD6F3}
<?PHP
					   }else{?>
        A:link {text-decoration: none; color: #0069B3}
        A:visited {text-decoration: none; color: #0069B3}
        A:active {text-decoration: none; color: #0069B3}
        A:hover { text-decoration: none; color: #0069B3}
		    <?PHP }?>
.menu_item{
	  
	  line-height: 3.2em;
	  border: 1px solid #666;
	  background-image: linear-gradient( to bottom, #fff, #ccc );
				     font-family: arial, sans-serif;
				     font-size: 1.2em;				     
				     }

  .menu_item:hover {
    border-color: #3c7fb1;
	  background-image: linear-gradient( to top, #fff, #ccc );
				     font-family: arial, sans-serif;
				       }

    .menu_item:focus {
    padding: 0  0.56em 0 0.68em;
    }


table.calendar {

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
  global $encoding, $PHPSESSID, $menuDivider, $_theme, $closeDIV, $conn, $day, $month, $year, $toggleString2;
  //echo "<B>SESSID" . $PHPSESSID . "</B><BR>\n";

  foreach ($menus as $key => $menu) {
    if ($menu['Menu pai'] == $menupai) {
      switch ($menu['tipo']) {
      case 6:
	$menu_pai2 = $menu['codigo'];	
        menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de indentação'], $menu['codigo']);
	$toggleString2 = $toggleString."&toggle[]=M".$menu_pai2;

        //echo "<script> console.log('\$toggleString2: " . $toggleString2 . "'); </script>\n"; 

	//echo $toggleString2;
        //echo "<PRE>"; var_dump($menu); echo "</PRE>";	
        $queryShares  = $menu['Consulta (SQL)'];
        $resultShares = pg_exec ($conn, $queryShares);
        $shares = pg_fetch_all ($resultShares);	
	
        foreach($shares as $share){
          menuItem("compartilhamento.php?codigo=" . $share['codigo'] . "&PHPSESSID=" . $PHPSESSID . $toggleString2 . ((isset($share['tabela']) && $share['tabela'])?"&d=" . urlencode($share['tabela']):""),
		   mb_substr($share['nome'], 0, 19, $encoding) . "...", $menu['Nível de indentação'] + 1, 0, $share['nome'], NULL);
        }
        echo $closeDIV;
	break;	
      case 5: //Pasta
	$menu_pai2 = $menu['codigo'];
	menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de indentação'], $menu['codigo']);
	$toggleString2 = $toggleString."&toggle[]=M".$menu_pai2;
        //echo "<script> console.log('\$toggleString2: " . $toggleString2 . "'); </script>\n"; 

	//echo $toggleString2;
	submenu($menus, $menu_pai2, $toggleString2);
        echo $closeDIV;
	break;
      case 1: //Link
        //echo "____" . intval(strpos($menu['Link'], "?"));
	menuItem($menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?") . "PHPSESSID="  . $PHPSESSID . $toggleString, $menu['Etiqueta'],
		 $menu['Nível de indentação'], false, NULL, $menu['codigo']);
	break;
      case 2: // Formulário
	menuItem("forms.php?form=". $menu['Formulário'] . "&PHPSESSID=" . $PHPSESSID . $toggleString . "&" . $menu['argumentos'],
		 $menu['Etiqueta'], $menu['Nível de indentação'], false, NULL, $menu['codigo']);
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
    echo "      window.location = \"" . $menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?") .  "day=\" + d + \"&month=\" + m + \"&year=\" + y + \"&" . $toggleString . "\";\n";  // Sem frames
  else
    echo "      parent.frames['centro'].location.href = \"" . $menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?") .  "day=\" + d + \"&month=\" + m + \"&year=\" + y;\n"; // com frames
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
  if (stripos("_" . $_theme, 'fancy') ||  stripos("_" . $_theme, 'tron')){
?>
        A:link {text-decoration: none; color: #ACD6F3}
        A:visited {text-decoration: none; color: #ACD6F3}
        A:active {text-decoration: none; color: #ACD6F3}
        A:hover { text-decoration: none; color: #ACD6F3}
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

<style>
   .hover-link {
<?PHP
    if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
      echo "    border-top: 1px solid LightBlue;";
    else
      echo "    border-top: 1px solid DarkGray;";
?>
    /* background-color: lightblue; /* Initial background color */
  }
  .hover-link:hover {
<?PHP
    if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
      echo "    border-top: 1px solid LightBlue;";
    else
      echo "    border-top: 1px solid DarkGray;";
?>
    background-color: lightgray; /* Background color on hover */
<?PHP
    if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo "color: darkblue;\n";
?>
  }
</style>

<SCRIPT LANGUAGE='javascript'>
function toggleMenu(id1){
  var field1 = document.getElementById(id1);
  if ((field1 == null)){
  }
  else
    if (field1.style.display == "none"){
      field1.style.display = "block";
      //$('.menu_item').animate({"top": "0px"}, 500);
    }
    else{
      //$('.menu_item').animate({"bottom": "-300px"}, 500);
      field1.style.display = "none";
    }
}
</SCRIPT>
<DIV ALIGN="left">

<?PHP
    /*

<div class="nav-side-menu">
    <div class="brand">Brand Logo</div>
    <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>
  
        <div class="menu-list">
  
            <ul id="menu-content" class="menu-content collapse in">
                <li>
                  <a href="#">
                  <i class="fa fa-dashboard fa-lg"></i> Dashboard
                  </a>
                </li>

                <!-- pasta -->
                <li  data-toggle="collapse" data-target="#products" class="collapsed active">
                  <a href="#"><i class="fa fa-gift fa-lg"></i> UI Elements <span class="arrow"></span></a>
                </li>
                <!-- items da pasta -->
                <ul class="sub-menu collapse" id="products">
                    <li class="active"><a href="#">CSS3 Animation</a></li>
                    <li><a href="#">General</a></li>
                    <li><a href="#">Buttons</a></li>
                    <li><a href="#">Tabs & Accordions</a></li>
                    <li><a href="#">Typography</a></li>
                    <li><a href="#">FontAwesome</a></li>
                    <li><a href="#">Slider</a></li>
                    <li><a href="#">Panels</a></li>
                    <li><a href="#">Widgets</a></li>
                    <li><a href="#">Bootstrap Model</a></li>
                </ul>
            </ul>
     </div>
</div>

     */
?>
    <TABLE WIDTH="100%" HEIGHT="" CELLSPACING=0 CELLPADDING=0 BORDER=0>
  <TR>
  <TD ALIGN="left" VALIGN="top" class="onde_menu">
<?PHP
    //    echo "   <HR SIZE=1 WIDTH=\"95%\">\n";

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

 if (isset($grupos) && is_array($grupos))
    $grupos = implode(",", $grupos);
//$sql = "SELECT DISTINCT m.*\n";
$sql  = "SELECT DISTINCT m.ordem as realordem, m.ordem, m.codigo, \n";
$sql .= "   m.\"Menu pai\", m.\"Etiqueta\", m.\"contar no fim para o início\", \n";
$sql .= "   m.\"Nível de indentação\", m.\"Formulário\", m.\"Link\", m.tipo,  \n";
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
$sql .= "   m.\"Nível de indentação\", m.\"Formulário\", m.\"Link\", m.tipo,  \n";
$sql .= "   m.\"Consulta (SQL)\", m.argumentos\n";
$sql .= "   FROM menus AS m, menus_grupos AS mg\n";
$sql .= "   WHERE \n";
if (isset($grupos)) $sql .= "       mg.grupo IN (" . $grupos . ") AND \n";
$sql .= "     mg.menu = m.codigo\n";
$sql .= "     AND m.\"contar no fim para o início\"  = true\n";
$sql .= "\n"; 

 
// $sql = "SELECT DISTINCT m.*\n";
// $sql .= "   FROM menus AS m, menus_grupos AS mg\n";
// $sql .= "   WHERE mg.grupo IN (" . $grupos . ") AND mg.menu = m.codigo\n";
// $sql .= "ORDER BY m.tipo, realordem;";

$sql .= "ORDER BY realordem;";
 
// echo "<PRE>" . $sql . "</PRE>";
$result = pg_query($conn, $sql);
 if ($result)
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
	if (is_string($bot) && strpos($bot, "camerasBot")){
	  $bot = explode("?", $bot);
	  $camerasBotPid = intval(trim($bot[0]));
	}
      }
    }

    //echo $menuMargin;
    //echo "<a id=\"demandasBotDisplay\" href=\"botControl.php?toggle[]=hardcoded_developer_menu\" ";
    //if ( !stripos("_" . $_theme, "frameless")) echo "target=\"centro\"";
    //echo ">";
    if ($demandasBotPid)
      $demandasBotMenuLabel =  "demandasBot OK[" . $demandasBotPid . "]";
    else
      $demandasBotMenuLabel =  "<FONT COLOR=\"#FF0000\">demandasBot parado</FONT><BR>";
    echo "</a>\n";

    menuItem("botControl.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     $demandasBotMenuLabel, 1, false, NULL, "demandasBotDisplay");

    /*
    echo $menuMargin;
    if ($editaisBotPid)
      echo "editaisBot OK [" . $editaisBotPid . "]<BR>\n";
    else
      echo "<FONT COLOR=\"#FF0000\">editaisBot parado</FONT><BR>\n";
    echo $menuMargin;
    if ($camerasBotPid)
      echo "camerasBot OK [" . $camerasBotPid . "]<BR>\n";
    else
      echo "<FONT COLOR=\"#FF0000\">camerasBot parado</FONT><BR>\n";
    */

    echo $menuDivider;
    menuItem("configura.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Op&ccedil;&otilde;es", 1, false, NULL, NULL);
    menuItem("sessionManager.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Sessões", 1, false, NULL, NULL);
    menuItem("query.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "Consulta [SQL]", 1, false, NULL, NULL);
     menuItem("cleanUp.php?PHPSESSID=" . $PHPSESSID . "&toggle[]=hardcoded_developer_menu",
	     "<B><FONT COLOR='#FF0000'>Limpar sess&otilde;es orf&atilde;s</FONT></B>", 1, false, NULL, NULL);
    echo $menuDivider;
    echo $closeDIV;
  }
  $sentido = $menu['contar no fim para o início'];
 
  //echo $sentido;

  if ($menu['tipo'] == 1 && is_null($menu['Menu pai'])) {
    menuItem($menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?") . "PHPSESSID=" . $PHPSESSID, $menu['Etiqueta'], $menu['Nível de indentação'], false, NULL, $menu['codigo']);
  }

  if ($menu['tipo'] == 2 && is_null($menu['Menu pai'])) {
    menuItem("forms.php?PHPSESSID=" . $PHPSESSID . "&form=" . $menu['Formulário'],
	     $menu['Etiqueta'], $menu['Nível de indentação'], false, NULL, $menu['codigo']);
  }

  if ($menu['tipo'] == 3 && is_null($menu['Menu pai'])) {
    //echo "<PRE>"; var_dump($menu); echo "</PRE>";
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
    echo "<DIV ";
    if (stripos("_" . $_theme, "frameless")) echo " class=\"hover-link\"";	
	echo "><INPUT style=\"width: ";
    if (stripos("_" . $_theme, "frameless"))
	  echo "150px;";
	else
	  echo "110px;";
    if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')) echo " color: darkblue;";
    echo "\" TYPE=\"TEXT\" CLASS=\"TEXT\" ";
    echo " NAME=\"nome\" id=\"query\" SIZE=10 onkeyup=\"myFunction()\" ";
    echo " STYLE=\"";
    if ($isMobile) echo " width: 250; height: 50; font-size: 32;\" ";
    echo "\"";
    echo " MAXLENGTH=80 onkeypress=\"if (window.event.keyCode==13) busca(window.event.keyCode);\" >&nbsp&nbsp&nbsp";
    echo "<a id=\"ancora\"";
    if (!stripos("_" . $_theme, "frameless")) {echo "target=\"centro\"";}
    echo "onClick=\"(busca(0));\"><IMG SRC=\"images/mag-glass_10x10.gif\" BORDER=0 ALT=\"`o\"></a></div>";
    if (!stripos("_" . $_theme, "frameless")) echo "<HR>";	
    echo $menuDivider;
    ?>
<script>
   console.log('Tipo: <?PHP echo $menu['tipo']; ?>');
   console.log('link: <?PHP echo $menu['Link']; ?>');
function busca(teste){
  // alert(teste);
  // console.log(teste);
  // document.getElementById("ancora").innerHTML="";
  // setar o attributo href do a
  var ancora = document.getElementById("ancora");
  var query =  document.getElementById("query");
  ancora.href="<?PHP echo $menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?"); ?>nome=" + query.value;
  if (teste==13) {
    //location.href="pesquisa.php?nome=" + query.value;
    window.open("<?PHP echo $menu['Link'] . (intval(stripos($menu['Link'], "?"))?"&":"?");
    foreach($toggle as $toggle_name)
      echo "&t[]=" . $toggle_name;?>&nome=" + query.value, <?PHP
		if ( !stripos("_" . $_theme, "frameless") ) echo "\"centro\""; else echo "\"_top\""; ?>);
  }
  //query.value = "";
  return true;
}
</script>  
<?PHP

    
  }
  if ($menu['tipo'] == 5 && is_null($menu['Menu pai'])) {
    menuTogglePoint("M" . $menu['codigo'], $menu['Etiqueta'], $menu['Nível de indentação'], $menu['codigo']);
    $menu_pai = $menu['codigo'];
    $toggleString = "&toggle[]=M".$menu_pai;
    submenu($menus, $menu_pai, $toggleString);
    echo $closeDIV;
  }
 
}

/////////////////////////////////////////////////////////////////////////////////////////
/*
if (!$isMobile){
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
*/
if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron'))
  echo "<DIV style=\" border-top: 1px solid LightBlue; width=100%;\";></DIV>\n";
else
  echo "<DIV style=\" border-top: 1px solid DarkGray; width=100%;\";></DIV>\n";

?>
</TD>
</TR>
</TABLE>
</DIV>
<script>

function myFunction() {
  // Declare variables
  var input, filter, table, tr, td, i, j, txtValue;
  input = document.getElementById("query");
  filter = input.value.toUpperCase();
  table = document.getElementById("cTable");
  tr = table.getElementsByTagName("tr");
  
  // Loop through all table rows, and hide those who don't match the search query
  for (i = 1; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td");
    txtValue = '';
    for (j = 0; j < td.length; j++) {
      if (td[j]){
        txtValue += ' ' + td[j].textContent || td[j].innerText;
        if (td[j].getElementsByTagName("span").length){
          span_title = td[j].getElementsByTagName("span");
          txtValue += ' ' + span_title.item(0).title;
          //console.log(span_title.item(0).title);
	}
      }
    }
    // remove acentos
    // https://stackoverflow.com/questions/990904/remove-accents-diacritics-in-a-string-in-javascript
    //console.log(filter.normalize("NFD").replace(/[\u0300-\u036f]/g, ""));

    //console.log('tr: ' + i + ' td: ' + j + ' txtValue: ' + txtValue);
    if (txtValue.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase().indexOf(filter.normalize("NFD").replace(/[\u0300-\u036f]/g, "")) > -1) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }

}
</script>
