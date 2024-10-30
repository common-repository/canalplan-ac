// Support routines for planning pages.  Major code is in separate files

var active_item = 'tab-input';

function Setup_Plan() {
  var i = document.getElementById('tab-input');
  i.style.backgroundColor = "#ffffd8";
  Set_Corners(i);
  i = document.getElementById('tab-route');
  i.style.backgroundColor = "#ffff00";
  Set_Corners(i);
  i = document.getElementById('tab-help');
  i.style.backgroundColor = "#ffff00";
  Set_Corners(i);
  actobj = actb(document.getElementById("textplace"), new Array());
  actobj.actb_delimiter = new Array();   // no delimiters allowed
}

function Set_Corners(item) {
  var fg = item.style.backgroundColor;
  var bits = item.getElementsByTagName("b");
  var bg = '#fff';
  for (var i = 0; i < bits.length; i++) {
    if (bits[i].getAttribute("class") == 'rtop') {
      bits[i].style.background = bg;
    } else {
      bits[i].style.background = fg;
    }
  }
};

function Switch_Tab(x) {
  var i = document.getElementById(active_item);
  i.style.backgroundColor = "#ffff00";
  Set_Corners(i);
  i = document.getElementById(active_item.replace("tab-", "pane-"));
  i.style.display = "none";

  active_item = 'tab-' + x;
  i = document.getElementById(active_item);
  i.style.backgroundColor = "#ffffd8";
  Set_Corners(i);
  i = document.getElementById(active_item.replace("tab-", "pane-"));
  i.style.display = "block";
  if (x == 'route') {
    DownloadRoute();
  }
}

function ActOnList(mode) {
  var sel = document.getElementById('place_list');
  var toselect = [];
  var l1 = [];
  var l2 = [];
  var wk = new Option("**", "**");
  for (var i = 0; i < sel.options.length; i++) {
    if (sel.options[i].selected) {
      switch (mode) {
        case "del":
          break;
        case "up":
        case "top":
        case "sel":
          l1.push(sel.options[i]);
          break;
        case "clr":
        case "tog":
          sel.options[i].selected = false;
          l1.push(sel.options[i]);
          break;
        case "bot":
          l2.push(sel.options[i]);
          break;
        case "dwn":
          l2.push(sel.options[i]);
          break;
        case "rev":
          l2.push(sel.options[i]);
          break;
      }
    } else {            // item is not selected
      switch (mode) {
        case "del":
        case "bot":
        case "clr":
          l1.push(sel.options[i]);
          break;
        case "sel":
        case "tog":
          sel.options[i].selected = true;
          l1.push(sel.options[i]);
          break;
        case "top":
          l2.push(sel.options[i]);
          break;
        case "up":
          if (wk.text != '**') {
            l1.push(wk);
          }
          wk = sel.options[i];
          break;
        case "dwn":
          l1.push(sel.options[i]);
          for (var ii = 0; ii < l2.length; ii++) {
            l1.push(l2[ii]);
          }
          l2.length = 0;
          break;
        case "rev":
          while (l2.length)
            l1.push(l2.pop());
          l1.push(sel.options[i]);
          break;
      }
    }
  }
  switch (mode) {
    case "up":
      if (wk.text != "**")
        l1.push(wk);
      break;
    case "down":
      for (var i = 0; i < l2.length; i++)
        l1.push(l2[i]);
      l2.length = 0;
      break;
  }
  ClearSelection(sel);
  AppendSelection(sel, l1);
  AppendSelection(sel, l2);
  // turn selections back on
  for (var i = 0; i < toselect.length; i++) {
    sel.options[toselect[i]].selected = true;
  }
}

function ClearSelection(sel) {
  for (var i = sel.length - 1; i >= 0; i--)
    sel[i] = null;
}

function AppendSelection(sel, lst) {
  for (var i = 0; i < lst.length; i++) {
    sel.options[sel.length] = lst[i];
  }
}

function edit_selector(e) {
  var x = document.getElementById('textplace');
  if (x.value) {
    Stuff_It(x.value);
  }
  x.value = e.text;
  e.text = '';
  e.value = '';
}

function Stuff_It(t) {
  var txt = t.replace(/^ .* $/g, "");
  if (txt == "")
    return;
  var sel = document.getElementById("place_list");
  for (i = 0; i < sel.options.length; i++) {
    if (sel.options[i].text == "" && txt) {
      sel.options[i].text = txt;
      sel.options[i].value = txt;
      txt = "";
    }
    sel.options[i].selected = false;
  }
  if (txt) {
    var nopt = new Option(txt, txt, false, true);
    addEvent(nopt, "dblclick", function () { edit_selector(this) });
    sel.options[sel.options.length] = nopt;
  }
}

