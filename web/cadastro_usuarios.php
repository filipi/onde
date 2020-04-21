<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 0; $ehXML = 0;
$headerTitle = "Cadastro";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
/**
 * @brief generate a random login number with 8 digit as standard
 * @param $length integer the length of the string to be generated. Standard is 8
 * @return $randomString a random string with $length size.
 */
function generateRandomString($length = 8) {
  $characters = '0123456789';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

///////////////////////////////////////////////////////////////////////////////
$logo_image = "yourlogo";
$name_image = "yourlogo";
if (file_exists("./images/" . $logo_image))
echo ($organizationWebSiteURL ? "<a href=\"" . $organizationWebSiteURL . "\" target=\"_blank\">" : "") . "<img border=\"0\" src=images/" . $logo_image . " width=\"116\">" . "<img border=\"0\" src=images/" . $name_image . " width=\"156\">" . ($site_ideia_URL ? "</a>" : "") . "\n";
else{
if (file_exists("../images/" . $logo_image))
  echo ($organizationWebSiteURL ? "<a href=\"" . $organizationWebSiteURL . "\" target=\"_blank\">" : "") . "<img  border=\"0\" src=../images/" . $logo_image . ">" . ($site_ideia_URL ? "</a>" : "") . "\n";
}

if(isset($_POST['email']) && !empty($_POST['email'])){ //verify if a email was typed
  if($_POST['email'] == $_POST['emailConfirm']){ //Verify if both emails are equals
    if($_POST['passwd'] == $_POST['passwdConfirm']){ //Verify if both passwords are equals
      //Pass informations to variables
      $name = addslashes($_POST['name']);
      $lastname = addslashes($_POST['lastname']);
      $email = addslashes($_POST['email']);
      $passwd = crypt(trim($_POST['passwd']), '9$');

      //Verify if the email exists on the DB

      $query  = "SELECT login ";
      $query .= "FROM usuarios ";
      $query .= "where email = '" . $email . "';";
      $result = pg_query($conn, $query);
      $result = pg_fetch_row($result);
      $result = intval(trim($result[0]));
      if($result){
        echo "Email já cadastrado";
      } else {
        do{
          $login  = generateRandomString();
          $query  = "SELECT login ";
          $query .= "FROM usuarios ";
          $query .= "where login = '" . $login . "';";
          $result = pg_query($conn, $query);
          $result = pg_fetch_row($result);
          $result = intval(trim($result[0]));
        }
        while($result);
        
        $query  = "INSERT INTO usuarios(\n";
        $query .= "login, nome, email, sobrenome, senha, ativo) \n";
        $query .= "values(\n";
        $query .= "'" . $login      ."', \n";
        $query .= "'" . $name       ."', \n";
        $query .= "'" . $email      ."', \n";
        $query .= "'" . $lastname  ."', \n";
        $query .= "'" . $passwd     ."', \n";
        $query .= "true)\n";
        $result = pg_query($conn, $query);

        if($result){
          echo "usuario inserido com sucesso";
        } else {
          echo "Falha ao cadastrar";
        }

        $query  = "SELECT codigo FROM grupos \n";
        $query .= " WHERE nome = 'usuários';\n";
        $result = pg_query($conn, $query);
        $group  = pg_fetch_row($result);
        $group  = intval(trim($group[0]));

        if($group){
          $query = "INSERT INTO usuarios_grupos(grupo, usuario) \n";
          $query .= " VALUES (\n";
          $query .= $group . ", '";
          $query .= $login . "');\n";
          $result = pg_query($conn, $query);
        }
      }

    }else{
      echo "<div>As senhas devem coincidir.</div";

    }
  } else {
    echo "<div>Os e-mails devem coincidir.</div";
  }

  
} else { 
  header('Location: cadastro_usuarios.php');
}


?>

<form method="POST">

    <b>Nome</b> <br>
    <pre> <input type="text" class="onde" name="name"></pre>
    
    <b>Sobrenome</b> <br>
    <pre> <input type="text" class="onde" name="lastname"></pre>

    <b>E-mail</b> <br>
    <pre> <input type="email" class="onde" name="email"></pre>

    <b>Confirmação de email</b> <br>
    <pre> <input type="email" class="onde" name="emailConfirm"></pre>

    <b>Senha</b> <br>
    <pre> <input type="password" class="onde" name="passwd"></pre>
    
    <b>Confirmação de senha</b> <br>
    <pre> <input type="password" class="onde" name="passwdConfirm"></pre>

    <pre> <input type="submit" value="Enviar"></pre>
</FORM>
<?PHP
include "page_footer.inc";
?>
