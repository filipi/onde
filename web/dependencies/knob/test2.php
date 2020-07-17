<?php
session_start();

if(isset($_SESSION['views']))
$_SESSION['views']=$_SESSION['views']+1;
else
$_SESSION['views']=1;
$dados['views'] = $_SESSION['views'];
echo json_encode($dados);
?>
