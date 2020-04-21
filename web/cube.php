<?= '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' ?>
<?PHP
  /**
   * 8x8x8 LED Cube Voxel Editor
   * $Id: cube.php,v 1.28 2018/06/01 19:44:13 filipi Exp $
   */
$useSessions = 1; $ehXML = 0;
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);include "page_header.inc";

$leftOffset = 0;
if (stripos("_" . $_theme, "frameless"))  $leftOffset = 208;


?>
<script>
var animation = new Array(8);
for (var i = 0; i < 16; i++) {
  animation[i] = new Array(8);
  for (var j = 0; j < 8; j++) {
    animation[i][j] = new Array(8);
    for (var k = 0; k < 8; k++) {
      animation[i][j][k] = new Array(8);
      for (var l = 0; l < 8; l++) {
	animation[i][j][k][l] = false;
      }
    }
  }
 }
var animationAux = new Array(8);
for (var i = 0; i < 16; i++) {
  animationAux[i] = new Array(8);
  for (var j = 0; j < 8; j++) {
    animationAux[i][j] = new Array(8);
    for (var k = 0; k < 8; k++) {
      animationAux[i][j][k] = new Array(8);
      for (var l = 0; l < 8; l++) {
	animationAux[i][j][k][l] = false;
      }
    }
  }
 }
var line = 0;
var plane = 0;
var voxel = 0;
var frame = 0;
var codigo = -1;

var c = 0;
var t;
var timer_is_on = 0;


function timedCount(){
  var currentFrame = $( "#timeSlider" ).slider( "value" );
  currentFrame = currentFrame < 15 ? currentFrame + 1 : 0;
  $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame);
  $( "#timeSlider" ).slider( "value", currentFrame );
  drawCube(currentFrame);
  t = setTimeout("timedCount()",50);
}

function doTimer() {
  if (!timer_is_on)  {
    timer_is_on = 1;
    timedCount();
  }
}

function getCube(codigo){
$("#loading_gif").css("display", "block"); 
$.getJSON("json-cube.php?jsoncallback=",
	  {codigo: codigo},
	  function(data) {
	    frame=-1;
	    $.each(data, function(i,item){
		frame++;
		$.each(item, function(i1,item1){
		    line = -1
		      $.each(item1, function(i2,item2){
			  line++;
			  $.each(item2, function(i3,item3){
			      plane = -1;
			      $.each(item3, function(i4,item4){
				  plane++;
				  $.each(item4, function(i5,item5){
				      voxel = -1;
				      $.each(item5, function(i6,item6){
					  voxel++;
					  $.each(item6, function(i7,item7){
					      animation[frame][line][plane][voxel] = item7;
					    });
					});
				    });
				});
			    });
			});
		  });
	      });
	    frame = 0;
            line = 7;
            $( "#timeSlider" ).slider( "option", "value", 0 );
            $( "#lineSlider" ).slider( "option", "value", 7 );
            $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame);
	    drawCube(frame);
            $("#loading_gif").css("display", "none"); 
	  });

}

function drawCube(frame){
  for (var j = 0; j < 8; j++) {
    for (var k = 0; k < 8; k++) {
      for (var l = 0; l < 8; l++) { 
	if (animation[frame][j][k][l]){
	  if (j==line) $("#" + parseInt(l) + "_" + parseInt(k)).css("background-color", "blue"); 
	  $("#" + k + "_" + j + "_" + l).html("<img src=images/drawing.gif>");
	  //$("#" + k + "_" + j + "_" + l).html("<img src=images/drawing3.png>");
	}
	else {                    
	  if (j==line) $("#" + parseInt(l) + "_" + parseInt(k)).css("background-color", "transparent");
	  $("#" + k + "_" + j + "_" + l).html("");
	  //$("#" + k + "_" + j + "_" + l).html("<img src=images/drawing10.gif>");
	  //$("#" + k + "_" + j + "_" + l).html("<img src=images/drawing.png>");
	}
      }
    }
  }
}

$(function() {
    $( "#timeSlider" ).slider({
      min: 0,
          max: 15,
	  slide: function(event, ui) {
	  frame = ui.value;
	  $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame);
	  drawCube(frame);
	}

      });
    $( "#lineSlider" ).slider({
      orientation: "vertical",
	  min: 0,
	  max: 7,
	  value: 7,
	  slide: function(event, ui) {
	  line = ui.value;//7 - ui.value;
	  $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame);
	  for (var k = 0; k < 8; k++) {
	    for (var l = 0; l < 8; l++) { 
	      if (animation[frame][line][k][l])
		$("#" + parseInt(l) + "_" + parseInt(k)).css("background-color", "blue"); 
	      else 
		$("#" + parseInt(l) + "_" + parseInt(k)).css("background-color", "transparent");
	    }
	  }
	}
      });        
    $(".pixel").click(function () {
	var index = $(".pixel").index(this);
	//plane = parseInt((index - 515)%8);
	//voxel = parseInt((index - 515)/8);
	plane = parseInt((index)%8);
	voxel = parseInt((index)/8);
	//$("#infoPanel").text("That was div index #" + index + " line: " + plane + " coluna: " + voxel);
	animation[frame][line][plane][voxel] = !animation[frame][line][plane][voxel];
	if (animation[frame][line][plane][voxel]){
	  $("#" + parseInt(voxel) + "_" + parseInt(plane)).css("background-color", "blue"); 
	  $("#" + plane + "_" + line + "_" + voxel).html("<img src=images/drawing.gif>");
	  //$("#" + plane + "_" + line + "_" + voxel).html("<img src=images/drawing3.png>");
	}
	else{
	  $("#" + parseInt(voxel) + "_" + parseInt(plane)).css("background-color", "transparent");
	  $("#" + plane + "_" + line + "_" + voxel).html("");
	  //$("#" + plane + "_" + line + "_" + voxel).html("<img src=images/drawing10.gif>");
	  //$("#" + plane + "_" + line + "_" + voxel).html("<img src=images/drawing.png>");
	}
      });
  });


function loadList(){
  $("#lista1").empty();
  $("#lista1").css('line-height', 0.8);
  $.getJSON('json-cube.php', function(data) {
      $.each(data, function(i,item){
	  $("#lista1").append("<div id=anchor_"+item.codigo+" onmouseover=\"document.body.style.cursor='pointer'\" onmouseout=\"document.body.style.cursor='default'\" >["+item.codigo+"] "+item.observacao+"</div>");
	  //$("#lista1").append("<div id=anchor_"+item.codigo+">"+item.observacao+" "+item.codigo+"</div>");
          
          //$("anchor_"+item.codigo).css('cursor', 'pointer');
          //$("anchor_"+item.codigo).css('line-height', '50%');
          $('#anchor_'+item.codigo).bind('click', function() {
            getCube(item.codigo);
            codigo = item.codigo;
            $("#observacao").val(item.observacao);     
            //alert('Carregada animação ' + item.codigo + '<BR>' + item.observacao);
          });
	  $("#lista1").append("<BR>");

	});
    });
}

line = 7;
</script>

<?PHP
  //<!-- top incrementa 15 e left incrementa 26 -->
//$left = 212;
$left = 212 + $leftOffset;
//$top    = 15;
$top    = 0;
$zindex = 770;

//echo "<PRE>";
for ($plane=0;$plane<8;$plane++){
  $top += 15;
  for ($line=7;$line>-1;$line--){
    $left += 26;
    for ($i=0;$i<8;$i++){
      echo '<div class="voxel" id="' . $plane . '_' . $line . '_' . $i . '" style="width: 52px; height: 60px; position: absolute;' . "\n";
      echo '  left: ' . $left . 'px;  top: ' . $top . 'px; z-index: ' . $zindex . ';">';
      echo '';
      //echo '<img src="images/drawing10.gif">';
      //echo '<img src="images/drawing.png">';
      echo '</div>' . "\n";
      $zindex += 1;
      $top += 15;
      $left -= 26;
    }
    $left += (8*26)-26;
    $top -= (8*15)-30;
    $zindex -= 18;
  }
  $left += 26;
  $top -= 8*30;
  $zindex += 180;
 }
// left do timeSlider com frame: 130
// left do lineSlider com frame: 550
// top do lineSlider com frame: 165
// left do drawingCanvas com frame: 650

echo "  <div id=\"timeSlider\" style=\"width: 400px; position: absolute;\n";
echo "  left: " . intval(92 + $leftOffset) . "px;  top: 500px; z-index: 1;\"></div>\n";
echo "  <div id=\"lineSlider\" style=\"height: 270px; position: absolute;\n";
echo "  left: " . intval(562 + $leftOffset) . "px;  top: 55px; z-index: 1;\"></div>\n";
echo "  <div id=\"drawingCanvas\" style=\"width: 320px; height: 320; position: absolute;\n";
echo "  border: solid 1px;\n";
echo "  left: " . intval(592 + $leftOffset) . "px;  top: 30px; z-index: 1;\"></div>\n";

$top = 30;
$left = 592 + $leftOffset;
//$left = 800;
for ($i = 0; $i < 8; $i++){
  for ($j = 0; $j < 8; $j++){
    echo '<div class="pixel" id=' . $i . '_' . $j . ' style="width: 40px; height: 40; position: absolute;' . "\n";
    echo '  border: solid 1px;' . "\n";
    echo '  left: ' . $left . 'px;  top: ' . $top  . 'px; z-index: 1;"></div>' . "\n"; 
    $left += 40;
  }
  $top += 40;
  //$left = 650;
  //$left = 800;
  $left = 592 + $leftOffset;

 }
echo "<div id=\"infoPanel\" style=\"font-size: 24; width: 350px; height: 80; position: absolute;\n";
echo "  border: solid 1px;\n";
echo "  left: " . intval(562 + $leftOffset) . "px;  top: 360px; z-index: 1;\"></div>\n";
echo "   <div id=\"lista1\" style=\"width: 330px; height: 300; position: absolute;\n";
echo "  border: solid 1px; overflow-y: scroll;overflow-x: hidden; white-space: nowrap;\n";
echo "  left: " . intval(562 + $leftOffset) . "px;  top: 450px; z-index: 1; padding: 10px; \">\n";
echo "  </div>\n";
for($i=0;$i<32;$i++) echo "<BR>";
echo "  <input class=onde size=70 type=text id=observacao style=\"font-size: 18; width: 400;\">\n";
echo "<BR>\n";
echo "  <button id=novo>Novo</button>\n";
echo "  <button id=salvar>Salvar</button>\n";
echo "  <button id=saveas>Salvar como</button><BR>\n";
echo "  <button id=clone>Desloca face esq. para dentro</button>\n";
echo "  <button id=clone2>Desloca face dir. para dentro</button><BR>\n";
echo "  <button id=clone3>Desloca face superior para baixo</button><BR>\n";
echo "  <button id=clone4>Extrude face superior pra baixo</button><BR>\n";
echo "  <button id=seno> seno</button>\n";
echo "  <button id=parabola>parabola</button>\n";
echo "  <button id=sinxcos>seno x cosseno</button>\n";
echo "  <button id=flip>flip</button>\n";
echo "  <button id=rotate>rotate</button><BR><BR>\n";
echo "  <div id=\"play\" style=\"width: 50px; height: 50px; position: absolute;\n";
echo "  left: " . intval(32 + $leftOffset) . "px;  top: 460px; z-index: 2000; display: block;\">\n";
echo "  <img width=50 height=50 src=\"images/play.jpg\"></div>\n";
echo "  <div id=\"pause\" style=\"width: 50px; height: 50px; position: absolute;\n";
echo "  left: " . intval(32 + $leftOffset) . "px;  top: 460px; z-index: 2000; display: none;\">\n";
echo "  <img width=50 height=50 src=\"images/pause.jpg\"></div>\n";
?>
  <div id="lista2"></div>
  <script>
  $("#play").bind('click', function(){      
      $("#play").css("display", "none"); 
      $("#pause").css("display", "block"); 
      doTimer();
    });
  $("#pause").bind('click', function(){
      $("#play").css("display", "block"); 
      $("#pause").css("display", "none"); 
      clearTimeout(t);
      timer_is_on = 0;
    });


  $("#rotate").bind('click', function(){
    for (var i = 0; i < 16; i++) 
      for (var j = 0; j < 8; j++) 
        for (var k = 0; k < 8; k++) 
          for (var l = 0; l < 8; l++)
            animationAux[i][j][k][l]=animation[i][k][j][l];

    for (var i = 0; i < 16; i++) 
      for (var j = 0; j < 8; j++) 
        for (var k = 0; k < 8; k++) 
          for (var l = 0; l < 8; l++)
            animation[i][j][k][l]=animationAux[i][j][k][l];

      drawCube(frame);

    });

  $("#flip").bind('click', function(){
    for (var i = 0; i < 16; i++) 
      for (var j = 0; j < 8; j++) 
        for (var k = 0; k < 8; k++) 
          for (var l = 0; l < 8; l++)
            animationAux[i][j][k][l]=animation[i][j][l][k];

    for (var i = 0; i < 16; i++) 
      for (var j = 0; j < 8; j++) 
        for (var k = 0; k < 8; k++) 
          for (var l = 0; l < 8; l++)
            animation[i][j][k][l]=animationAux[i][j][k][l];

      drawCube(frame);

    });


  $("#novo").bind('click', function(){
    for (var frame = 0; frame < 16; frame++) 
      for (var plane = 0; plane < 8; plane++) 
        for (var line = 0; line < 8; line++) 
          for (var voxel = 0; voxel < 8; voxel++)
              animation[frame][plane][line][voxel]=0;
    line = 7;
    plane = 0;
    voxel = 0;
    frame = 0;
    codigo = -1;
    $( "#timeSlider" ).slider( "option", "value", 0 );
    $( "#lineSlider" ).slider( "option", "value", 7 );
    $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame);
     drawCube(frame);
    $("#observacao").val("");     
    });
  $("#clone").bind('click', function(){
      for (var i=7;i>-1  ;i--){
        //$("#lista2").append("copiando " + parseInt(frame+7) + " para " + parseInt(i) +"<BR>");
        for( var x=0;x<8;x++)
          for( var y=0;y<8;y++){
            animation[frame+7-i][x][y][i] = animation[frame][x][y][7];
	  }
      }
    });
  $("#clone2").bind('click', function(){
      for (var i=7;i>-1  ;i--){
        //$("#lista2").append("copiando " + parseInt(frame+7) + " para " + parseInt(i) +"<BR>");
        for( var x=0;x<8;x++)
          for( var y=0;y<8;y++){
            animation[frame+7-i][x][i][y] = animation[frame][x][7][y];
	  }
      }
      drawCube(frame);
    });
  $("#clone3").bind('click', function(){
      for (var i=7;i>-1  ;i--){
        //$("#lista2").append("copiando " + parseInt(frame+7) + " para " + parseInt(i) +"<BR>");
        for( var x=0;x<8;x++)
          for( var y=0;y<8;y++){
            animation[frame+7-i][i][x][y] = animation[frame][7][x][y];
	  }
      }
      drawCube(frame);
    });

  $("#clone4").bind('click', function(){
      for (var i=7;i>-1  ;i--){
        //$("#lista2").append("copiando " + parseInt(frame+7) + " para " + parseInt(i) +"<BR>");
        for( var x=0;x<8;x++)
          for( var y=0;y<8;y++){
            animation[frame][i][x][y] = animation[frame][7][x][y];
	  }
      }
      drawCube(frame);
    });
  $("#seno").bind('click', function(){
      for (var i=0;i<16;i++)
        for( var x=0;x<8;x++){
          y = parseInt(4+(2*Math.sin(i+(x*120))));
          //alert(y);
          animation[i][(i<8?i:i-8)][x][y] = true;
        }
      drawCube(frame);
    });
  var z =0;
  $("#sinxcos").bind('click', function(){
      for (var i=0;i<16;i++)
        for( var y=0;y<8;y++)
          for( var x=0;x<8;x++){
            //z = parseInt(4+(4*Math.sin((4+y*100))));
            //z = parseInt(4+(4*Math.sin((x*100))));
            z = parseInt(4+(2*(Math.sin(i+x*100)*Math.sin(i+4+y*100))));
            //alert(z);
            //z = (x%2) && (y%2) ? i : 1;
            animation[i][z][x][y] = true;
          }
      drawCube(frame);
    });
  $("#parabola").bind('click', function(){
      for (var i=0;i<16;i++)
        for( var x=0;x<8;x++){
          //alert( y );
          y = parseInt( ((x-i)*(x-i))/3 );
          //y = parseInt(Math.sin(4 + Math.sqrt(Math.pow(x,2) + Math.pow(i,2))));
          animation[i][(i<8?i:i-8)][x][y] = true;
        }
      drawCube(frame);
    });

  $("#salvar").bind('click', function(){
      $("#saving_gif").css("display", "block"); 
      if (codigo == -1){
        $.post('json-cube.php', {anim: animation, observacao: $("#observacao").val()}, function(data) {
	    //alert(data);	
  	    $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame+'<PRE>'+data+'</PRE>');
          loadList();
        });
      }
      else{
        $.post('json-cube.php', {anim: animation, codigo: codigo, observacao: $("#observacao").val()}, function(data) {
	    //alert(data);	
  	    $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame+'<PRE>'+data+'</PRE>');
          $("#saving_gif").css("display", "none"); 
          loadList();
        });
      }
    });
    $("#saveas").bind('click', function(){
      $("#saving_gif").css("display", "block"); 
        $.post('json-cube.php', {anim: animation, observacao: $("#observacao").val()}, function(data) {
	    //alert(data);	
  	    $("#infoPanel").html('Linha: '+line+'<BR>Frame: '+frame+'<PRE>'+data+'</PRE>');
          $("#saving_gif").css("display", "none"); 
          loadList();
        });
    });
  
loadList();

$(function() {
    $( "button" )
      .button()
      .click(function( event ) {
	  event.preventDefault();
	});
  });

</script>

<div id="loading_gif" style="width: 500px; position: absolute;
  left: <?PHP echo intval(42 + $leftOffset); ?>px;  top: 50px; z-index: 2000; display: none;"><img src="images/loading_gif.gif"></div>

<div id="saving_gif" style="width: 500px; position: absolute;
  left: <?PHP echo intval(192 + $leftOffset); ?>px;  top: 200px; z-index: 2000; display: none;"><img src="images/saving.gif"></div>

<?PHP
  for($i=0;$i<10;$i++) echo "<BR>";
include "page_footer.inc";
?>

