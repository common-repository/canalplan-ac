// any or all of the route maps - March 2011
// ported to maplibre GL - August-November 2018

var status_helper = {main: false, daily: [], changed: false, maps: [], sent: [], lasttime: performance.now()};
var img_preload = [];

// in an ideal world we'd use promises for this.  But IE.
function Add_Markers_Start(imgurl,map, stops) {
    // load all the images - doing it like this makes them run in parallel
    var promises = [];
    var stopdat = [];
   // var imgurl='" . plugin_dir_url(__FILE__) . "markers/';
    for(let i=0; i<stops.length; i++) {
        promises.push[map.loadImage(imgurl+stops[i].icon+".png").then((image) => map.addImage(stops[i].icon, image.data))];
        // prepare the dataset to display at the same time
        stopdat.push({
            "type": "feature",
            "geometry": {
                "type": "Point",
                "coordinates": [stops[i].lng, stops[i].lat]
            },
            "properties" : {
                "icon": stops[i].icon,
                "refid": stops[i].idx
            }
        });
    }
    // allSettled means it will continue when all done, even if there are errors
    Promise.allSettled(promises).then(() => {
        // now display everything
        map.addLayer({
            "id": "stopping-markers",
            "type": "symbol",
            "source": {
                "type": "geojson",
                "data": {
                    "type": "FeatureCollection",
                    "features": stopdat
                }
            },
            "layout": {
                "icon-image": "{icon}",
                "icon-anchor": "bottom",
                "icon-size": 1
            }
        });
        // attach pop-ups to the new layer - close clone of code in mapbox_mapstuff
        var markerpopup = new maplibregl.Popup({
            className: 'placeinfo-popup',
            closeButton: true,
            closeOnClick: true
        });
        map.on('mouseenter','stopping-markers',function(e) {
            // if(popuplive)
            // return;
            // popuplive = true;
            map.getCanvas().style.cursor = 'pointer';
            var coordinates = e.features[0].geometry.coordinates.slice();
            markerpopup.setLngLat(coordinates)
                .setHTML(Get_Info_Content(e.features[0].properties.refid))
                .addTo(map);
        });
    });
}

function Get_Info_Content(idx) {
    var hd = steveqa('day_header_'+idx);
    var bd = steveqa('day_body_'+idx);
    var toret = '';
    if (typeof(hd.innerHTML)!= "undefined") 
	toret += hd.innerHTML;
      if (typeof(bd.innerHTML)!= "undefined") {
	if(toret)
	    toret += '<br>';
	toret += bd.innerHTML;
    }
    return toret;
}

