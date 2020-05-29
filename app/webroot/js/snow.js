Amount = 50;

Image0 = new Image();
Image0.src = "http://szenprogs.ru/images/snow/snow1.gif";

grphcs = new Array(1);
grphcs[0] = "http://szenprogs.ru/images/snow/snow1.gif";

Ypos = new Array();
Xpos = new Array();
Speed = new Array();
Step = new Array();
Cstep = new Array();
var YPosA;
ns = (document.layers)?1:0;
if (ns) {
  for (i = 0; i < Amount; i++) {
    var P = Math.floor(Math.random()*grphcs.length);
    rndPic = grphcs[P];
    document.write("<LAYER NAME='sn"+i+"' LEFT=0 TOP=0><a http://szenprogs.ru/blog/2009-05-06-41><img src="+rndPic+"><\/a><\/LAYER>");
  }
} else {
  document.write('<div style="position:absolute;top:0px;left:0px"><div style="position:relative">');
  for (i = 0; i < Amount; i++) {
    var P = Math.floor(Math.random()*grphcs.length);
    rndPic = grphcs[P];
    document.write('<img id="si'+i+'" src="'+rndPic+'" style="position:absolute; top:0px; left:0px; width:20px; height:20px;">');
  }
  document.write('<\/div><\/div>');
}
WinHeight=(document.layers)?window.innerHeight:window.document.body.clientHeight;
WinWidth=(document.layers)?window.innerWidth:window.document.body.clientWidth;
for (i=0; i < Amount; i++) {
  Ypos[i] = Math.round(Math.random()*WinHeight);
  Xpos[i] = Math.round(Math.random()*WinWidth);
  Speed[i]= Math.random()*3+2;
  Cstep[i] = 0;
  Step[i] = Math.random()*0.1+0.05;
}
function fall() {
  var WinHeight = (document.layers)?window.innerHeight:window.document.body.clientHeight;
  var WinWidth = (document.layers)?window.innerWidth:window.document.body.clientWidth;
  var hscrll = (document.layers)?window.pageYOffset:document.body.scrollTop;
  var wscrll = (document.layers)?window.pageXOffset:document.body.scrollLeft;
  for (i=0; i < Amount; i++) {
    sy = Speed[i]*Math.sin(90*Math.PI/180);
    sx = Speed[i]*Math.cos(Cstep[i]);
    Ypos[i] += sy;
    Xpos[i] += sx;
    if (Ypos[i] > WinHeight) {
      Ypos[i] = -60;
      Xpos[i] = Math.round(Math.random()*WinWidth);
      Speed[i] = Math.random()*5+2;
    }
    if (ns) {
      document.layers['sn'+i].left = Xpos[i];
      document.layers['sn'+i].top = Ypos[i]+hscrll;
    } else {
      document.getElementById('si'+i).style.left = Xpos[i]+'px';
      YPosA=Ypos[i]+hscrll;
      document.getElementById('si'+i).style.top = YPosA+'px';
    }
    Cstep[i] += Step[i];
  }
  setTimeout('fall()',50);
}
fall();
