<?PHP
if (isset($_GET['PHPSESSID']))
  $PHPSESSID = $_GET['PHPSESSID'];
$useSessions = 1; $ehXML = 1;
include "iniset.php";
//include("masterFormStartup.inc");
//include("monitorLADStartup.inc");
include "page_header.inc";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
  <HEAD>
    <TITLE>ONDE fLame Work</TITLE>
     <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?PHP echo $encoding; ?>">    <META HTTP-EQUIV="Expires" CONTENT="Wed, 9 Sep 1998 12:00:00 GMT">

    <link href="dependencies/jquery/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <script src="dependencies/jquery/jquery.min.js"></script>
    <script src="dependencies/jquery/jquery-ui.min.js"></script>


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

body {
<?PHP
    if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')){
      echo "        background-color : black;\n";
      echo "        color : lightblue;\n";
    }
    else{
      echo "        background-color : white;\n";
    }
?>
	scrollbar-arrow-color:black;
	scrollbar-track-color:white;
	scrollbar-shadow-color:black;
	scrollbar-face-color: #dfdfdf;
	scrollbar-highlight-color:black;
	scrollbar-darkshadow-color:silver;
	scrollbar-3dlight-color:silver;
}
    </STYLE>
  <!-- calendar stylesheet -->
  <link rel="stylesheet" type="text/css" media="all" href="dependencies/calendar/css/calendar-blue.css" title="winter" />


  <!-- main calendar program -->
  <script type="text/javascript" src="dependencies/calendar/js/calendar.js"></script>

  <!-- language for the calendar -->
  <script type="text/javascript" src="dependencies/calendar/lang/calendar-en.js"></script>

  <!-- the following script defines the Calendar.setup helper function, which makes
       adding a calendar a matter of 1 or 2 lines of code. -->
  <script type="text/javascript" src="dependencies/calendar/js/calendar-setup.js"></script>
  </HEAD>
  <BODY BGCOLOR="WHITE">
<?PHP
   if($_menu_from_db)
     include("database_menu.inc");
   else
     include("menu.inc");
 ?>
  </BODY>
</HTML>
