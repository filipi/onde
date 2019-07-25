<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1;
$ehXML = 0;
$headerTitle = "Página de gabarito";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario
//echo "<img id = '".$id."' src = '".$id.".jpeg"

?>
<html>
  <head>
    <style>
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
    </style>
  </head>

  <body onload="(doVisuTimer())">
  <?PHP
  //  $_debug = 3;
    //                                                     width, default, submit, echoCaption, filter, desc
  echo "<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  dbcombo("albuns", "codigo", "\"Álbum\"", $conn, "album", NULL,  NULL,    NULL,   NULL,        NULL,   NULL);
  // echo $conn;
  // $query = "select codigo from albuns";
  // $result = pg_exec($conn, $query);
  // $data = pg_fetch_all($reult);		     
  // echo "<PRE>";  var_dump($data); echo "</PRE>";

  ?> 
    <div style="width:100%; height: 100%;">
      <div class="container">
       <div class="display"  >

        <?php ////////////Coleta as imagens do banco de dados e adiciona ao HTML
          $sql = "SELECT codigo FROM arquivos where \"Conteúdo\" IS NOT NULL order by \"Nome do arquivo\" ";
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
      <div style="width: 1536x; height: 307px;">
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
</html>
<?PHP
include "page_footer.inc";
?>
