// Mapbox GL code for maps for place gazetteers
// August 2018, structure based on gaz_google_map from 2006 - 2013
//*** SHOULD NOW BE OBSOLETE - APRIL 2021

var map;

var marker_geojson = {
    "type": "FeatureCollection",
    "features": [{
        "type": "Feature",
        "geometry": {
            "type": "Point",
            "coordinates": [cp_info.lng, cp_info.lat]
        },
        "properties": {
            "title": cp_info.name
        }
    }]
};
    
function Gaz_Map() {
    map = Create_Map({
        container: 'map',
        center: [cp_info.lng, cp_info.lat],
        attributionControl: true,
        zoom: cp_info.map_zoom - 6  // adjust from suitable for Google maps
    },{resizable:true});

    var marker = new mapboxgl.Marker({
        draggable: true
    })
        .setLngLat([cp_info.lng,cp_info.lat])
        .addTo(map);
    map.canalplan.places[cp_info.id] = true;  // already displayed

    function Marker_Dragged() {
        var lnglat = marker.getLngLat();
        map.flyTo({center: lnglat});
        var x = $$('map_coord_form');
        if(x.style.display != 'block') {     // first call
	          $$('MoveButton').onclick = function() {
	              Send_Changes(marker.getLngLat()); // NOT newpos - closure effect
	          };
	          $$('ClearButton').onclick = function() {
	              Restore_Marker(map,marker);
	          };
        }
        x.style.display = 'block';
        Update_Coordinates(lnglat);
    }

    marker.on('dragend',Marker_Dragged);

    map.canalplan.lastcentre = [cp_info.lng, cp_info.lat];

    function Marker_Moving() {
        var lnglat = marker.getLngLat();
        marker_geojson.features[0].geometry.coordinates = [lnglat.lng, lnglat.lat];
        map.getSource('markerlabel').setData(marker_geojson);
    }
    
    marker.on('drag',Marker_Moving);

    map.on('load',function() {
        // this adds the name below the marker
        map.addSource('markerlabel', {
            type: "geojson",
            data: marker_geojson
        });

        map.addLayer({
            id: "markerlabel",
            type: "symbol",
            source: "markerlabel",
            "layout": {
                "text-field": "{title}",
                "text-font": ["Open Sans Semibold"],
                "text-offset": [0, 0.1],
                "text-anchor": "top"
            }
        });
    });
}          

function Send_Changes(pt) {
    var fd = new FormData();
    fd.append('mode','adjust_coordinates');
    fd.append('latitude',pt.lat);
    fd.append('longitude',pt.lng);
    fd.append('placeid',cp_info.id);
    if(cp_info.name.indexOf('!') == 0)
        fd.append('markerplace',true);
    Async_Post("../cgi-bin/api.cgi", fd, function(savestat) {
        if(savestat.status == 'ok') {
            window.scrollTo(0,0);  // so shows banner when reloaded
            location.reload();
        }
    });
    var x = $$('map_coord_form');
    x.style.display = 'none';
}

function Update_Coordinates(pt) {
    var ts = $$("latlongshow");
    if(ts)
	      ts.value = pt.lat+','+pt.lng;
}

function Restore_Marker(map,marker) {
    marker.setLngLat([cp_info.lng,cp_info.lat]);
    var x = $$('map_coord_form');
    x.style.display = 'none';
    map.flyTo({center: [cp_info.lng, cp_info.lat]});
    marker_geojson.features[0].geometry.coordinates = [cp_info.lng, cp_info.lat];
    map.getSource('markerlabel').setData(marker_geojson);
}

function Map_Show_Links() {
    var todo = JSON.parse(cp_info.markerlist);
    var lines = [];
    var bounds = new mapboxgl.LngLatBounds();
//** Add markers for each marker place
    for(i in todo) {
        path1 = [];
        for(j=0;j<todo[i].porder.length;j++) {
            var pnt = [todo[i][todo[i].porder[j]].lng,todo[i][todo[i].porder[j]].lat];
            path1.push(pnt);
            bounds.extend(pnt);
        }
        lines.push(turf.lineString(path1));
    }
    map.fitBounds(bounds,{padding:20});
    var linejson = {"type": "FeatureCollection",
                    "features": lines
                   };
    var layerdat = map.getSource('cplinklines');
    if(layerdat == null) {
        map.addLayer({
            "id": "cplinklines",
            "type": "line",
            "source": {
                "type": "geojson",
                "data": linejson
            },
            "layout": {},
            "paint":{
                "line-width":5,
                "line-color": '#FFFF00'
            }
        });
    }
    window.map_animations = [];
    for(i=0; i<todo.length;i++) {
        window.map_animations.push(Do_Animate_Line(i,linejson.features[i]));
    }
}

function Do_Animate_Line(index,line) {
//    console.log(line);
    var linevars = {index: index,
                    counter: 0,
                    linelen: turf.length(line),
                    point: {
                        "type": "FeatureCollection",
                        "features": [{
                            "type": "Feature",
                            "properties": {},
                            "geometry": {
                                "type": "Point",
                                "coordinates": line.geometry.coordinates[0]
                            }
                        }]
                    },
                    route: line,
                    steps: 15 * line.geometry.coordinates.length // n steps per segment
                   };
//    console.log(linevars);
    map.addSource('animated_point_'+i, {
        "type": "geojson",
        "data": linevars.point
    });
    
    map.addLayer({
        "id": "animated_point_"+i,
        "source": 'animated_point_'+i,
        "type": "symbol",
        "layout": {
            "icon-image": "airport-15",
            "icon-rotate": ["get", "bearing"],
            "icon-rotation-alignment": "map",
            "icon-allow-overlap": true,
            "icon-ignore-placement": true
        }
    });
//    console.log("Going");
    Animate_Line(linevars);
}

function Animate_Line(linedata) {
    // update point
    // calculate distance along line
    var curdist = linedata.counter/ linedata.steps * linedata.linelen;
//    console.log("LDP ",linedata.point);
//    console.log("LDPF ",linedata.point.features);
//    console.log("LDPF0 ",linedata.point.features[0]);
    linedata.point.features[0].geometry.coordinates = turf.along(linedata.route, curdist);
    // calculate bearing to rotate icon correctly
    linedata.point.features[0].properties.bearing = turf.bearing(
        turf.point(linedata.route.geometry.coordinates[linedata.counter >= linedata.steps ? linedata.counter - 1 : linedata.counter]),
        turf.point(linedata.route.geometry.coordinates[linedata.counter >= linedata.steps ? linedata.counter : linedata.counter + 1])
    );
    // Update the source with this new data.
    map.getSource('animated_point_'+linedata.index).setData(linedata.point);
    
    ++linedata.counter;
    // Request the next frame of the animation so long as destination.
    // has not been reached.
//    if (linedata.point.features[0].geometry.coordinates[0] !== destination[0]) {
        requestAnimationFrame(Animate_Line);
//    }
}

// animation example from https://developers.google.com/maps/documentation/javascript/symbols#add_to_polyline
function was_Animate_Line(line) {
    var count = 0;
    var ww=window.setInterval(function() {
        count = (count + 1) % 200;        
        var icons = line.get('icons');
        icons[0].offset = (count / 2) + '%';
        line.set('icons', icons);
  }, 40);
    return ww;
}


function wqs_Map_Show_Links() {
    // subsequent clicks - stop/start the animation
    if(typeof window.map_animations != 'undefined') {
        for(i in window.map_animations) {
            clearInterval(window.map_animations[i]);
        }
        return;
    }
    var todo = JSON.parse(cp_info.markerlist);
    var workingcolour = '#00FF00';
    var path1 = [];
    window.map_animations=[];
    var lineSymbol = {
        path: google.maps.SymbolPath.FORWARD_OPEN_ARROW,
        strokeColor: '#000'
    };
    for(i in todo) {
        path1 = [];
        for(j=0;j<todo[i].porder.length;j++) {
            path1.push(new google.maps.LatLng(todo[i][todo[i].porder[j]].lat,todo[i][todo[i].porder[j]].lng));
        }
        var line = new google.maps.Polyline({
            path: path1,
            strokeColor: workingcolour,
            strokeOpacity: 1.0,
            strokeWeight: 4,
            icons: [{
                icon: lineSymbol,
                offset: '0%'
            }],                
            map: window.map
        });
        window.map_animations.push(Animate_Line(line));
    }
}
