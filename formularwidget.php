<?php

?>
        <script type="text/javascript">
            var strProxy = "<?php echo plugins_url("/proxy.php?url=", __FILE__); ?>"; // IE 8 braucht das trotz JSON-Daten...
            var strCkanLink = "http://www.data.gv.at/";
            var strDataPool = strCkanLink + "katalog/api/";
            var strTaglist = "rest/tag";
            var strSearchDS = "search/dataset";
            var strSearchRes = "search/resource";
            var strGetDS = "rest/dataset";
            var strResUrls = "";
        
            jQuery(document).ready(function($) {
                
                loadHiddenfields();
                jQuery(document).find("#<?php echo $this->get_field_id( ucfirst($instance["type"]) . "Container" ); ?>").show();
                jQuery(document).find(":input[name='<?php echo $this->get_field_name( 'type' ); ?>']").change(function() {
                    jQuery(document).find(".TypeContainer").hide();
                    jQuery(this).next().next().toggle();
                });
                
                
                jQuery(document).find(":input[id=wpCKANDataViewer_type_metadata]").change(function() {
                    prepareMetadatalist();
                });
                
                
                $("#grid_save").click(function() {
                    setHiddenfields();
                });
                
                function setHiddenfields() {
                   window.ReclineData = {};
                    strVal = $("input[id^=<?php echo $this->get_field_id( 'wpCKANDataViewer_type' ); ?>]:radio:checked").attr("value");
                    switch(strVal) {
                        case "grid":
                            window.ReclineData = {grid: {hidden: []}};
                            window.ReclineData.grid.hidden = window.dataExplorer.state.attributes["view-grid"].hiddenColumns;
                            break;
                        case "map":
                            window.ReclineData = {map: {}};
                            window.ReclineData.map = window.dataExplorer.state.attributes["view-map"];
                            break;
                        case "graph":
                            window.ReclineData = {graph: {}};
                            window.ReclineData.graph = window.dataExplorer.state.attributes["view-graph"];
                            break;
                    }
                    window.ReclineData.filters = JSON.stringify(window.dataExplorer.state.get("query").filters).replace(/"/gi, "'").replace("]", "").replace("[", "");
                    $("#<?php echo $this->get_field_id( 'grid' ); ?>").attr("value", JSON.stringify(window.ReclineData.grid));
                    $("#<?php echo $this->get_field_id( 'graph' ); ?>").attr("value", JSON.stringify(window.ReclineData.graph));
                    $("#<?php echo $this->get_field_id( 'map' ); ?>").attr("value", JSON.stringify(window.ReclineData.map));
                    $("#<?php echo $this->get_field_id( 'filters' ); ?>").attr("value", window.ReclineData.filters);
                    $("#<?php echo $this->get_field_id( 'format' ); ?>").attr("value", window.ReclineData.format); 
                }
                
                function loadHiddenfields() {
                    window.ReclineData = {};
                    var strType = "<? echo $instance["type"]; ?>";
                    var strFilterField = "#<?php echo $this->get_field_id( 'filters' ); ?>";
                    window.ReclineData.filters = "<?php echo ($instance["filters"]); ?>";
                    window.ReclineData.format = "<?php echo ($instance["format"]); ?>";
                    window.ReclineData[strType] = <?php echo (empty($instance[$instance["type"]]) || $instance[$instance["type"]] == "{" ? "{}" : $instance[$instance["type"]]); ?>;
                    $("#<?php echo $this->get_field_id( strType ); ?>").attr("value", JSON.stringify(window.ReclineData[strType]));
                    $("#<?php echo $this->get_field_id( 'filters' ); ?>").attr("value", window.ReclineData.filters); 
                }
                
                $(".TypeContainer > a").click(function() {
                    var strVal = $("input[id^=<?php echo $this->get_field_id( 'wpCKANDataViewer_type' ); ?>]:radio:checked").attr("value");
                    var strFormat = $("#<?php echo $this->get_field_id( 'format' ); ?>").attr("value");
                    var objDefault = <?php echo (empty($instance[$instance["type"]]) || $instance[$instance["type"]] == "{" ? "{}" : $instance[$instance["type"]]); ?>;
                    var strFilterField = "#<?php echo $this->get_field_id( 'filters' ); ?>";
                    var objFilter= ($(strFilterField).attr("value") != "" ? [JSON.parse($(strFilterField).attr("value").replace(/'/gi, '"'))] : [<?php echo ($instance["filters"]); ?>]);
                    var options = {
                        "filters": objFilter,
                        format: strFormat
                    };
                    options[strVal] = (typeof(window.ReclineData) !== "undefined" ? window.ReclineData[strVal] : objDefault);
                    wpCKANReclineEditor.initExplorer(".data-explorer-here", "none", $("#<?php echo $this->get_field_id( 'url' ); ?>").attr("value"), strVal, options, strProxy);
                });
                
                
                $("#<?php echo $this->get_field_id( 'combores' ); ?>").change(function() {
                    if (typeof($("#<?php echo $this->get_field_id( 'combores' ); ?> option:selected").attr("value")) != "undefined") {
                        var aryResValue = $("#<?php echo $this->get_field_id( 'combores' ); ?> option:selected").attr("value").split(",");
                        var strResId = aryResValue[0];
                        var strResFormat = aryResValue[1];
                        $("#<?php echo $this->get_field_id( 'format' ); ?>").attr("value", strResFormat);
                        var url = "";
                        if (strResUrls[strResId]) {
                            if (($.trim(strResUrls[strResId]).indexOf("http") == -1) && ($.trim(strResUrls[strResId]).indexOf("/") == 0)) {
                                url = strCkanLink + strResUrls[strResId];
                            } else {
                                url = strResUrls[strResId];
                            }
                        }
                        $("#<?php echo $this->get_field_id( 'rohdatenlink' ); ?> a").attr("href", url).text(url);
                        $("#<?php echo $this->get_field_id( 'rohdatenlink' ); ?>").show();
                        $("#<?php echo $this->get_field_id( 'url' ); ?>").attr("value", url);
                    }
                }).change();
                
                
                jQuery("#savewidget").click(function() {
                    var str = [];
                    $("input[name^=metafields]:checkbox:checked").each(function(idx, val) {
                        str.push(val.value + "(" + $(this).next("b").text() + ")" );
                    });
                    $("#<?php echo $this->get_field_id( 'metafields' ); ?>").attr("value", str.join(","));
                });
                
            });
            
            function prepareMetadatalist() {
                    var strTag = $("#<?php echo $this->get_field_id( 'combokat' ); ?> option:selected").text();
                    var strId = $("#<?php echo $this->get_field_id( 'combods' ); ?> option:selected").attr("value");
                    $("#<?php echo $this->get_field_id( 'combods' ); ?>").show();
                    getListOfMetadata(strTag, strId);
            }
            getCategories();
            function getCategories() {
                $.ajax({
                    url: strProxy + encodeURIComponent(strDataPool + strTaglist),
                    dataType: "json"
                }).done(function(result) {
                    $("#<?php echo $this->get_field_id( 'combokat' ); ?>").html("");
                    if (result != null)
                        $.each(result, function(idx, val) {
                            var strSelected = (val == "<?php echo ($instance["combokat"]) ?>" ? "selected='selected'" : "");
                           $("#<?php echo $this->get_field_id( 'combokat' ); ?>").append("<option value='" + val + "' " + strSelected + ">" + val + "</option");
                        });
                    $("#<?php echo $this->get_field_id( 'combokat' ); ?>").change(function() {
                        var strTagname = $("#<?php echo $this->get_field_id( 'combokat' ); ?> option:selected").text();
                        getDataset(strTagname);
                    }).change();
                });
            }
            
            function getDataset(strTagname) {
                if(strTagname != "")
                $.ajax({
                    url: strProxy + encodeURIComponent(strDataPool + strSearchDS + "?tags=" + strTagname + "&all_fields=1"),
                    dataType: "json"
                }).done(function(result) {
                    if (result.count != 0) {
                        $("#<?php echo $this->get_field_id( 'combods' ); ?>").html("");
                        if (result["results"] != null)
                        $.each(result["results"], function(idx, val) {
                            delete val['dict'];
                            if(typeof(val["res_format"]) != "undefined") {
                                if (inArray(["CSV", "csv", "JSON", "json"], val["res_format"])) {
                                    var strSelected = (idx == "<?php echo ($instance["combods"]) ?>" ? "selected='selected'" : "");
                                    $("#<?php echo $this->get_field_id( 'combods' ); ?>").append("<option value='" + idx + "' " + strSelected + ">" + val["title"] + "</option");
                                }
                            }
                        })
                        $("#<?php echo $this->get_field_id( 'combods' ); ?>").change(function() {
                            var strResId = $("#<?php echo $this->get_field_id( 'combods' ); ?> option:selected").attr("value");
                            if (typeof(strResId) != "undefined") {
                                $("#<?php echo $this->get_field_id( 'message_error' ); ?>").hide();
                                getResource(result["results"][strResId]);
                                $("#<?php echo $this->get_field_id( 'metaurl' ); ?>").attr("value", strDataPool + strGetDS + "/" + result["results"][0]["name"]);
                                if($("#<?php echo $this->get_field_id( 'content' ); ?>").attr("value") != "<?php echo ($instance["content"]); ?>")
                                $("#<?php echo $this->get_field_id( 'content' ); ?>").attr("value", result["results"][0]["title"]);
                                if ($("input[id^=<?php echo $this->get_field_id( 'wpCKANDataViewer_type' ); ?>]:radio:checked").attr("value") == "metadata") {
                                    prepareMetadatalist();
                                }
                            } else {
                                $("#<?php echo $this->get_field_id( 'combods' ); ?>").html("");
                                $("#<?php echo $this->get_field_id( 'combores' ); ?>").html("");
                                $("#<?php echo $this->get_field_id( 'url' ); ?>").attr("value", "");
                                $("#<?php echo $this->get_field_id( 'message_error' ); ?>").html("<p><b>Fehler:</b> <?php _e("Unter diesem Stichwort gibt es keine CSV oder JSON Daten.", "wpckan"); ?></p>").show();
                                $("#<?php echo $this->get_field_id( 'rohdatenlink' ); ?>").hide();
                            }
                        }).change()
                    }
                });
            }
            
            function getResource(data) {
                if (data != undefined) {
                    strResUrls =  data["res_url"]; 

                    $("#<?php echo $this->get_field_id( 'combores' ); ?>").html("");
                    if(data["res_format"] != null)
                        $.each(data["res_format"], function(idx2, val2) {
                            if (inArray(["csv", "json"], [data["res_format"][idx2].toLowerCase()])) {
                                var strSelected = (idx2 + "," + data["res_format"][idx2].toLowerCase() == "<?php echo ($instance["combores"]) ?>" ? "selected='selected'" : "");
                                $("#<?php echo $this->get_field_id( 'combores' ); ?>").append("<option value='" + idx2 + "," + data["res_format"][idx2].toLowerCase() +"' " + strSelected + ">" + data["res_name"][idx2] + "</option");
                            }
                        });
                    $("#<?php echo $this->get_field_id( 'combores' ); ?>").change();
                
                }
            }
            
            function inArray(needle, haystack) {
                var length = haystack.length;
                for(var j = 0; j < needle.length; j++) {
                    for(var i = 0; i < length; i++) {
                        if(haystack[i] == needle[j]) return true;
                    }
                }
                return false;
            }
            
            
            function getListOfMetadata(strTag, id) {
                var aryMetafields = ($("#<?php echo $this->get_field_id( 'metafields' ); ?>").attr("value") || ("<?php echo ($instance["metafields"]); ?>") ).split(",");
                var aryMetaNames = [];
                var aryMetaTitle = [];
                if(aryMetafields != null)
                    $.each(aryMetafields,function(idx, val) {
                        if (val != "") {
                            var field = val.split("(");
                            aryMetaNames.push(field[0]);
                            aryMetaTitle[field[0]] = field[1].replace(")", "");
                        }
                    });
                
                $.ajax({
                    url: strProxy + encodeURIComponent($("#<?php echo $this->get_field_id( 'metaurl' ); ?>").attr("value")),
                    dataType: "json"
                }).done(function(result) {
                    if (result) {
                        var strOut;
                        var i = 0;
                        var str = "<table>";
                        var strChecked = " checked='checked' ";
                        var strTitle = "";
                        var blChecked = false;
                        for( property in result ) {
                            i++;
                            blChecked = false;
                            strTitle = property;
                            if (inArray([property], aryMetaNames)) {
                                blChecked = !blChecked;
                                strTitle = aryMetaTitle[property];
                            }
                            str += "<tr style='margin:0px; padding:0px;'><td style='margin:0px; padding:2px;'><input type='checkbox' " + (blChecked ? strChecked : "") + " value='" + property + "' name='metafields[" + i + "]' />&nbsp;&nbsp;<b id='MetaProp[" + i + "]' class='MetaProp'>" + strTitle + "</b>: </td></tr><tr><td style='margin:0px; padding:0px;'>" + result[property] + " </td></tr>";
                        }
                        $("#<?php echo $this->get_field_id( 'combods' ); ?>").append(str + "</table>");
                    }
                });
                
            }
            
            var oldHtml = "";
            jQuery(".MetaProp").live("click",clicklistener);;
                
            function clicklistener() {
                if ($(this).attr("class") != "MetaProbTB") {
                    oldHtml = $(this).html();
                    $(this).html('<input type="text" value="' + $(this).text() + '" class="MetaTB"/>');
                    $(this).find(":input").focus();
                    $(this).attr("class", "MetaProbTB");
                }
            }
            jQuery(".MetaProbTB, MetaProb").live("focusout", focuslost).live("blur", focuslost);
            
            function focuslost() {
                if ($(this).attr("class") != "MetaProb") {
                    var strVal = $(this).find(".MetaTB").attr("value");
                    if (strVal == "") $(this).html(oldHtml);
                    else $(this).html(strVal);
                    $(this).attr("class", "MetaProb").unbind("click").live("click",clicklistener);
                }
            }
            
        </script>
        <style type="text/css">
            .TypeContainer {
                display:none;
            }
            #TB_overlay { z-index:9; }
            
            #TB_window {
                z-index:10;
                height:570px !important;
                margin-top: -300px !important;
            }
                
            #TB_ajaxContent {
                height:540px !important; 
            }
            
            .recline-slickgrid {
                height: 450px;
            }
            
            .recline-map .map {
                height: 450px;
            }
            
            .recline-graph .graph {
                height: 450px;
            }
        </style>
        <div id="wpckan-form">
        
            <input type="hidden" name="<?php echo $this->get_field_name( 'grid' ); ?>" id="<?php echo $this->get_field_id( 'grid' ); ?>" value='<?php echo ($instance["grid"]); ?>'/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'graph' ); ?>" id="<?php echo $this->get_field_id( 'graph' ); ?>" value="<?php echo ($instance["graph"]); ?>"/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'map' ); ?>" id="<?php echo $this->get_field_id( 'map' ); ?>" value="<?php echo esc_attr($instance["map"]); ?>"/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'filters' ); ?>" id="<?php echo $this->get_field_id( 'filters' ); ?>" value="<?php echo ($instance["filters"]); ?>"/>
                                            
            <input type="hidden" name="<?php echo $this->get_field_name( 'url' ); ?>" id="<?php echo $this->get_field_id( 'url' ); ?>" class="wpckan_url" value=""/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'metaurl' ); ?>" id="<?php echo $this->get_field_id( 'metaurl' ); ?>" value=""/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'metafields' ); ?>" id="<?php echo $this->get_field_id( 'metafields' ); ?>" value="<?php echo ($instance["metafields"]); ?>"/>
            <input type="hidden" name="<?php echo $this->get_field_name( 'format' ); ?>" id="<?php echo $this->get_field_id( 'format' ); ?>" value=""/>
            <b><u>Vor</u> der Konfiguration, bitte einmal Speichern drücken <br />oder Seite neu laden! (im Nichtzugänglichkeitsmodus)</b><br />
            <br /><span><b><?php _e('Schritt', 'wpckan')?> 1: <?php _e('Auswahl', 'wpckan')?></b></span>
            <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->get_field_id('combokat'); ?>"><? _e("Stichwort:", "wpckan"); ?></label></th>
                <td>
                    <select style="width:180px" name="<?php echo $this->get_field_name('combokat'); ?>" id="<?php echo $this->get_field_id( 'combokat' ); ?>" title="test"></select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->get_field_id( 'combods' ); ?>"><? _e("Datensatz:", "wpckan"); ?></label></th>
                <td>
                    <select style="width:180px" name="<?php echo $this->get_field_name( 'combods' ); ?>" id="<?php echo $this->get_field_id( 'combods' ); ?>"></select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->get_field_id( 'combores' ); ?>"><? _e("Ressource:", "wpckan"); ?></label></th>
                <td>
                    <select style="width:180px" name="<?php echo $this->get_field_name( 'combores' ); ?>" id="<?php echo $this->get_field_id( 'combores' ); ?>"></select>
                </td>
            </tr>
            </table>
            <table class="form-table">
                <tr valign="top">
                    <td scope="row" style="width:80px;"><label for="wpCKANDataViewer_content"><?php _e('ausgewählte Rohdaten:', 'wpckan')?></label></td>
                    <td>
                        <div style="width:265px; margin: 0px !important; display:none;" id="<?php echo $this->get_field_id( 'message_error' ); ?>" class="error below-h2"></div>
                        <div id="<?php echo $this->get_field_id( 'rohdatenlink' ); ?>" style="display:none;"><img src="<?php echo plugins_url('/accept-icon.png', __FILE__); ?>" height="16" style="vertical-align:middle;" /> <a href="" target="_blank">-</a></div>
                    </td>
                </tr>
            </table>
            <br /><span><b><?php _e('Schritt', 'wpckan')?> 2: <?php _e('Konfiguration', 'wpckan')?></b></span>
            <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->get_field_id( 'content' ); ?>"><? _e("Datenbeschreibung:", "wpckan"); ?></label></th>
                <td>
                    <input type="text" style="width:180px" name="<?php echo $this->get_field_name( 'content' ); ?>" id="<?php echo $this->get_field_id( 'content' ); ?>" value="<?php echo ($instance["content"]); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_Size"><? _e("Größe:", "wpckan"); ?></label></th>
                <td>
                    <input type="text" size="2" name="<?php echo $this->get_field_name( 'height' ); ?>" id="<?php echo $this->get_field_id( 'height' ); ?>" value="<?php echo ($instance["height"]); ?>"/> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->get_field_id( 'wpCKANDataViewer_type_grid' ); ?>">Typ:</label></th>
                <td>
                    <input type="radio" name="<?php echo $this->get_field_name( 'type' ); ?>" value="grid" <?php echo ($instance["type"] == 'grid' ? 'checked="checked"' : '')?> id="<?php echo $this->get_field_id( 'wpCKANDataViewer_type_grid' ); ?>"> <? _e("Tabelle", "wpckan"); ?><br>
                    <div id="<?php echo $this->get_field_id( 'GridContainer' ); ?>" class="TypeContainer">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Tabelle konfiguration.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="<?php echo $this->get_field_name( 'type' ); ?>" value="graph" <?php echo ($instance["type"] == 'graph' ? 'checked="checked"' : '')?> id="<?php echo $this->get_field_id( 'wpCKANDataViewer_type_grid' ); ?>"> <? _e("Graph", "wpckan"); ?><br>
                    <div id="<?php echo $this->get_field_id( 'GraphContainer' ); ?>" class="TypeContainer">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Graph konfiguration.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="<?php echo $this->get_field_name( 'type' ); ?>" value="map" <?php echo ($instance["type"] == 'map' ? 'checked="checked"' : '')?> id="<?php echo $this->get_field_id( 'wpCKANDataViewer_type_grid' ); ?>"> <? _e("Map", "wpckan"); ?></br>
                    <div id="<?php echo $this->get_field_id( 'MapContainer' ); ?>" class="TypeContainer">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Map konfiguration.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="<?php echo $this->get_field_name( 'type' ); ?>" value="metadata" <?php echo ($instance["type"] == 'metadata' ? 'checked="checked"' : '')?> id="wpCKANDataViewer_type_metadata"> <? _e("Metadaten", "wpckan"); ?></br>
                    <div id="<?php echo $this->get_field_id( 'combods' ); ?>" class="TypeContainer">
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_"><? _e("Öffnen als...", "wpckan"); ?></label></th>
                <td>
                    <input type="radio" name="<?php echo $this->get_field_name( 'opentype' ); ?>" value="popup" <?php echo ($instance["opentype"] == 'popup' ? 'checked="checked"' : '')?> id="wpCKANDataViewer_open_popup"> <? _e("Popup-Fenster", "wpckan"); ?><br>
                    <input type="radio" name="<?php echo $this->get_field_name( 'opentype' ); ?>" value="tab" <?php echo ($instance["opentype"] == 'tab' ? 'checked="checked"' : '')?> id="wpCKANDataViewer_open_tab"> <? _e("neuer Tab", "wpckan"); ?><br>
                </td>
            </tr>
        </table>
        </div>
        <script type="text/javascript" src="<?php echo plugins_url("/recline/initRecline.js", __FILE__); ?>"></script>
        <div id="TableEditorContainer" style="display:none; z-index:999;">            
            <div class="data-explorer-here" style="margin-top:10px;"></div>
            
            <div class="button button-primary button-large" id="grid_save" onclick="tb_remove()" style="color:white; margin-top: 5px; float:right; "> &nbsp;&nbsp;<? _e("Änderungen speichern", "wpckan"); ?>&nbsp;&nbsp;</div>
            <div style="clear: both;"></div>
        </div>