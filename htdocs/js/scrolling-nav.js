$(function(){$('a.page-scroll').bind('click',function(event){var $anchor=$(this);$('html, body').stop().animate({scrollTop:$($anchor.attr('href')).offset().top-55},1500,'easeInOutExpo');event.preventDefault()})})
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return-h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}
return-h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}
return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return-h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}
return-h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}
return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return-h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return-h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}
if(f==g){return a+h}
if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}
return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return-h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return-h/2*(Math.sqrt(1-f*f)-1)+a}
return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}
if((h/=k)==1){return e+l}
if(!j){j=k*0.3}
if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}
return-(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}
if((h/=k)==1){return e+l}
if(!j){j=k*0.3}
if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}
return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}
if((h/=k/2)==2){return e+l}
if(!j){j=k*(0.3*1.5)}
if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}
if(h<1){return-0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}
return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}
return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}
return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}
if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}
return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}
return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});window.onbeforeunload=function(){}
function getTimeRemaining(endtime){var t=Date.parse(endtime)-Date.parse(new Date());var seconds=Math.floor((t/1000)%60);var minutes=Math.floor((t/1000/60)%60);var hours=Math.floor((t/(1000*60*60))%24);var days=Math.floor(t/(1000*60*60*24));if(seconds<0){days=0;hours=0;minutes=0;seconds=0}
return{'total':t,'days':days,'hours':hours,'minutes':minutes,'seconds':seconds}}
function initializeClock(clockClassName,endtime,elementPosition){var clock=document.getElementsByClassName(clockClassName);var daysSpan=clock[elementPosition].querySelector('.days');var hoursSpan=clock[elementPosition].querySelector('.hours');var minutesSpan=clock[elementPosition].querySelector('.minutes');var secondsSpan=clock[elementPosition].querySelector('.seconds');var time_split=endtime.split("/");var server_end=time_split[1]*1000;var server_now=time_split[0]*1000;var client_now=new Date().getTime();var end=server_end-server_now+client_now;var _second=1000;var _minute=_second*60;var _hour=_minute*60;var _day=_hour*24;var timer;function timer_check(){var now=new Date();var distance=Math.round((end-now)/1000)*1000;if(distance<0){clearInterval(timer);return}
var days=Math.floor(distance/_day);var hours=Math.floor((distance%_day)/_hour);var minutes=Math.floor((distance%_hour)/_minute);var seconds=Math.floor((distance%_minute)/_second);daysSpan.innerHTML=days;hoursSpan.innerHTML=('0'+hours).slice(-2);minutesSpan.innerHTML=('0'+minutes).slice(-2);secondsSpan.innerHTML=('0'+seconds).slice(-2)}
timer_check();timer=setInterval(timer_check,1000)}
function progressbar(id,endtime,days,elementPosition){var barLength=(Date.parse(endtime)-Date.parse(new Date()))/days;if(barLength<0){barLength=0}
var elementName=document.getElementsByClassName(id);elementName[elementPosition].style.width=(100*barLength)+'%'}
function initializeCountdown(){var x=document.getElementsByClassName("savings-countdown-div");var i=0;for(i=0;i<x.length;i++){initializeClock("savings-countdown-div",x[i].lastChild.innerHTML,i)}}
initializeCountdown()
