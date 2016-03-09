function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    tzOffset = date.getTimezoneOffset();
    date.setTime(date.getTime()+(days*24*60*60*1000+tzOffset));
    var expires = "; expires="+date.toUTCString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name,"",-1);
}

function toInteger(number){ 
  return Math.round(
    Number(number)
  ); 
}

function addUnreadLink(ts) {
  var pageing = document.getElementById('next_prev');
  var newdiv = document.createElement('div');
  newdiv.setAttribute('id', 'unread_link');
  newdiv.innerHTML = '<a href="/uudet/'+ts+'">Lisää lukemattomia artikkeleita</a>';
  pageing.appendChild(newdiv);
}


var last_visit = readCookie('edarit_visit');
var elements = document.querySelectorAll('#poblo_list li');
var unixtime = elements[0].dataset.ts


if (!last_visit) {
  var milliseconds = (new Date).getTime();
  last_visit = unixtime;
  createCookie('edarit_visit', unixtime, 14);
}

last_read_index = -1;
var all_unread = false;
for (var i = 0; i < elements.length; i++) {
  if (elements[i].dataset.ts > last_visit) {
    last_read_index = i;
    all_unread = true;
  }
}

if (last_read_index == 29 && all_unread) {
  addUnreadLink(last_visit);
}
else if (last_read_index != -1) {
  last_read = elements[last_read_index+1];
  last_read.setAttribute('id', 'type_2')
}

createCookie('edarit_visit', unixtime, 14);