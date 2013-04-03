<?php
define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');

$aryCKanWidgets = get_option('widget_CkanWidget');
$id = intval($_GET["id"]);

if (isset($aryCKanWidgets[$id])) {
    $aryData = $aryCKanWidgets[$id];
    ?>
	<!DOCTYPE>
    <html>
    <head>
        <?php add_recline_scripts_wp(); wp_head(); ?>
        <script type="text/javascript" src="recline/initRecline.js"> </script>
		<title>Open Data Viewer for Austria</title>
    </head>
    <body>
        <style type="text/css">
			html, body {
				background-color: #f5f5f5;
			}
            .recline-slickgrid {
                height: 450px !important;
				width: 940px !important;
            }
            
            .recline-map .map {
                height: 450px;
            }
            
            .recline-graph .graph {
                height: 450px;
            }
			#TitleContainer {
				width:945px;
				height: 20px;
				font-size: 13px;
				margin:auto;
				text-align:left;
				padding:4px 0px 4px 6px;
				background-color: #222;
				color:#cfcfcf;
			}
			#BorderContainer {
				width:950px;
				margin:auto;
				border:1px solid #555;
				background-color:#fff;
			}
			#ExplorerContainer {
				width:940px;
				margin:auto;
				text-align:left;
				padding:5px;
				background-color:#fff;
			}
			
		</style>
        <script type="text/javascript">
            jQuery(function($) {
				var strClass = '.Explorer';
				var strType = '<?php echo $aryData['type']; ?>';
				var objOptions = <?php echo ($aryData[$aryData['type']] == "" ? "{}": $aryData[$aryData['type']]); ?>;
				var url = "<?php echo urlencode($aryData['url']); ?>" + "&ispost=0&id=<?php echo $id; ?>";
				var strFilters = "<?php echo $aryData['filters']; ?>";
                wpCKANReclineViewer.createDataViewer(strClass, url, strType, objOptions, strFilters, "proxy.php?url=", 500, 500);
            });
        </script>
    <div style="text-align:center;">
		<div id="BorderContainer">
			<div id="TitleContainer">
				Open Data Viewer for Austria
			</div>
			<div id="ExplorerContainer" style="">            
				<div class="Explorer" style=""></div>
				<div style="clear: both;"></div>
			</div>
		</div>
    </div>
    </body>
  
    </html>
    <?php
}




?>