/* 
Created by: Kenrick Beckett

Name: Chat Engine
*/

var instanse = false;
var instanse2 = false;
var state = 0;
var admin = 'admin';
var mes;
var chatId;
var custName;
var custLName;
var custEmail;
var custPhone;
var urlw;

var today = new Date();
var yr = today.getFullYear();
var day = today.getDate();
day = (parseInt(day, 10) < 10 ) ? ('0'+day) : (day);
var month = today.getMonth() + 1;
month = (parseInt(month, 10) < 10 ) ? ('0'+month) : (month);
var dateS = day + '.' + month + '.'+yr;
var datePo = dateS;

function Chat(id,change) {
    this.update = updateChat;
    this.send = sendChat;
	this.close = closeChat;
	chatId=id;
if(change==false) urlw="/call_process.html";
else urlw="/reverse_process.html";
}

//Updates the chat
function updateChat() {

	 if(!instanse){
		 instanse = true;
	     $.ajax({
			   type: "POST",
			   url: urlw,
			   data: {  
			   			'function': 'update',
						'state': state,
						'chatId': chatId,
						'admin' : admin
						},
			   dataType: "json",
			   success: function(data){
				   if(data.text){
						for (var i = 0; i < data.text.length; i++) {
                            $('#chat-area').append($("<p>"+ data.text[i] +"</p>"));
                        }								  
				   }
				   document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
				   instanse = false;
				   state = data.state;			   
				   if(data.hasNew && admin=="client") {
						OLO.startAttention();
					}
			   },
			});
	 }
	 else {
		 setTimeout(updateChat(), 1500);
	 }
}

//send the message
function sendChat(message)
{   
    //updateChat();
     $.ajax({
		   type: "POST",
		   url: urlw,
		   data: {  
		   			'function': 'send',
					'message': message,
					'chatId': chatId,
					'admin' : admin
				 },
		   dataType: "json",
		   success: function(data){
			   //updateChat();
		   }
		});
}

//close chat message
function closeChat()
{    
    //updateChat();
     $.ajax({
		   type: "POST",
		   url: urlw,
		   async : false,
		   data: {  
		   			'function': 'close',
					'chatId': chatId,
					'admin' : admin
				 },
		   dataType: "json",
		   success: function(data){
			   //updateChat();
		   }
		});
}

function fillFilter()
{
	custName = $('#name').val();
	custLName = $('#lastname').val();
	custEmail = $('#email').val();
	custPhone = $('#phone').val();
	dateS = $('#date_s').val();
	datePo = $('#date_po').val();
}

function updateCallCenterMenu(){
	 if(!instanse2){
		 instanse2 = true;
	     $.ajax({
			   type: "POST",
			   url: "/call_process.html",
			   data: {  
			   			'function': 'update_menu',
						'custName': custName,
						'custLName': custLName,
						'custEmail': custEmail,
						'custPhone': custPhone,
						'dateS': dateS,
						'datePo': datePo
						},
			   dataType: "json",
			   success: function(data){
				   if(data.text){
						$('#call-menu-area').html(data.text);							  
				   }
				   instanse2 = false;
				   if(data.hasNew)
						OLO.startAttention();
			   },
			});
	 }
	 else {
		 setTimeout(updateCallCenterMenu, 5000);
	 }
}



function cOLO() {

  this.blinkStep = 0;
  this.titleBlink = [ '*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*', 'Новое непрочитанное сообщение!' ];
  this.soundManager = null;
  
  var oThis = this;
 
  this.initSound();
  jQuery(document).click(function(){ oThis.clearAttention(); });
}

cOLO.prototype.initSound = function()
{
    if (typeof(soundManager) == 'undefined') {
      return false;
    }
	soundManager.setup({ url: '/audio/swf/'});
    this.soundManager = soundManager;	
}

cOLO.prototype.startAttention = function()
{
  if (this.blinkStep != 0) {
    return false; // already started
  }
  this.blinkStep = 1;
  var oThis = this;
  this.oldTitle = document.title;
  
  var mySound = this.soundManager.createSound({
		id: 'aSound',
		url: '/audio/mak.mp3'
	  });
  mySound.play();
  
  jQuery(document).everyTime('1s', 'blink', function() { oThis.blinkNext(); });
}
	
cOLO.prototype.clearAttention = function()
{
  if (!this.blinkStep) {
    return false; // nothing to clear
  }
  this.blinkStep = 0;
  jQuery(document).stopTime('blink');
  document.title = this.oldTitle;
}

cOLO.prototype.blinkNext = function()
{  
  document.title = this.titleBlink[this.blinkStep - 1];
  this.blinkStep++;
  if (this.blinkStep > this.titleBlink.length) {
    this.blinkStep = 1;
  }
}

var OLO;
$(document).ready(function() {
  OLO = new cOLO();
});
