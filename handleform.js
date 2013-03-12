/**
 * Handle: wpCKANDataViewerAdmin
 * Version: 0.0.1
 * Deps: jquery
 * Enqueue: true
 */

var wpCKANDataViewerAdmin = function () {};
 
wpCKANDataViewerAdmin.prototype = {
    options           : {},
    generateShortCode : function() {
        var content = this['options']['content'];
        delete this['options']['content'];
        var attrs = '';
        jQuery.each(this['options'], function(name, value){
            if (value != '') {
                attrs += ' ' + name + '="' + value + '"';
            }
        });
        return '[ckan' + attrs + ']' + content + '[/ckan]'
    },
    sendToEditor      : function(f, strAryname, reclineData) {
        var collection = jQuery(f).find("input[id^=" + strAryname + "]:not(input:radio),input[id^=" + strAryname + "]:radio:checked");
        var $this = this;
        collection.each(function () {
            var name = this.name.substring(strAryname.length +1, this.name.length-1);
            $this['options'][name] = this.value;
        });
        // Checkboxen der Metadaten auswerten
        collection = jQuery(f).find("input[name^=metafields]:checkbox:checked");
        $this['options']['metafields'] = [];
        collection.each(function () {
            $this['options']['metafields'].push(this.value + "(" + $(this).next("b").html() + ")");
        });
        
        if (reclineData != undefined) {
            if (reclineData.Grid != undefined) {
                $this['options']['hiddenColumns'] = reclineData.Grid.hiddenColumns;
                if(reclineData.Grid.columnsWidth != null) $this['options']['columnsWidth'] = JSON.stringify(reclineData.Grid.columnsWidth).replace(/"/gi, "'").replace("]", "").replace("[", "");
                if(reclineData.Grid.columnsOrder != null) $this['options']['columnsOrder'] = JSON.stringify(reclineData.Grid.columnsOrder).replace(/"/gi, "").replace("]", "").replace("[", "");
            }
            if (reclineData.Map != undefined) {
                if(reclineData.Map.autoZoom != null) $this['options']['autoZoom'] = reclineData.Map.autoZoom;
                if(reclineData.Map.cluster != null) $this['options']['cluster'] = reclineData.Map.cluster;
                if(reclineData.Map.comma != null) $this['options']['comma'] = reclineData.Map.comma;
                if(reclineData.Map.geomField != null) $this['options']['geomField'] = reclineData.Map.geomField;
                if(reclineData.Map.latField != null) $this['options']['latField'] = reclineData.Map.latField;
                if(reclineData.Map.lonField != null) $this['options']['lonField'] = reclineData.Map.lonField;                
            }
            if (reclineData.Graph != undefined) {
                if(reclineData.Graph.series != null) $this['options']['series'] = JSON.stringify(reclineData.Graph.series).replace(/"/gi, "").replace("]", "").replace("[", "");
                if(reclineData.Graph.graphType != null) $this['options']['graphType'] = reclineData.Graph.graphType;
                if(reclineData.Graph.group != null) $this['options']['group'] = reclineData.Graph.group;
            }
            
            $this['options']['filters'] = reclineData.filters;
            $this['options']['transform'] = reclineData.transform;
        }
        send_to_editor(this.generateShortCode());
        return false;
    }
}
 
var wpCKANDataViewerAdmin = new wpCKANDataViewerAdmin();