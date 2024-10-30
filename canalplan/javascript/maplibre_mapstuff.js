// all rewritten during migration to mapbox GL - August 2018
// almost all code is in here, as we want the features to work on all maps

/* there is a delay when loading the text for the popup, which can
 * mean you have moved out before it has arrived.  This flag prevents
 * the popup then appearing and never going away.  I'm sure there must
 * be a way not to keep it global, but I haven't worked it out yet */

var mb_ms_globals = {popuplive: false,
                     infopopup: null,
                     hoveredww: null
                    };

// these create event functions bound to the relevant map
function MB_pl_enter(map) {
    return function(e) {
        if(!e.features[0].id)  // if no id (say a tap symbol) do nothing
            return;
        if(mb_ms_globals.hoveredpl) {
            map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_places', id: mb_ms_globals.hoveredpl}, {textcol: "black"});
        }
        mb_ms_globals.hoveredpl = e.features[0].id;            
        map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_places', id: mb_ms_globals.hoveredpl}, {textcol: "purple"});
        
        mb_ms_globals.popuplive = true;
        map.getCanvas().style.cursor = 'pointer';
        var coordinates = e.features[0].geometry.coordinates.slice();
        Async_JSON('../cgi-bin/api.cgi?mode=place&id='+e.features[0].properties.cp_id, Show_Place_Info, {map:map, pu:mb_ms_globals.infopopup, coordinates:coordinates});
    };
}

function MB_pl_leave(map) {
    return function() {
        map.getCanvas().style.cursor = '';
        mb_ms_globals.infopopup.remove();
        mb_ms_globals.popuplive = false;
        if(mb_ms_globals.hoveredpl) {
            map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_places', id: mb_ms_globals.hoveredpl}, {textcol: "black"});
        }
        mb_ms_globals.hoveredpl = null;
    };
}

function MB_pl_click(map) {
    return function(e) {
        if(!e.features[0].id)  // if no id (say a tap symbol) do nothing
            return;
        window.open('/place/'+e.features[0].properties.cp_id,'_blank');
    };
}

function MB_ww_enter(map) {
    return function(e) {
        if (mb_ms_globals.hoveredww) {
            map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_waterways', id: mb_ms_globals.hoveredww}, {hover: false});
        }
        mb_ms_globals.hoveredww = e.features[0].id;
        map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_waterways', id: mb_ms_globals.hoveredww}, {hover: true});
        mb_ms_globals.popuplive = true;
        map.getCanvas().style.cursor = 'pointer';
        Async_JSON('../cgi-bin/api.cgi?mode=waterway-info&id='+e.features[0].properties.cp_id, Show_Waterway_Info, {map:map, pu:mb_ms_globals.infopopup, coordinates:e.lngLat});
    };
}

function MB_ww_leave(map) {
    return function() {
        map.getCanvas().style.cursor = '';
        mb_ms_globals.infopopup.remove();
        if (mb_ms_globals.hoveredww) {
            map.setFeatureState({source: 'canalplantiles', sourceLayer: 'canalplan_waterways', id: mb_ms_globals.hoveredww}, {hover: false});
        }
        mb_ms_globals.hoveredww =  null;
        mb_ms_globals.popuplive = false;
    };
}

function MB_ww_click(map) {
    return function(e) {
        window.open('/waterway/'+e.features[0].properties.cp_id,'_blank');   
    };
}

function Create_Map(opts, cpopts) {
    if(opts.style == undefined) {
        if(cp_info.map_stylepath && cp_info.map_stylefile)
            opts.style = cp_info.map_stylepath + cp_info.map_stylefile;
        else
            throw "No style - not in opts.style or cp_info.map_*";
    }
    if(opts.style.indexOf('.json') === -1)
        opts.style += '.json';
    if(cpopts.nocanals) {
        opts.style = opts.style.replace(/(\.[\w\d_-]+)$/i, '_background$1');
    }
    if(cpopts.faded) {
        opts.style = opts.style.replace(/(\.[\w\d_-]+)$/i, '_faded$1');
    }
    if(cpopts.closeup)
        opts.style = opts.style.replace(/(\.[\w\d_-]+)$/i, '_closeup$1');
    // if resizable, will add it elsewhere later
    if(cpopts.resizable)
        opts.attributionControl = false;
    else
        opts.attributionControl = true;  // always want it
    var map = new mapboxgl.Map(opts);
    map.canalplan = new Object;
    map.canalplan.fetchcounter=-1;
    map.canalplan.places = [];
    map.canalplan.geojson = null;
    if(opts.bounds) {
        // to change it when the div size changes - resize doesn't do enough
        map.canalplan.resize_map=function() {
            map.resize();
            map.fitBounds(opts.bounds);
        };
    }
    if(cpopts.resizable) {
        // new place for the attribution control to make room for resizer
//        let container = $$('map-wrapper');
//        if(container == null)
//            throw "Adding resizer when no 'map-wrapper' element"

        map.addControl(new mapboxgl.AttributionControl(), 'top-left');
        var resizer = Create_Resizer(map);
    }

    if(cpopts.nogazclick) {
    } else {
        map.on('load', function() {Setup_Actions(map, cpopts)});
    }

    if(cpopts.nocontrol) {
    } else {
        map.addControl(new mapboxgl.NavigationControl());
        //    map.addControl(new mapboxgl.FullscreenControl())
    }

    map.canalplan.options = cpopts;
    return map;   
}

// for each of mouse-in, mouse-out and click set the following in cpopts
// nopl[enter|out|click] set to anything - do nothing
// onpl[enter|out|click] use this function instead of the default
function MB_Add_Place_Actions(map, cpopts) {
    // attach functions to all places (need to iterate over layers)
    // we find all layers with ids starting "cp-place-"
    map.getStyle().layers.filter(layer => layer.id.startsWith('cp-place-')).forEach(function(v) {  
        if(cpopts.noplenter) {  // suppress all activity
        } else {
            if(cpopts.onplenter) {   // user supplied function
                map.on('mouseenter', v.id, cpopts.onplenter);
            } else {  // default - show an info box
                map.on('mouseenter', v.id, MB_pl_enter(map));
            }
        }
        if(cpopts.noplout) {  // suppress all activity
        } else {
            if(cpopts.onplout) {  // user supplied function
                map.on('mouseleave', v.id, cpopts.onplout);
            } else { // default - clear info box              
                map.on('mouseleave', v.id, MB_pl_leave(map));
            }
        }
        if(cpopts.noplclick) {
        } else {
            if(cpopts.onplclick) {
                map.on('click', v.id, cpopts.onplclick);
            } else {
                map.on('click', v.id, MB_pl_click(map));
            }
        }
    });
}

function MB_Remove_Place_Actions(map, funstogo) {
    var newtogo = {};
    if(funstogo) {
        if(funstogo.onplenter)
            newtogo.mouseenter = funstogo.onplenter;
        if(funstogo.onplout)
            newtogo.mouseleave = funstogo.onplout;
        if(funstogo.onlick)
            newtogo.click = funstogo.onplclick;
    } else {
        newtogo = {mouseenter:MB_pl_enter(map), mouseleave:MB_pl_leave(map),click:MB_pl_click(map)};
    }
    ['dot','spot','feature','village','town','city'].forEach(function(l) {  
        Object.keys(newtogo).forEach(function(v) {
            map.off(v, 'cp-place-'+l, newtogo[v]);
        });
    });
}

// for each of mouse-in, mouse-out and click set the following in cpopts
// noww[enter|out|click] set to anything - do nothing
// onww[enter|out|click] use this function instead of the default
function MB_Add_Waterway_Actions(map, cpopts) {
    // The difference in coordinates is because for places we want to anchor on
    // the dot of the place, but for waterways on where the mouse is 
    map.getStyle().layers.filter(layer => layer.id.startsWith('cp-waterway-')).forEach(function(v) {  
        if(cpopts.nowwenter) {  // suppress all activity
        } else {
            if(cpopts.onwwenter) {  // user supplied function
                map.on('mouseenter', v.id, cpopts.onwwenter);
            } else {   // default - expand the line and show info box
                map.on('mouseenter', v.id, MB_ww_enter(map));
            }
        }
        if(cpopts.nowwout) {  // suppress all activity
        } else {
            if(cpopts.onwwout) {  // user supplied function
                map.on('mouseleave', v.id, cpopts.onwwout);
            } else {   // default - restore line and remove info box
                map.on('mouseleave', v.id, MB_ww_leave(map));
            }
        }
        if(cpopts.nowwclick) {  // suppress all activity
        } else {
            if(cpopts.onwwclick) {  // user supplied function
                map.on('click', v.id, cpopts.onwwclick);
            } else {   // default - open gazetteer in new page/tab
                map.on('click', v.id, MB_ww_click(map));
            }
        }
    });
}

function MB_Remove_Waterway_Actions(map, funstogo) {
    var newtogo = {};
    if(funstogo) {
        if(funstogo.onwwenter)
            newtogo.mouseenter = funstogo.onwwenter;
        if(funstogo.onwwout)
            newtogo.mouseleave = funstogo.onwwout;
        if(funstogo.onlick)
            newtogo.click = funstogo.onwwclick;
    } else {
        newtogo = {mouseenter:MB_ww_enter(map), mouseleave:MB_ww_leave(map),click:MB_ww_click(map)};
    }
    ['narrow','broad','commercial','small','large','tidal','seaway','lake'].forEach(function(l) {
        ['','-excluded'].forEach(function(xc) {
            var lname = 'cp-waterway-'+l+xc;
            Object.keys(newtogo).forEach(function(v) {
                map.off(v, lname, newtogo[v]);
            });
        });
    });
}
    
function Setup_Actions(map, cpopts) {
    mb_ms_globals.infopopup = new mapboxgl.Popup({
        className: 'placeinfo-popup',
        closeButton: false,
        closeOnClick: true
    });

    if(cpopts.noplaceactions) {   // suppresses all in one go
    } else
        MB_Add_Place_Actions(map, cpopts);

    if(cpopts.nowaterwayactions) {
    } else {
        MB_Add_Waterway_Actions(map, cpopts);
    }
}

function Show_Place_Info(vals, parms) {
    if(mb_ms_globals.popuplive == false) return;
    var description = '<b>'+vals.name+'</b><br>'+vals.detail;
    parms.pu.setLngLat(parms.coordinates)
        .setHTML(description)
        .addTo(parms.map);
}

function Show_Waterway_Info(vals, parms) {
    if(mb_ms_globals.popuplive == false) return;
    var description = '<b>'+vals.fullname+'</b><br>'+vals.about;
    parms.pu.setLngLat(parms.coordinates)
        .setHTML(description)
        .addTo(parms.map);
}

function Create_Resizer(map) {
    if(typeof CPResizeControl == 'undefined') {
        Load_And_Run_JS("/can_js/resize_map.js", function() {Really_Create_Resizer(map)});
    } else
        Really_Create_Resizer(map);
}

function Really_Create_Resizer(map) {
    var rz = new CPResizeControl(map);
    rz.changeCallBack = function(map, newwidth,newheight, dx, dy) {
	var container = $$('map-wrapper');
 	var curcent = map.getCenter();
        if(container) {
	    var width = parseInt(container.style.width);
	    var height = parseInt(container.style.height);
	    width += dx;
	    height += dy;
	    container.style.width = width + "px";
	    container.style.height = height + "px";
        }
	map.fire('resize');
	map.setCenter(curcent);
    };
    return rz;
}

function Coordinates_To_Geodata(plist) {
    var coords = [];
    for(i=0; i<plist.length; i++)
        coords.push([plist[i].lng, plist[i].lat]);
    return {
        "type": "FeatureCollection",
        "features": [{
            "type": "Feature",
            geometry: {
                "type": "LineString",
                "properties": {},
                "coordinates": coords
            }
        }]};
}

function Dimensions_From_Geodata(mapdiv, dat) {
    var bounds = turf.bbox(dat);
    var size = [mapdiv.offsetWidth,mapdiv.offsetHeight];
    var res = geoViewport.viewport(bounds, size, 5,18, 512, true);
    res.zoom -= 1;   // can't get 512 tile size to work, so do this instead
    return res;
}

// may not need anything below this line

// From: https://gist.github.com/ismaels/6636986
// source: http://doublespringlabs.blogspot.com.br/2012/11/decoding-polylines-from-google-maps.html
function Decode_Polyline(encoded) {
    var points=[]
    var index = 0, len = encoded.length;
    var lat = 0, lng = 0;
    while (index < len) {
        var b, shift = 0, result = 0;
        do {
            b = encoded.charAt(index++).charCodeAt(0) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlat = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
        lat += dlat;
        shift = 0;
        result = 0;
        do {
            b = encoded.charAt(index++).charCodeAt(0) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlng = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
        lng += dlng;       
        // lng/lat pairs is standard for geojson
        points.push([lng/1E5, lat/1E5]);
    }
    return points
}

/* Pass any number of polylines in, get a geojson object out */
function Polylines_To_LineString(pdata) {
    var features = [];
    for(var i=0; i<pdata.length; i++) {
	      if(typeof(pdata[i]) == 'string' && (pdata[i]=='no polyline' || pdata[i] == 'at all'))
            continue;
        var points = Decode_Polyline(pdata[i].pline);
        features.push({
            type: "Feature",
            geometry: {
                type: "LineString",
                properties: {},
                coordinates: points
            }
        });
    }
    var geodata = {
        type: "FeatureCollection",
        features: features
    };
    return geodata
}


// for pdf generation - so it knows the page is ready
function Check_Mystuff(mapdata) {
    if(status_helper.getting_background == true && status_helper.gotbackground == false) {
        setTimeout(Check_Mystuff,1000);
    } else {
        window.status = 'cpdone';
    }
}

// do want this!

function Any_Closeup_Maps() {
    var el = document.getElementsByClassName('closeup_map');
    for(let i=0; i<el.length; i++) {
        setTimeout(function() {   // run in parallel
            Show_Closeup_Map(el[i]);
        },0);
    }
}

function Show_Closeup_Map(mdiv) {
    // try to get a cached image, call plotting otherwise
    var img = new Image();
    var plid = mdiv.getAttribute('data-placeid');
    var acttxt = mdiv.getAttribute('data-onclick');
    var action = null;
    if(acttxt)
        action = Function(acttxt);
    img.addEventListener('load', function() {Got_Closeup(mdiv, img, action)});
    img.addEventListener('error', function() {Failed_Closeup(mdiv, plid, action)});
    if(plid.indexOf(':') == -1) {
        img.src = "/html/cached_closeup_maps/"+plid[0]+'/'+plid+".png";
//        img.setAttribute('data-type', 'place');
    } else {
        var tmap = {'a':'area','w':'waterway','f':'feature'};
        var i = plid.charAt(0);
        var type = tmap[i];
        if(type == null) throw "Unknown type in closeup map id"
        img.src = "/html/cached_closeup_maps/"+type+"/"+plid.slice(2);
//        img.setAttribute('data-type', type);
    }
}

function Got_Closeup(mdiv, img, action) {
    // insert it, making it circular and with click-to-edit etc
    var ih = '<img class="cc_map" src="'+img.src+'"';
    if(action)
        ih += 'onclick="'+action+'"';
    ih += '>';
    mdiv.innerHTML = ih;
}

function Failed_Closeup(mdiv, plid, action) {
    // failed to pull from cache, so draw it and upload
    var lattr = mdiv.getAttribute('data-latitude');
    if(lattr) {
        var la = parseFloat(lattr);
        var lo = parseFloat(mdiv.getAttribute('data-longitude'));
        var zo = parseInt(mdiv.getAttribute('data-zoom'));
        Draw_Closeup_Map(mdiv, plid, la, lo, zo, action);
    } else {
        Async_JSON('../cgi-bin/api.cgi?mode=location_map_bounds&id='+encodeURIComponent(plid), Parse_And_Draw_Closeup_Map, {plid:plid, mc: mdiv, action:action});
    }
}

function Parse_And_Draw_Closeup_Map(dat, parm) {
    var mz = dat.defzoom;
    if(parm.plid.indexOf(':') == -1) {
        if(dat.mapping_JSON.closeup_zoom)
            mz = dat.mapping_JSON.closeup_zoom;
        Draw_Closeup_Map(parm.mc, parm.plid, dat.latitude, dat.longitude, mz, parm.action);
    } else {
        switch(parm.plid.charAt(0)) {
            case 'a':
            Load_And_Run_JS("/pages/code/javascript/area_map.js", function() {Draw_Area_Closeup_Map({mdiv: parm.mc, dl: dat, fid: parm.plid.slice(2)})});
            break;
            case 'f':
            dat.showon = parm.mc;
            Load_And_Run_JS("/pages/code/javascript/feature_map.js", function() {Draw_Feature_Closeup_Map({mdiv: parm.mc, dl: dat, fid: parm.plid.slice(2)})});
            break;
            case 'w':
            dat.showon = parm.mc;
            Load_And_Run_JS("/pages/code/javascript/waterway_map.js", function() {Draw_Waterway_Closeup_Map({dl: dat, wid: parm.plid.slice(2)})});
            break;
            default:
            throw "Unimplemented type for closeup map id:"+plid
            break;
        }
    }
}

function Draw_Closeup_Map(mdiv, plid, lat, lng, zoom, action) {
    var cmap = Create_Map({
        container: mdiv,
        center: [lng,lat],
        zoom: zoom
    }, {closeup:true, nocontrol: true, noplenter:true, noplout:true, noplclick:true});
    cmap.on('idle', function() {
        Upload_Closeup(cmap, "place", plid);
    });
    cmap.on('click',action);
    mdiv.onClick = action;
}

// takes bounds.ne_lat etc.  Returns same
function Map_Padding(bounds, padding) {
    const latpad  = (bounds.ne_lat - bounds.sw_lat) * padding/100.0
    const lngpad = (bounds.ne_lng - bounds.sw_lng) * padding/100.0
    bounds.ne_lat = bounds.ne_lat + latpad
    bounds.sw_lat = bounds.sw_lat - latpad
    bounds.ne_lng = bounds.ne_lng + lngpad
    bounds.sw_lng = bounds.sw_lng - lngpad
    return bounds;
}

function Page_Show_Waterway_Map(parms) {
    // for the closeup we shrink the bounds to show more surrounds
    parms.dl.displaybounds = Map_Padding(parms.dl.displaybounds, 25);
    var cmap = Show_Waterway_Map(parms.wid, parms.dl, {nocontrol: true, noplenter:true, noplout:true, noplclick:true, nowwenter:true, nowwout:true, nowwclick:true});
    cmap.on('idle', function() {
        Upload_Closeup(cmap, "waterway", parms.wid);
    });
}

function Upload_Closeup(map, type, id) {
    map.getCanvas().toBlob(function(b) {
        var fd = new FormData();
        fd.append("mapimage", b);
        fd.append("mode","closeupmap");
        fd.append("type",type);
        fd.append("filename",id);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/cgi-bin/callback.cgi', true);
        xhr.send(fd);
        xhr.addEventListener("load", function() {
	    //console.log("Successfully sent ",id," of type ",type);
        });
    });
}

// turns the data API calls give into an array for a map bounds call
function Bounds_Data_To_Array(bnds) {
    return [[bnds.sw_lng, bnds.sw_lat], [bnds.ne_lng, bnds.ne_lat]];
}
