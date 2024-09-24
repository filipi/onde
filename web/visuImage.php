<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1;
$ehXML = 0;
$headerTitle = "Ground truth generation tool";
include "iniset.php";
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
//echo "<img id = '".$id."' src = '".$id.".jpeg"

?>
<html>
  <head>
        <script src="dependencies/knob/js/jquery.knob-1.0.1.1.js"></script>
        <link href="dependencies/knob/stylesheet.css" rel="stylesheet" type="text/css">
        <script>
            $(function() {
                $(".knob").knob();
                /*$(".knob").knob(
                                {
                                'change':function(e){
                                        console.log(e);
                                    }
                                }
                            )
                           .val(79)
                           ;*/
            });
        </script>
    <style>
	input{
	  box-shadow: none;
	  }
      .display {        
        width: 100%;
        height: 360px;
        margin-top: auto;
        overflow: hidden;
      }
      .images{
        width: auto;
        height: auto;
        display: block;
        margin-left: auto;
        margin-right: auto;
        top: auto;
        bottom: auto;
      }
      .playbutton{
        width: 50px;
        height: 50px;
        position: absolute;
      }
      .imageslider{
        width: 368px;
        background-color: white;
        position: relative;
        left: 40px;
        top: 20px;
      }
      .container{
        position: relative;
        width: 1536px;
        height: 550px;
        top: 50px;
        left: 50px;
      }
      .rotate90 {
        -webkit-transform: rotate(90deg);
        -moz-transform: rotate(90deg);
        -o-transform: rotate(90deg);
        -ms-transform: rotate(90deg);
        transform: rotate(90deg);
      }
    </style>
  </head>

  <body onload="(doVisuTimer())">
  <?PHP
  if (!isset($_POST['album'])) $_POST['album'] = 11;
  //  $_debug = 3;
    //                                                   width, default,                 submit, echoCaption, filter, desc
  echo "<FORM METHOD=POST>\n";
  echo "<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  echo "<B>Select dataset</B>";
  echo "<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  dbcombo("albuns", "codigo", "\"Álbum\"", $conn, "album", NULL,  intval($_POST['album']),    1,   NULL,        " codigo <> 1",   NULL);
  // echo $conn;
  // $query = "select codigo from albuns";
  // $result = pg_exec($conn, $query);
  // $data = pg_fetch_all($reult);		     
  // echo "<PRE>";  var_dump($data); echo "</PRE>";
  echo "</FORM>\n";

//echo $_POST['album'];

  $sql  = "SELECT codigo FROM arquivos\n";
  $sql .= "  where \"Conteúdo\" IS NOT NULL\n";
  $sql .= "  and small = false and thumb = false \n";
  if ( isset($_POST['album']) && intval($_POST['album']) )
    $sql .= " and codigo in (select arquivo from arquivos_albuns where album = " . intval($_POST['album'])  . ")\n";
    $sql .= " and codigo not in (select arquivo from arquivos_albuns where album = 1)\n";
  $sql .= "  order by \"Nome do arquivo\" ";

//echo "<PRE>" . $sql . "</PRE>";

//echo "<div style=\"width:100%; height: 35%;\">\n";
echo "<div style=\"width:100%;\">\n";

?> 
      <div class="container">
       <div class="display"  >

        <?php ////////////Coleta as imagens do banco de dados e adiciona ao HTML

          $result = pg_query($conn, $sql);
          $total = pg_NumRows($result);
          $colunas = pg_NumFields($result);

          if ($total) {
            $row = pg_fetch_all($result);
            if ($_debug) {
              echo "<pre>";
              var_dump($row);
              echo "</pre>";
            }
            foreach ($row as $key) {
              echo "<center>";
              echo "<img class = 'images' src= 'loadFiles.php?id=" . $key['codigo'] . "&image=1&field=images' >\n";
              echo "</center>";
            }
          }
          //pg_close($conn);
        ?>
      </div>
      <?php
//      echo "      <div style=\"width: 400x; height: 80px;\">\n";
echo "      <div style=\"width: 1536x; height: 307px;\">\n";
	    ?>
        <div class = "playbutton" onclick="playDisplay()">
          <img id="imgPlayStop" src="images/play.jpg" width=50 height=50 >
        </div>
        <div>
          <input id="timeSlider" class="imageslider" type="range" />
        </div>
      </div>
    </div>
  </div>
  </body>

  <script type="text/javascript">

    ///////////////////////////////////////////////////Declarações para imagens;
    var index = 0;
    var images = document.getElementsByClassName("images");
    var visu_timer_is_on = 0;

    //////////////////////////////////////////////////Declarações para slide bar
    var playDisp = 0;
    var playBut = document.getElementById('imgPlayStop');
    var timerSlider = document.getElementById("timeSlider");
    timeSlider.min = "0";
    timeSlider.max = images.length - 1;

    ///////////////////////////////////Altera a imagem de acordo com a slide bar
    timeSlider.oninput = function(){
      playDisp = 1;
      playBut.src = "images/pause.jpg";
      for( i = 0; i < images.length; i++){
        images[i].style.display = "none";
      }
      images[this.value].style.display = " block";
    }

    ////////////////////////////////////// ///////////////Visualizador de imagens

    function changeDisplay(){
      var i;
      if(playDisp == 0){
          for( i = 0; i < images.length; i ++){
            images[i].style.display = "none";
          }
          index++;
          if(index > images.length) {
            index = 1;
          }
          images[index-1].style.display  = "block";
          t = setTimeout("changeDisplay()", 100);
          timeSlider.value = index-1;
      }
    }

    ////////////////////////////////////////////Inicia o visualizador de imagens
    function doVisuTimer() {
      if (!visu_timer_is_on)  {
        visu_timer_is_on = 1;
        changeDisplay();
      }
    }

    ////////////////////////////////////////////////////////////////Play e pause
    function playDisplay(){
      if(!playDisp){
        playDisp = 1;
        playBut.src = "images/pause.jpg";
      } else {
        playDisp = 0;
        playBut.src = "images/play.jpg";
        changeDisplay();
      }
    }
  </script>

        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob" data-cursor=true data-skin="tron" value="35">
        </div>

        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob"data-width="250" data-min="-100" value="44">
        </div>

        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob" data-width="300" data-cursor=true value="29">
        </div>

        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-3008949-6']);
            _gaq.push(['_trackPageview']);
        </script>
        <script type="text/javascript">
            (function() {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
            })();
        </script>


  </html>
<?PHP
include "page_footer.inc";
?>
