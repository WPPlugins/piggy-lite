/*!
 * Add to Homescreen v2.0.8 ~ Copyright (c) 2013 Matteo Spinelli, http://cubiq.org
 * Released under MIT license, http://cubiq.org/license
 */
var addToHome=(function(w){var nav=w.navigator,isIDevice="platform" in nav&&(/iphone|ipod|ipad/gi).test(nav.platform),isIPad,isRetina,isSafari,isStandalone,OSVersion,startX=0,startY=0,lastVisit=0,isExpired,isSessionActive,isReturningVisitor,balloon,overrideChecks,positionInterval,closeTimeout,options={autostart:true,returningVisitor:false,animationIn:"drop",animationOut:"fade",startDelay:2000,lifespan:15000,bottomOffset:14,expire:0,message:"",touchIcon:false,arrow:true,hookOnLoad:true,closeButton:true,iterations:100},intl={en_us:"Install this web app on your %device: tap %icon and then <strong>Add to Home Screen</strong>.",};function init(){if(!isIDevice){return}var now=Date.now(),i;if(w.addToHomeConfig){for(i in w.addToHomeConfig){options[i]=w.addToHomeConfig[i]}}if(!options.autostart){options.hookOnLoad=false}isIPad=(/ipad/gi).test(nav.platform);isRetina=w.devicePixelRatio&&w.devicePixelRatio>1;isSafari=(/Safari/i).test(nav.appVersion)&&!(/CriOS/i).test(nav.appVersion);isStandalone=nav.standalone;OSVersion=nav.appVersion.match(/OS (\d+_\d+)/i);OSVersion=OSVersion&&OSVersion[1]?+OSVersion[1].replace("_","."):0;lastVisit=+w.localStorage.getItem("addToHome");isSessionActive=w.sessionStorage.getItem("addToHomeSession");isReturningVisitor=options.returningVisitor?lastVisit&&lastVisit+28*24*60*60*1000>now:true;if(!lastVisit){lastVisit=now}isExpired=isReturningVisitor&&lastVisit<=now;if(options.hookOnLoad){w.addEventListener("load",loaded,false)}else{if(!options.hookOnLoad&&options.autostart){loaded()}}}function loaded(){w.removeEventListener("load",loaded,false);if(!isReturningVisitor){w.localStorage.setItem("addToHome",Date.now())}else{if(options.expire&&isExpired){w.localStorage.setItem("addToHome",Date.now()+options.expire*60000)}}if(!overrideChecks&&(!isSafari||!isExpired||isSessionActive||isStandalone||!isReturningVisitor)){return}var touchIcon="",platform=nav.platform.split(" ")[0],language=nav.language.replace("-","_");balloon=document.createElement("div");balloon.id="addToHomeScreen";balloon.style.cssText+="left:-9999px;-webkit-transition-property:-webkit-transform,opacity;-webkit-transition-duration:0;-webkit-transform:translate3d(0,0,0);position:"+(OSVersion<5?"absolute":"fixed");if(options.message in intl){language=options.message;options.message=""}if(options.message===""){options.message=language in intl?intl[language]:intl.en_us}if(options.touchIcon){touchIcon=isRetina?document.querySelector('head link[rel^=apple-touch-icon][sizes="114x114"],head link[rel^=apple-touch-icon][sizes="144x144"],head link[rel^=apple-touch-icon]'):document.querySelector('head link[rel^=apple-touch-icon][sizes="57x57"],head link[rel^=apple-touch-icon]');if(touchIcon){touchIcon='<span style="background-image:url('+touchIcon.href+')" class="addToHomeTouchIcon"></span>'}}balloon.className=(isIPad?"addToHomeIpad":"addToHomeIphone")+(touchIcon?" addToHomeWide":"");balloon.innerHTML=touchIcon+options.message.replace("%device",platform).replace("%icon",OSVersion>=4.2?'<span class="addToHomeShare'+(OSVersion>=7?" addToHomeShareOS7":"")+'"></span>':'<span class="addToHomePlus">+</span>')+(options.arrow?'<span class="addToHomeArrow"></span>':"")+(options.closeButton?'<span class="addToHomeClose">\u00D7</span>':"");document.body.appendChild(balloon);if(options.closeButton){balloon.addEventListener("click",clicked,false)}if(!isIPad&&OSVersion>=6){window.addEventListener("orientationchange",orientationCheck,false)}setTimeout(show,options.startDelay)}function show(){var duration,iPadXShift=208;if(isIPad){if(OSVersion<5){startY=w.scrollY;startX=w.scrollX}else{if(OSVersion<6){iPadXShift=160}}balloon.style.top=startY+options.bottomOffset+"px";balloon.style.left=startX+iPadXShift-Math.round(balloon.offsetWidth/2)+"px";switch(options.animationIn){case"drop":duration="0.6s";balloon.style.webkitTransform="translate3d(0,"+-(w.scrollY+options.bottomOffset+balloon.offsetHeight)+"px,0)";break;case"bubble":duration="0.6s";balloon.style.opacity="0";balloon.style.webkitTransform="translate3d(0,"+(startY+50)+"px,0)";break;default:duration="1s";balloon.style.opacity="0"}}else{startY=w.innerHeight+w.scrollY;if(OSVersion<5){startX=Math.round((w.innerWidth-balloon.offsetWidth)/2)+w.scrollX;balloon.style.left=startX+"px";balloon.style.top=startY-balloon.offsetHeight-options.bottomOffset+"px"}else{balloon.style.left="50%";balloon.style.marginLeft=-Math.round(balloon.offsetWidth/2)-(w.orientation%180&&OSVersion>=6?40:0)+"px";balloon.style.bottom=options.bottomOffset+"px"}switch(options.animationIn){case"drop":duration="1s";balloon.style.webkitTransform="translate3d(0,"+-(startY+options.bottomOffset)+"px,0)";break;case"bubble":duration="0.6s";balloon.style.webkitTransform="translate3d(0,"+(balloon.offsetHeight+options.bottomOffset+50)+"px,0)";break;default:duration="1s";balloon.style.opacity="0"}}balloon.offsetHeight;balloon.style.webkitTransitionDuration=duration;balloon.style.opacity="1";balloon.style.webkitTransform="translate3d(0,0,0)";balloon.addEventListener("webkitTransitionEnd",transitionEnd,false);closeTimeout=setTimeout(close,options.lifespan)}function manualShow(override){if(!isIDevice||balloon){return}overrideChecks=override;loaded()}function close(){clearInterval(positionInterval);clearTimeout(closeTimeout);closeTimeout=null;if(!balloon){return}var posY=0,posX=0,opacity="1",duration="0";if(options.closeButton){balloon.removeEventListener("click",clicked,false)}if(!isIPad&&OSVersion>=6){window.removeEventListener("orientationchange",orientationCheck,false)}if(OSVersion<5){posY=isIPad?w.scrollY-startY:w.scrollY+w.innerHeight-startY;posX=isIPad?w.scrollX-startX:w.scrollX+Math.round((w.innerWidth-balloon.offsetWidth)/2)-startX}balloon.style.webkitTransitionProperty="-webkit-transform,opacity";switch(options.animationOut){case"drop":if(isIPad){duration="0.4s";opacity="0";posY+=50}else{duration="0.6s";posY+=balloon.offsetHeight+options.bottomOffset+50}break;case"bubble":if(isIPad){duration="0.8s";posY-=balloon.offsetHeight+options.bottomOffset+50}else{duration="0.4s";opacity="0";posY-=50}break;default:duration="0.8s";opacity="0"}balloon.addEventListener("webkitTransitionEnd",transitionEnd,false);balloon.style.opacity=opacity;balloon.style.webkitTransitionDuration=duration;balloon.style.webkitTransform="translate3d("+posX+"px,"+posY+"px,0)"}function clicked(){w.sessionStorage.setItem("addToHomeSession","1");isSessionActive=true;close()}function transitionEnd(){balloon.removeEventListener("webkitTransitionEnd",transitionEnd,false);balloon.style.webkitTransitionProperty="-webkit-transform";balloon.style.webkitTransitionDuration="0.2s";if(!closeTimeout){balloon.parentNode.removeChild(balloon);balloon=null;return}if(OSVersion<5&&closeTimeout){positionInterval=setInterval(setPosition,options.iterations)}}function setPosition(){var matrix=new WebKitCSSMatrix(w.getComputedStyle(balloon,null).webkitTransform),posY=isIPad?w.scrollY-startY:w.scrollY+w.innerHeight-startY,posX=isIPad?w.scrollX-startX:w.scrollX+Math.round((w.innerWidth-balloon.offsetWidth)/2)-startX;if(posY==matrix.m42&&posX==matrix.m41){return}balloon.style.webkitTransform="translate3d("+posX+"px,"+posY+"px,0)"}function reset(){w.localStorage.removeItem("addToHome");w.sessionStorage.removeItem("addToHomeSession")}function orientationCheck(){balloon.style.marginLeft=-Math.round(balloon.offsetWidth/2)-(w.orientation%180&&OSVersion>=6?40:0)+"px"}init();return{show:manualShow,close:close,reset:reset}})(window);