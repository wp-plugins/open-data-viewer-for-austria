=== Open Data Viewer for Austria ===
Contributors: ondics
Donate link: http://ondics.de
Tags: ckan, opendata, open, data, apps, meta, visualization, okfn, austria, table, chart, map, shortcode, sidebar, widget, apps4austria
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Open Data Viewer for Austria brings the full power of open data from Austria to your WordPress Site.

== Description ==

**Open Data Viewer for Austria** integrates open data from the Austrian open data portal [data.gv.at](http://www.data.gv.at) to your WordPress site using tables, charts and maps.

= Try the Demo =
If you're interested in seeing what this plugin has to offer, try out the **Open Data Viewer for Austria** test drive! This site shows some stunning open data dataset presentations with the latest features of Open Data Viewer for Austria.

[**Open Data Viewer for Austria** Test Drive](http://apps4austria-open-data.ondics.de)

= Features =

Main Features of this plugin include:

- Dataset dropdown selection from the open data portal Austria
- Simple WordPress shortcodes for data and metadata
- The tag-assistent makes it even easier to decorate your pages
- Use the widget to show Open Data in sidebars, footers or anywhere you want
- Datasets can be displayed in tabular, graphical or map style
- Datasets can be filtered
- Sidebar widgets can be enlarged and fly in as popovers

All data is displayed live from the open data portal Austria. Forget copy and paste!

You **don't** need any login or API key for the Austrian Open Data Portal. Just download this plugin and get started. 

Have fun!

= Detailed Description =

The plugin offers a lot of features that makes integration of open data really easy and smooth.
In this version, csv and json are supported as data formats. 

1. Use a tabular view for comparing or following data series

- Variable colum widths
- Hide columns to mask data or to fit display size
- Arrange columns in new order

2. Use a graphical view for appealing charts and to beautify raw data 

- 5 chart styles: bar, line, point, point-line and columns
- Highly configurable
- Tooltips show up on mouse over

3. Show geographic data using the interactive map feature with integrated open street map 

- Show markers for geo points 
- Show areas for geo boundary data
- Unification of markers depending on scale factor
- Display infoboxes with additional info on click

4. Display meta data of datasets

- Choose between selected or full meta data display
- Define your meta data description instead of standard tag description

= Need more Info? =
This plugin is developed and provided to the WordPress and open data community by [Ondics GmbH](http://ondics.de) in Esslingen, Germany. 

This plugin is submitted to the [Apps4Austria Challenge](http://www.digitales.oesterreich.gv.at/site/7771/default.aspx).

There is a german documentation available at [apps4austria-open-data.ondics.de](http://apps4austria-open-data.ondics.de/?page_id=357).

The software used for the Open Data Portal for Austria is CKAN. CKAN is the leading open data portal software. Find more about CKAN in german at [ckan.de](http://ckan.de). The CKAN homepage is [ckan.org](http://ckan.org). 

== Installation ==

You can download and install *Open Data Viewer for Austria* using the built in WordPress plugin installer. If you download *Open Data Viewer for Austria* 
manually, make sure it is uploaded to "/wp-content/plugins/opendataviewerforaustria/".

You *don't* need any login or API key for the Austrian Open Data Portal. 
Just download this plugin and get started. 

== Frequently Asked Questions ==

= Why have you developed the Plugin? =

We want to fuel the open data community. Open data often lacks apps and business processes that rely on them. With the plugin we show how to make open data an integral part of a website. Since there are over 12.000 WordPress installations in Austria ( [Source: CMSCrawler](http://cmscrawler.com/tld/at) ), millions of visitors will see open data without recognizing them explicitly. We call that deep open data integration. Please join us to make the world a little bit better using that open data approach.

This plugin is submitted to the [Apps4Austria Challenge](http://www.digitales.oesterreich.gv.at/site/7771/default.aspx).

= How to display geo data? =

In a csv-file you just need to have a latitude and a longitude column. The rest is magic. Map areas can be fully handled in GeoJSON format. For the GeoJSON spec see here: [www.geojson.org](http://www.geojson.org/).

= Will you continue your efforts with this Plugin? =

Yes. We will provide support and updates from time to time. We have a bunch of features in our minds. Stay tuned to see our next releases. 

= What shortcodes are introduced by this plugin? =

There is only one shortcode introduced: 

<code>[ckan <property>=<value> ...]<description>[/ckan]</code>

This shortcode can be adjusted to your needs using property/value pairs.

* General Properties
 -  `url`
 -  `metaurl`
 -  `height`
 -  `width`
 -  `type` (see type specific formats below)
 -  `filters`
 -  `format`

* Table properties (`type="table"`)
 -  `hiddencolumns`
 -  `columnsorder`
 -  `columnswidth`

* Chart properties (`type="graph"`)
 - `graphtype`
 - `series`
 - `group`

* Map properties (`type="map"`)
 - `latfield`
 - `lonfield`
 - `geomfield`
 - `cluster`
 - `autozoom`

* Meta data (`type="metadata"`)
 - `metafields`

= Some WordPress shortcode examples, please! =

Display a map area of the town district "Donaustadt (Bezirk 22)" in Vienna:

<code>[ckan url="http://data.wien.gv.at/daten/geoserver/ows?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:BEZIRKSGRENZEOGD&srsName=EPSG:4326&outputFormat=json" metaurl="http://www.data.gv.at/katalog/api/rest/dataset/ogdwien_bezirksgrenzen" height="400" width="600" type="map" autoZoom="true" format="geojson" geomField="SHAPE" filters="{'type':'term','field':'BEZ','term':'22'}"]Bezirksgrenzen[/ckan]</code>

Display a map of traffic lights with acoustic signals in Vienna:

<code>[ckan url="http://data.wien.gv.at/daten/geoserver/ows?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:AKUSTISCHEAMPELOGD&srsName=EPSG:4326&outputFormat=csv" metaurl="http://www.data.gv.at/katalog/api/rest/dataset/ogdwien_ampeln-mit-akustikkennung-standorte" height="400" width="600" type="map" autoZoom="true" geomField="SHAPE" filters="{'type':'term','field':'BEZIRK','term':'22'}"]Ampeln mit Akustikkennung - Standorte [/ckan]</code>

Display a line chart showing the occupancy of homes for the elderly in Linz up to 2011 (Altenheime in Linz):

<code>[ckan url="http://data.linz.gv.at/katalog/soziales_gesellschaft/senior/staedtische_senioren_pflegeheime/shstadj.csv" metaurl="http://www.data.gv.at/katalog/api/rest/dataset/ogdlinz_belegung-der-staedtischen-senioren-und-pflegeheime" height="300px" width="700px" type="graph" graphType="lines-and-points" series="Zugang weiblich,Abgang weiblich,Zugang männlich,Abgang männlich" group="Jahr"]Belegung der städtischen Senioren- und Pflegeheime[/ckan]</code>

Display this dataset in tabular format:

<code>[ckan url="http://data.linz.gv.at/katalog/soziales_gesellschaft/senior/private_seniorenheime/2010/shprges_2010.csv" metaurl="http://www.data.gv.at/katalog/api/rest/dataset/ogdlinz_belegung-der-staedtischen-senioren-und-pflegeheime" height="210px" width="600px" type="grid" hiddenColumns="Verpflegstage,Personal insgesamt gerechnet als Vollzeitbeschäftigte"]Belegung der städtischen Senioren- und Pflegeheime[/ckan]</code>

Display some meta data about this dataset:

<code>[ckan url="http://data.linz.gv.at/katalog/soziales_gesellschaft/senior/staedtische_senioren_pflegeheime/shstadj.csv" metaurl="http://www.data.gv.at/katalog/api/rest/dataset/ogdlinz_belegung-der-staedtischen-senioren-und-pflegeheime" height="250" width="600" type="metadata" metafields="license_title(Lizenz),maintainer_email(Verantwortlicher),author(Autor),groups(Gruppen)"]Belegung der städtischen Senioren- und Pflegeheime[/ckan]</code>


== Screenshots ==

1. **Map-Points**: Display markers on a map from geo coordinates
2. **Meta data**: Add some meta data to datasets
3. **Tables**: Display all kind of data in configurable table format
4. **Map-Areas**: A nice feature to highlight bounded areas
5. **Shortcode Assistant**: Select datasets from a list and build your own shortcodes. No docs required!
6. **Shortcode Assistant**: Configure meta data to match your needs
7. **Table modifications**: Select columns, adjust size, ...
8. **Charts**: Configuration of line charts
9. **Maps**: Configuration of map display

== Changelog ==

= 1.0.7 =

* security fix (thanks to C. Mehlmauer)

= 1.0.6 =

* Widget configuration now working in non-accessibility-mode
* Fixed javascript bug in admin-mode

= 1.0.5 =

* Resolved conflicts in admin-backend

= 1.0.4 =

* Improved usability in tag-assitant and widget
* Fullscreen view

= 1.0.3 =

* Resolved conflicts in admin-backend after plugin installation (Image upload, stylesheet)
* Improved plugin speed (loading on demand only)

= 1.0.2 =

* Replacing cUrl with WordPress stuff

= 1.0.1 =

* Improving installation process
* Removed screenshots from plugin folder

= 1.0 =

* Select datasets from Open Data for Austria Portal
* Shortcode assistant for pages
* Interactive widget with popover enlargement
* Display datasets as tables, charts or maps
* Highly configurable by shortcode properties
