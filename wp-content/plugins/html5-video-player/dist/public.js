(()=>{"use strict";var e;(e=jQuery)(document).ready((function(){const n=e(".h5vp_player_initializer");n&&t.initialize(n)})),e(window).on("elementor/frontend/init",(function(){elementorFrontend.hooks.addAction("frontend/element_ready/H5VPPlayer.default",(function(e,n){t.initialize(e)}))}));class t{static initialize(e){var t,n,o,i,l;let r=arguments.length>1&&void 0!==arguments[1]&&arguments[1],s=arguments.length>2&&void 0!==arguments[2]&&arguments[2],a=window.innerWidth;const u=this.getDetectorData(e,r,s);if(!u)return!1;const{wrapper:d,mediaElement:c,options:v,infos:p}=u;a<768&&(v.controls=this.controlsForMobile(null==v?void 0:v.controls));const y=new Plyr(c,v);if(v.muted&&v.autoplay&&(y.volume=0),null===(t=y.elements.volume)||void 0===t||null===(n=t.querySelector('[data-plyr="mute"]'))||void 0===n||n.addEventListener("click",(function(){0===y.volume&&(y.volume=.5)})),null!=p&&p.setSource&&(y.source={type:"video",title:"Title",sources:[{src:null==p?void 0:p.source,type:"video/mp4"}],poster:null==p?void 0:p.poster}),(null===(o=window)||void 0===o?void 0:o.innerWidth)<992&&(y.on("enterfullscreen",(e=>{var t,n;null===(t=screen)||void 0===t||null===(n=t.orientation)||void 0===n||n.lock("landscape")})),y.on("exitfullscreen",(e=>{var t,n;null===(t=screen)||void 0===t||null===(n=t.orientation)||void 0===n||n.lock("portrait")}))),y.on("enterfullscreen",(function(){d.classList.add("fullscreen")})),y.on("exitfullscreen",(function(){d.classList.remove("fullscreen")})),null==v||null===(i=v.controls)||void 0===i||!i.includes("progress")){const e=d.querySelector(".plyr__controls");e&&(e.style.background="transparent")}"undefined"!=typeof h5vp&&Boolean(parseInt(null===(l=h5vp)||void 0===l?void 0:l.pauseOther))&&y.on("play",(()=>{var e;if(location.pathname.includes("wp-admin"))return!1;const t=null===(e=d.dataset)||void 0===e?void 0:e.uniqueId,n=document.querySelectorAll(`video:not([data-unique-id="${t}"] video, a video)`);Object.values(n).map((e=>e.pause()))}))}static getDetectorData(e,t,n){let o=null;if(null===e)return!1;if(void 0!==e[0])return e.map(((e,o)=>{this.initialize(o,t,n)})),!1;if(void 0!==e.length&&0===e.length)return!1;null!==e.querySelector(".h5vp_player")&&(e=e.querySelector(".h5vp_player"));let i=jQuery(e).attr("data-settings");var l,r;i&&(i=JSON.parse(i),e.dataset.settings="",t||(t=null===(l=i)||void 0===l?void 0:l.options),n||(n=null===(r=i)||void 0===r?void 0:r.infos));return o=e.querySelector("video"),{wrapper:e,options:t,mediaElement:o,infos:n}}static controlsForMobile(e){return e=this.removeControl(e,"restart"),e=this.removeControl(e,"duration"),e=this.removeControl(e,"airplay"),this.removeControl(e,"pip")}static removeControl(e,t){const n=e.indexOf(t);return-1!=n&&e.splice(n,1),e}static setHeight(e,t){const n=jQuery(e).width();t.on("loadeddata",(function(){const o=t.ratio;if(!o)return!1;const[i,l]=o.split(":"),r=n/i*l;e.classList.add("plyr_set_height"),jQuery(e).find(".plyr").css("height",r+"px")})),t.on("ready",(function(){setTimeout((()=>{var t;null===(t=e.querySelector(".plyr"))||void 0===t||t.removeAttribute("style")}),300)}))}}})();