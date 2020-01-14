browserName = navigator.appName;
browserVer = parseInt(navigator.appVersion);
var agent = navigator.userAgent;
var ua = navigator.userAgent.toLowerCase();
var msie_ver = ua.charAt(ua.indexOf('msie')+5);

// этих ресурсов нет, зачем этот код тут?s
//if (ua.indexOf('opera') != -1) {
//	document.writeln("<link rel=stylesheet type=text/css href=\"/css/op.css\">");
//}
//else if ((ua.indexOf('msie') != -1) && (msie_ver == 5)) { // IE 7.0
//	document.writeln("<link rel=stylesheet type=text/css href=\"/css/ie5_5.css\">");
//}
//else if ((ua.indexOf('msie') != -1) && (msie_ver == 6)) { // IE 7.0
//	document.writeln("<link rel=stylesheet type=text/css href=\"/css/ie6.css\">");
//}
//else if ((ua.indexOf('msie') != -1) && (msie_ver == 7)) { // IE 7.0
//	document.writeln("<link rel=stylesheet type=text/css href=\"/css/ie7.css\">");
//}
//else if ((browserVer >3) &&(browserName == "Netscape")) {
//	document.writeln("<link rel=stylesheet type=text/css href=\"/css/nn.css\">");
//}

 $(function(){
  
  $(window).bind("load resize", function(){
  var ac_h = $('.main_second_page').height();
  var hall = $(window).height() - 180;
  
  if (ac_h < hall) {$('.main_page').css('min-height', hall)}
 })

  });


 $(document).ready(function(){
  open_win();
//  $('#tehp').toggle(function(){
//    close_win();
//   }, function(){
//    open_win();
//  });
  $('#tehp .x_say_more').click(function(){close_win(); 
	$(this).hide();
	$('#tehp .x_say_more2').animate({'right':'1px'},1300);
});	
  $('#tehp .x_say_more2').click(function(){open_win(); 
	$(this).animate({'right':'400px'},1300);
	$('#tehp .x_say_more').show();
});	

  
 })
 function open_win() {$('#tehp').animate({'left':'0px','cursor':'default'},1300);}; 
 function close_win() {$('#tehp').animate({'left':'-310px','cursor':'pointer'},1300);}


  


