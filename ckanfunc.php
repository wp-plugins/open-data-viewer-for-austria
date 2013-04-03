<?php
/*
Plugin Name: Open Data Viewer for Austria
Plugin URI: http://apps4austria-open-data.ondics.de
Description: Open Data Viewer for Austria brings the full power of open data from Austria to your Wordpress site.
Version: 1.0.7
Author: Ondics GmbH
Author URI: http://ondics.de
License: None
*/

global $NumberOfGrids;

// Load languages
function plugin_init() {
	load_plugin_textdomain( 'wpckan', false, plugin_dir_path( __FILE__ ) . '/languages/' );
}
add_action('plugins_loaded', 'plugin_init');


function handleAdminMenu() {
    // Add the Tag-Assistent on blogpages and sites in the adminmenu
    add_meta_box('ckanMetaBox', 'OpenData CKAN Viewer Austria - Tag Assistant', 'insertForm', 'post', 'normal');
    add_meta_box('ckanMetaBox', 'OpenData CKAN Viewer Austria - Tag Assistant', 'insertForm', 'page', 'normal');
}
add_action('admin_menu', 'handleAdminMenu');

function insertForm() {
  include_once (plugin_dir_path( __FILE__ ) . "/formularshortcode.php");
}

// ShortCode 'ckan'
add_shortcode('ckan', 'addCKANView');
function addCKANView ($attr, $content) {
	global $NumberOfGrids;
	if (!empty($NumberOfGrids)) $NumberOfGrids ++; else $NumberOfGrids = 1;
     $attr = shortcode_atts(array(
		'url' => '',
		'type' => 'table',
		'filters' => '',
		'width' => null,
		'height' => 150,
		'hiddencolumns' => '',
		'columnswidth' => '',
		'columnsorder' => '',
		'autozoom' => "true",
		'latfield' => '',
		'lonfield' => '',
		'geomfield' => null,
		'cluster' => "false",
		'graphtype' => "",
		'series' => "",
		'group' => "",
		'metaurl' => "",
		'metafields' => "",
		"format" => ""

	), $attr);

	// Array of options for the viewers on the website
	$aryOptions = array();
	switch($attr['type']) {
		case "grid":
			$strWidth = $attr["columnswidth"];
			if (!empty($attr["columnswidth"])) {
				$strWidth = json_decode("[" . str_replace("'", '"', $attr["columnswidth"]) . "]", true);
			}
			$aryOptions = array(
				"hiddenColumns" => explode(",", $attr["hiddencolumns"]),
				"columnsOrder" => explode(",", $attr["columnsorder"]),
				"columnsWidth" => $strWidth,
				"format" => $attr["format"]
			);
		break;
		case "graph":
			$aryOptions = array(
				"graphType" => $attr["graphtype"],
				"group" => $attr["group"],
				"series" => explode(",", $attr["series"]),
				"format" => $attr["format"]
			);
		break;
		case "map":
			$aryOptions = array(
				"geomField" => $attr["geomfield"],
				"lonField" => $attr["lonfield"],
				"latField" => $attr["latfield"],
				"autoZoom" => $attr["autozoom"],
				"cluster" => $attr["cluster"],
				"format" => $attr["format"]
			);
		break;
		case "metadata":
			$aryOptions = array(
				"metafields" => $attr["metafields"],
				"metaurl" => $attr["metaurl"],
				"format" => $attr["format"]
			);
		break;
	}

	$strOptionsJson = json_encode($aryOptions);
	$str = '
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			wpCKANReclineViewer.createDataViewer(".data-explorer-here' . $NumberOfGrids . '", "' . urlencode(str_replace("&amp;","&",$attr['url'])) . '&ispost=1&id=' . get_the_ID() . '", "' . urlencode(str_replace("&amp;","&",$attr['metaurl'])) . '&ispost=1&id=' . get_the_ID() . '", "' . $attr['type'] . '", ' . $strOptionsJson . ', "' . $attr['filters'] . '", "' . plugins_url("/proxy.php?url=", __FILE__) . '", "' . $attr['height'] . '", "' . $attr['width'] . '");
				$.ajax({
					url: "' . $attr["metaurl"] . '",
					dataType: "json"
				}).done(function (res) {
					var str = "<b>" + res["title"] + "</b><br />";
					str += "Beschreibung: " + res["notes"] + "<br />";
					str += "Autor: " + res["extras"]["publisher"] + "<br />";
					str += "&copy; " + res["extras"]["license_citation"] + "";
					$("a.csstooltip' . $NumberOfGrids . ' > span").html(str);
				})
		});
	</script>
	<div class="data-explorer-here' . $NumberOfGrids . '" style="width:' . $attr['width'] . 'px;"></div><div style="clear: both;">
	<div id="" valign="top" style="font-size: 11px; margin-top:0px; padding-top:0px;">';
	if (!empty($attr["metaurl"])) {
		$str .= '<a href="'. $attr["metaurl"] .'" class="csstooltip' . $NumberOfGrids . ' csstooltipcls">' . (!empty($content) ? $content : __("Informationen.", "wpckan")) . '<span>' . __("Keine Informationen.", "wpckan") . '</span></a>';
	}
	$str .= '</div></div>';
	return $str;
}

// loading popup script
add_action('init', 'init_thickbox');
function init_thickbox() {
		add_thickbox();
}

function add_recline_scripts_admin($hook) {
	if ($hook == "edit.php" || $hook == "widgets.php" || $hook == "post.php" || $hook == "post-new.php") { // enqueue only if admin on editing page oder widget page
		$browser = browser_info();
		$path = plugins_url( '/recline/' , __FILE__ );

		wp_enqueue_script('wpCKANDataViewerAdmin', plugins_url("/handleform.js", __FILE__));
		wp_enqueue_script( 'wpckan-less', plugins_url("/less-1.3.3.min.js", __FILE__) );

		// Enqueue Styles
		echo '<link rel="stylesheet/less" type="text/css" href="' . $path . "vendor/bootstrap/2.0.2/css/bootstrap.css" . '">';
		wp_enqueue_style( 'wpckanstyle-leaflet', 	$path . "vendor/leaflet/0.4.4/leaflet.css");
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_style( 'wpckanstyle-leaflet-ie', 	$path . "vendor/leaflet/0.4.4/leaflet.ie.css");
		wp_enqueue_style( 'wpckanstyle-marker', 	$path . "vendor/leaflet.markercluster/MarkerCluster.css");
		wp_enqueue_style( 'wpckanstyle-marker-default', $path . "vendor/leaflet.markercluster/MarkerCluster.Default.css");
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_style( 'wpckanstyle-marker-ie', 	$path . "vendor/leaflet.markercluster/MarkerCluster.Default.ie.css");
		wp_enqueue_style( 'wpckanstyle-slickgrid', 	$path . "vendor/slickgrid/2.0.1/slick.grid.css");
		wp_enqueue_style( 'wpckanstyle-grid', 		$path . "css/grid.css");
		wp_enqueue_style( 'wpckanstyle-slickgrid-css', 	$path . "css/slickgrid.css");
		wp_enqueue_style( 'wpckanstyle-graph', 		$path . "css/graph.css");
		wp_enqueue_style( 'wpckanstyle-map', 		$path . "css/map.css");
		wp_enqueue_style( 'wpckanstyle-multiview',	$path . "css/multiview.css");

		// Enqueue Scripts
		wp_enqueue_script( 'wpckan-jQuery', $path . "vendor/jquery/1.7.1/jquery.js");
		wp_enqueue_script( 'wpckan-backbone', $path . "vendor/backbone/0.9.2/backbone.js");
		wp_enqueue_script( 'wpckan-mustache', $path . "vendor/mustache/0.5.0-dev/mustache.js");
		wp_enqueue_script( 'wpckan-boostrap', $path . "vendor/bootstrap/2.0.2/bootstrap.js");
		wp_enqueue_script( 'wpckan-flotr2', $path . "vendor/flotr2/flotr2.js" );
		wp_enqueue_script( 'wpckan-leaflet', $path . "vendor/leaflet/0.4.4/leaflet.js" );
		wp_enqueue_script( 'wpckan-leaflet-marker', $path . "vendor/leaflet.markercluster/leaflet.markercluster.js" );
		wp_enqueue_script( 'wpckan-slickgrid-jqueryui', $path . "vendor/slickgrid/2.0.1/jquery-ui-1.8.16.custom.min.js" );
		wp_enqueue_script( 'wpckan-slickgrid-eventdrag', $path . "vendor/slickgrid/2.0.1/jquery.event.drag-2.0.min.js" );
		wp_enqueue_script( 'wpckan-slickgrid', $path . "vendor/slickgrid/2.0.1/slick.grid.min.js" );
		if(isset($browser["msie"]) && $browser["msie"] < 8)
			wp_enqueue_script( 'wpckan-json2', $path . "vendor/json/json2.js" );
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_script( 'wpckan-flashcanvas', $path . "vendor/FlashCanvas/bin/flashcanvas.js" );
		wp_enqueue_script( 'wpckan-ecma-fixes', $path . "src/ecma-fixes.js" );
		wp_enqueue_script( 'wpckan-model', $path . "src/model.js" );
		wp_enqueue_script( 'wpckan-backend-memory', $path . "src/backend.memory.js" );
		wp_enqueue_script( 'wpckan-backend-csv', $path . "src/backend.csv.js" );
		wp_enqueue_script( 'wpckan-backend-geojson', $path . "src/backend.geojson.js" );

		wp_enqueue_script( 'wpckan-view-grid', $path . "src/view.grid.js" );
		wp_enqueue_script( 'wpckan-view-slickgrid', $path . "src/view.slickgrid.js" );
		wp_enqueue_script( 'wpckan-view-transform', $path . "src/view.transform.js" );
		wp_enqueue_script( 'wpckan-data-transform', $path . "src/data.transform.js" );
		wp_enqueue_script( 'wpckan-view-graph', $path . "src/view.graph.js" );
		wp_enqueue_script( 'wpckan-view-map', $path . "src/view.map.js" );
		wp_enqueue_script( 'wpckan-view-pager', $path . "src/widget.pager.js" );
		wp_enqueue_script( 'wpckan-view-queryeditor', $path . "src/widget.queryeditor.js" );
		wp_enqueue_script( 'wpckan-view-filtereditor', $path . "src/widget.filtereditor.js" );
		wp_enqueue_script( 'wpckan-view-fields', $path . "src/widget.fields.js" );
		wp_enqueue_script( 'wpckan-multiview', $path . "src/view.multiview.js" );
		wp_enqueue_script( 'wpckan-app', $path . "initRecline.js");
		wp_enqueue_script('wpckan-underscore', plugins_url("/underscore.js", __FILE__), array("underscore")); // to solve the conflict with the media uploader and recline, don't ask why
	}
}
add_action('admin_enqueue_scripts', 'add_recline_scripts_admin');

function add_recline_scripts_wp() {
		$browser = browser_info();
		$path = plugins_url( '/recline/' , __FILE__ );
		wp_enqueue_style( 'wpckanstyle-map', 		plugins_url("/tooltip.css", __FILE__));

		wp_enqueue_script( 'wpckan-less', plugins_url("/less-1.3.3.min.js", __FILE__) );

		// Enqueue Styles
		wp_enqueue_style( 'wpckanstyle-leaflet', 	$path . "vendor/leaflet/0.4.4/leaflet.css");
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_style( 'wpckanstyle-leaflet-ie', 	$path . "vendor/leaflet/0.4.4/leaflet.ie.css");
		wp_enqueue_style( 'wpckanstyle-marker', 	$path . "vendor/leaflet.markercluster/MarkerCluster.css");
		wp_enqueue_style( 'wpckanstyle-marker-default', $path . "vendor/leaflet.markercluster/MarkerCluster.Default.css");
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_style( 'wpckanstyle-marker-ie', 	$path . "vendor/leaflet.markercluster/MarkerCluster.Default.ie.css");
		wp_enqueue_style( 'wpckanstyle-slickgrid', 	$path . "vendor/slickgrid/2.0.1/slick.grid.css");
		wp_enqueue_style( 'wpckanstyle-grid', 		$path . "css/grid.css");
		wp_enqueue_style( 'wpckanstyle-slickgrid-css', 	$path . "css/slickgrid.css");
		wp_enqueue_style( 'wpckanstyle-graph', 		$path . "css/graph.css");
		wp_enqueue_style( 'wpckanstyle-map', 		$path . "css/map.css");

		// Enqueue Scripts
		wp_enqueue_script( 'wpckan-jQuery', $path . "vendor/jquery/1.7.1/jquery.js");
		wp_enqueue_script( 'wpckan-backbone', $path . "vendor/backbone/0.9.2/backbone.js", array("underscore"));
		wp_enqueue_script( 'wpckan-mustache', $path . "vendor/mustache/0.5.0-dev/mustache.js");
		wp_enqueue_script( 'wpckan-flotr2', $path . "vendor/flotr2/flotr2.js" );
		wp_enqueue_script( 'wpckan-leaflet', $path . "vendor/leaflet/0.4.4/leaflet.js" );
		wp_enqueue_script( 'wpckan-leaflet-marker', $path . "vendor/leaflet.markercluster/leaflet.markercluster.js" );
		wp_enqueue_script( 'wpckan-slickgrid-jqueryui', $path . "vendor/slickgrid/2.0.1/jquery-ui-1.8.16.custom.min.js" );
		wp_enqueue_script( 'wpckan-slickgrid-eventdrag', $path . "vendor/slickgrid/2.0.1/jquery.event.drag-2.0.min.js" );
		wp_enqueue_script( 'wpckan-slickgrid', $path . "vendor/slickgrid/2.0.1/slick.grid.min.js" );
		if(isset($browser["msie"]) && $browser["msie"] < 8)
			wp_enqueue_script( 'wpckan-json2', $path . "vendor/json/json2.js" );
		if(isset($browser["msie"]) && $browser["msie"] < 9)
			wp_enqueue_script( 'wpckan-flashcanvas', $path . "vendor/FlashCanvas/bin/flashcanvas.js" );
		wp_enqueue_script( 'wpckan-ecma-fixes', $path . "src/ecma-fixes.js" );
		wp_enqueue_script( 'wpckan-model', $path . "src/model.js" );
		wp_enqueue_script( 'wpckan-backend-memory', $path . "src/backend.memory.js" );
		wp_enqueue_script( 'wpckan-backend-csv', $path . "src/backend.csv.js" );
		wp_enqueue_script( 'wpckan-backend-geojson', $path . "src/backend.geojson.js" );

		wp_enqueue_script( 'wpckan-view-grid', $path . "src/view.grid.js" );
		wp_enqueue_script( 'wpckan-view-slickgrid', $path . "src/view.slickgrid.js" );
		wp_enqueue_script( 'wpckan-view-transform', $path . "src/view.transform.js" );
		wp_enqueue_script( 'wpckan-data-transform', $path . "src/data.transform.js" );
		wp_enqueue_script( 'wpckan-view-graph', $path . "src/view.graph.js" );
		wp_enqueue_script( 'wpckan-view-map', $path . "src/view.map.js" );
		wp_enqueue_script( 'wpckan-multiview', $path . "src/view.multiview.js" );
		wp_enqueue_script( 'wpckan-app', $path . "initRecline.js");
		wp_enqueue_script('wpckan-underscore', plugins_url("/underscore.js", __FILE__), array("underscore")); // to solve the conflict with the media uploader and recline, don't ask why
}
add_action('wp_head', 'add_recline_scripts_wp');


// To get the Current Browser and Version
function browser_info($agent=null) {
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko');

  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

  if (!preg_match_all($pattern, $agent, $matches)) return array();

  $i = count($matches['browser'])-1;
  return array($matches['browser'][$i] => $matches['version'][$i]);
}


// #################WIDGET Code###########################
class CkanWidget extends WP_Widget
{
  function __construct()
  {
    $widget_ops = array('classname' => 'CkanWidget', 'description' => 'Open Data Viewer for Austria' );
	$control_ops = array('width' => 400);
    parent::__construct('CkanWidget', 'Open Data Viewer for Austria', $widget_ops, $control_ops);
  }

  // Formular aufbauen,
  // @param instance Gespeicherte Werte.
  function form($instance) {
    $instance = wp_parse_args((array) $instance, array( // Default Werte setzten
		'combokat' => '',
		'combods' => '',
		'combores' => '',
		'content' => '',
		'height' => 150,
		'type' => 'grid',
		'opentype' => 'popup',
		'url' => '',
		'metaurl' => "",
		'filters' => '',
		'grid' => '',
		'graph' => '',
		'map' => '',
		'metafields' => '',
		'format' => '',
		'transform' => ''
	));
	include (plugin_dir_path( __FILE__ ) . "/formularwidget.php");
  }

  // Speichern der Einstellung
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['combokat'] = $new_instance['combokat'];
	$instance['combods'] = $new_instance['combods'];
	$instance['combores'] = $new_instance['combores'];
	$instance['content'] = $new_instance['content'];
	$instance['height'] = $new_instance['height'];
	$instance['type'] = $new_instance['type'];
	$instance['url'] = $new_instance['url'];
	$instance['metaurl'] = $new_instance['metaurl'];
	$instance['opentype'] = $new_instance['opentype'];
	$instance['filters'] = $new_instance['filters'];
	$instance['grid'] = $new_instance['grid'];
	$instance['graph'] = $new_instance['graph'];
	$instance['map'] = $new_instance['map'];
	$instance['metafields'] = $new_instance['metafields'];
	$instance['format'] = $new_instance['format'];
    return $instance;
  }

  // Anzeige
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
    $title = (empty($instance['content']) ? ' ' : apply_filters('widget_title', $instance['content']));
	echo $before_widget;
    if (!empty($title))
      echo $before_title . $title . $after_title;

	echo '<div class="' . $this->get_field_id( 'data-explorer-here' ) . '" style="margin-top:10px;"></div><div style="clear: both;"></div>';

	if($instance['type'] != "metadata") {  // Link "Vergrößern"
		if ($instance['opentype'] == "popup")
			echo '<div class="' . $this->get_field_id( 'ZoomLink' ) . '"><a href="#TB_inline?height=550&width=950&inlineId=' . $this->get_field_id( 'ZoomPopup2' ) . '" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline">Vergrößern</a></div>';
		else
			echo '<div class=""><a href="' . plugins_url( '/' , __FILE__ ) . 'showdataexplorer.php?id=' . $this->number . '" title="OpenData CKAN Viewer Austria" target="_blank">Vergrößern</a></div>';
	}
	?>
		<div id="<?php echo $this->get_field_id( 'ZoomPopup2' ); ?>" style="display:none;">
			<div id="<?php echo $this->get_field_id( 'ZoomPopup' ); ?>" style="display:block;">
				<div class="<?php echo $this->get_field_id( 'data-explorer-popup' ); ?>"></div><div style="clear: both;"></div>
			</div>
		</div>
		<style type="text/css">
            #TB_overlay { z-index:1000; }

            #TB_window {
                z-index:1001;
                height:570px !important;
                margin-top: -300px !important;
            }

            #TB_ajaxContent {
                height:540px !important;
            }
		</style>

		<script type="text/javascript">
		jQuery(function($) {
			var widgetWidth = $(".widget-area").width(); // get the sidebar width
			var widgetHeight = '<?php echo $instance['height']; ?>';
			var popupWidth = 950;
			var popupHeight = 530;
			var strClass = '.<?php echo $this->get_field_id( 'data-explorer-here' ); ?>';
			var strType = '<?php echo $instance['type']; ?>';
			var objOptions = <?php echo ($instance[$instance['type']] == "" || $instance[$instance['type']] == "{" ? "{}" : $instance[$instance['type']]); ?>;
			var url = "<?php echo urlencode($instance['url']); ?>" + "&ispost=0&id=<?php echo $this->number; ?>";
			var strFilters = "<?php echo $instance['filters']; ?>";
			var strFormat = "<?php echo $instance['format']; ?>";

			switch (strType) {
				case "metadata":
					objOptions = {metafields: "<?php echo $instance['metafields']; ?>", metaurl: "<?php echo $instance['metaurl']; ?>"};
				break;
				case "graph": // graph should be a bit smaller
					popupHeight -= 30;
					popupWidth -= 40;
				break
			}
			objOptions.format = strFormat;
			wpCKANReclineViewer.createDataViewer(strClass, url, '<?php echo urlencode(str_replace("&amp;","&",$instance['metaurl'])); ?>' + "&ispost=0&id=<?php echo $this->number; ?>", strType, objOptions, strFilters, "<?php echo plugins_url("/proxy.php?url=", __FILE__); ?>" , widgetHeight, widgetWidth); // Widget
			$(".<?php echo $this->get_field_id( 'ZoomLink' );?> > a").click(function() {
				wpCKANReclineViewer.createDataViewer("#<?php echo $this->get_field_id( 'ZoomPopup' ); ?>", url, strType, objOptions, strFilters, "<?php echo plugins_url("/proxy.php?url=", __FILE__); ?>",popupHeight, popupWidth); // Widget im Popup
			});
		});

		</script>
	<?php
    echo $after_widget;
  }

}
add_action( 'widgets_init', create_function('', 'return register_widget("CkanWidget");') );

?>