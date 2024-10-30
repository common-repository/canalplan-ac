// PolylineEncoder.js by Mark McClure  April/May 2007
// 
// This module defines a PolylineEncoder class to encode
// polylines for use with Google Maps together with a few
// auxiliary functions. Documentation at
// http://facstaff.unca.edu/mcmcclur/GoogleMaps/EncodePolyline/PolylineEncoder.html

PolylineEncoder = function(numLevels, zoomFactor, verySmall, forceEndpoints) {
  var i;
  if(!numLevels) {
    numLevels = 18;
  }
  if(!zoomFactor) {
    zoomFactor = 2;
  }
  if(!verySmall) {
    verySmall = 0.00001;
  }
  if(!forceEndpoints) {
    forceEndpoints = true;
  }
  this.numLevels = numLevels;
  this.zoomFactor = zoomFactor;
  this.verySmall = verySmall;
  this.forceEndpoints = forceEndpoints;
  this.zoomLevelBreaks = new Array(numLevels);
  for(i = 0; i < numLevels; i++) {
    this.zoomLevelBreaks[i] = verySmall*Math.pow(zoomFactor, numLevels-i-1);
  }
}


PolylineEncoder.prototype.dpEncode = function(points) {
  var absMaxDist = 0;
  var stack = [];
  var dists = new Array(points.length);
  var maxDist, maxLoc, temp, first, last, current;
  var i, encodedPoints, encodedLevels;
  
  if(points.length > 2) {
    stack.push([0, points.length-1]);
    while(stack.length > 0) {
      current = stack.pop();
      maxDist = 0;
      for(i = current[0]+1; i < current[1]; i++) {
        temp = this.distance(points[i], 
          points[current[0]], points[current[1]]);
        if(temp > maxDist) {
          maxDist = temp;
          maxLoc = i;
          if(maxDist > absMaxDist) {
            absMaxDist = maxDist;
          }
        }
      }
      if(maxDist > this.verySmall) {
        dists[maxLoc] = maxDist;
        stack.push([current[0], maxLoc]);
        stack.push([maxLoc, current[1]]);
      }
    }
  }
  
  encodedPoints = this.createEncodings(points, dists);
  encodedLevels = this.encodeLevels(points, dists, absMaxDist);
  return {
    encodedPoints: encodedPoints,
    encodedLevels: encodedLevels,
    encodedPointsLiteral: encodedPoints.replace(/\\/g,"\\\\")
  }
}

PolylineEncoder.prototype.dpEncodeToJSON = function(points,
  color, weight, opacity) {
  var result;
  
  if(!opacity) {
    opacity = 0.9;
  }
  if(!weight) {
    weight = 3;
  }
  if(!color) {
    color = "#0000ff";
  }
  result = this.dpEncode(points);
  return {
    color: color,
    weight: weight,
    opacity: opacity,
    points: result.encodedPoints,
    levels: result.encodedLevels,
    numLevels: this.numLevels,
    zoomFactor: this.zoomFactor
  }
}

PolylineEncoder.prototype.dpEncodeToGPolyline = function(points,
  color, weight, opacity) {
  if(!opacity) {
    opacity = 0.9;
  }
  if(!weight) {
    weight = 3;
  }
  if(!color) {
    color = "#0000ff";
  }
  return new GPolyline.fromEncoded(
    this.dpEncodeToJSON(points, color, weight, opacity));
}

PolylineEncoder.prototype.dpEncodeToGPolygon = function(pointsArray,
  boundaryColor, boundaryWeight, boundaryOpacity,
  fillColor, fillOpacity, fill, outline) {
  var i, boundaries;
  if(!boundaryColor) {
    boundaryColor = "#0000ff";
  }
  if(!boundaryWeight) {
    boundaryWeight = 3;
  }
  if(!boundaryOpacity) {
    boundaryOpacity = 0.9;
  }
  if(!fillColor) {
    fillColor = boundaryColor;
  }
  if(!fillOpacity) {
    fillOpacity = boundaryOpacity/3;
  }
  if(fill==undefined) {
    fill = true;
  }
  if(outline==undefined) {
    outline = true;
  }
  
  boundaries = new Array(0);
  for(i=0; i<pointsArray.length; i++) {
    boundaries.push(this.dpEncodeToJSON(pointsArray[i],
      boundaryColor, boundaryWeight, boundaryOpacity));
  }
  return new GPolygon.fromEncoded({
    polylines: boundaries,
    color: fillColor,
    opacity: fillOpacity,
    fill: fill,
    outline: outline
  });
}

PolylineEncoder.prototype.distance = function(p0, p1, p2) {
  var u, out;
  
  if(p1.lat() === p2.lat() && p1.lng() === p2.lng()) {
    out = Math.sqrt(Math.pow(p2.lat()-p0.lat(),2) + Math.pow(p2.lng()-p0.lng(),2));
  }
  else {
    u = ((p0.lat()-p1.lat())*(p2.lat()-p1.lat())+(p0.lng()-p1.lng())*(p2.lng()-p1.lng()))/
      (Math.pow(p2.lat()-p1.lat(),2) + Math.pow(p2.lng()-p1.lng(),2));
  
    if(u <= 0) {
      out = Math.sqrt(Math.pow(p0.lat() - p1.lat(),2) + Math.pow(p0.lng() - p1.lng(),2));
    }
    if(u >= 1) {
      out = Math.sqrt(Math.pow(p0.lat() - p2.lat(),2) + Math.pow(p0.lng() - p2.lng(),2));
    }
    if(0 < u && u < 1) {
      out = Math.sqrt(Math.pow(p0.lat()-p1.lat()-u*(p2.lat()-p1.lat()),2) +
        Math.pow(p0.lng()-p1.lng()-u*(p2.lng()-p1.lng()),2));
    }
  }
  return out;
}

PolylineEncoder.prototype.createEncodings = function(points, dists) {
  var i, dlat, dlng;
  var plat = 0;
  var plng = 0;
  var encoded_points = "";

  for(i = 0; i < points.length; i++) {
    if(dists[i] != undefined || i == 0 || i == points.length-1) {
      var point = points[i];
      var lat = point.lat();
      var lng = point.lng();
      var late5 = Math.floor(lat * 1e5);
      var lnge5 = Math.floor(lng * 1e5);
      dlat = late5 - plat;
      dlng = lnge5 - plng;
      plat = late5;
      plng = lnge5;
      encoded_points += this.encodeSignedNumber(dlat) + 
        this.encodeSignedNumber(dlng);
    }
  }
  return encoded_points;
}

PolylineEncoder.prototype.computeLevel = function(dd) {
  var lev;
  if(dd > this.verySmall) {
    lev=0;
    while(dd < this.zoomLevelBreaks[lev]) {
      lev++;
    }
    return lev;
  }
}

PolylineEncoder.prototype.encodeLevels = function(points, dists, absMaxDist) {
  var i;
  var encoded_levels = "";
  if(this.forceEndpoints) {
    encoded_levels += this.encodeNumber(this.numLevels-1)
  } else {
    encoded_levels += this.encodeNumber(
      this.numLevels-this.computeLevel(absMaxDist)-1)
  }
  for(i=1; i < points.length-1; i++) {
    if(dists[i] != undefined) {
      encoded_levels += this.encodeNumber(
        this.numLevels-this.computeLevel(dists[i])-1);
    }
  }
  if(this.forceEndpoints) {
    encoded_levels += this.encodeNumber(this.numLevels-1)
  } else {
    encoded_levels += this.encodeNumber(
      this.numLevels-this.computeLevel(absMaxDist)-1)
  }
  return encoded_levels;
}


PolylineEncoder.prototype.encodeNumber = function(num) {
  var encodeString = "";
  var nextValue, finalValue;
  while (num >= 0x20) {
    nextValue = (0x20 | (num & 0x1f)) + 63;

    encodeString += (String.fromCharCode(nextValue));
    num >>= 5;
  }
  finalValue = num + 63;

  encodeString += (String.fromCharCode(finalValue));
  return encodeString;
}

// This one is Google's verbatim.
PolylineEncoder.prototype.encodeSignedNumber = function(num) {
  var sgn_num = num << 1;
  if (num < 0) {
    sgn_num = ~(sgn_num);
  }
  return(this.encodeNumber(sgn_num));
}


PolylineEncoder.latLng = function(y, x) {
	this.y = y;
	this.x = x;
}
PolylineEncoder.latLng.prototype.lat = function() {
	return this.y;
}
PolylineEncoder.latLng.prototype.lng = function() {
	return this.x;
}


PolylineEncoder.pointsToLatLngs = function(points) {
	var i, latLngs;
	latLngs = new Array(0);
	for(i=0; i<points.length; i++) {
		latLngs.push(new PolylineEncoder.latLng(points[i][0], points[i][1]));
	}
	return latLngs;
}


PolylineEncoder.pointsToGLatLngs = function(points) {
	var i, gLatLngs;
	gLatLngs = new Array(0);
	for(i=0; i<points.length; i++) {
		gLatLngs.push(new GLatLng(points[i][0], points[i][1]));
	}
	return gLatLngs;
}
