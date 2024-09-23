<?PHP
  /**
   * $Id: json-cube.php,v 1.8 2014/02/04 18:32:59 filipi Exp $
   * á
   */
$useSessions = 1; $ehXML = 1;
include "iniset.php";
include "page_header.inc";

$action = "list";
if ($_POST['anim']){
  $action = "insert";
  $observacao = pg_escape_string($_POST['observacao']);
}
if ($_POST['codigo']){
  $action = "update";
  $codigo = intval($_POST['codigo']);
}
if ($_GET['codigo']){
 $action = "load";
 $codigo = intval($_GET['codigo']);
}

switch ($action){
 case "load":
   if ($codigo){
     $query = "select \"Conteúdo\" as conteudo from framescubo where codigo = " . $codigo;
     $result = pg_exec ($conn, $query);
     $row = pg_fetch_row($result, 0);

     $dados =  preg_replace("/(\r\n|\n\r|\n|\r)/", ',', $row[0]);
     $dataArray = explode(",", $dados);
     $key = 0;
     for ($frame = 0; $frame < 16; $frame++){
       for ($line = 0; $line < 8; $line++){
	 for ($plane = 0; $plane < 8; $plane++){
	   $dataItem = $dataArray[$key];          
	   for ($voxel = 0; $voxel < 8; $voxel++){
	     $anim[$frame]['frame'][$line]['line'][$plane]['plane'][$voxel]['voxel'] = ($dataItem & 1) ? true : false;
	     $dataItem = $dataItem >> 1;
	   }
	   $key++;
	 }
       }
     }
     $anim = json_encode($anim);
     //$anim = str_replace("]", "]\n", $anim);
     //$anim = str_replace("}", "}\n", $anim);
     //$anim = str_replace("{", "{\n  ", $anim);
     echo $anim;
   }
   break;
 case "list":
   $query = "select codigo, nome as observacao from framescubo order by nome";
   $result = pg_exec ($conn, $query);
   $frames  = pg_fetch_all ($result);
   $frames =  json_encode($frames);
   echo $frames;
   break;
 case "insert":
   $query  = "INSERT INTO framescubo (usuario, nome, \"Conteúdo\") VALUES (";
   $query .= "'" . $_SESSION['matricula'] . "',";
   $query .= "'" . $observacao . "','";
   foreach ($_POST['anim'] as $frame => $frames){
     foreach ($frames as $line => $lines){
       foreach ($lines as $plane => $planes){
         //foreach ($planes as $key => $voxel){                     
           //$query .= "[" . $frame . "][" . $line . "][" . $plane . "][" . $key . "] = " . $voxel . "\n";
	 //}
         $numero = 0;
         for ($i = 7; $i>=0; $i--){
           $numero = ($numero << 1) + ($planes[$i]=='true' ? 1 : 0 );
	 }
	 $query .= $numero . "\n";
       }
     }
   }
   $query  .= "')";
   $result = pg_exec($conn, $query);
   if ($result)
     echo "Salvo com sucesso";
   else{
     echo pg_last_error();
   }
   break;
 case "update":
   $query  = "UPDATE framescubo SET";
   $query .= "  usuario = '" . $_SESSION['matricula'] . "',\n";
   $query .= "  nome = '" . $observacao . "',\n";
   $query .= "  \"Conteúdo\" = '";
   foreach ($_POST['anim'] as $frame => $frames){
     foreach ($frames as $line => $lines){
       foreach ($lines as $plane => $planes){
         $numero = 0;
         for ($i = 7; $i>=0; $i--){
           $numero = ($numero << 1) + ($planes[$i]=='true' ? 1 : 0 );
	 }
	 $query .= $numero . "\n";
       }
     }
   }
   $query  .= "'\n";
   $query  .= "  WHERE codigo = " . $codigo;
   $result = pg_exec($conn, $query);
   if ($result)
     echo "Salvo com sucesso";
   else{
     echo pg_last_error();
   }
   break;
 }
include "page_footer.inc";
?>
