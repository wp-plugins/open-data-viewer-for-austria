
<script type="text/javascript">
            var strProxy = "<?php echo plugins_url('/proxy.php?url=', __FILE__); ?>"; // IE 8 braucht das...
            // CKAN Urls -> API V. 1.0
            var strCkanLink = "http://www.data.gv.at/";
            var strDataPool = strCkanLink + "katalog/api/";
            var strTaglist = "rest/tag";
            var strSearchDS = "search/dataset";
            var strSearchRes = "search/resource";
            var strGetDS = "rest/dataset";
            // Gewählte Ressource hinterlegen
            var strResUrls = "";
            
            jQuery(document).ready(function($) {
                // Tooltip für Metadaten
                //jQuery( document ).tooltip();  
                
                // Wenn Typ der Anzeige gewählt wird entsprechenden Container anzeigen
                jQuery(document).find(":input[name='wpCKANDataViewer[type]']").change(function() {
                    jQuery(document).find(".TypeContainer").hide();
                    jQuery(this).next().next().toggle();
                });
                // Wenn bei Typ der Anzeige Metadata gewählt
                jQuery(document).find(":input[id=wpCKANDataViewer_type_metadata]").change(function() {
                    var strTag = $("#wpCKANDataViewer_kat option:selected").text();
                    var strId = $("#wpCKANDataViewer_ds option:selected").attr("value");
                    $("#MetadataContainer").show();
                    getListOfMetadata(strTag, strId);
                });
                // Wenn im Popup speichern gedrückt wurde, Recline-Werte auslesen und Tag erzeugen
                $("#grid_save").click(function() {
                    strVal = $("input[id^=wpCKANDataViewer]:radio:checked").attr("value");
                    switch(strVal) {
                        case "grid":
                            window.ReclineData = {Grid: {}};
                            window.ReclineData.Grid = window.dataExplorer.state.attributes["view-grid"];
                            break;
                        case "map":
                            window.ReclineData = {Map: {}};
                            window.ReclineData.Map = window.dataExplorer.state.attributes["view-map"];
                            break;
                        case "graph":
                            window.ReclineData = {Graph: {}};
                            window.ReclineData.Graph = window.dataExplorer.state.attributes["view-graph"];
                            break;
                    }

                    window.ReclineData.filters = JSON.stringify(window.dataExplorer.state.get("query").filters).replace(/"/gi, "'").replace("]", "").replace("[", "");
                    return wpCKANDataViewerAdmin.sendToEditor($("#wpckan-form"), 'wpCKANDataViewer', window.ReclineData);
                });
                
                // Bei Start, Tags laden... dann Datensätze und Ressourcen
                getCategories();
                // Wenn Ressource gewählt wird Url und Format des gewählten Datensatz auslesen
                $("#wpCKANDataViewer_res").change(function() {
                    if (typeof($("#wpCKANDataViewer_res option:selected").attr("value")) != "undefined") {
                        var aryResValue = $("#wpCKANDataViewer_res option:selected").attr("value").split(",");
                        var strResId = aryResValue[0];
                        $("#wpCKANDataViewer_format").attr("value", aryResValue[1]);
                        var url = "";
                        if (strResUrls[strResId]) {
                            if (($.trim(strResUrls[strResId]).indexOf("http") == -1) && ($.trim(strResUrls[strResId]).indexOf("/") == 0)) {
                                url = strCkanLink + strResUrls[strResId];
                            } else {
                                url = strResUrls[strResId];
                            }
                        }
                        $("#rohdatenlink a").attr("href", url).text(url);
                        $("#rohdatenlink").show();
                        $("#wpCKANDataViewer_url").attr("value", url);
                    }
                }).change();
            });
            
            // Funktion läd Tags und zeigt diese in der ComboBox an
            function getCategories() {
                jQuery.ajax({
                    url: strProxy + encodeURIComponent(strDataPool + strTaglist),
                    dataType: "json"
                }).done(function(result) {
                    $("#wpCKANDataViewer_kat").html("");
                    $.each(result, function(idx, val) {
                       $("#wpCKANDataViewer_kat").append("<option>" + val + "</option");
                    });
                    $("#wpCKANDataViewer_kat").change(function() {
                        var strTagname = $("#wpCKANDataViewer_kat option:selected").text();
                        getDataset(strTagname);
                    }).change();
                });
            }
            // Funktion läd die Datensätze zu einem Tag und fühlt dann die Ressourcencombobox
            function getDataset(strTagname) {
                jQuery.ajax({
                    url: strProxy + encodeURIComponent(strDataPool + strSearchDS + "?tags=" + strTagname + "&all_fields=1"),
                    dataType: "json"
                }).done(function(result) {
                    if (result.count != 0) {
                        $("#wpCKANDataViewer_ds").html("");
                        $.each(result["results"], function(idx, val) {
                            delete val['dict'];
                            if(typeof(val["res_format"]) != "undefined") {
                                if (inArray(["CSV", "csv","JSON", "json"], val["res_format"])) {
                                    $("#wpCKANDataViewer_ds").append("<option value='" + idx + "'>" + val["title"] + "</option");
                                }
                            }
                        });
                        $("#wpCKANDataViewer_ds").change(function() {
                            var strResId = $("#wpCKANDataViewer_ds option:selected").attr("value");
                            if (typeof(strResId) != "undefined") {
                                $("#message_error").hide();
                                getResource(result["results"][strResId]);
                                $("#wpCKANDataViewer_metaurl").attr("value", strDataPool + strGetDS + "/" + result["results"][0]["name"]);
                                $("#wpCKANDataViewer_content").attr("value", result["results"][0]["title"]);
                            } else {
                                $("#wpCKANDataViewer_ds").html("");
                                $("#wpCKANDataViewer_res").html("");
                                $("#wpCKANDataViewer_url").attr("value", "");
                                $("#message_error").html("<p><b>Fehler:</b> <?php _e('Unter diesem Stichwort gibt es keine CSV oder JSON Daten.', 'wpckan'); ?></p>").show();
                                $("#rohdatenlink").hide();
                            }
                        }).change();
                    }
                });
            }
            // Funktion bekommt die Metadaten des gewählten Datensatzes und füllt dann die ResourcenCombo mit den Urls wenn JSON oder CSV vorhanden ist
            function getResource(data) {
                if (data != undefined) {
                    strResUrls =  data["res_url"]; 
                    $("#wpCKANDataViewer_res").html("");
                    $.each(data["res_format"], function(idx2, val2) {
                        if (inArray(["csv", "json"], [data["res_format"][idx2].toLowerCase()])) {
                            $("#wpCKANDataViewer_res").append("<option value='" + idx2 + "," + data["res_format"][idx2].toLowerCase() +"' >" + data["res_name"][idx2] + "</option");
                        }
                    });
                    $("#wpCKANDataViewer_res").change();
                }
            }
            // Hilfsfunktion prüft ob Werte eines Arrays in einem anderem Array sind
            function inArray(needle, haystack) {
                var length = haystack.length;
                for(var j = 0; j < needle.length; j++) {
                    for(var i = 0; i < length; i++) {
                        if(haystack[i] == needle[j]) return true;
                    }
                }
                return false;
            }
            
            // METADATA:
            // Funktion holt die JSON-Metadaten für den Typ Metadata und erzeugt die Tabelle mit den Werten
            function getListOfMetadata(strTag, id) {
                $.ajax({
                    url: strProxy + encodeURIComponent($("#wpCKANDataViewer_metaurl").attr("value")),
                    dataType: "json"
                }).done(function(result) {
                    if (result) {
                        var strOut;
                        var i = 0;
                        var str = "<table>";
                        for( property in result ) {
                            i++;
                            str += "<tr><td><input type='checkbox' value='" + property + "' name='metafields[" + i + "]' />&nbsp;&nbsp;<b id='MetaProp[" + i + "]' class='MetaProp'>" + property + "</b>: </td><td>" + result[property] + " </td></tr>";
                        }
                        $("#MetadataContainer").append(str + "</table>");
                    }
                });
            }
            
            var oldHtml = "";
            // Listener wenn auf eine Propertie geklickt wird -> Textbox anzeigen
            jQuery(".MetaProp").live("click",clicklistener);
            function clicklistener() {
                if ($(this).attr("class") != "MetaProbTB") {
                    oldHtml = $(this).html();
                    $(this).html('<input type="text" value="' + $(this).text() + '" class="MetaTB"/>');
                    $(this).find(":input").focus();
                    $(this).attr("class", "MetaProbTB");
                }
            }
            // Listener wenn Textbox verlassen wird -> Textbox verschwindet und Wert wird übernommen
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
                /* margin-top: -300px !important; */
                top: 50%;
                width: 1000px !important;
                margin-left: -500px !important;
            }
                
            #TB_ajaxContent {
                width:950px !important;
                height:950px !important; 
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
        <input type="hidden" name="wpCKANDataViewer[url]" id="wpCKANDataViewer_url" class=".wpckan_url" value=""/>
        <input type="hidden" name="wpCKANDataViewer[metaurl]" id="wpCKANDataViewer_metaurl" value=""/>
        <input type="hidden" name="wpCKANDataViewer[format]" id="wpCKANDataViewer_format" value=""/>
        <table class="form-table">
       <br /><span><b><?php _e('Schritt', 'wpckan')?> 1: <?php _e('Auswahl', 'wpckan')?></b></span>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_kat"><?php _e('Stichwort:', 'wpckan')?></label></th>
                <td>
                    <select style="width:250px" name="wpCKANDataViewer[kat]" id="wpCKANDataViewer_kat" title="test"></select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_ds"><?php _e('Datensatz:', 'wpckan')?></label></th>
                <td>
                    <select style="width:250px" name="wpCKANDataViewer[ds]" id="wpCKANDataViewer_ds"></select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_res"><?php _e('Ressource:', 'wpckan')?></label></th>
                <td>
                    <select style="width:250px" name="wpCKANDataViewer[res]" id="wpCKANDataViewer_res"></select>
                </td>
            </tr>
        </table>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_content"><?php _e('ausgewählte Rohdaten:', 'wpckan')?></label></th>
                <td>
                    <div style="width:380px; margin: 0px !important; display:none;" id="message_error" class="error below-h2"></div>
                    <div id="rohdatenlink" style="display:none;"><img src="<?php echo plugins_url('/accept-icon.png', __FILE__); ?>" height="16" style="vertical-align:middle;" /> <a href="" target="_blank">-</a></div>
                </td>
            </tr>
        </table>
       <br /><span><b><?php _e('Schritt', 'wpckan')?> 2: <?php _e('Konfiguration', 'wpckan')?></b></span>
        <br />
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_content"><?php _e('Datenbeschreibung:', 'wpckan')?></label></th>
                <td>
                    <input type="text" size="60" name="wpCKANDataViewer[content]" id="wpCKANDataViewer_content" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_Size"><?php _e('Größe:')?></label></th>
                <td>
                    <?php _e('Höhe:')?> <input type="text" size="5" name="wpCKANDataViewer[height]" id="wpCKANDataViewer_height" value="250"/> 
                    <?php _e('Breite:')?> <input type="text" size="5" name="wpCKANDataViewer[width]" id="wpCKANDataViewer_width" value="400"/> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wpCKANDataViewer_type"><?php _e('Typ:', 'wpckan')?></label></th>
                <td>
                    <input type="radio" name="wpCKANDataViewer[type]" value="grid" checked="checked" id="wpCKANDataViewer_type_grid"> <? _e("Tabelle", "wpckan"); ?><br>
                    <div id="TableContainer" class="TypeContainer" style="display: block;">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Tabelle konfigurieren.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="wpCKANDataViewer[type]" value="graph" id="wpCKANDataViewer_type_graph"> <? _e("Graph", "wpckan"); ?><br>
                    <div id="GraphContainer" class="TypeContainer">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Graph konfigurieren.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="wpCKANDataViewer[type]" value="map" id="wpCKANDataViewer_type_map"> <? _e("Map", "wpckan"); ?></br>
                    <div id="MapContainer" class="TypeContainer">
                        <a href="#TB_inline?height=550&width=950&inlineId=TableEditorContainer" title="OpenData CKAN Viewer Austria" class="thickbox" class="openrecline"><? _e("Map konfigurieren.", "wpckan"); ?></a>
                    </div>
                    <input type="radio" name="wpCKANDataViewer[type]" value="metadata" id="wpCKANDataViewer_type_metadata"> <? _e("Metadaten", "wpckan"); ?></br>
                    <div id="MetadataContainer" class="TypeContainer">
                    
                    </div>
                </td>
            </tr>
        </table>
        </div>
        <script type="text/javascript">
            jQuery(function($) {
                $(".TypeContainer > a").click(function() {
                    var options = {
                        format: $("#wpCKANDataViewer_format").attr("value"),
                        grid: {},
                        map: {},
                        graph: {}
                    };
                    wpCKANReclineEditor.initExplorer(".data-explorer-here", "#message_error", $("#wpCKANDataViewer_url").attr("value"), $("input[name^=wpCKANDataViewer]:radio:checked").attr("value"), options, strProxy);
                });
            });
        </script>
        <div id="TableEditorContainer" style="display:none; z-index:999;">            
            <div class="data-explorer-here" style="margin-top:10px;"></div>
            
            <div class="button button-primary button-large" id="grid_save" onclick="tb_remove()" style="color:white; margin-top: 5px; float:right; "> &nbsp;&nbsp;<? _e("Änderungen speichern", "wpckan"); ?>&nbsp;&nbsp;</div>
            <div style="clear: both;"></div>
        </div>
        <span class="submit">
            <input type="button" class="button" onclick="return wpCKANDataViewerAdmin.sendToEditor(this.form, 'wpCKANDataViewer', window.ReclineData);" value="<?php _e('Einfügen', "wpckan"); ?>" />            <br />
        </span>