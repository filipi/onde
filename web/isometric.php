<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 0;
$headerTitle = "Página de gabarito";
include "iniset.php";
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
?>
<style>
/**
 * CSS3 Isometric Text Demo v2
 */
@font-face {
	font-family: 'LeagueGothicRegular';
	src: url('http://www.midwinter-dg.com/blog_demos/css-text-shadows/fonts/league_gothic-webfont.eot');
	src: url('http://www.midwinter-dg.com/blog_demos/css-text-shadows/fonts/league_gothic-webfont.eot?iefix') format('eot'), url('http://www.midwinter-dg.com/blog_demos/css-text-shadows/fonts/league_gothic-webfont.woff') format('woff'), url('http://www.midwinter-dg.com/blog_demos/css-text-shadows/fonts/league_gothic-webfont.ttf') format('truetype'), url('http://www.midwinter-dg.com/blog_demos/css-text-shadows/fonts/league_gothic-webfont.svg#webfontIQSKTUY8') format('svg');
	font-weight: normal;
	font-style: normal;
}

@font-face {
	font-family: 'ArchitectsDaughterRegular';
	src: url('http://www.midwinter-dg.com/blog_demos/css-isometric-text/architectsdaughter-webfont.eot');
	src: url('http://www.midwinter-dg.com/blog_demos/css-isometric-text/architectsdaughter-webfont.eot?#iefix') format('embedded-opentype'), url('http://www.midwinter-dg.com/blog_demos/css-isometric-text/architectsdaughter-webfont.woff') format('woff'), url('http://www.midwinter-dg.com/blog_demos/css-isometric-text/architectsdaughter-webfont.ttf') format('truetype'), url('http://www.midwinter-dg.com/blog_demos/css-isometric-text/architectsdaughter-webfont.svg#ArchitectsDaughterRegular') format('svg');
	font-weight: normal;
	font-style: normal;
}

body {
	width: 1000px;
	color: #fff;
	position: relative;
	background-color: #2f5faf;
	background-image: url(http://www.midwinter-dg.com/blog_demos/css-isometric-text/blueprint.png);
	-webkit-font-smoothing: antialiased;
}

h1 {
	font: 80px 'LeagueGothicRegular';
	position: relative;
	top: -30px;
	left: 100px;
	color: rgba(0,0,0,0);

	-webkit-transform: skew(63deg,-26.6deg);
	-moz-transform: skew(63deg,-26.6deg);
	-o-transform: skew(63deg,-26.6deg);
	-ms-transform: skew(63deg,-26.6deg);
	transform: skew(63deg,-26.6deg);
	text-shadow: 0 0 3px rgba(0, 0, 128, 0.25);
	z-index: 50;
}

h1:after {
	content: "ISOMETRIC TEXT";
	position: absolute;
	top: 15px;
	left: 25px;
	color: rgba(255,255,255,1);

	-webkit-transform: skew(-63deg) scale(1,.5);
	-moz-transform: skew(-63deg) scale(1,.5);
	-o-transform: skew(-63deg) scale(1,.5);
	-ms-transform: skew(-63deg) scale(1,.5);
	transform: skew(-63deg) scale(1,.5);
	text-shadow: -1px -1px 1px #aaa, -2px -2px 1px #999, -3px -3px 1px #888, -4px -4px 1px #777, -5px -5px 1px #666, -6px -6px 1px #555, -7px -7px 5px rgba(0, 0, 128, 0.75);
	z-index: 100;
}

p {
	width: 460px;
	height: 220px;
	position: absolute;
	top: 180px;
	left: 430px;
	font: 14px/24px 'ArchitectsDaughterRegular';
	color: rgba(255,255,255,.35);
	text-align: center;

	-webkit-transform: skew(63deg,-26.6deg);
	-moz-transform: skew(63deg,-26.6deg);
	-o-transform: skew(63deg,-26.6deg);
	-ms-transform: skew(63deg,-26.6deg);
	transform: skew(63deg,-26.6deg);
	position: absolute;
	z-index: 50;
}
</style>
<h1>ISOMETRIC TEXT</h1>
<p>
	Here's a reworking of my original CSS3 
	Isometric Text demo, this version improves 
	on the first attempt by using the CSS :after 
	selector to duplicate the title to create 
	the shadow - in fact the title IS the shadow 
	&amp; the Isometric lettering is the :after 
	content. 
</p>

<?PHP
include "page_footer.inc";
?>
