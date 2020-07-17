<?= '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' ?>
<?PHP  
$headerTitle = "Developer > Modelo";
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);include "page_header.inc";
echo "<BR>\n";
if ($_debug) echo "<B>Conection handle=" . $conn . "</B><BR>\n";
if ($_debug) echo "<B>Genero: " . $_POST['genero'] . "</B><BR>\n";
$_POST['buttonrow'][$_SESSION['matricula']]="Detalhes...";
$query = "SELECT * FROM umlpositions";
$result = pg_exec ($conn, $query);
$total  = pg_numrows($result);
$linhas = 0;
while ($linhas<$total){
  $row = pg_fetch_assoc ($result);
  $position[$row['id']]['id'] = $row['id'];
  $position[$row['id']]['top'] = $row['toppos'];
  $position[$row['id']]['left'] = $row['leftpos'];
  $linhas++;
}
/*
  <!-- Libs -->
  <link rel="stylesheet" type="text/css" href="wireit/wireit-0.6.0a/lib/yui/reset-fonts/reset-fonts.css" /> 
*/
?>
<script type="text/javascript" src="dependencies/wireit/wireit-0.6.0a/lib/yui/utilities/utilities.js"></script>
  <script type="text/javascript" src="dependencies/wireit/wireit-0.6.0a/lib/excanvas.js"></script>
  <!-- WireIt -->
  <script type="text/javascript" src="dependencies/wireit/wireit-0.6.0a/build/wireit-min.js"></script>
  <link rel="stylesheet" type="text/css" href="dependencies/wireit/wireit-0.6.0a/assets/WireIt.css" />
  <style>
  table.onde{
    top: 100px;
    width: 100%;
  }
  th.onde{
    border: 1px solid;
    color: black;
    height: 12px;
    margin: 0;
    padding: 0;  
  }
  td.onde{
    border: 1px solid;
    color: black;
    height: 12px;
    margin: 0;
    padding: 0;  
  }
  div.blockBox {
    /* WireIt */
    position: absolute;
    z-index: 5;
    opacity: 0.8;
    margin: 0;
    padding: 0;	
    padding-left: 10px;
    /* Others */
    width: 200px;
    height: 100px;
    background-color: rgb(255,200,200);
    cursor: move;
    border: 1px solid;
 view }


</style>
<P>
</P>
<div class="viewport">
<div id="UML" class="room" STYLE="background: lightblue; width: 4000px; height: 5000px;">
<?PHP
$query  = "SELECT c.oid, t.tablename\n";
$query .= "  FROM pg_tables as t, pg_class as c\n";
$query .= "  WHERE (tableowner<>'postgres' OR t.tablename in ('pg_class', 'pg_constraint', 'pg_attribute') ) AND c.relname=t.tablename";
$result = pg_exec ($conn, $query);
$tables = pg_fetch_all ($result);
$linhas = 0;
$left = (stripos("_" . $_theme, "frameless")) ? 350 : 0;;
$top = 100;


  //  echo "<script>";
  //  echo "\n console.log('";
  //  ob_start();
  //  var_dump($tables);
  //  $dump = ob_get_clean();
  //  echo stripLineBreaks($dump, "\\n");
  //  echo "');\n";
  //  echo "</script>";
  

if ($_debug){
  togglePoint("toggle_tables", "Informações de debug", 0);
  echo "<PRE>\n";
  var_dump($tables);
  echo "</PRE>\n";
  echo $closeDIV;
 }

//////////////////////////////////////////////////////////////////
foreach ($tables as $table){
  $formulario['tabela'] = $table['tablename'];
  $id = fixField($table['tablename']);
  if ($left>=( (stripos("_" . $_theme, "frameless")) ? 1150 : 1500) ){
    $left = (stripos("_" . $_theme, "frameless")) ? 350 : 0;
    if (!isset($position['block' . $id])) $top += 100;
  }
  $innerQuery = $dataDictionary;
  $innerQuery.= " AND\n    t.tablename='" . $table['tablename'] . "'";
  $innerResult = pg_exec ($conn, $innerQuery);
  $innerTotal  = pg_numrows($innerResult);

  echo "<div id='block" . $id ."' class=\"blockBox\" style=\"left: ";
  if (isset($position['block' . $id]))
    echo $position['block' . $id]['left'];
  else 
    echo $left;
  echo "px; top: ";
  if (isset($position['block' . $id]))
    echo $position['block' . $id]['top'];
  else 
    echo $top;
  echo "px;  height: " . (intval($innerTotal)*17 + 16) . "px; \">";
  echo "<span style=\"color: black;\" title=\"criar um novo formulário para a tabela " . $table['tablename'] . "\">";
  echo "<a href=\"newform.php?tablename=" . $table['tablename'];
  foreach($toggle as $item) echo "&t[]=" . $item;
  echo "\">";  
  echo "<img src=\"images/newform.png\" width=12></a></span>";

  $form = 0;
  $query = "SELECT codigo FROM forms where tabela = '" . $table['tablename'] . "' order by codigo desc";
  $result = pg_exec($conn, $query);
  $row = pg_fetch_row($result, 0);
  $form = $row[0];
  
  if (!strpos("_" . $table['tablename'], "<span")) echo "<span style=\"color: black;\" title=\"" . $table['tablename'] . "\">";
  if ($form){
    echo "<a style=\"color: brown;\" href=\"forms.php?form=" . $form;
    foreach($toggle as $item) echo "&t[]=" . $item;    
    echo "\">";
  }
  echo ( (strlen($table['tablename']) > 29) ? substr($table['tablename'], 0, 26) . "..." : $table['tablename'] );
  if ($form) echo "</a>";
  //echo strlen($table['tablename']);
  //echo substr($table['tablename'], 0, 5);
  //echo $table['tablename'] );
  if (!strpos("_" . $table['tablename'], "<span")) echo "</span>\n";
  if (!isset($position['block' . $id])) $left += 300;
  
  echo "<table class=\"onde\">\n";
  while ($linhas<$innerTotal){
    echo "<tr>\n";
    $row = pg_fetch_row ($innerResult, $linhas);

    //if (PHP_VERSION_ID >= 50500)
    if (!strpos("_" . $row[0], "<span")) echo "<td class=\"onde\" style=\"width: 10px;\"><span title=\"" . $row[0] . "\">";
    echo (mb_strlen($row[0]) < 15 ? $row[0] : mb_substr($row[0], 0, 15, $encoding) . "...");
    if (!strpos("_" . $row[0], "<span")) echo "</span></td>";
    //else
    //echo "<td class=\"onde\" style=\"width: 10px;\"><span title=\"" . $row[0] . "\">" . (strlen($row[0]) < 15 ? $row[0] : substr($row[0], 0, 15, $encoding) . "...") . "</span></td>";


    echo "<td class=\"onde\" style=\"width: 10px;\">" . $row[1] . "</td>";
    echo "<td class=\"onde\" style=\"width: 10px;\">" . (intval($row[2]-4)<0?"":intval($row[2]-4)) . "</td>";
    $relations = checkRelations($linhas);
    $relations['Array'] = pg_fetch_assoc ($relations['result'], 0);

    if ($relations['Array']){
      $hasRelations[$table['tablename']] = true;
      $references[$table['tablename']][  $linhas ]['columnname'] = $row[0];
      $references[$table['tablename']][  $linhas ]['indice'] = (intval($linhas)*2)+1;
      $references[$table['tablename']][  $linhas ]['referenceTable'] = $relations['Array']['referenced'];
      $references[$table['tablename']][  $linhas ]['referenceField'] = $relations['Array']['referencedfield'];   
      if ($_debug){
	togglePoint("toggle_" . fixField($table['tablename']) . "_" . $linhas, "detalhes", 0);      
	if ($_debug>1){
	  echo "<PRE>\n";
	  var_dump($relations);
	  echo "</PRE>\n";
	}
        //if ($table['tablename']=="checkpoints")
        //  echo "has relations: " . $hasRelations[$table['tablename']] . "<BR>row[0] " . $row[0] . "<BR>\n";
	echo (intval($linhas)*2)+1 . ")" . $row[0] . " references " . $relations['Array']['referenced'] . "(" . $relations['Array']['referencedfield'] . ")";
	echo $closeDIV;
      }
    }
    else
      if (!isset($hasRelations[$table['tablename']])) $hasRelations[$table['tablename']] = false;
    $fieldIndexes[$table['tablename']][$row[0]] = $linhas * 2;
    $linhas++;
  }
  $linhas = 0;
  echo "</tr>\n";
  echo "</table>\n";
  echo "</div>\n";
}
//echo "//PASSEI  has relations: " . $hasRelations["checkpoints"] . "\n//row[0] " . $row[0] . "<BR>\n";
?>
</div>
</div>
<script>
window.onload = function() {
<?PHP
  $linhas = 0;
  foreach ($tables as $table){
    echo "\$( \"#block" . fixField($table['tablename']) . "\" ).draggable();\n";
    $id = fixField($table['tablename']);
    $linhas++;
    $innerQuery = $dataDictionary;
    $innerQuery.= " AND\n    t.tablename='" . $table['tablename'] . "'";
    $innerResult = pg_exec ($conn, $innerQuery);
    $innerTotal  = pg_numrows($innerResult);
    echo "    var block" . $id . " = YAHOO.util.Dom.get('block" . $id . "');\n";
    echo "    var terminals" . $id . " = [\n";
    while ($linhas<=$innerTotal){
      echo "      new WireIt.Terminal(block" . $id . ", {direction: [-1,0], offsetPosition: [-14," . (($linhas*17)-5) . "]}),\n";
      echo "      new WireIt.Terminal(block" . $id . ", {direction: [1,0],  offsetPosition: [195," . (($linhas*17)-5) . "]})";
      if ($linhas<$innerTotal) echo ",\n";
      $linhas++;
    }
    $linhas = 0;
    echo "    ];\n";
    echo "    \n";
    echo "    new WireIt.util.DD(terminals" . $id . ",block" . $id . ");\n";
    echo "\n";
  }

  $linhas = 0;
//   echo "\n console.log('";
//   ob_start();
//   var_dump($fieldIndexes);
//   $dump = ob_get_clean();
//   echo stripLineBreaks($dump, "\\n");
//   echo "');\n";

//    echo "passei\n";
foreach ($tables as $table){
  if ($hasRelations[$table['tablename']]){
    foreach($references[$table['tablename']] as $reference){
      $linhas++;
      echo "//" . $table['tablename'] . ".";
      echo "" . $reference['column'] . "";
      echo " -> ";
      echo "" . $reference['referenceTable'] . ".";
      echo "" . $reference['referenceField'] . "\n";
      echo "//" . $reference['referenceTable']  . "\n";
      echo "//" . $reference['referenceField']  . "\n";
      echo "//" . $fieldIndexes[ $reference['referenceTable'] ][ $reference['referenceField'] ] . "\n";
      echo "var w" . $linhas . " = new WireIt.BezierWire(terminals" . fixField($reference['referenceTable']) . "[" . $fieldIndexes[ $reference['referenceTable'] ][ $reference['referenceField'] ]. "], ";
      echo "terminals" . fixField($table['tablename']) . "[" . $reference['indice']   . "], document.body);\n";
      echo "w" . $linhas . ".redraw();\n";    
    }

  }
}
  ?>
};

  $(function() {
      $( "#UML" ).droppable({
	drop: function( event, ui ) {
	    $( this )
	      .addClass( "ui-state-highlight" )
	      .find( "p" )
	      .html( "Dropped!" );

          /* console.log("Altura:"+ui.draggable[0].clientHeight);
          console.log("Largura:"+ui.draggable[0].clientWidth);
          console.log(ui.draggable[0]); */
   	      $.getJSON("json-wireit.php?callback=?", 
			      {id: ui.draggable.attr("id"),
			       top: ui.draggable.position().top, 
			       left: ui.draggable.position().left,
             height: ui.draggable[0].clientHeight,
             width: ui.draggable[0].clientWidth,
				  });
	  }
	});
    });


//https://codepen.io/JTParrett/pen/rkofB
var curYPos, curXPos, curDown;

window.addEventListener('mousemove', function(e){ 
  if(curDown){
    window.scrollTo(document.body.scrollLeft + (curXPos - e.pageX), document.body.scrollTop + (curYPos - e.pageY));
  }
});

window.addEventListener('mousedown', function(e){ 
  curYPos = e.pageY; 
  curXPos = e.pageX; 
  curDown = true; 
});

window.addEventListener('mouseup', function(e){ 
  curDown = false; 
});
</script>
<?PHP
$linhas = 0;
$innerResult = pg_exec ($conn, $innerQuery);
$innerTotal  = pg_numrows($innerResult);
$row = pg_fetch_row ($innerResult, 0);      
$relations = checkRelations($linhas);
/*
  carrega as coordenadas das posicoes das tabelas
  primeiro varre todas as tabelas e mapeia as relacoes
  desenha as tabelas
  desenha os fios.
*/
include "page_footer.inc";

?>
