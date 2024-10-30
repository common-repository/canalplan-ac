 async function get_cpids(url) {
  let response = await fetch(url);
  let ids = await response.text();
  console.log("Found ",ids);
  return ids;
}

async function Canalplan_Download_Code(t,bid) {
	let xxxx= await get_cpids(wpcontent+"/canalplan-ac/canalplan/canalplan.php?place="+encodeURIComponent(t)+"&blogid="+bid);
	console.log("return from call ",xxxx);
	xxx=xxxx.replace("\n","");
	if (xxx.substr(0,1)=="X" && xxx.length >=5 ) {xxx= xxx.substr(1,99)};
	xxx=xxx.replace(" ","");
	console.log("final return ",xxx);
	return(xxx);
}

async function getCanalPlan(tag) {
code_id= await Canalplan_Download_Code(tag,cplogid);
junk=document.getElementById("CanalPlanID");
junk.value="";
junk2=document.getElementById("tagtypeID");
junk3=document.getElementById("content");
tagcode=junk2.value;
tagextend=code_id.substring(0,1);
//code_id=code_id.substring(1);
if (tagextend=="W") {tagcode=tagcode+tagextend};
if (tagextend=="F") {tagcode=tagcode+tagextend};
tinyMCE.execCommand('mceInsertContent', false, '[['+ tagcode +':' + tag + '|' + code_id + ']]' + ' ');
// The next bit works if you are in HTML raw mode
junk3.value=junk3.value+' [['+ tagcode +':' + tag + '|' + code_id + ']]' + ' '
return;
}

function getCanalRoute(tag) {
junk2=document.getElementById("routetagtypeID");
junk3=document.getElementById("content");
tinyMCE.execCommand('mceInsertContent', false, '[['+ junk2.value +':' + tag + ']]' + ' ');
// The next bit works if you are in HTML raw mode
junk3.value=junk3.value+'[['+ junk2.value +':' + tag + ']]' + ' '
return;
}
