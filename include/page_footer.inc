<?PHP
  //$authlog_query  = "INSERT INTO authlog (" . (intval($matricula) ? "matricula, " : '') . "senha, IP, page, error)\n";
  //$authlog_query .= "  VALUES (" . (intval($matricula) ? "'" . $matricula . "', " : '') . "'" . $senha . "',\n";
$authlog_query  = "INSERT INTO authlog (" . (intval($matricula) ? "matricula, " : '') . "IP, page, error)\n";
$authlog_query .= "  VALUES (" . (intval($matricula) ? "'" . $matricula . "', " : '') . "\n";
$authlog_query .= "          '" . $ip . "', '";
$authlog_query .= basename($_SERVER["PHP_SELF"]) . "', 'footer')\n";
$authlog_exe = pg_exec($conn,$authlog_query);

if (!$authlog_exe){
  $authlog_query = "INSERT INTO authlog(error) VALUES('" . pg_last_error() . "')";
  $authlog_exe = pg_exec($conn,$authlog_query);
}

if ($conn)
  pg_close($conn);

if (!$ehXML){?>
  <CENTER style = " width: 100%; float: left; bottom: 0px;">
  <?PHP
/*
    if ($_SESSION['h_log'] && $_SESSION['matricula']){
    ?>
      <A HREF="<?PHP echo "inicio.php" . ($PHPSESSID ? "?PHPSESSID=" . $PHPSESSID : "");
    ?>" BORDER=0>[ P&aacute;gina Inicial ]</A><BR>
    <?PHP
    }
    else{?>
  <A HREF="./" BORDER=0 TARGET="_top">[
    P&aacute;gina Inicial ]</A><BR>
    <?PHP
	
    }
*/
echo "  <HR WIDTH=\"90%\" SIZE=\"1\">\n";
echo "  <DIV CLASS=\"coment\">\n";
/*
    ?>
   Informa&ccedil;&otilde;es:
   <?PHP echo $fone; ?> ou <a href="mailto:<?PHP echo $email;
   ?>"><?PHP echo $email; ?></A><BR>
*/
?>
<A href="sobre.php<?PHP 
       if (trim($PHPSESSID)) echo "?PHPSESSID=" . $PHPSESSID; ?>
    "><?PHP echo $SYSTEM_NAME; ?> Vers&atilde;o <?PHP echo $SYSTEM_VERSION; ?></A></DIV>
  <?PHP
  if (stripos("_" . $_theme, 'fancy') || stripos("_" . $_theme, 'tron')){
    $logo_image = "logoIDEIAglow_2018.png";
  }
  else{
    $logo_image = "logo_ideia_2018.png";
  }
  //$onde_logo_themes = 0;
  $onde_logo_themes[] = 'frameless';
  $onde_logo_themes[] = 'framelessbootstrap';
  $onde_logo_themes[] = 'framelessNew';

  if (in_array($_theme, $onde_logo_themes)) $logo_image = 'logo_onde.png';

  if (file_exists("./images/" . $logo_image))
    echo ($organizationWebSiteURL ? "<a href=\"" . $organizationWebSiteURL . "\" target=\"_blank\">" : "") . "<img border=\"0\" src=images/" . $logo_image . ">" . ($site_ideia_URL ? "</a>" : "") . "\n";
  else{
    if (file_exists("../images/" . $logo_image))
      echo ($organizationWebSiteURL ? "<a href=\"" . $organizationWebSiteURL . "\" target=\"_blank\">" : "") . "<img  border=\"0\" src=../images/" . $logo_image . ">" . ($site_ideia_URL ? "</a>" : "") . "\n";
  }
  ?>
  </CENTER>
  
<?PHP
  //echo "_menu: " . $_menu . "<BR>\n";
  // Esse teste nao eh estritamente necessario. Caso a variavel _menu chegue aqui
  // vazia ao inves de PASSEI, para um caso que deveria ter PASSEI, provavelmente
  // alguma pagina encontrou uma condicao de erro e deu exit de dentro de uma
  // funcao sem ter declarado o $_menu global. 
  // caso nao se consiga encontrar a funcao, este teste e sua respectiva
  // chave de fechamento, indicada abaixo, podem ser comentados.
  if ( stripos("_" . $_theme, "frameless") && !$ehXML && $_menu == "PASSEI"){  
?>
        </div><!--end content-->        
      <?PHP /*<div class="bot-pad"></div><!--preserve the footer space-->*/?>
      <?PHP


  if (!in_array(basename($_SERVER['PHP_SELF']), $withoutMenu)){
      ?>
    
    </div><!--end main-->
</div><!--end innerwrap-->

      <?PHP 
      }

      /*
<div id="footer">
    <div id="innerfoot">
        <h3>Fixed Footer</h3>
    </div>
</div>
*/
?>

</div><!--end wrapper-->
      <script>//console.log('passei');
      </script>

<!--[if IE 6]><?PHP
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"themeAssets/" . $_theme . "/css/ie6.css\" media=\"screen\">\n";
?>

   
<script type="text/javascript">
   
   $(function(){                       // run all of this on document.ready (document.ready is jQuery talk)
   
      var resizing = false;
   
      init();                          // run the init function

      $(window).resize(function(){     // bind a resize event to window
         init();                       // run the init function on window resize
		 window.location.reload()      // reload the window after init() runs
      });
      
      function init(){   
	  
         if(!resizing){
		    resizing=true;
            window.setTimeout(function(){    // all code in a setTimeout will run after the set # of milliseconds passes as set below
               var viewPortHeight = $(window).height(); // set viewport height to a variable
                  
		var headerHeight = 0;//$("#header").css("height"); // set header height to a variable
               //headerHeight = headerHeight.replace(/px/,'');  // remove the 'px' from the previous variable so we can preform Math with it
		var footerHeight = 0;//$("#footer").css("height");
               //footerHeight = footerHeight.replace(/px/,'');
            
		var headerFooterHeight = 0;//parseFloat(headerHeight) + parseFloat(footerHeight); // add header and footer height
            
               var elHeight = viewPortHeight - headerFooterHeight; // subtract that value from viewPortHeight
               elHeight = (elHeight - 17) + "px"; // elHeight is how high the fixed element will be, add the 'px' back on
            
               $("#right-inner").css('height',elHeight); // set the fixed elements height
               			
               $("#innerwrap").scroll(function () { // bind a scroll event to #innerwrap
                  var scrollY = $("#innerwrap").scrollTop(); // scrollY will equal the amount #innerwrap has scrolled each time it scrolls
				  $("#right-inner").css('top',scrollY); // set #right-inner top property
                  $("#right-inner").css({ 'overflow-y' : 'auto', 'overflow-x' : 'hidden' });
               });
               resizing = false;
            },50); // setTimeout will run in 50 milliseconds
         }
	  }  
   });
</script>
    <![endif]-->

    <script>//console.log('passei');
      </script>
<?PHP
    } // FECHA: if ( stripos("_" . $_theme, "frameless") && !$ehXML && $_menu == "PASSEI"){  

    if (!stripos("_" . $_SERVER['PHP_SELF'], "uploadArquivos"))
      echo "      <script type=\"text/javascript\">\$(\".chosen-select\").chosen();</script>\n";
     ?>
    
  </BODY>
</HTML>
<?PHP
}
?>