<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
/*****************************************************************************
 * 
 * 
 * 
 *****************************************************************************/
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1;
$ehXML = 0;
$headerTitle = "";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
global $PHPSESSID;

//var_dump($_POST);
echo $_POST['valor'];
?>
  <!-- <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"> -->
  <!-- <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css"> -->
  <!-- <link href="themeAssets/frameless-startbootstrap-sb-admin/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->

  <link rel="stylesheet" href="dependencies/bootstrap/css/upload_bootstrap.css">
  <!--  <link rel="stylesheet" href="css/bootstrap.css"> -->
  <!-- <link rel="stylesheet" href="css/bootstrap-theme.min.css"> -->
  <script src="dependencies/dropzone/dropzone.js"></script>
  <!-- js for the add files area /-->
  <!-- <script src="dependencies/jquery/jquery-3.3.1.js"></script> -->

  <script src="dependencies/bootstrap/js/bootstrap.js"></script>
  
  <script> 
   console.log('passei');
    var j331 = jQuery.noConflict(true);
    //var Dropzone = require("enyo-dropzone");
    //Dropzone.autoDiscover = false;
  </script>

<div class="container" id="container">

</br>
<h4>Se deseja realizar o upload de imagens, selecione um álbum.</h4>
<!-- <h4>Caso não sejam imagens, selecione "Nenhum" na caixa de seleção abaixo.</h4> -->
  <div class="form-group">
    <h4>Álbum:</h4>
    <select id='album' class="form-control form-control-lg" style="width: 200px;">
      <option value="">Selecionar...</option>
      <!-- <option value="-1">Nenhum</option> -->
    </select>
    </br></br>
  </div>
  </br></br>
  <div id="actions" class="row">

    <div class="col-lg-7">
      <!-- The fileinput-button span is used to style the file input field as button -->
      <span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Add files...</span>
      </span>
      <button id = "start" type="submit" class="btn btn-primary start">
        <i class="glyphicon glyphicon-upload"></i>
        <span>Iniciar upload</span>
      </button>
      <button type="reset" class="btn btn-warning cancel">
        <i class="glyphicon glyphicon-ban-circle"></i>
        <span>Cancelar upload</span>
      </button>
      <span class="btn btn-info">
        <i class="glyphicon glyphicon-film"></i>
        <a href="visuImage.php" style="text-decoration: none; color: #FFF">Ver imagens</a>
      </span>
    </div>

    <div class="col-lg-4">
      <!-- The global file processing state -->
      <span class="fileupload-process">
        <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100"
          aria-valuenow="0">
          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
        </div>
      </span>
    </div>

  </div>

  <!-- This is used as the file preview template -->
  
  <div class="table table-striped files" id="previews">

    <div id="template" class="row">

      <div class = "col-lg-1" >
        <span class="preview">
          <img data-dz-thumbnail />
        </span>
      </div>
      <div class = "col-lg-3">
        <p class="name" data-dz-name></p>
        <strong class="error text-danger" data-dz-errormessage></strong>
      </div>
      <div class = "col-lg-5">
        <p class="size" data-dz-size></p>
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
        </div>
      </div>
      <div class = "col-lg-1">
        <button  id = "start" class="btn btn-primary start">
          <i class="glyphicon glyphicon-upload"></i>
          <span>Start</span>
        </button>
        <button data-dz-remove class="btn btn-warning cancel">
          <i class="glyphicon glyphicon-ban-circle"></i>
          <span>Cancel</span>
        </button>
        <button data-dz-remove class="btn btn-danger delete">
          <i class="glyphicon glyphicon-remove"></i>
          <span>Delete</span>
        </button>
      </div>
    </div>

  </div> 
  <script>
    //"use strict";
    j331(function(){
    /**
     * Esconde a área de upload;
     * listAlbuns.php retorna a lista de albuns existentes no banco;
     * Após selecionar um album, o upload é liberado;
    */
      //j331('#actions').hide();
      // /$('#previews').hide();
      j331.getJSON("listAlbuns.php", function( albuns ){
        j331.each( albuns, function(i, a){
          j331("<option value='"+a.codigo+"'>"+a.Álbum+"</option>").appendTo("#album");
        });
      });
      var value;
      var myDropzone, previewNode, previewTemplate;
      // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
      previewNode = document.querySelector("#template");
      previewNode.id = "template";
      previewTemplate = previewNode.parentNode.innerHTML;
      previewNode.parentNode.removeChild(previewNode);
    /**
     * Quando a caixa de seleção é alterada captura o ID correspondente,
     * destroy o objeto Dropzone, caso exista, e cria um novo
     * inserindo o valor do ID na URL destino do arquivo;
     */
      //j331('#album').bind('change', function(){
        //value = j331('#album').val()
        //if(value){
          //console.log("Value = "+value);
          //j331('#actions').show();
          var sendUrl = "handleUpload.php?PHPSESSID=<?php echo $PHPSESSID; ?>"; //&album="+j331('#album').val();  
          //console.log(sendUrl);
    //###################################################################################
    /**
     * Chamada do plugin DropzoneJS
     * src: http://www.dropzonejs.com/#
     * Copyright (c) 2012 Matias Meno
     * MIT License;
     */     
         /*  if(myDropzone){
            myDropzone.destroy();
          } */
          myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
            url: sendUrl,
            paramName: 'files',
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 20,
            previewTemplate: previewTemplate,
            autoQueue: false, // Make sure the files aren't queued until manually added
            previewsContainer: "#previews", // Define the container to display the previews
            clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
          });
          myDropzone.on("addedfile", function (file) {
            // Hookup the start button
            file.previewElement.querySelector(".start").onclick = function () { myDropzone.enqueueFile(file); };
          });
          // Update the total progress bar
          myDropzone.on("totaluploadprogress", function (progress) {
            document.querySelector("#total-progress .progress-bar").style.opacity = 0;
          });
          myDropzone.on("sending", function (file) {
            // Show the total progress bar when upload starts
            document.querySelector("#total-progress").style.opacity = "1";
            // And disable the start button
            file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
          });
          myDropzone.on("processing", function(file) {
            //if(j331('#album').val() > 0){
              this.options.url = sendUrl + "&album="+j331('#album').val();  
            //} else {
             // this.options.url = sendUrl;
           // }
          }); 
          // Hide the total progress bar when nothing's uploading anymore
          myDropzone.on("queuecomplete", function (progress) {
            document.querySelector("#total-progress").style.opacity = 0;
            
          });
          // Setup the buttons for all transfers
          // The "add files" button doesn't need to be setup because the config
          // `clickable` has already been specified.
          document.querySelector("#actions .start").onclick = function () {
            myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
          };
          document.querySelector("#actions .cancel").onclick = function () {
            myDropzone.removeAllFiles(true);
          };
        /* } else {
          j331('#actions').hide(); */
        //}
      //});
    });
  </script>

<!-- js for the add files area /-->
</div>

</br></br></br></br>
<?PHP

include "page_footer.inc";
?>
