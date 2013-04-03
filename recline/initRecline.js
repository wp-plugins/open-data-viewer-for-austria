String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};


var wpCKANReclineEditor = function () {}
 
 
// Erzeugt einen ReclineEditor für den Adminbereich
wpCKANReclineEditor.prototype = {
  initExplorer: function (strContainer, strErrorContainer, strUrl, strExplorerType, options, strProxyUrl) {
    strProxyUrl = strProxyUrl || "../wp-content/plugins/ckan/proxy.php?url=";
    
    if (strUrl != "") {
      $(strContainer).html("");
      window.dataExplorer = null;
      window.explorerDiv = $(strContainer);
        
      var dataset;
      var strBackend = options.format || "csv";
      if (strBackend == "json") strBackend = "geojson";
      dataset = new recline.Model.Dataset({
        "url": strProxyUrl + encodeURIComponent(strUrl),
        "backend": strBackend
      });
      this.createExplorer(dataset, strExplorerType, options);
    } else {
      $(strErrorContainer).html("<p><b>Fehler: </b>Sie haben keinen Datensatz ausgewählt.</p>").show();
      return false;
    }
  },
  createExplorer: function(dataset, strExplorerType, options) {
    // remove existing data explorer view
    var reload = false;
    if (window.dataExplorer) {
      window.dataExplorer.remove();
      reload = true;
    }
    window.dataExplorer = null;
    var $el = $('<div />');
    $el.appendTo(window.explorerDiv);
    
    if (typeof(options.grid) != "undefined") {
      if (typeof(options.grid.hidden) != "undefined")
        options.grid.hiddenColumns = options.grid.hidden;
    }
                  
    strVal = strExplorerType;
    var views = [
      {
        id: 'grid',
        label: 'Grid',
        view: new recline.View.SlickGrid({
          model: dataset,
          state: options.grid
        })
      }
    ];
    
    switch(strVal) {
      case "map":
        views.push({
          id: 'map',
          label: 'Map',
          view: new recline.View.Map({
            model: dataset,
            state: options.map
          })
        });
        break;
      case "graph":
        views.push({
          id: 'graph',
          label: 'Graph',
          view: new recline.View.Graph({
            model: dataset,
            state: options.graph
          })
        });
        break;
    }
    
    window.dataExplorer = new recline.View.MultiView({
      model: dataset,
      el: $el,
      views: views,
      sidebarViews : [{         // Nur FilterEditor anzeigen
          id: 'filterEditor',
          label: 'Filters',
          view: new recline.View.FilterEditor({
            model: dataset
          })
      }]
    });
    
    window.dataExplorer = wpCKANHelper._addFilter2( window.dataExplorer, options.filters);
  }
}
var wpCKANReclineEditor = new wpCKANReclineEditor();

// Erzeugt einen Viewer für die Seite/Widget
var wpCKANReclineViewer = function () {}
wpCKANReclineViewer.prototype = {
    createDataViewer: function (strContainerId, strUrl, strMetaUrl, strType, objTypeOptions, strFilters, strProxyUrl, height, width) {
        $(strContainerId).html("");
        window.dataExplorer = null;
        window.explorerDiv = $(strContainerId);
        
        var reload = false;
        if (window.dataExplorer) {
            window.dataExplorer.remove();
            reload = true;
        }
        window.dataExplorer = null;
        var $el = $('<div />');
        $el.appendTo(window.explorerDiv);
        var strProxy = strProxyUrl || "../wp-content/plugins/ckan/proxy.php?url=";
        var format = objTypeOptions.format || "csv";
        if (format == "json") format = "geojson";
        
        var dataset = new recline.Model.Dataset({
            url: strProxy + strUrl,
            backend: format
        });
    
        switch(strType) {
            case "grid":
                if (typeof(objTypeOptions.hidden) != "undefined")
                    objTypeOptions.hiddenColumns = objTypeOptions.hidden;
                var grid = new recline.View.SlickGrid({
                    model: dataset,
                    state: objTypeOptions
                });
                grid.$el.height(height);
                grid.$el.width(width);
                $(strContainerId).append(grid.el);
                try {
                    dataset.fetch();
                } catch (e) { alert("Der Datensatz kann nicht gefunden werden."); };
                
                grid = wpCKANHelper._addFilter(grid, strFilters);
                grid.show();
            break;
            case "graph":
                var options = objTypeOptions;
                var graph = new recline.View.Graph({
                    model: dataset,
                    el: $(strContainerId),
                    state: options
                });
                graph = wpCKANHelper._addFilter(graph, strFilters);
                try {
                    dataset.fetch();
                } catch (e) { alert("Der Datensatz kann nicht gefunden werden."); };
                $(strContainerId).height(height);
                $(strContainerId + ' .recline-graph').height(height);
                $(strContainerId).width(width);
                $(strContainerId + ' .recline-graph .graph').width(width);
                $(".panel.graph").attr("canvasHeight", height);
                graph.render();
            break;
            case "map":
                if (objTypeOptions.autoZoom == "true" || objTypeOptions.autoZoom === true) objTypeOptions.autoZoom = true; else objTypeOptions.autoZoom = false;
                if (objTypeOptions.cluster == "true" || objTypeOptions.cluster === true) objTypeOptions.cluster = true; else objTypeOptions.cluster = false;
                
                var options = objTypeOptions;
                var map = new recline.View.Map({
                    model: dataset,
                    el: $(strContainerId),
                    state: options
                });
                map.render();
                try {
                    dataset.fetch().done(function() {
                        map.map._sizeChanged = true; // Reseten der Ansicht, da Map im Popup die Größe (size) 0 hat. Ursache unklar
                        map.redraw();
                        map.state.set({cluster: objTypeOptions.cluster});
                        map.show();
                    });
                } catch (e) { alert("Der Datensatz kann nicht gefunden werden."); };
                
                
                map = wpCKANHelper._addFilter(map, strFilters);
                $(strContainerId).height(height);
                $(strContainerId + ' .recline-map .map').height(height);
                $(strContainerId).width(width);
                $(strContainerId + ' .recline-map .map').width(width);
            break;
            case "metadata":
                var options = objTypeOptions;
                if(options.metafields != "") {
                    var opts = options;
                    var url = opts.metaurl;
                    $.ajax({
                        url: strProxy + (strMetaUrl),
                        contentType: "json",
                        dataType: "json"
                    }).done(function(result) {
                        var aryMetafields = opts.metafields.split(",");
                        var str = "";
                        $.each(aryMetafields, function(idx, val) {
                            var field = val.split("(");
                            field[1] = field[1].replace(")", "");
                            if (typeof(result[field[0]]) != "undefined") {
                                if (result[field[0]] == null) result[field[0]] = "";
                                if (result[field[0]].indexOf("http") != -1) result[field[0]] = "<a href='" + result[field[0]] + "' target='_blank'>" + result[field[0]] + "</a>";
                                str += "<tr><td style='width:" + width * (1/3) + "px;'>" + field[1] + ":&nbsp;&nbsp;</td><td style='display:block; width:" + width * (2/3) + "px; word-break: break-all; '>" + result[field[0]] + "</td></tr>";
                            }
                            $(strContainerId).html("<table style='table-layout: fixed;'>" + str + "</table>");
                        });
                    });
                }
            break;
        }
    }
}
var wpCKANReclineViewer = new wpCKANReclineViewer();


// Helperfunktionen
var wpCKANHelper = function () {}
wpCKANHelper.prototype = {
  _stringToTransformFunct: function(strFunction) {
        try {
            return strFunction.replace(/\(LSB\)/g, "[").replace(/\(RSB\)/g, "]").replace(/\\n\)/g, "");
        } catch (e){return "";};
  },
  _addFilter: function (scope, filters) {
        if (filters != undefined && filters != "") {
            filters = "[" + filters.replace(/'/gi, '"') + "]";
            var aryFilters = JSON.parse(filters);
            if (aryFilters != null)
            $.each(aryFilters, function(id, val) {
                scope.options.model.queryState.addFilter(val);
            });
        }
        return scope;
  },
  _addFilter2: function (scope, aryFilters) {
    if (aryFilters != undefined && aryFilters != "") {
      $.each(aryFilters, function(id, val) {
        scope.options.model.queryState.addFilter(val);
      });
    }
    return scope;
  },
  _inArray: function (needle, haystack) {
        var length = haystack.length;
        for(var j = 0; j < needle.length; j++) {
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle[j]) return true;
            }
        }
        return false;
    }
}
var wpCKANHelper = new wpCKANHelper();
