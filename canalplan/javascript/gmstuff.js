    // A Textbox is a simple overlay that lays text onto the map.  It is based
    // on Google's example of "Rectangle".  
    function Textbox(position, text) {
      this.position_ = position;
      this.text_ = text;
    }
    Textbox.prototype = new GOverlay();

    // Creates the DIV holding the text
    Textbox.prototype.initialize = function(map) {
      // Create the DIV representing our rectangle
      var div = document.createElement("div");
      div.style.position = "absolute";
      div.innerHTML=this.text_;

      // Our box is flat against the map, so we add our selves to the
      // MAP_PANE pane, which is at the same z-index as the map itself (i.e.,
      // below the marker shadows)
      map.getPane(G_MAP_MAP_PANE).appendChild(div);

      this.map_ = map;
      this.div_ = div;
    }

    // Remove the main DIV from the map pane
    Textbox.prototype.remove = function() {
      this.div_.parentNode.removeChild(this.div_);
    }

    // Copy our data to a new Rectangle
    Textbox.prototype.copy = function() {
      return new Textbox(this.position_, this.text_);
    }

    // Redraw the rectangle based on the current projection and zoom level
    Textbox.prototype.redraw = function(force) {
      // We only need to redraw if the coordinate system has changed
      if (!force) return;

      // Calculate the DIV coordinates of the point
      var c1 = this.map_.fromLatLngToDivPixel(this.position_);
      var h = parseInt(this.div_.clientHeight);
      // Now position our DIV based on the DIV coordinates of our bounds
      this.div_.style.left = (c1.x+h/4) + "px";
      this.div_.style.top = (c1.y-h/3) + "px";
      this.div_.style.width = "150px";
    }

