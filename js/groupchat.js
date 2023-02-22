/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

var rllinput = rlsbint = null;
var message = '';
var working = false;
var scrollchat = true;
var ulastmsgid = 0;
var usract = 1;
var winIsActive = true;
var ctimer = 5000;

$(document).ready(function() {
	
	$("#message").on("keypress", function(e) {
		if (e.keyCode == 13 && !e.shiftKey) {
			e.preventDefault();
			sendInputGC();
		}
	});
	
	$("#sendMessage").on("click", function(e) {
		e.preventDefault();
		sendInputGC();
	});

	// Finally start the event.
	sseJAK(ctimer);

});

$(document).on("click", ".edit-quote", function(e) {
	e.preventDefault();

	if ($(this).hasClass('active')) {
		$('#msgquote, #message').val("");
		$('.edit-quote').removeClass('active');
		$('.media-text').removeClass('highlight');
		$('#message').focus();
	} else {
		$('#message').val("");
		$('.edit-quote').removeClass('active');
		$('.media-text').removeClass('highlight');
		$(this).addClass('active');
		var quotemsg = $(this).data("msg");
		var quoteid = $(this).data("id");
		$('#msgquote').val(quotemsg);
		$('#msg_'+quoteid).addClass('highlight');
		$('#message').focus();
	}
});

$(document).on("click", ".edit-remove", function(e) {
	e.preventDefault();

	// Close the message connection
	if (rlsbint) {
		clearInterval(rlsbint);
		rlsbint = null;
	}

	var _this = $(this);
	var msgid = $(_this).data("msgid");
					
	$.ajax({
		type: "POST",
		url: ls.main_url + 'include/gcmod.php',
		data: "action=delmsg&id="+msgid,
		dataType: 'json',
		success: function(msg){

			// We have a success		
			if (msg.status) $('#postid_'+msgid+', #msg_'+msgid).remove();

			// Bring back the chat messages this time faster
			sseJAK(2000);
			return true;
		}
	});
});

$(document).on("click", ".edit-ban", function(e) {
	e.preventDefault();

	// Close the message connection
	if (rlsbint) {
		clearInterval(rlsbint);
		rlsbint = null;
	}

	var _this = $(this);
	var userid = $(_this).data("userid");
					
	$.ajax({
		type: "POST",
		url: ls.main_url + 'include/gcmod.php',
		data: "action=banusr&id="+userid,
		dataType: 'json',
		success: function(msg){

			// Bring back the chat messages this time faster
			sseJAK(2000);
			return true;
		}
	});
});

// use the property name to generate the prefixed event name
var visProp = getHiddenProp();
if (visProp) {
  var evtname = visProp.replace(/[H|h]idden/,'') + 'visibilitychange';
  document.addEventListener(evtname, visChange);
}

function visChange() {

	// Close the message connection
	if (rlsbint) {
		clearInterval(rlsbint);
		rlsbint = null;
	}

	// Check the visibility status once and for all (ignoring new windows)
   	if (isHidden()) {
		winIsActive = false;
		sseJAK(15000);
	} else {
		winIsActive = scrollchat = true;
		sseJAK(2000);
	}
}

function sseJAK(timer) {

	setCheckerGC();
	if (!rlsbint) rlsbint = setInterval(function(){setCheckerGC()}, timer);		

}

function sendInputGC() {
	
	if(working) return false;
	
	working = true;

	// Close the message connection
	if (rlsbint) {
		clearInterval(rlsbint);
		rlsbint = null;
	}
	
	/* This flag will prevent multiple comment submits: */
	$("#sendMessage i").removeClass("fa-paper-plane").addClass("fa-spinner fa-pulse");
	$('#message').removeClass("is-invalid");
	
	var messageC = $('#message').val();
	var message = encodeURIComponent(messageC);

	// Do we have a message
	if (message.length <= 1 || usract != 1) {
		$("#sendMessage i").removeClass("fa-spinner fa-pulse").addClass("fa-paper-plane");
		$('#message').addClass("is-invalid");
		working = false;
		// Bring back the chat messages this time faster
		sseJAK(5000);
		return false;
	}

	var msgquote = $('#msgquote').val();

	var request = $.ajax({
	  url: ls.main_url + 'include/gcinsert.php',
	  type: "POST",
	  data: "msg="+message+"&msgquote="+msgquote,
	  dataType: "json",
	  cache: false
	});
	
	request.done(function(msg) {
		if (msg.status == 1) {
			$('#msgquote, #message').val("");
			$('.edit-quote').removeClass('active');
			$('.media-text').removeClass('highlight');
			countdown();
			ctimer = 2000;
		} else {
			$('#message').addClass("is-invalid");
			$("#sendMessage i").removeClass("fa-spinner fa-pulse").addClass("fa-paper-plane");
			//Create an instance of Notyf
			var notyf = new Notyf();
			//Display an alert notification
			notyf.alert(msg.html);

			ctimer = 5000;
		}
		
		working = false;
		
	});

	// Bring back the chat messages this time faster
	sseJAK(ctimer);
}

function setCheckerGC() {

	var request = $.ajax({
	  url: ls.main_url + 'include/gcinform.php',
	  type: "GET",
	  data: "lastid="+ulastmsgid+"&usract="+usract+"&active="+winIsActive,
	  dataType: "json",
	  cache: false
	});
	
	request.done(function(msg) {
	
		handlemsg(msg);
	
	});
	
}

function handlemsg(msg) {
		
	// We have a new message
	if (msg.newmsg == 1) {

		$("#group_chat_output").append(msg.html);

		ulastmsgid = msg.lastid;
		usract = 1;

		scrollchat = true;
		scrollBottom();
		
	}

	// Banned
	if (msg.newmsg == 2) {

		$("#group_chat_output").append(msg.html);

		ulastmsgid = msg.lastid;
		usract = 2;

		scrollchat = true;
		scrollBottom();
		
	}

	// offline
	if (msg.newmsg == 3) {

		$("#group_chat_output").append(msg.html);

		ulastmsgid = msg.lastid;
		usract = 3;

		scrollchat = true;
		scrollBottom();
		
	}

	// We have new or gone customers reload the visitor list
	if (msg.vislist) $("#visitorslist").html(msg.vislist);

	// We have a message to delete
	if (msg.delmsg) {
		msg.delmsg.forEach(function(obj) {
			$('#postid_'+obj+', #msg_'+obj).remove();
			// console.log(obj);
		});
	}

}

function scrollBottom() {
	$('#group_chat_output').animate({
	    scrollTop: $('#group_chat_output')[0].scrollHeight
	}, 300);
	// set scrollchat to false
	scrollchat = false;
}

function countdown() {
    var count = 6;
    var timer = setInterval(function() {
        count--;
        if (count < 1) { 
        	$('#sendMessage').html('<i class="fa fa-paper-plane"></i>');
        	clearInterval(timer);
        } else {
        	$('#sendMessage').html(count.toString());
        }
    }, 1000);
}

function getHiddenProp(){
    var prefixes = ['webkit','moz','ms','o'];
    
    // if 'hidden' is natively supported just return it
    if ('hidden' in document) return 'hidden';
    
    // otherwise loop over all the known prefixes until we find one
    for (var i = 0; i < prefixes.length; i++){
        if ((prefixes[i] + 'Hidden') in document) 
            return prefixes[i] + 'Hidden';
    }

    // otherwise it's not supported
    return null;
}

function isHidden() {
    var prop = getHiddenProp();
    if (!prop) return false;
    
    return document[prop];
}

!function(){function n(n,t){for(property in t)t.hasOwnProperty(property)&&(n[property]=t[property]);return n}function t(n,t){var e=document.createElement("div");e.className="notyf__toast";var o=document.createElement("div");o.className="notyf__wrapper";var i=document.createElement("div");i.className="notyf__icon";var a=document.createElement("i");a.className=t;var r=document.createElement("div");r.className="notyf__message",r.innerHTML=n,i.appendChild(a),o.appendChild(i),o.appendChild(r),e.appendChild(o);var c=this;return setTimeout(function(){e.className+=" notyf--disappear",e.addEventListener(c.animationEnd,function(n){n.target==e&&c.container.removeChild(e)});var n=c.notifications.indexOf(e);c.notifications.splice(n,1)},c.options.delay),e}function e(){var n,t=document.createElement("fake"),e={transition:"animationend",OTransition:"oAnimationEnd",MozTransition:"animationend",WebkitTransition:"webkitAnimationEnd"};for(n in e)if(void 0!==t.style[n])return e[n]}this.Notyf=function(){this.notifications=[];var t={delay:2e3,alertIcon:"notyf__icon--alert",confirmIcon:"notyf__icon--confirm"};arguments[0]&&"object"==typeof arguments[0]?this.options=n(t,arguments[0]):this.options=t;var o=document.createDocumentFragment(),i=document.createElement("div");i.className="notyf",o.appendChild(i),document.body.appendChild(o),this.container=i,this.animationEnd=e()},this.Notyf.prototype.alert=function(n){var e=t.call(this,n,this.options.alertIcon);e.className+=" notyf--alert",this.container.appendChild(e),this.notifications.push(e)},this.Notyf.prototype.confirm=function(n){var e=t.call(this,n,this.options.confirmIcon);e.className+=" notyf--confirm",this.container.appendChild(e),this.notifications.push(e)}}(),function(){"function"==typeof define&&define.amd?define("Notyf",function(){return Notyf}):"undefined"!=typeof module&&module.exports?module.exports=Notyf:window.Notyf=Notyf}();