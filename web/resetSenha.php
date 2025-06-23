<?PHP
$useSessions = 0; $ehXML = 0;
$withoutMenu[] = "resetSenha.php";
if (isset($_GET['demanda']))  $demanda = intval($_GET['demanda']);
if (isset($_GET['form'])) $form = intval($_GET['form']);
include "iniset.php";
//include "start_sessao.inc";
include "page_header.inc";
//echo "<PRE>"; var_dump($_SESSION); echo "</PRE>";

require("class.phpmailer.php");
require_once('class.html2text.inc'); 


ini_set('session.save_path',"../session_files");
session_name('pass_reset');
session_start();

$email = pg_escape_string($conn, trim($_POST['email']));
if (!$email){
  $_SESSION['greatings'] = "oi";
  $_SESSION['start'] = time();
  ?>
    <BR>
       <BR>
       <center>
       <table width=400>
       <tr>
       <th>Troca de senha</th>
       </tr>
       <tr>
       <td>
       <div class=coment>
       <center>
       <br>
       Para trocar a sua senha, preencha abaixo o e-mail que você tem cadastrado no seu perfil de usuário no <?PHP echo $SYSTEM_NAME; ?>. Um e-mail será enviado para este endereço com instruções para a recuperar o seu acesso ao sistema.<br>
       <form method=POST action=<?PHP echo basename($_SERVER['PHP_SELF']); ?>
       <input type='hidden' name='SEND' value='t'>
          <table style='border-width: 0px;'>
          <tr>
          <td style='border-width: 0px;'> 
          <div class=coment> 
          <b>Informe o endereço de e-mail associado a sua conta:</b><br>
          <input type='text' name='email' size='20'
          class "ui-input ui-widget ui-corner-all"
          STYLE="height: 28px; width: 350px"       
          <?PHP /*maxlength='12' */?> ><br>
          <br>
          <center>
          <input type='submit' value='Solicitar Nova Senha'
          CLASS="ui-button ui-widget ui-corner-all">
          </center>
          </div>
          </td>
          </tr>
          </table>
          </form>
          </center>
          </div>
          </td>
          </tr>
          </table>
          </center>
          <?PHP
          }
 else{
   if ($_SESSION['greatings'] &&  //  se a sesscao foi iniciada
       parse_url($_SERVER['HTTP_REFERER'])['path'] == $_SERVER['PHP_SELF'] &&// se veio do mesmo endereco
       (time() - $_SESSION['start']) > 2 && // se já passaram pelo menos 3 segundos do primeiro acesso
       (time() - $_SESSION['start']) < 120 // se não passaram mais que 120 segundos do primeiro acesso
       ){
     //echo "SESSAO greatings...: " . $_SESSION['teste'] . "<BR>";
     //echo "SESSAO: " . $_SESSION['PHPSESSID'] . "<BR>";
     //echo print_r(parse_url($_SERVER['HTTP_REFERER']), true). "<BR>";
     //echo print_r(pathinfo($_SERVER['HTTP_REFERER']), true). "<BR>";
     //echo dirname($_SERVER['HTTP_REFERER']). "<BR>";
     //echo basename($_SERVER['HTTP_REFERER']). "<BR>";
     //echo $_SERVER['HTTP_REFERER']. "<BR>";
     //echo parse_url($_SERVER['HTTP_REFERER'])['path'] . "<BR>";
     //echo $_SERVER['PHP_SELF']. "<BR>";
     //echo "START: " . $_SESSION['start'] . "<BR>";
     //echo "NOW: " . time() . "<BR>";

     $query = "select login, nome, email, moodle_id, ativo from usuarios where email = '" . $email . "'";
     $result = pg_exec($conn, $query);
     if ($result){
       $usuario = pg_fetch_assoc($result, 0);
     }
     $moodle_id = intval($usuario['moodle_id']);
     $nomes = explode(" ", trim($usuario['nome']));
     $primeiro_nome = ucfirst($nomes[0]);

     //echo "Moodle ID: " . $moodle_id . "<BR>\n";
     //echo "<PRE>" . $query . "</PRE>";
     //echo "Usuario: " . $usuario . "<BR>\n";
     //echo $email . "<BR>";
     
     $diferenca =  intval(time() - $_SESSION['start']);
     //echo "Difference: " . $diferenca . "<BR>";
     
     $origem = $_SERVER['REMOTE_ADDR'];
     //echo "Origem: " . $origem . "<BR>";
     $referencia = $_SERVER['HTTP_REFERER'];
     //echo "Referência: " . $referencia . "<BR>";

     $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
     $hash = hash("sha256", $email . $now->format("m-d-Y H:i:s.u"), false);
     //echo "Hash: " . $hash . "<BR>";

     $query  = "delete from reseta_senha where email = '" . $email . "' OR ";
     $query .= "  hash = '" . $hash . "'";
     $result = pg_exec($conn, $query);
     
     $query  = "insert into reseta_senha (email, hash, diferenca, referencia, origem) \n values (";
     $query .= " '" . $email . "', \n";
     $query .= " '" . $hash . "', \n";
     $query .= " " . $diferenca . ", \n";
     $query .= " '" . $referencia . "', \n";
     $query .= " '" . $origem. "') ";

     //echo "<PRE>" . $query . "</PRE>";
     $result = pg_exec($conn, $query);
     if ($result){

       $emailArray['content']  = "<P>Olá " . $primeiro_nome . ",</P>";
       $emailArray['content'] .= "<P>Alguém (provavelmente você) solicitou uma nova senha ";
       $emailArray['content'] .= "para sua conta no '" . $SYSTEM_NAME . "' </P>\n";

       if ($moodle_id && $usuario['ativo']){
         $emailArray['content'] .= "<P>Infelizmente as senhas não podem ser modificadas neste site,";
         $emailArray['content'] .= " por favor acesse ". $site_de_troca_de_sena_de_sua_organizacao . " para alterar sua senha ";
         $emailArray['content'] .= "de rede, ou entre em contato com o " . $suporte_tecnico_de_sua_organizacao . " no telefone ";
         $emailArray['content'] .= $ramal_suporte . " ou através do e-mail " . $email_suporte . ".</P>\n";
       }
       if (!$moodle_id && $usuario['ativo']){
         $emailArray['content'] .= "<P>Caso você não tenha feito essa solicitação, por favor, ";
         $emailArray['content'] .= "desconsidere este e-mail.</P>\n";
         $emailArray['content'] .= "<P>Caso contrário, por favor clique ou acesse o link abaixo para trocar a sua senha:</P>";
         $emailArray['content'] .= "<P><a href=\"" . dirname($_SERVER['HTTP_REFERER']). "/doResetPass.php?h=" . $hash . "\">";
         $emailArray['content'] .= "Clique aqui para resetar sua senha</a></P>";
         $emailArray['content'] .= "<P>Caso o link acima não esteja funcionando, acesso o seguinte endereço:</P>";
         $emailArray['content'] .= "<P>" . dirname($_SERVER['HTTP_REFERER']). "/doResetPass.php</P>";
       }

       $mail = new PHPMailer();
       $mail->From     = $email_do_remetente; // do not reply
       $mail->FromName = $nome_do_remetente; // nome do sistema
       $mail->Sender   = $system_mail_from;
       $mail->HostName = $system_mail_host;
       $mail->Host     = $system_mail_host;
       $mail->Port     = $system_mail_port;
       $mail->Mailer   = $system_mail_mailer;
       if (isset($system_mail_user) && $system_mail_user && isset($system_mail_password)){
         $mail->SMTPAuth = true; 
 		 $mail->Username  = $system_mail_user;
         $mail->Password  = $system_mail_password;
	   }

	   
       $mail->CharSet = $encoding;
       $assunto  = "[ONDE] Solicitação de alteração de senha ";
       $mail->Subject  = stripAccents($assunto);
       $mail->Body    = str_replace(" src=\"logo.png\"", " src=\"cid:1272542224.13304.4.camel@brainstorm\"", $emailArray['content']);
       $h2t = new \Html2Text\Html2Text($emailArray['content']);
        $emailArray['text'] = $h2t->gettext(); 

       $mail->AltBody = $emailArray['text'];
       //$mail->AddAddress("filipi@...", $recipient['nome'] );
       $mail->AddAddress($email, $recipient['nome'] );
       if (file_exists("logo.png"))
         $mail->AddEmbeddedImage('logo.png', '1272542224.13304.4.camel@brainstorm', 'logo.png');
       else
         $mail->AddEmbeddedImage('/var/www/scripts/logo.png', '1272542224.13304.4.camel@brainstorm', 'logo.png');
       if ($usuario){
         if(!$mail->Send()) 
	   echo "ERRO - Erro ao enviar messagem para " . $recipient['nome'] . "(" . $recipient['email'] . ")\n";
       }
	 
       //else 
       //echo $recipient['nome'] . "(" . $recipient['email'] . ") OK!\n";
       // Clear all addresses and attachments for next loop
       $mail->ClearAddresses();
       $mail->ClearAttachments();

       echo "<center><table width=400><tr><td><div class=coment><center><br>";    
       echo "<H2>Pronto</H2>";
       echo "<P>Um e-mail com instruções sobre como redefinir sua senha foi enviado para o endereço que você forneceu.</P2><BR>";
       echo "</center></div></td></tr></table></center>\n";

       echo "<center><a href=\"frm_login.php\">Efetuar login</a>";


       //echo "<a href=\"" . dirname($_SERVER['HTTP_REFERER']). "/doResetPass.php?h=" . $hash . "\">";
       //echo "Clique aqui para resetar sua senha</a><BR>";
       //echo dirname($_SERVER['HTTP_REFERER']). "/doResetPass.php<BR>";
     }
     else
       echo pg_last_error();
   }
   else{
     echo "ERRO!<BR>";
   }   
 }
include "page_footer.inc";
?>
