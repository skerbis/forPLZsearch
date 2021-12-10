<div class="uk-margin">
    <form class="" id="search" action="<?php echo rex_getUrl('REX_ARTICLE_ID')?>#map">
 <input class="uk-search-input" id="one" name="plz" type="number" placeholder="PLZ">
<input class="uk-search-input" id="two" name="distance" type="number" value = 10 placeholder="Search">
        <button type="submit">Eingaben absenden</button>
    </form>
</div>

<?php
$geoJson = plzsearch::getPlaces('rex_kunden', 'json');    
$placedata = plzsearch::searchByPostCode(rex_request('plz','int'));
$distance =  rex_request('distance','int');          
$lat = $placedata['lat'];
$lon = $placedata['lon'];
$searchplace = $placedata['place_name'];
$plz = plzsearch::searchByLatLon($lat, $lon, $distance);        
$cood = plzsearch::getPlaces('rex_kunden', 'latlon',$plz);    
if ($cood==null)
{
 $cood = plzsearch::getPlaces('rex_kunden', 'latlon');  
 $bounds =  'map.fitBounds(markers.getBounds())';   
}
else
{
 $bounds =  'var bounds = new L.LatLngBounds(['.$cood.']);
 map.fitBounds(bounds)';
}
$dataset =  plzsearch::getPlaces('rex_kunden', 'dataset', $plz);  

        
          
?>



<div id="map" style="min-height: 600px; height: 30vh; display: block; width: 100%;"></div>

<!-- in den Head des Templates -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.6.0/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.6.0/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster-src.min.js"></script>
<!-- ENDE: in den Head des Templates -->



<script type="text/javascript">
    <?php echo $geoJson; ?>
</script>

<script type="text/javascript">
    
    L.Control.ZoomMin = L.Control.Zoom.extend({
  options: {
    position: "topleft",
    zoomInText: "+",
    zoomInTitle: "Zoom in",
    zoomOutText: "-",
    zoomOutTitle: "Zoom out",
    zoomMinText: "<i class='fa fa-eye' aria-hidden='true'></i>",
    zoomMinTitle: "<i class='fa fa-eye' aria-hidden='true'></i>"
  },

  onAdd: function (map) {
    var zoomName = "leaflet-control-zoom"
      , container = L.DomUtil.create("div", zoomName + " leaflet-bar")
      , options = this.options

    this._map = map

    this._zoomInButton = this._createButton(options.zoomInText, options.zoomInTitle,
     zoomName + '-in', container, this._zoomIn, this)

    this._zoomOutButton = this._createButton(options.zoomOutText, options.zoomOutTitle,
     zoomName + '-out', container, this._zoomOut, this)

    this._zoomMinButton = this._createButton(options.zoomMinText, options.zoomMinTitle,
     zoomName + '-min', container, this._zoomMin, this)

    this._updateDisabled()
    map.on('zoomend zoomlevelschange', this._updateDisabled, this)

    return container
  },
  _zoomMin: function () {
    if (this.options.minBounds) {
      return this._map.fitBounds(this.options.minBounds);
    }
    this._map.setZoom(this._map.getMinZoom())
  },
  _updateDisabled: function () {
    var map = this._map
      , className = "leaflet-disabled"
    L.DomUtil.removeClass(this._zoomInButton, className)
    L.DomUtil.removeClass(this._zoomOutButton, className)
    L.DomUtil.removeClass(this._zoomMinButton, className)
    if (map._zoom === map.getMinZoom()) {
      L.DomUtil.addClass(this._zoomMinButton, className)
    }
  }
})


    var tiles = L.tileLayer('/osmtype/german/{z}/{x}/{y}.png', {
        zoomControl: false,
        maxZoom: 16,
        minZoom: 0,
        attribution: '&copy; Map: <a href="/osmtype/german/{z}/{x}/{y}.png">OpenStreetMap</a> contributors'
    });


    var map = L.map('map', {
            zoomControl: false


        })
        .addLayer(tiles);


    var markers = L.markerClusterGroup();
    var geoJsonLayer = L.geoJson(geoJsonData, {
        onEachFeature: function(feature, layer) {
            layer.bindPopup(feature.descriptionhtml);
        }
   });
 
    <?=$bounds?>
    
    markers.addLayer(geoJsonLayer);
    map.addLayer(markers);
   


map.addControl(new L.Control.ZoomMin())
    // map.addControl(new L.Control.ZoomMin())
</script>
