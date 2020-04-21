<?PHP
$withoutMenu[] = "formImpressao.php";
include("forms.php");
if ($formulario['Impressão em paisagem'] == 't'){
//http://stackoverflow.com/questions/138422/landscape-printing-from-html
?>
<style type="text/css" media="print">
  @page { size: landscape; }
  div.page    { 
    writing-mode: tb-rl;
  }
</style>
<?PHP
}
?>
<script type='text/javascript'>
  $(function() {
    window.print();
  });
</script>


