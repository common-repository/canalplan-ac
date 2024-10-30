
function addEvent(obj,event_name,func_name){
  if (obj.attachEvent){
    obj.attachEvent("on"+event_name, func_name);
  }else if(obj.addEventListener){
    obj.addEventListener(event_name,func_name,true);
  }else{
    obj["on"+event_name] = func_name;
  }
}

function removeEvent(obj,event_name,func_name){
  if (obj.detachEvent){
    obj.detachEvent("on"+event_name,func_name);
  }else if(obj.removeEventListener){
    obj.removeEventListener(event_name,func_name,true);
  }else{
    obj["on"+event_name] = null;
  }
}
function stopEvent(evt){
  evt || window.event;
  if (evt.stopPropagation){
    evt.stopPropagation();
    evt.preventDefault();
  }else if(typeof evt.cancelBubble != "undefined"){
    evt.cancelBubble = true;
    evt.returnValue = false;
  }
  return false;
}
function getElement(evt){
  if (window.event){
    return window.event.srcElement;
  }else{
    return evt.currentTarget;
  }
}
function getTargetElement(evt){
  if (window.event){
    return window.event.srcElement;
  }else{
    return evt.target;
  }
}
function stopSelect(obj){
  if (typeof obj.onselectstart != 'undefined'){
    addEvent(obj,"selectstart",function(){ return false;});
  }
}
function getCaretEnd(obj){
  if(typeof obj.selectionEnd != "undefined"){
    return obj.selectionEnd;
  }else if(document.selection&&document.selection.createRange){
    var M=document.selection.createRange();
    try{
      var Lp = M.duplicate();
      Lp.moveToElementText(obj);
    }catch(e){
      var Lp=obj.createTextRange();
    }
    Lp.setEndPoint("EndToEnd",M);
    var rb=Lp.text.length;
    if(rb>obj.value.length){
      return -1;
    }
    return rb;
  }
}
function getCaretStart(obj){
  if(typeof obj.selectionStart != "undefined"){
    return obj.selectionStart;
  }else if(document.selection&&document.selection.createRange){
    var M=document.selection.createRange();
    try{
      var Lp = M.duplicate();
      Lp.moveToElementText(obj);
    }catch(e){
      var Lp=obj.createTextRange();
    }
    Lp.setEndPoint("EndToStart",M);
    var rb=Lp.text.length;
    if(rb>obj.value.length){
      return -1;
    }
    return rb;
  }
}
function setCaret(obj,l){
  obj.focus();
  if (obj.setSelectionRange){
    obj.setSelectionRange(l,l);
  }else if(obj.createTextRange){
    m = obj.createTextRange();
    m.moveStart('character',l);
    m.collapse();
    m.select();
  }
}
function setSelection(obj,s,e){
  obj.focus();
  if (obj.setSelectionRange){
    obj.setSelectionRange(s,e);
  }else if(obj.createTextRange){
    m = obj.createTextRange();
    m.moveStart('character',s);
    m.moveEnd('character',e);
    m.select();
  }
}
String.prototype.addslashes = function(){
  return this.replace(/(["\\\.\|\[\]\^\*\+\?\$\(\)])/g, '\\$1');
//" the above string confuses emacs syntax highlighting - this restores it
}
String.prototype.trim = function () {
    return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
};
function curTop(obj){
  toreturn = 0;
  while(obj){
    toreturn += obj.offsetTop;
    obj = obj.offsetParent;
  }
  return toreturn;
}
function curLeft(obj){
  toreturn = 0;
  while(obj){
    toreturn += obj.offsetLeft;
    obj = obj.offsetParent;
  }
  return toreturn;
}
function isNumber(a) {
    return typeof a == 'number' && isFinite(a);
}
function replaceHTML(obj,text){
  while(el = obj.childNodes[0]){
    obj.removeChild(el);
  };
  obj.appendChild(document.createTextNode(text));
}

function canalplan_actb(obj,ca,path){
  /* ---- Public Variables ---- */
  this.canalplan_actb_timeOut = -1; // Autocomplete Timeout in ms (-1: autocomplete never time out)
  this.canalplan_actb_lim = 10;    // Number of elements autocomplete can show (-1: no limit)
  this.canalplan_actb_firstText = false; // should the auto complete be limited to the beginning of keyword?
  this.canalplan_actb_mouse = true; // Enable Mouse Support
  this.canalplan_actb_delimiter = new Array(';',',');  // Delimiter for multiple autocomplete. Set it to empty array for single autocomplete
  this.canalplan_actb_startcheck = 3; // Show widget only after this number of characters is typed in.
  this.canalplan_actb_lastdownload = '';  // last string sent to server
  /* ---- Public Variables ---- */

  /* --- Styles --- */
  this.canalplan_actb_bgColor = '#FFFFFF';
  this.canalplan_actb_textColor = '#000000';
  this.canalplan_actb_hColor = '#c0c0c0';
  this.canalplan_actb_fFamily = 'Verdana';
  this.canalplan_actb_fSize = '11px';
  this.canalplan_actb_hStyle = 'text-decoration:underline;font-weight:bold;';
  this.canalplan_actb_borderStyle = '1px solid black';
  /* --- Styles --- */

  /* ---- Private Variables ---- */
  var canalplan_actb_delimwords = new Array();
  var canalplan_actb_cdelimword = 0;
  var canalplan_actb_delimchar = new Array();
  var canalplan_actb_display = false;
  var canalplan_actb_pos = 0;
  var canalplan_actb_total = 0;
  var canalplan_actb_curr = null;
  var canalplan_actb_rangeu = 0;
  var canalplan_actb_ranged = 0;
  var canalplan_actb_bool = new Array();
  var canalplan_actb_pre = 0;
  var canalplan_actb_toid;
  var canalplan_actb_tomake = false;
  var canalplan_actb_getpre = "";
  var canalplan_actb_mouse_on_list = 1;
  var canalplan_actb_kwcount = 0;
  var canalplan_actb_caretmove = false;
  this.canalplan_actb_keywords = new Array();
  /* ---- Private Variables---- */

  this.canalplan_actb_keywords = ca;
  var canalplan_actb_self = this;

  canalplan_actb_curr = obj;

  addEvent(canalplan_actb_curr,"focus",canalplan_actb_setup);
  function canalplan_actb_setup(){
    addEvent(document,"keydown",canalplan_actb_checkkey);
    addEvent(canalplan_actb_curr,"blur",canalplan_actb_clear);
    addEvent(document,"keypress",canalplan_actb_keypress);
  }

  function canalplan_actb_clear(evt){
    if (!evt) evt = event;
    removeEvent(document,"keydown",canalplan_actb_checkkey);
    removeEvent(canalplan_actb_curr,"blur",canalplan_actb_clear);
    removeEvent(document,"keypress",canalplan_actb_keypress);
    canalplan_actb_removedisp();
  }
  function canalplan_actb_parse(n){
    if (canalplan_actb_self.canalplan_actb_delimiter.length > 0){
      var t = canalplan_actb_delimwords[canalplan_actb_cdelimword].trim().addslashes();
      var plen = canalplan_actb_delimwords[canalplan_actb_cdelimword].trim().length;
    }else{
      var t = canalplan_actb_curr.value.addslashes();
      var plen = canalplan_actb_curr.value.length;
    }
    var tobuild = '';
    var i;

    if (canalplan_actb_self.canalplan_actb_firstText){
      var re = new RegExp("^" + t, "i");
    }else{
      var re = new RegExp(t, "i");
    }
    var p = n.match.search(re);

    for (i=0;i<p;i++){
      tobuild += n.disp.substr(i,1);
    }
    tobuild += "<font style='"+(canalplan_actb_self.canalplan_actb_hStyle)+"'>"
    for (i=p;i<plen+p;i++){
      tobuild += n.disp.substr(i,1);
    }
    tobuild += "</font>";
      for (i=plen+p;i<n.disp.length;i++){
      tobuild += n.disp.substr(i,1);
    }
    return tobuild;
  }
  function canalplan_actb_generate(){
    if (document.getElementById('tat_table')){ canalplan_actb_display = false;document.body.removeChild(document.getElementById('tat_table')); }
    if (canalplan_actb_kwcount == 0){
      canalplan_actb_display = false;
      return;
    }
    a = document.createElement('table');
    a.cellSpacing='1px';
    a.cellPadding='2px';
    a.style.position='absolute';
    a.style.top = eval(curTop(canalplan_actb_curr) + canalplan_actb_curr.offsetHeight) + "px";
    a.style.left = curLeft(canalplan_actb_curr) + "px";
    a.style.backgroundColor=canalplan_actb_self.canalplan_actb_bgColor;
    a.id = 'tat_table';
    a.style.border = canalplan_actb_borderStyle;
    document.body.appendChild(a);
    var i;
    var first = true;
    var j = 1;
    if (canalplan_actb_self.canalplan_actb_mouse){
      a.onmouseout = canalplan_actb_table_unfocus;
      a.onmouseover = canalplan_actb_table_focus;
    }
    var counter = 0;

    for (i=0;i<canalplan_actb_self.canalplan_actb_keywords.length;i++){
      if (canalplan_actb_bool[i]){
        counter++;
        r = a.insertRow(-1);
        if (first && !canalplan_actb_tomake){
          r.style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
          first = false;
          canalplan_actb_pos = counter;
        }else if(canalplan_actb_pre == i){
          r.style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
          first = false;
          canalplan_actb_pos = counter;
        }else{
          r.style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
        }
        r.id = 'tat_tr'+(j);
        c = r.insertCell(-1);
        c.style.color = canalplan_actb_self.canalplan_actb_textColor;
        c.style.fontFamily = canalplan_actb_self.canalplan_actb_fFamily;
        c.style.fontSize = canalplan_actb_self.canalplan_actb_fSize;
        c.innerHTML = canalplan_actb_parse(canalplan_actb_self.canalplan_actb_keywords[i]);
        c.id = 'tat_td'+(j);
        c.setAttribute('pos',j);
        if (canalplan_actb_self.canalplan_actb_mouse){
          c.style.cursor = 'pointer';
          c.onclick=canalplan_actb_mouseclick;
          c.onmouseover = canalplan_actb_table_highlight;
        }
        j++;
      }
      if (j - 1 == canalplan_actb_self.canalplan_actb_lim && j < canalplan_actb_total){
        r = a.insertRow(-1);
        r.style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
        c = r.insertCell(-1);
        c.style.color = canalplan_actb_self.canalplan_actb_textColor;
        c.style.fontFamily = 'arial narrow, sans-serif';
        c.style.fontSize = canalplan_actb_self.canalplan_actb_fSize;
        c.align='center';
        replaceHTML(c,'\\/');
        if (canalplan_actb_self.canalplan_actb_mouse){
          c.style.cursor = 'pointer';
          c.onclick = canalplan_actb_mouse_down;
        }
        break;
      }
    }
    canalplan_actb_rangeu = 1;
    canalplan_actb_ranged = j-1;
    canalplan_actb_display = true;
    if (canalplan_actb_pos <= 0) canalplan_actb_pos = 1;
  }
  function canalplan_actb_remake(){
    document.body.removeChild(document.getElementById('tat_table'));
    a = document.createElement('table');
    a.cellSpacing='1px';
    a.cellPadding='2px';
    a.style.position='absolute';
    a.style.top = eval(curTop(canalplan_actb_curr) + canalplan_actb_curr.offsetHeight) + "px";
    a.style.left = curLeft(canalplan_actb_curr) + "px";
    a.style.backgroundColor=canalplan_actb_self.canalplan_actb_bgColor;
    a.style.border = canalplan_actb_borderStyle;
    a.id = 'tat_table';
    if (canalplan_actb_self.canalplan_actb_mouse){
      a.onmouseout= canalplan_actb_table_unfocus;
      a.onmouseover=canalplan_actb_table_focus;
    }
//    document.body.appendChild(a);
    var i;
    var first = true;
    var j = 1;
    if (canalplan_actb_rangeu > 1){
      r = a.insertRow(-1);
      r.style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
      c = r.insertCell(-1);
      c.style.color = canalplan_actb_self.canalplan_actb_textColor;
      c.style.fontFamily = 'arial narrow, sans-serif';
      c.style.fontSize = canalplan_actb_self.canalplan_actb_fSize;
      c.align='center';
      replaceHTML(c,'/\\');
      if (canalplan_actb_self.canalplan_actb_mouse){
        c.style.cursor = 'pointer';
        c.onclick = canalplan_actb_mouse_up;
      }
    }
    for (i=0;i<canalplan_actb_self.canalplan_actb_keywords.length;i++){
      if (canalplan_actb_bool[i]){
        if (j >= canalplan_actb_rangeu && j <= canalplan_actb_ranged){
          r = a.insertRow(-1);
          r.style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
          r.id = 'tat_tr'+(j);
          c = r.insertCell(-1);
          c.style.color = canalplan_actb_self.canalplan_actb_textColor;
          c.style.fontFamily = canalplan_actb_self.canalplan_actb_fFamily;
          c.style.fontSize = canalplan_actb_self.canalplan_actb_fSize;
          c.innerHTML = canalplan_actb_parse(canalplan_actb_self.canalplan_actb_keywords[i]);
          c.id = 'tat_td'+(j);
          c.setAttribute('pos',j);
          if (canalplan_actb_self.canalplan_actb_mouse){
            c.style.cursor = 'pointer';
            c.onclick=canalplan_actb_mouseclick;
            c.onmouseover = canalplan_actb_table_highlight;
          }
          j++;
        }else{
          j++;
        }
      }
      if (j > canalplan_actb_ranged) break;
    }
    if (j-1 < canalplan_actb_total){
      r = a.insertRow(-1);
      r.style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
      c = r.insertCell(-1);
      c.style.color = canalplan_actb_self.canalplan_actb_textColor;
      c.style.fontFamily = 'arial narrow, sans-serif';
      c.style.fontSize = canalplan_actb_self.canalplan_actb_fSize;
      c.align='center';
      replaceHTML(c,'\\/');
      if (canalplan_actb_self.canalplan_actb_mouse){
        c.style.cursor = 'pointer';
        c.onclick = canalplan_actb_mouse_down;
      }
    }
    document.body.appendChild(a);

  }
  function canalplan_actb_goup(){
    if (!canalplan_actb_display) return;
    if (canalplan_actb_pos == 1) return;
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
    canalplan_actb_pos--;
    if (canalplan_actb_pos < canalplan_actb_rangeu) canalplan_actb_moveup();
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list=0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
    // opera sometimes needs coaxing to redraw the element
    if (window.opera) {
	document.getElementById('tat_table').style.display='none';
	document.getElementById('tat_table').style.display='block';
    }
  }
  function canalplan_actb_godown(){
    if (!canalplan_actb_display) return;
    if (canalplan_actb_pos == canalplan_actb_total) return;
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
    canalplan_actb_pos++;
    if (canalplan_actb_pos > canalplan_actb_ranged) canalplan_actb_movedown();
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list=0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
    // opera sometimes needs coaxing to redraw the element
    if (window.opera) {
	document.getElementById('tat_table').style.display='none';
	document.getElementById('tat_table').style.display='block';
    }
  }
  function canalplan_actb_movedown(){
    canalplan_actb_rangeu++;
    canalplan_actb_ranged++;
    canalplan_actb_remake();
  }
  function canalplan_actb_moveup(){
    canalplan_actb_rangeu--;
    canalplan_actb_ranged--;
    canalplan_actb_remake();
  }

  /* Mouse */
  function canalplan_actb_mouse_down(){
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
    canalplan_actb_pos++;
    canalplan_actb_movedown();
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
    canalplan_actb_curr.focus();
    canalplan_actb_mouse_on_list = 0;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list=0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
  }
  function canalplan_actb_mouse_up(evt){
    if (!evt) evt = event;
    if (evt.stopPropagation){
      evt.stopPropagation();
    }else{
      evt.cancelBubble = true;
    }
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
    canalplan_actb_pos--;
    canalplan_actb_moveup();
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
    canalplan_actb_curr.focus();
    canalplan_actb_mouse_on_list = 0;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list=0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
  }
  function canalplan_actb_mouseclick(evt){
    if (!evt) evt = event;
    if (!canalplan_actb_display) return;
    canalplan_actb_mouse_on_list = 0;
    canalplan_actb_pos = this.getAttribute('pos');
    canalplan_actb_penter();
  }
  function canalplan_actb_table_focus(){
    canalplan_actb_mouse_on_list = 1;
  }
  function canalplan_actb_table_unfocus(){
    canalplan_actb_mouse_on_list = 0;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list = 0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
  }
  function canalplan_actb_table_highlight(){
    canalplan_actb_mouse_on_list = 1;
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_bgColor;
    canalplan_actb_pos = this.getAttribute('pos');
    while (canalplan_actb_pos < canalplan_actb_rangeu) canalplan_actb_moveup();
    while (canalplan_actb_pos > canalplan_actb_ranged) canalplan_actb_movedown();
    document.getElementById('tat_tr'+canalplan_actb_pos).style.backgroundColor = canalplan_actb_self.canalplan_actb_hColor;
    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list = 0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);
  }
  /* ---- */

  function canalplan_actb_insertword(a){
    if (canalplan_actb_self.canalplan_actb_delimiter.length > 0){
      str = '';
      l=0;
      for (i=0;i<canalplan_actb_delimwords.length;i++){
        if (canalplan_actb_cdelimword == i){
          prespace = postspace = '';
          gotbreak = false;
          for (j=0;j<canalplan_actb_delimwords[i].length;++j){
            if (canalplan_actb_delimwords[i].charAt(j) != ' '){
              gotbreak = true;
              break;
            }
            prespace += ' ';
          }
          for (j=canalplan_actb_delimwords[i].length-1;j>=0;--j){
            if (canalplan_actb_delimwords[i].charAt(j) != ' ') break;
            postspace += ' ';
          }
          str += prespace;
          str += a;
          l = str.length;
          if (gotbreak) str += postspace;
        }else{
          str += canalplan_actb_delimwords[i];
        }
        if (i != canalplan_actb_delimwords.length - 1){
          str += canalplan_actb_delimchar[i];
        }
      }
      canalplan_actb_curr.value = str;
      setCaret(canalplan_actb_curr,l);
    }else{
      canalplan_actb_curr.value = a;
    }
    canalplan_actb_mouse_on_list = 0;
    canalplan_actb_removedisp();
  }
  function canalplan_actb_penter(){
    if (!canalplan_actb_display) return;
    canalplan_actb_display = false;
    var word = '';
    var c = 0;
    for (var i=0;i<=canalplan_actb_self.canalplan_actb_keywords.length;i++){
      if (canalplan_actb_bool[i]) c++;
      if (c == canalplan_actb_pos){
	  word = canalplan_actb_self.canalplan_actb_keywords[i].disp;
	word=word.replace('\r','');
        break;
      }
    }
    canalplan_actb_insertword(word);
    l = getCaretStart(canalplan_actb_curr);
  }
  function canalplan_actb_removedisp(){
    if (canalplan_actb_mouse_on_list==0){
      canalplan_actb_display = 0;
      if (document.getElementById('tat_table')){ document.body.removeChild(document.getElementById('tat_table')); }
      if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    }
  }
  function canalplan_actb_keypress(e){
    if (canalplan_actb_caretmove) stopEvent(e);
    return !canalplan_actb_caretmove;
  }
  function canalplan_actb_checkkey(evt){
    if (!evt) evt = event;
    a = evt.keyCode;
    caret_pos_start = getCaretStart(canalplan_actb_curr);
    canalplan_actb_caretmove = 0;
    switch (a){
      case 38:
        canalplan_actb_goup();
        canalplan_actb_caretmove = 1;
        return false;
        break;
      case 40:
        canalplan_actb_godown();
        canalplan_actb_caretmove = 1;
        return false;
        break;
      // NMA: Nov 2006  -  split tab and CR and gave tab new properties
      case 9:
        if (canalplan_actb_display) {
          canalplan_actb_caretmove = 1;
	  canalplan_actb_display = 0;
          if (document.getElementById('tat_table')){ document.body.removeChild(document.getElementById('tat_table')); }
          if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
          return false;
        } else {
          return true;
        }
        break;
      case 13:
        if (canalplan_actb_display) {
          canalplan_actb_caretmove = 1;
          canalplan_actb_penter();
          return false;
        } else {
          return true;
        }
        break;
      default:
        setTimeout(function(){canalplan_actb_tocomplete(a)},50);
        break;
    }
  }

  function canalplan_actb_tocomplete(kc){
    if (kc == 38 || kc == 40 || kc == 13) return;
    var i;
    if (canalplan_actb_display){
      var word = 0;
      var c = 0;
      for (var i=0;i<=canalplan_actb_self.canalplan_actb_keywords.length;i++){
        if (canalplan_actb_bool[i]) c++;
        if (c == canalplan_actb_pos){
          word = i;
          break;
        }
      }
      canalplan_actb_pre = word;
    }else{ canalplan_actb_pre = -1};

    if (canalplan_actb_curr.value == ''){
      canalplan_actb_mouse_on_list = 0;
      canalplan_actb_removedisp();
      return;
    }
    if (canalplan_actb_self.canalplan_actb_delimiter.length > 0){
      caret_pos_start = getCaretStart(canalplan_actb_curr);
      caret_pos_end = getCaretEnd(canalplan_actb_curr);

      delim_split = '';
      for (i=0;i<canalplan_actb_self.canalplan_actb_delimiter.length;i++){
        delim_split += canalplan_actb_self.canalplan_actb_delimiter[i];
      }
      delim_split = delim_split.addslashes();
      delim_split_rx = new RegExp("(["+delim_split+"])");
      c = 0;
      canalplan_actb_delimwords = new Array();
      canalplan_actb_delimwords[0] = '';
      for (i=0,j=canalplan_actb_curr.value.length;i<canalplan_actb_curr.value.length;i++,j--){
        if (canalplan_actb_curr.value.substr(i,j).search(delim_split_rx) == 0){
          ma = canalplan_actb_curr.value.substr(i,j).match(delim_split_rx);
          canalplan_actb_delimchar[c] = ma[1];
          c++;
          canalplan_actb_delimwords[c] = '';
        }else{
          canalplan_actb_delimwords[c] += canalplan_actb_curr.value.charAt(i);
        }
      }

      var l = 0;
      canalplan_actb_cdelimword = -1;
      for (i=0;i<canalplan_actb_delimwords.length;i++){
        if (caret_pos_end >= l && caret_pos_end <= l + canalplan_actb_delimwords[i].length){
          canalplan_actb_cdelimword = i;
        }
        l+=canalplan_actb_delimwords[i].length + 1;
      }
      var ot = canalplan_actb_delimwords[canalplan_actb_cdelimword].trim();
      var t = canalplan_actb_delimwords[canalplan_actb_cdelimword].addslashes().trim();
    }else{
      var ot = canalplan_actb_curr.value;
      var t = canalplan_actb_curr.value.addslashes();
    }
    if (ot.length == 0){
      canalplan_actb_mouse_on_list = 0;
      canalplan_actb_removedisp();
    }
    if (ot.length < canalplan_actb_self.canalplan_actb_startcheck) return this;
    if (canalplan_actb_self.canalplan_actb_firstText){
      var re = new RegExp("^" + t, "i");
    }else{
      var re = new RegExp(t, "i");
    }
    if(canalplan_actb_self.canalplan_actb_lastdownload == '') {
      Download_Candidates(t,canalplan_actb_self,cplogid);
    } else if(t.match(canalplan_actb_self.canalplan_actb_lastdownload) == null) {
      Download_Candidates(t,canalplan_actb_self);
    }
    canalplan_actb_total = 0;
    canalplan_actb_tomake = false;
    canalplan_actb_kwcount = 0;
    for (i=0;i<canalplan_actb_self.canalplan_actb_keywords.length;i++){
      canalplan_actb_bool[i] = false;
      if (re.test(canalplan_actb_self.canalplan_actb_keywords[i].match)){
        canalplan_actb_total++;
        canalplan_actb_bool[i] = true;
        canalplan_actb_kwcount++;
        if (canalplan_actb_pre == i) canalplan_actb_tomake = true;
      }
    }

    if (canalplan_actb_toid) clearTimeout(canalplan_actb_toid);
    if (canalplan_actb_self.canalplan_actb_timeOut > 0) canalplan_actb_toid = setTimeout(function(){canalplan_actb_mouse_on_list = 0;canalplan_actb_removedisp();},canalplan_actb_self.canalplan_actb_timeOut);

    canalplan_actb_generate();
  }

  function Download_Candidates(t,canalplan_actb_self,bid) {
    xmlhttp=null
    // clear keywords now, so we don't get stuff from old set
    canalplan_actb_self.canalplan_actb_keywords = new Array();
    // first for all but IE, else for IE
    if (window.XMLHttpRequest) {
      xmlhttp=new XMLHttpRequest()
    } else if (window.ActiveXObject) {
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")
    }
    if (xmlhttp!=null) {
      xmlhttp.onreadystatechange=function() {
        // if xmlhttp shows "loaded"
        if (xmlhttp.readyState==4) {
          // if "OK"
          if (xmlhttp.status==200) {
            var response = xmlhttp.responseText;
            canalplan_actb_self.canalplan_actb_keywords = explode('#',response);
	    canalplan_actb_self.canalplan_actb_lastdownload = t;
            canalplan_actb_tocomplete(0);  // dummy value
          }
        }
      };
 //    xmlhttp.open("GET",wpcontent+"/canalplan-ac/canalplan/canalplan.php?match="+t,true)
 //console.log(encodeURIComponent(t));
     xmlhttp.open("GET",wpcontent+"/canalplan-ac/canalplan/canalplan.php?match="+encodeURIComponent(t)+"&blogid="+bid,true)
      xmlhttp.send(null)
    }
  }

  function explode(separator, string) {
    var list = new Array();
    if (separator == null) return false;
    if (string == null) return false;

    var currentStringPosition = 0;
    while (currentStringPosition<string.length) {
      var nextIndex = string.indexOf(separator, currentStringPosition);
      if (nextIndex == -1) break;
      var word = string.slice(currentStringPosition, nextIndex);
      word=word.replace('\r','');
      list.push({disp:word,match:UTF8_Folded(word)});
      currentStringPosition = nextIndex+1;
    }
    if (list.length<1) {
	list.push({disp:string,match:UTF8_Folded(string)});
    } else {
	list.push({disp:string.slice(currentStringPosition, string.length),match:UTF8_Folded(string.slice(currentStringPosition, string.length))});
    }
    return list;
  }
  return this;
}

/* This table is made by merging and capitalising two tables by
 * Andreas Gohr(andi@splitbrain.org) taken from his UTF helper
 * functions.  These are released under the GPL. */

var UTF8_table = {
    'à':'a', 'ô':'o', 'ď':'d', 'ḟ':'f', 'ë':'e', 'š':'s', 'ơ':'o',
    'ß':'ss','ă':'a', 'ř':'r', 'ț':'t', 'ň':'n', 'ā':'a', 'ķ':'k',
    'ŝ':'s', 'ỳ':'y', 'ņ':'n', 'ĺ':'l', 'ħ':'h', 'ṗ':'p', 'ó':'o',
    'ú':'u', 'ě':'e', 'é':'e', 'ç':'c', 'ẁ':'w', 'ċ':'c', 'õ':'o',
    'ṡ':'s', 'ø':'o', 'ģ':'g', 'ŧ':'t', 'ș':'s', 'ė':'e', 'ĉ':'c',
    'ś':'s', 'î':'i', 'ű':'u', 'ć':'c', 'ę':'e', 'ŵ':'w', 'ṫ':'t',
    'ū':'u','č':'c', 'ö':'oe', 'è':'e', 'ŷ':'y', 'ą':'a', 'ł':'l',
    'ų':'u', 'ů':'u', 'ş':'s', 'ğ':'g', 'ļ':'l', 'ƒ':'f', 'ž':'z',
    'ẃ':'w', 'ḃ':'b', 'å':'a', 'ì':'i', 'ï':'i', 'ḋ':'d', 'ť':'t',
    'ŗ':'r', 'ä':'ae', 'í':'i', 'ŕ':'r', 'ê':'e', 'ü':'ue', 'ò':'o',
    'ē':'e','ñ':'n', 'ń':'n', 'ĥ':'h', 'ĝ':'g', 'đ':'d', 'ĵ':'j',
    'ÿ':'y', 'ũ':'u', 'ŭ':'u', 'ư':'u', 'ţ':'t', 'ý':'y', 'ő':'o',
    'â':'a', 'ľ':'l', 'ẅ':'w', 'ż':'z', 'ī':'i', 'ã':'a', 'ġ':'g',
    'ṁ':'m', 'ō':'o', 'ĩ':'i', 'ù':'u', 'į':'i', 'ź':'z', 'á':'a',
    'û':'u', 'þ':'th', 'ð':'dh', 'æ':'ae', 'µ':'u',
    'À':'A', 'Ô':'O', 'Ď':'D', 'Ḟ':'F', 'Ë':'E', 'Š':'S', 'Ơ':'O',
    'ß':'SS','Ă':'A', 'Ř':'R', 'Ț':'T', 'Ň':'N', 'ā':'A', 'Ķ':'K',
    'Ŝ':'S', 'Ỳ':'Y', 'Ņ':'N', 'Ĺ':'L', 'ħ':'H', 'Ṗ':'P', 'Ó':'O',
    'Ú':'U', 'ě':'E', 'É':'E', 'Ç':'C', 'Ẁ':'W', 'Ċ':'C', 'Õ':'O',
    'Ṡ':'S', 'Ø':'O', 'Ģ':'G', 'ŧ':'T', 'Ș':'S', 'Ė':'E', 'Ĉ':'C',
    'Ś':'S', 'Î':'I', 'Ű':'U', 'Ć':'C', 'Ę':'E', 'Ŵ':'W', 'Ṫ':'T',
    'ū':'U', 'Č':'C', 'Ö':'OE', 'È':'E', 'Ŷ':'Y', 'Ą':'A', 'ł':'L',
    'Ų':'U', 'Ů':'U', 'Ş':'S', 'Ğ':'G', 'Ļ':'L', 'Ƒ':'F', 'Ž':'Z',
    'Ẃ':'W', 'Ḃ':'B', 'Å':'A', 'Ì':'I', 'Ï':'I', 'Ḋ':'D', 'Ť':'T',
    'Ŗ':'R', 'Ä':'AE', 'Í':'I', 'Ŕ':'R', 'Ê':'E', 'Ü':'UE', 'Ò':'O',
    'ē':'E', 'Ñ':'N', 'Ń':'N', 'Ĥ':'H', 'Ĝ':'G', 'đ':'D', 'Ĵ':'J',
    'Ÿ':'Y', 'Ũ':'U', 'Ŭ':'U', 'Ư':'U', 'Ţ':'T', 'Ý':'Y', 'Ő':'O',
    'Â':'A', 'Ľ':'L', 'Ẅ':'W', 'Ż':'Z', 'ī':'I', 'Ã':'A', 'Ġ':'G',
    'Ṁ':'M', 'ō':'O', 'Ĩ':'I', 'Ù':'U', 'Į':'I', 'Ź':'Z', 'Á':'A',
    'Û':'U', 'Þ':'TH', 'Ð':'DH', 'Æ':'AE',
};

function UTF8_Folded(inp) {
  var retv = "";
  for(i=0; i<inp.length;i++) {
      if(UTF8_table[inp[i]])
	  retv += UTF8_table[inp[i]];
      else
	  retv += inp[i];
  }
  return retv;
}
