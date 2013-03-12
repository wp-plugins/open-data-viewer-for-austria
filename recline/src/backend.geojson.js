/* Simple Backend for GeoJson.
* A working sample of GeoJson: http://data.wien.gv.at/daten/wfs?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:WELTKULTERBEOGD&srsName=EPSG:4326&outputFormat=json
*/

this.recline = this.recline || {};
this.recline.Backend = this.recline.Backend || {};
this.recline.Backend.GeoJson = this.recline.Backend.GeoJson || {};

(function($, my) {
    my.__type__ = 'geojson';
    my.timeout = 20000;

    my.fetch = function(dataset) {
        var jqxhr = $.ajax({
            url: dataset.url,
            dataType: 'json'
        });
        var dfd = $.Deferred();
        _wrapInTimeout(jqxhr).done(function(results) {

        if (results.error) {
            dfd.reject(results.error);
        }

        
          
        var normalizedRecs = _normalizeRecords(results.features);
        this.data = normalizedRecs;
        var fieldDescr = _handleFieldDescription(normalizedRecs[0]);
            dfd.resolve({
                total: normalizedRecs.length,
                records: normalizedRecs,
                fields: fieldDescr,
                useMemoryStore: true
            });
        })
        .fail(function(arguments) {
            dfd.reject(arguments);
        });
        return dfd.promise();
    };

    // ## _wrapInTimeout
    var _wrapInTimeout = function(ourFunction) {
      var dfd = $.Deferred();
      var timer = setTimeout(function() {
          dfd.reject({
              message: 'Request Error: Backend did not respond after ' + (my.timeout / 1000) + ' seconds'
          });
      }, my.timeout);
      ourFunction.done(function(arguments) {
          clearTimeout(timer);
          dfd.resolve(arguments);
      })
      .fail(function(arguments) {
          clearTimeout(timer);
          dfd.reject(arguments);
      });
      return dfd.promise();
    }
  
    // convert geojson to normal format
    function _normalizeRecords(records) {
        var aryRecs = [];
        _.each(records, function (f) {
            var tmp = [];
            tmp = f.properties;
            tmp.FID = f.id;
            tmp[f.geometry_name] = JSON.stringify(f);
            aryRecs.push(tmp);
        });
        return aryRecs;
    };
    
    function _handleFieldDescription(description) {
        var res = [];
        for (var k in description) {
            res.push({id:k, type:typeof(description[k])});
        }
        return res;
    }
}(jQuery, this.recline.Backend.GeoJson));
