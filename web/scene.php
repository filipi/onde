<?PHP
//////////////////////// Tratar todos os GET aqui para eviter injecao de codigo
///////////////////////////////////////////////////////////////// Tratando POST
/////////////////////////////////////////////// GET passado para links (action)
///////////////////////////////////////////////////////////////////// Cabecalho
$useSessions = 1; $ehXML = 0;
$headerTitle = "Editor de cenas";
include "iniset.php";
include "page_header.inc";
//////////////////////////////////////////////////////////////// Funcoes locais
///////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// Finaliza solicitacao
//////////////////////////////////////////////////////////// remove solicitacao
////////////////////////////////////////////////// Carrega solicitacao desejada
////////////////////////////////////////////////////////////// Monta formulario

$teste =  "teste \"";
echo "<FORM METHOD=POST>";

echo $_POST['teste'] . "<BR>\n";
echo "<PRE>";
echo stripslashes($_POST['teste']) . "\n";

echo htmlentities($_POST['teste']) . "\n";
echo htmlentities(htmlspecialchars($_POST['teste'], ENT_QUOTES, ISO-8859-1)) . "\n";
echo htmlentities(htmlspecialchars_decode($_POST['teste'], ENT_QUOTES)) . "\n";
echo pg_escape_string($_POST['teste']) . "\n";
echo "</PRE>";

echo "<textarea name=teste ROWS=30 COLS=100>\n";

echo $_POST['teste'] . "\n";

echo htmlspecialchars_decode($_POST['teste'], ENT_QUOTES) . "\n";
echo htmlspecialchars($_POST['teste'], ENT_QUOTES, ISO-8859-1) . "\n";
echo pg_escape_string($_POST['teste']) . "\n";

echo "</textarea><BR>\n";

echo "<INPUT TYPE=SUBMIT><BR>";

?>
<input type="text" name="teste2" id="f_date_teste2" value=""><button type="reset" id="f_trigger_teste2; ?>">...</button><script type="text/javascript">
   Calendar.setup({
     inputField     :    "f_date_teste2",      // id of the input field
	 ifFormat       :    "%d/%m/%Y",       // format of the input field
	 showsTime      :    false,            // will display a time selector
	 button         :    "f_trigger_teste2",   // trigger for the calendar (button ID)
	 singleClick    :    false,           // double-click mode
	 step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	 });
</script><BR><BR>
<?PHP

echo "</FORM>";
?>

		<link rel="stylesheet" href="dependencies/three.js/editor/css/main.css">

		<script src="dependencies/three.js/build/three.js"></script>

		<script src="dependencies/three.js/examples/js/controls/TransformControls.js"></script>

		<script src="dependencies/three.js/examples/js/libs/chevrotain.min.js"></script> <!-- VRML -->
		<script src="dependencies/three.js/examples/js/libs/jszip.min.js"></script>
		<script src="dependencies/three.js/examples/js/libs/inflate.min.js"></script> <!-- FBX -->

		<script src="dependencies/three.js/examples/js/loaders/AMFLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/ColladaLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/DRACOLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/FBXLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/GLTFLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/deprecated/LegacyGLTFLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/KMZLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/MD2Loader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/OBJLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/MTLLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/PLYLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/STLLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/SVGLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/TGALoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/TDSLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/VRMLLoader.js"></script>
		<script src="dependencies/three.js/examples/js/loaders/VTKLoader.js"></script>

		<script src="dependencies/three.js/examples/js/exporters/ColladaExporter.js"></script>
		<script src="dependencies/three.js/examples/js/exporters/GLTFExporter.js"></script>
		<script src="dependencies/three.js/examples/js/exporters/OBJExporter.js"></script>
		<script src="dependencies/three.js/examples/js/exporters/STLExporter.js"></script>

		<script src="dependencies/three.js/examples/js/renderers/Projector.js"></script>
		<script src="dependencies/three.js/examples/js/renderers/RaytracingRenderer.js"></script>
		<script src="dependencies/three.js/examples/js/renderers/SVGRenderer.js"></script>

		<link rel="stylesheet" href="dependencies/three.js/editor/js/libs/codemirror/codemirror.css">
		<link rel="stylesheet" href="dependencies/three.js/editor/js/libs/codemirror/theme/monokai.css">
		<script src="dependencies/three.js/editor/js/libs/codemirror/codemirror.js"></script>
		<script src="dependencies/three.js/editor/js/libs/codemirror/mode/javascript.js"></script>
		<script src="dependencies/three.js/editor/js/libs/codemirror/mode/glsl.js"></script>

		<script src="dependencies/three.js/editor/js/libs/system.min.js"></script>
		<script src="dependencies/three.js/editor/js/libs/esprima.js"></script>
		<script src="dependencies/three.js/editor/js/libs/jsonlint.js"></script>
		<script src="dependencies/three.js/editor/js/libs/glslprep.min.js"></script>

		<link rel="stylesheet" href="dependencies/three.js/editor/js/libs/codemirror/addon/dialog.css">
		<link rel="stylesheet" href="dependencies/three.js/editor/js/libs/codemirror/addon/show-hint.css">
		<link rel="stylesheet" href="dependencies/three.js/editor/js/libs/codemirror/addon/tern.css">
		<script src="dependencies/three.js/editor/js/libs/codemirror/addon/dialog.js"></script>
		<script src="dependencies/three.js/editor/js/libs/codemirror/addon/show-hint.js"></script>
		<script src="dependencies/three.js/editor/js/libs/codemirror/addon/tern.js"></script>
		<script src="dependencies/three.js/editor/js/libs/acorn/acorn.js"></script>
		<script src="dependencies/three.js/editor/js/libs/acorn/acorn_loose.js"></script>
		<script src="dependencies/three.js/editor/js/libs/acorn/walk.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/polyfill.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/signal.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/tern.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/def.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/comment.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/infer.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ternjs/doc_comment.js"></script>
		<script src="dependencies/three.js/editor/js/libs/tern-threejs/threejs.js"></script>

		<script src="dependencies/three.js/editor/js/libs/signals.min.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ui.js"></script>
		<script src="dependencies/three.js/editor/js/libs/ui.three.js"></script>

		<script src="dependencies/three.js/editor/js/libs/html2canvas.js"></script>
		<script src="dependencies/three.js/editor/js/libs/three.html.js"></script>

		<script src="dependencies/three.js/editor/js/libs/app.js"></script>
		<script src="dependencies/three.js/editor/js/Player.js"></script>
		<script src="dependencies/three.js/editor/js/Script.js"></script>

		<script src="dependencies/three.js/examples/js/vr/WebVR.js"></script>

		<script src="dependencies/three.js/editor/js/EditorControls.js"></script>
		<script src="dependencies/three.js/editor/js/Storage.js"></script>

		<script src="dependencies/three.js/editor/js/Editor.js"></script>
		<script src="dependencies/three.js/editor/js/Config.js"></script>
		<script src="dependencies/three.js/editor/js/History.js"></script>
		<script src="dependencies/three.js/editor/js/Loader.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.File.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.Edit.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.Add.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.Play.js"></script>
		<!-- <script src="dependencies/three.js/editor/js/Menubar.View.js"></script> -->
		<script src="dependencies/three.js/editor/js/Menubar.Examples.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.Help.js"></script>
		<script src="dependencies/three.js/editor/js/Menubar.Status.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Scene.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Project.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Settings.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Settings.Shortcuts.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Settings.Viewport.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Properties.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Object.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.Geometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.BufferGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.Modifiers.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.BoxGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.CircleGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.CylinderGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.DodecahedronGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.ExtrudeGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.IcosahedronGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.OctahedronGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.PlaneGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.RingGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.SphereGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.ShapeGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.TetrahedronGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.TorusGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.TorusKnotGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.TubeGeometry.js"></script>
		<script src="dependencies/three.js/examples/js/geometries/TeapotBufferGeometry.js"></script>
		
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.TeapotBufferGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Geometry.LatheGeometry.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Material.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Animation.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.Script.js"></script>
		<script src="dependencies/three.js/editor/js/Sidebar.History.js"></script>
		<script src="dependencies/three.js/editor/js/Strings.js"></script>
		<script src="dependencies/three.js/editor/js/Toolbar.js"></script>
		<script src="dependencies/three.js/editor/js/Viewport.js"></script>
		<script src="dependencies/three.js/editor/js/Viewport.Camera.js"></script>
		<script src="dependencies/three.js/editor/js/Viewport.Info.js"></script>

		<script src="dependencies/three.js/editor/js/Command.js"></script>
		<script src="dependencies/three.js/editor/js/commands/AddObjectCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/RemoveObjectCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/MoveObjectCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetPositionCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetRotationCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetScaleCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetValueCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetUuidCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetColorCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetGeometryCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetGeometryValueCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/MultiCmdsCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/AddScriptCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/RemoveScriptCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetScriptValueCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetMaterialCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetMaterialColorCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetMaterialMapCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetMaterialValueCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetMaterialVectorCommand.js"></script>
		<script src="dependencies/three.js/editor/js/commands/SetSceneCommand.js"></script>

		<script>

			window.URL = window.URL || window.webkitURL;
			window.BlobBuilder = window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder;

			Number.prototype.format = function (){
				return this.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
			};

			//

			var editor = new Editor();

			var viewport = new Viewport( editor );
			document.body.appendChild( viewport.dom );

			var toolbar = new Toolbar( editor );
			document.body.appendChild( toolbar.dom );

			var script = new Script( editor );
			document.body.appendChild( script.dom );

			var player = new Player( editor );
			document.body.appendChild( player.dom );

			var sidebar = new Sidebar( editor );
			document.body.appendChild( sidebar.dom );

			var menubar = new Menubar( editor );
			document.body.appendChild( menubar.dom );

			//

			editor.storage.init( function () {

				editor.storage.get( function ( state ) {

					if ( isLoadingFromHash ) return;

					if ( state !== undefined ) {

						editor.fromJSON( state );

					}

					var selected = editor.config.getKey( 'selected' );

					if ( selected !== undefined ) {

						editor.selectByUuid( selected );

					}

				} );

				//

				var timeout;

				function saveState( scene ) {

					if ( editor.config.getKey( 'autosave' ) === false ) {

						return;

					}

					clearTimeout( timeout );

					timeout = setTimeout( function () {

						editor.signals.savingStarted.dispatch();

						timeout = setTimeout( function () {

							editor.storage.set( editor.toJSON() );

							editor.signals.savingFinished.dispatch();

						}, 100 );

					}, 1000 );

				};

				var signals = editor.signals;

				signals.geometryChanged.add( saveState );
				signals.objectAdded.add( saveState );
				signals.objectChanged.add( saveState );
				signals.objectRemoved.add( saveState );
				signals.materialChanged.add( saveState );
				signals.sceneBackgroundChanged.add( saveState );
				signals.sceneFogChanged.add( saveState );
				signals.sceneGraphChanged.add( saveState );
				signals.scriptChanged.add( saveState );
				signals.historyChanged.add( saveState );

			} );

			//

			document.addEventListener( 'dragover', function ( event ) {

				event.preventDefault();
				event.dataTransfer.dropEffect = 'copy';

			}, false );

			document.addEventListener( 'drop', function ( event ) {

				event.preventDefault();

				editor.loader.loadFiles( event.dataTransfer.files );

			}, false );

			function onWindowResize( event ) {

				editor.signals.windowResize.dispatch();

			}

			window.addEventListener( 'resize', onWindowResize, false );

			onWindowResize();

			//

			var isLoadingFromHash = false;
			var hash = window.location.hash;

			if ( hash.substr( 1, 5 ) === 'file=' ) {

				var file = hash.substr( 6 );

				if ( confirm( 'Any unsaved data will be lost. Are you sure?' ) ) {

					var loader = new THREE.FileLoader();
					loader.crossOrigin = '';
					loader.load( file, function ( text ) {

						editor.clear();
						editor.fromJSON( JSON.parse( text ) );

					} );

					isLoadingFromHash = true;

				}

			}

			// ServiceWorker

			if ( 'serviceWorker' in navigator ) {

				try {

					navigator.serviceWorker.register( 'sw.js' );

				} catch ( error ) {

				}

			}

			/*
			window.addEventListener( 'message', function ( event ) {

				editor.clear();
				editor.fromJSON( event.data );

			}, false );
			*/

		</script>
<?PHP
include "page_footer.inc";
?>
