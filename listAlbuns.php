<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)

///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1;
$ehXML = 1;
$headerTitle = "";
$myPATH = ini_get('include_path') . ':./include:../include:../../include';
ini_set('include_path', $myPATH);
include "light_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////

$query = "SELECT codigo, \"Álbum\" FROM albuns WHERE \"Álbum\" != 'Perfil';";
$result = pg_query($conn, $query);
$albuns = pg_fetch_all($result);

echo json_encode($albuns);



/* echo "<PRE>";
print_r($albuns);
echo "</PRE>";
 */

include "page_footer.inc";
