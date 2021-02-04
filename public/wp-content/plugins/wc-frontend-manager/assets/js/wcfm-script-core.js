$wcfm_products_table = '';
var $wcfm_is_valid_form = true;
var $wcfm_message_close_timer = '';

var tinyMce_toolbar = 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify |  bullist numlist outdent indent | link image | ltr rtl';
if ( wcfm_params.wcfm_allow_tinymce_options ) {
	tinyMce_toolbar = wcfm_params.wcfm_allow_tinymce_options;
}

$popup_width = '50%';
$large_popup_width = '75%';
if( jQuery(window).width() <= 768 ) {
	$popup_width = '95%';
	$large_popup_width = '95%';
}


/* WCFM qTip2 */
(function(t,e,s){(function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery","imagesloaded"],t):jQuery&&!jQuery.fn.qtip&&t(jQuery)})(function(o){function n(t,e,i,s){this.id=i,this.target=t,this.tooltip=E,this.elements=elements={target:t},this._id=$+"-"+i,this.timers={img:{}},this.options=e,this.plugins={},this.cache=cache={event:{},target:o(),disabled:S,attr:s,onTooltip:S,lastClass:""},this.rendered=this.destroyed=this.disabled=this.waiting=this.hiddenDuringWait=this.positioning=this.triggering=S}function r(t){return t===E||"object"!==o.type(t)}function a(t){return!(o.isFunction(t)||t&&t.attr||t.length||"object"===o.type(t)&&(t.jquery||t.then))}function h(t){var e,i,s,n;return r(t)?S:(r(t.metadata)&&(t.metadata={type:t.metadata}),"content"in t&&(e=t.content,r(e)||e.jquery||e.done?e=t.content={text:i=a(e)?S:e}:i=e.text,"ajax"in e&&(s=e.ajax,n=s&&s.once!==S,delete e.ajax,e.text=function(t,e){var r=i||o(this).attr(e.options.content.attr)||"Loading...",a=o.ajax(o.extend({},s,{context:e})).then(s.success,E,s.error).then(function(t){return t&&n&&e.set("content.text",t),t},function(t,i,s){e.destroyed||0===t.status||e.set("content.text",i+": "+s)});return n?r:(e.set("content.text",r),a)}),"title"in e&&(r(e.title)||(e.button=e.title.button,e.title=e.title.text),a(e.title||S)&&(e.title=S))),"position"in t&&r(t.position)&&(t.position={my:t.position,at:t.position}),"show"in t&&r(t.show)&&(t.show=t.show.jquery?{target:t.show}:t.show===M?{ready:M}:{event:t.show}),"hide"in t&&r(t.hide)&&(t.hide=t.hide.jquery?{target:t.hide}:{event:t.hide}),"style"in t&&r(t.style)&&(t.style={classes:t.style}),o.each(N,function(){this.sanitize&&this.sanitize(t)}),t)}function l(t,e){for(var i,s=0,o=t,n=e.split(".");o=o[n[s++]];)n.length>s&&(i=o);return[i||t,n.pop()]}function c(t,e){var i,s,o;for(i in this.checks)for(s in this.checks[i])(o=RegExp(s,"i").exec(t))&&(e.push(o),("builtin"===i||this.plugins[i])&&this.checks[i][s].apply(this.plugins[i]||this,e))}function p(t){return H.concat("").join(t?"-"+t+" ":" ")}function d(t){if(this.tooltip.hasClass(te))return S;clearTimeout(this.timers.show),clearTimeout(this.timers.hide);var e=o.proxy(function(){this.toggle(M,t)},this);this.options.show.delay>0?this.timers.show=setTimeout(e,this.options.show.delay):e()}function u(t){if(this.tooltip.hasClass(te))return S;var e=o(t.relatedTarget),i=e.closest(G)[0]===this.tooltip[0],s=e[0]===this.options.show.target[0];if(clearTimeout(this.timers.show),clearTimeout(this.timers.hide),this!==e[0]&&"mouse"===this.options.position.target&&i||this.options.hide.fixed&&/mouse(out|leave|move)/.test(t.type)&&(i||s))try{t.preventDefault(),t.stopImmediatePropagation()}catch(n){}else{var r=o.proxy(function(){this.toggle(S,t)},this);this.options.hide.delay>0?this.timers.hide=setTimeout(r,this.options.hide.delay):r()}}function f(t){return this.tooltip.hasClass(te)||!this.options.hide.inactive?S:(clearTimeout(this.timers.inactive),this.timers.inactive=setTimeout(o.proxy(function(){this.hide(t)},this),this.options.hide.inactive),s)}function g(t){this.rendered&&this.tooltip[0].offsetWidth>0&&this.reposition(t)}function m(t,i,s){o(e.body).delegate(t,(i.split?i:i.join(re+" "))+re,function(){var t=T.api[o.attr(this,Y)];t&&!t.disabled&&s.apply(t,arguments)})}function v(t,i,s){var r,a,l,c,p,d=o(e.body),u=t[0]===e?d:t,f=t.metadata?t.metadata(s.metadata):E,g="html5"===s.metadata.type&&f?f[s.metadata.name]:E,m=t.data(s.metadata.name||"qtipopts");try{m="string"==typeof m?o.parseJSON(m):m}catch(v){}if(c=o.extend(M,{},T.defaults,s,"object"==typeof m?h(m):E,h(g||f)),a=c.position,c.id=i,"boolean"==typeof c.content.text){if(l=t.attr(c.content.attr),c.content.attr===S||!l)return S;c.content.text=l}if(a.container.length||(a.container=d),a.target===S&&(a.target=u),c.show.target===S&&(c.show.target=u),c.show.solo===M&&(c.show.solo=a.container.closest("body")),c.hide.target===S&&(c.hide.target=u),c.position.viewport===M&&(c.position.viewport=a.container),a.container=a.container.eq(0),a.at=new j(a.at,M),a.my=new j(a.my),t.data($))if(c.overwrite)t.qtip("destroy");else if(c.overwrite===S)return S;return t.attr(X,i),c.suppress&&(p=t.attr("title"))&&t.removeAttr("title").attr(ie,p).attr("title",""),r=new n(t,c,i,!!l),t.data($,r),t.one("remove.qtip-"+i+" removeqtip.qtip-"+i,function(){var t;(t=o(this).data($))&&t.destroy()}),r}function y(t){return t.charAt(0).toUpperCase()+t.slice(1)}function b(t,e){var i,o,n=e.charAt(0).toUpperCase()+e.slice(1),r=(e+" "+ve.join(n+" ")+n).split(" "),a=0;if(me[e])return t.css(me[e]);for(;i=r[a++];)if((o=t.css(i))!==s)return me[e]=i,o}function w(t,e){return parseInt(b(t,e),10)}function x(t,e){this._ns="tip",this.options=e,this.offset=e.offset,this.size=[e.width,e.height],this.init(this.qtip=t)}function _(t,e){this.options=e,this._ns="-modal",this.init(this.qtip=t)}function q(t){this._ns="ie6",this.init(this.qtip=t)}var T,C,j,z,W,M=!0,S=!1,E=null,O="x",R="y",I="width",k="height",B="top",L="left",P="bottom",V="right",D="center",A="flipinvert",F="shift",N={},$="qtip",X="data-hasqtip",Y="data-qtip-id",H=["ui-widget","ui-tooltip"],G="."+$,U="click dblclick mousedown mouseup mousemove mouseleave mouseenter".split(" "),Q=$+"-fixed",J=$+"-default",K=$+"-focus",Z=$+"-hover",te=$+"-disabled",ee="_replacedByqTip",ie="oldtitle";BROWSER={ie:function(){for(var t=3,i=e.createElement("div");(i.innerHTML="<!--[if gt IE "+ ++t+"]><i></i><![endif]-->")&&i.getElementsByTagName("i")[0];);return t>4?t:0/0}(),iOS:parseFloat((""+(/CPU.*OS ([0-9_]{1,5})|(CPU like).*AppleWebKit.*Mobile/i.exec(navigator.userAgent)||[0,""])[1]).replace("undefined","3_2").replace("_",".").replace("_",""))||S},C=n.prototype,C.render=function(t){if(this.rendered||this.destroyed)return this;var e=this,i=this.options,s=this.cache,n=this.elements,r=i.content.text,a=i.content.title,h=i.content.button,l=i.position,c="."+this._id+" ",p=[];return o.attr(this.target[0],"aria-describedby",this._id),this.tooltip=n.tooltip=tooltip=o("<div/>",{id:this._id,"class":[$,J,i.style.classes,$+"-pos-"+i.position.my.abbrev()].join(" "),width:i.style.width||"",height:i.style.height||"",tracking:"mouse"===l.target&&l.adjust.mouse,role:"alert","aria-live":"polite","aria-atomic":S,"aria-describedby":this._id+"-content","aria-hidden":M}).toggleClass(te,this.disabled).attr(Y,this.id).data($,this).appendTo(l.container).append(n.content=o("<div />",{"class":$+"-content",id:this._id+"-content","aria-atomic":M})),this.rendered=-1,this.positioning=M,a&&(this._createTitle(),o.isFunction(a)||p.push(this._updateTitle(a,S))),h&&this._createButton(),o.isFunction(r)||p.push(this._updateContent(r,S)),this.rendered=M,this._setWidget(),o.each(i.events,function(t,e){o.isFunction(e)&&tooltip.bind(("toggle"===t?["tooltipshow","tooltiphide"]:["tooltip"+t]).join(c)+c,e)}),o.each(N,function(t){var i;"render"===this.initialize&&(i=this(e))&&(e.plugins[t]=i)}),this._assignEvents(),o.when.apply(o,p).then(function(){e._trigger("render"),e.positioning=S,e.hiddenDuringWait||!i.show.ready&&!t||e.toggle(M,s.event,S),e.hiddenDuringWait=S}),T.api[this.id]=this,this},C.destroy=function(t){function e(){if(!this.destroyed){this.destroyed=M;var t=this.target,e=t.attr(ie);this.rendered&&this.tooltip.stop(1,0).find("*").remove().end().remove(),o.each(this.plugins,function(){this.destroy&&this.destroy()}),clearTimeout(this.timers.show),clearTimeout(this.timers.hide),this._unassignEvents(),t.removeData($).removeAttr(Y).removeAttr("aria-describedby"),this.options.suppress&&e&&t.attr("title",e).removeAttr(ie),this._unbind(t),this.options=this.elements=this.cache=this.timers=this.plugins=this.mouse=E,delete T.api[this.id]}}return this.destroyed?this.target:(t!==M&&this.rendered?(tooltip.one("tooltiphidden",o.proxy(e,this)),!this.triggering&&this.hide()):e.call(this),this.target)},z=C.checks={builtin:{"^id$":function(t,e,i,s){var n=i===M?T.nextid:i,r=$+"-"+n;n!==S&&n.length>0&&!o("#"+r).length?(this._id=r,this.rendered&&(this.tooltip[0].id=this._id,this.elements.content[0].id=this._id+"-content",this.elements.title[0].id=this._id+"-title")):t[e]=s},"^prerender":function(t,e,i){i&&!this.rendered&&this.render(this.options.show.ready)},"^content.text$":function(t,e,i){this._updateContent(i)},"^content.attr$":function(t,e,i,s){this.options.content.text===this.target.attr(s)&&this._updateContent(this.target.attr(i))},"^content.title$":function(t,e,i){return i?(i&&!this.elements.title&&this._createTitle(),this._updateTitle(i),s):this._removeTitle()},"^content.button$":function(t,e,i){this._updateButton(i)},"^content.title.(text|button)$":function(t,e,i){this.set("content."+e,i)},"^position.(my|at)$":function(t,e,i){"string"==typeof i&&(t[e]=new j(i,"at"===e))},"^position.container$":function(t,e,i){this.tooltip.appendTo(i)},"^show.ready$":function(t,e,i){i&&(!this.rendered&&this.render(M)||this.toggle(M))},"^style.classes$":function(t,e,i,s){this.tooltip.removeClass(s).addClass(i)},"^style.width|height":function(t,e,i){this.tooltip.css(e,i)},"^style.widget|content.title":function(){this._setWidget()},"^style.def":function(t,e,i){this.tooltip.toggleClass(J,!!i)},"^events.(render|show|move|hide|focus|blur)$":function(t,e,i){tooltip[(o.isFunction(i)?"":"un")+"bind"]("tooltip"+e,i)},"^(show|hide|position).(event|target|fixed|inactive|leave|distance|viewport|adjust)":function(){var t=this.options.position;tooltip.attr("tracking","mouse"===t.target&&t.adjust.mouse),this._unassignEvents(),this._assignEvents()}}},C.get=function(t){if(this.destroyed)return this;var e=l(this.options,t.toLowerCase()),i=e[0][e[1]];return i.precedance?i.string():i};var se=/^position\.(my|at|adjust|target|container|viewport)|style|content|show\.ready/i,oe=/^prerender|show\.ready/i;C.set=function(t,e){if(this.destroyed)return this;var i,n=this.rendered,r=S,a=this.options;return this.checks,"string"==typeof t?(i=t,t={},t[i]=e):t=o.extend({},t),o.each(t,function(e,i){if(!n&&!oe.test(e))return delete t[e],s;var h,c=l(a,e.toLowerCase());h=c[0][c[1]],c[0][c[1]]=i&&i.nodeType?o(i):i,r=se.test(e)||r,t[e]=[c[0],c[1],i,h]}),h(a),this.positioning=M,o.each(t,o.proxy(c,this)),this.positioning=S,this.rendered&&this.tooltip[0].offsetWidth>0&&r&&this.reposition("mouse"===a.position.target?E:this.cache.event),this},C._update=function(t,e){var i=this,s=this.cache;return this.rendered&&t?(o.isFunction(t)&&(t=t.call(this.elements.target,s.event,this)||""),o.isFunction(t.then)?(s.waiting=M,t.then(function(t){return s.waiting=S,i._update(t,e)},E,function(t){return i._update(t,e)})):t===S||!t&&""!==t?S:(t.jquery&&t.length>0?e.children().detach().end().append(t.css({display:"block"})):e.html(t),s.waiting=M,(o.fn.imagesLoaded?e.imagesLoaded():o.Deferred().resolve(o([]))).done(function(t){s.waiting=S,t.length&&i.rendered&&i.tooltip[0].offsetWidth>0&&i.reposition(s.event,!t.length)}).promise())):S},C._updateContent=function(t,e){this._update(t,this.elements.content,e)},C._updateTitle=function(t,e){this._update(t,this.elements.title,e)===S&&this._removeTitle(S)},C._createTitle=function(){var t=this.elements,e=this._id+"-title";t.titlebar&&this._removeTitle(),t.titlebar=o("<div />",{"class":$+"-titlebar "+(this.options.style.widget?p("header"):"")}).append(t.title=o("<div />",{id:e,"class":$+"-title","aria-atomic":M})).insertBefore(t.content).delegate(".qtip-close","mousedown keydown mouseup keyup mouseout",function(t){o(this).toggleClass("ui-state-active ui-state-focus","down"===t.type.substr(-4))}).delegate(".qtip-close","mouseover mouseout",function(t){o(this).toggleClass("ui-state-hover","mouseover"===t.type)}),this.options.content.button&&this._createButton()},C._removeTitle=function(t){var e=this.elements;e.title&&(e.titlebar.remove(),e.titlebar=e.title=e.button=E,t!==S&&this.reposition())},C.reposition=function(i,s){if(!this.rendered||this.positioning||this.destroyed)return this;this.positioning=M;var n,r,a=this.cache,h=this.tooltip,l=this.options.position,c=l.target,p=l.my,d=l.at,u=l.viewport,f=l.container,g=l.adjust,m=g.method.split(" "),v=h.outerWidth(S),y=h.outerHeight(S),b=0,w=0,x=h.css("position"),_={left:0,top:0},q=h[0].offsetWidth>0,T=i&&"scroll"===i.type,C=o(t),j=f[0].ownerDocument,z=this.mouse;if(o.isArray(c)&&2===c.length)d={x:L,y:B},_={left:c[0],top:c[1]};else if("mouse"===c&&(i&&i.pageX||a.event.pageX))d={x:L,y:B},i=!z||!z.pageX||!g.mouse&&i&&i.pageX?(!i||"resize"!==i.type&&"scroll"!==i.type?i&&i.pageX&&"mousemove"===i.type?i:(!g.mouse||this.options.show.distance)&&a.origin&&a.origin.pageX?a.origin:i:a.event)||i||a.event||z||{}:z,"static"!==x&&(_=f.offset()),j.body.offsetWidth!==(t.innerWidth||j.documentElement.clientWidth)&&(r=o(j.body).offset()),_={left:i.pageX-_.left+(r&&r.left||0),top:i.pageY-_.top+(r&&r.top||0)},g.mouse&&T&&(_.left-=z.scrollX-C.scrollLeft(),_.top-=z.scrollY-C.scrollTop());else{if("event"===c&&i&&i.target&&"scroll"!==i.type&&"resize"!==i.type?a.target=o(i.target):"event"!==c&&(a.target=o(c.jquery?c:elements.target)),c=a.target,c=o(c).eq(0),0===c.length)return this;c[0]===e||c[0]===t?(b=BROWSER.iOS?t.innerWidth:c.width(),w=BROWSER.iOS?t.innerHeight:c.height(),c[0]===t&&(_={top:(u||c).scrollTop(),left:(u||c).scrollLeft()})):N.imagemap&&c.is("area")?n=N.imagemap(this,c,d,N.viewport?m:S):N.svg&&c[0].ownerSVGElement?n=N.svg(this,c,d,N.viewport?m:S):(b=c.outerWidth(S),w=c.outerHeight(S),_=c.offset()),n&&(b=n.width,w=n.height,r=n.offset,_=n.position),_=this.reposition.offset(c,_,f),(BROWSER.iOS>3.1&&4.1>BROWSER.iOS||BROWSER.iOS>=4.3&&4.33>BROWSER.iOS||!BROWSER.iOS&&"fixed"===x)&&(_.left-=C.scrollLeft(),_.top-=C.scrollTop()),(!n||n&&n.adjustable!==S)&&(_.left+=d.x===V?b:d.x===D?b/2:0,_.top+=d.y===P?w:d.y===D?w/2:0)}return _.left+=g.x+(p.x===V?-v:p.x===D?-v/2:0),_.top+=g.y+(p.y===P?-y:p.y===D?-y/2:0),N.viewport?(_.adjusted=N.viewport(this,_,l,b,w,v,y),r&&_.adjusted.left&&(_.left+=r.left),r&&_.adjusted.top&&(_.top+=r.top)):_.adjusted={left:0,top:0},this._trigger("move",[_,u.elem||u],i)?(delete _.adjusted,s===S||!q||isNaN(_.left)||isNaN(_.top)||"mouse"===c||!o.isFunction(l.effect)?h.css(_):o.isFunction(l.effect)&&(l.effect.call(h,this,o.extend({},_)),h.queue(function(t){o(this).css({opacity:"",height:""}),BROWSER.ie&&this.style.removeAttribute("filter"),t()})),this.positioning=S,this):this},C.reposition.offset=function(t,i,s){function n(t,e){i.left+=e*t.scrollLeft(),i.top+=e*t.scrollTop()}if(!s[0])return i;var r,a,h,l,c=o(t[0].ownerDocument),p=!!BROWSER.ie&&"CSS1Compat"!==e.compatMode,d=s[0];do"static"!==(a=o.css(d,"position"))&&("fixed"===a?(h=d.getBoundingClientRect(),n(c,-1)):(h=o(d).position(),h.left+=parseFloat(o.css(d,"borderLeftWidth"))||0,h.top+=parseFloat(o.css(d,"borderTopWidth"))||0),i.left-=h.left+(parseFloat(o.css(d,"marginLeft"))||0),i.top-=h.top+(parseFloat(o.css(d,"marginTop"))||0),r||"hidden"===(l=o.css(d,"overflow"))||"visible"===l||(r=o(d)));while(d=d.offsetParent);return r&&(r[0]!==c[0]||p)&&n(r,1),i};var ne=(j=C.reposition.Corner=function(t,e){t=(""+t).replace(/([A-Z])/," $1").replace(/middle/gi,D).toLowerCase(),this.x=(t.match(/left|right/i)||t.match(/center/)||["inherit"])[0].toLowerCase(),this.y=(t.match(/top|bottom|center/i)||["inherit"])[0].toLowerCase(),this.forceY=!!e;var i=t.charAt(0);this.precedance="t"===i||"b"===i?R:O}).prototype;ne.invert=function(t,e){this[t]=this[t]===L?V:this[t]===V?L:e||this[t]},ne.string=function(){var t=this.x,e=this.y;return t===e?t:this.precedance===R||this.forceY&&"center"!==e?e+" "+t:t+" "+e},ne.abbrev=function(){var t=this.string().split(" ");return t[0].charAt(0)+(t[1]&&t[1].charAt(0)||"")},ne.clone=function(){return new j(this.string(),this.forceY)},C.toggle=function(t,i){var s=this.cache,n=this.options,r=this.tooltip;if(i){if(/over|enter/.test(i.type)&&/out|leave/.test(s.event.type)&&n.show.target.add(i.target).length===n.show.target.length&&r.has(i.relatedTarget).length)return this;s.event=o.extend({},i)}if(this.waiting&&!t&&(this.hiddenDuringWait=M),!this.rendered)return t?this.render(1):this;if(this.destroyed||this.disabled)return this;var a,h,l=t?"show":"hide",c=this.options[l],p=(this.options[t?"hide":"show"],this.options.position),d=this.options.content,u=this.tooltip.css("width"),f=this.tooltip[0].offsetWidth>0,g=t||1===c.target.length,m=!i||2>c.target.length||s.target[0]===i.target;return(typeof t).search("boolean|number")&&(t=!f),a=!r.is(":animated")&&f===t&&m,h=a?E:!!this._trigger(l,[90]),h!==S&&t&&this.focus(i),!h||a?this:(o.attr(r[0],"aria-hidden",!t),t?(s.origin=o.extend({},this.mouse),o.isFunction(d.text)&&this._updateContent(d.text,S),o.isFunction(d.title)&&this._updateTitle(d.title,S),!W&&"mouse"===p.target&&p.adjust.mouse&&(o(e).bind("mousemove."+$,this._storeMouse),W=M),u||r.css("width",r.outerWidth(S)),this.reposition(i,arguments[2]),u||r.css("width",""),c.solo&&("string"==typeof c.solo?o(c.solo):o(G,c.solo)).not(r).not(c.target).qtip("hide",o.Event("tooltipsolo"))):(clearTimeout(this.timers.show),delete s.origin,W&&!o(G+'[tracking="true"]:visible',c.solo).not(r).length&&(o(e).unbind("mousemove."+$),W=S),this.blur(i)),after=o.proxy(function(){t?(BROWSER.ie&&r[0].style.removeAttribute("filter"),r.css("overflow",""),"string"==typeof c.autofocus&&o(this.options.show.autofocus,r).focus(),this.options.show.target.trigger("qtip-"+this.id+"-inactive")):r.css({display:"",visibility:"",opacity:"",left:"",top:""}),this._trigger(t?"visible":"hidden")},this),c.effect===S||g===S?(r[l](),after()):o.isFunction(c.effect)?(r.stop(1,1),c.effect.call(r,this),r.queue("fx",function(t){after(),t()})):r.fadeTo(90,t?1:0,after),t&&c.target.trigger("qtip-"+this.id+"-inactive"),this)},C.show=function(t){return this.toggle(M,t)},C.hide=function(t){return this.toggle(S,t)},C.focus=function(t){if(!this.rendered||this.destroyed)return this;var e=o(G),i=this.tooltip,s=parseInt(i[0].style.zIndex,10),n=T.zindex+e.length;return i.hasClass(K)||this._trigger("focus",[n],t)&&(s!==n&&(e.each(function(){this.style.zIndex>s&&(this.style.zIndex=this.style.zIndex-1)}),e.filter("."+K).qtip("blur",t)),i.addClass(K)[0].style.zIndex=n),this},C.blur=function(t){return!this.rendered||this.destroyed?this:(this.tooltip.removeClass(K),this._trigger("blur",[this.tooltip.css("zIndex")],t),this)},C.disable=function(t){return this.destroyed?this:("boolean"!=typeof t&&(t=!(this.tooltip.hasClass(te)||this.disabled)),this.rendered&&this.tooltip.toggleClass(te,t).attr("aria-disabled",t),this.disabled=!!t,this)},C.enable=function(){return this.disable(S)},C._createButton=function(){var t=this,e=this.elements,i=e.tooltip,s=this.options.content.button,n="string"==typeof s,r=n?s:"Close tooltip";e.button&&e.button.remove(),e.button=s.jquery?s:o("<a />",{"class":"qtip-close "+(this.options.style.widget?"":$+"-icon"),title:r,"aria-label":r}).prepend(o("<span />",{"class":"ui-icon ui-icon-close",html:"&times;"})),e.button.appendTo(e.titlebar||i).attr("role","button").click(function(e){return i.hasClass(te)||t.hide(e),S})},C._updateButton=function(t){if(!this.rendered)return S;var e=this.elements.button;t?this._createButton():e.remove()},C._setWidget=function(){var t=this.options.style.widget,e=this.elements,i=e.tooltip,s=i.hasClass(te);i.removeClass(te),te=t?"ui-state-disabled":"qtip-disabled",i.toggleClass(te,s),i.toggleClass("ui-helper-reset "+p(),t).toggleClass(J,this.options.style.def&&!t),e.content&&e.content.toggleClass(p("content"),t),e.titlebar&&e.titlebar.toggleClass(p("header"),t),e.button&&e.button.toggleClass($+"-icon",!t)},C._storeMouse=function(i){this.mouse={pageX:i.pageX,pageY:i.pageY,type:"mousemove",scrollX:t.pageXOffset||e.body.scrollLeft||e.documentElement.scrollLeft,scrollY:t.pageYOffset||e.body.scrollTop||e.documentElement.scrollTop}},C._bind=function(t,e,i,s,n){var r="."+this._id+(s?"-"+s:"");e.length&&o(t).bind((e.split?e:e.join(r+" "))+r,o.proxy(i,n||this))},C._unbind=function(t,e){o(t).unbind("."+this._id+(e?"-"+e:""))};var re="."+$;o(function(){m(G,["mouseenter","mouseleave"],function(t){var e="mouseenter"===t.type,i=o(t.currentTarget),s=o(t.relatedTarget||t.target),n=this.options;e?(this.focus(t),i.hasClass(Q)&&!i.hasClass(te)&&clearTimeout(this.timers.hide)):"mouse"===n.position.target&&n.hide.event&&n.show.target&&!s.closest(n.show.target[0]).length&&this.hide(t),i.toggleClass(Z,e)}),m("["+Y+"]",U,f)}),C._trigger=function(t,e,i){var s=o.Event("tooltip"+t);return s.originalEvent=i&&o.extend({},i)||this.cache.event||E,this.triggering=M,this.tooltip.trigger(s,[this].concat(e||[])),this.triggering=S,!s.isDefaultPrevented()},C._assignEvents=function(){var i=this.options,n=i.position,r=this.tooltip,a=i.show.target,h=i.hide.target,l=n.container,c=n.viewport,p=o(e),m=(o(e.body),o(t)),v=i.show.event?o.trim(""+i.show.event).split(" "):[],y=i.hide.event?o.trim(""+i.hide.event).split(" "):[],b=[];/mouse(out|leave)/i.test(i.hide.event)&&"window"===i.hide.leave&&this._bind(p,["mouseout","blur"],function(t){/select|option/.test(t.target.nodeName)||t.relatedTarget||this.hide(t)}),i.hide.fixed?h=h.add(r.addClass(Q)):/mouse(over|enter)/i.test(i.show.event)&&this._bind(h,"mouseleave",function(){clearTimeout(this.timers.show)}),(""+i.hide.event).indexOf("unfocus")>-1&&this._bind(l.closest("html"),["mousedown","touchstart"],function(t){var e=o(t.target),i=this.rendered&&!this.tooltip.hasClass(te)&&this.tooltip[0].offsetWidth>0,s=e.parents(G).filter(this.tooltip[0]).length>0;e[0]===this.target[0]||e[0]===this.tooltip[0]||s||this.target.has(e[0]).length||!i||this.hide(t)}),"number"==typeof i.hide.inactive&&(this._bind(a,"qtip-"+this.id+"-inactive",f),this._bind(h.add(r),T.inactiveEvents,f,"-inactive")),y=o.map(y,function(t){var e=o.inArray(t,v);return e>-1&&h.add(a).length===h.length?(b.push(v.splice(e,1)[0]),s):t}),this._bind(a,v,d),this._bind(h,y,u),this._bind(a,b,function(t){(this.tooltip[0].offsetWidth>0?u:d).call(this,t)}),this._bind(a.add(r),"mousemove",function(t){if("number"==typeof i.hide.distance){var e=this.cache.origin||{},s=this.options.hide.distance,o=Math.abs;(o(t.pageX-e.pageX)>=s||o(t.pageY-e.pageY)>=s)&&this.hide(t)}this._storeMouse(t)}),"mouse"===n.target&&n.adjust.mouse&&(i.hide.event&&this._bind(a,["mouseenter","mouseleave"],function(t){this.cache.onTarget="mouseenter"===t.type}),this._bind(p,"mousemove",function(t){this.rendered&&this.cache.onTarget&&!this.tooltip.hasClass(te)&&this.tooltip[0].offsetWidth>0&&this.reposition(t)})),(n.adjust.resize||c.length)&&this._bind(o.event.special.resize?c:m,"resize",g),n.adjust.scroll&&this._bind(m.add(n.container),"scroll",g)},C._unassignEvents=function(){var i=[this.options.show.target[0],this.options.hide.target[0],this.rendered&&this.tooltip[0],this.options.position.container[0],this.options.position.viewport[0],this.options.position.container.closest("html")[0],t,e];this.rendered?this._unbind(o([]).pushStack(o.grep(i,function(t){return"object"==typeof t}))):o(i[0]).unbind("."+this._id+"-create")},T=o.fn.qtip=function(t,e,i){var n=(""+t).toLowerCase(),r=E,a=o.makeArray(arguments).slice(1),l=a[a.length-1],c=this[0]?o.data(this[0],$):E;return!arguments.length&&c||"api"===n?c:"string"==typeof t?(this.each(function(){var t=o.data(this,$);if(!t)return M;if(l&&l.timeStamp&&(t.cache.event=l),!e||"option"!==n&&"options"!==n)t[n]&&t[n].apply(t,a);else{if(i===s&&!o.isPlainObject(e))return r=t.get(e),S;t.set(e,i)}}),r!==E?r:this):"object"!=typeof t&&arguments.length?s:(c=h(o.extend(M,{},t)),T.bind.call(this,c,l))},T.bind=function(t,e){return this.each(function(i){function n(t){function e(){c.render("object"==typeof t||r.show.ready),a.show.add(a.hide).unbind(l)}return c.disabled?S:(c.cache.event=o.extend({},t),c.cache.target=t?o(t.target):[s],r.show.delay>0?(clearTimeout(c.timers.show),c.timers.show=setTimeout(e,r.show.delay),h.show!==h.hide&&a.hide.bind(h.hide,function(){clearTimeout(c.timers.show)})):e(),s)}var r,a,h,l,c,p;return p=o.isArray(t.id)?t.id[i]:t.id,p=!p||p===S||1>p.length||T.api[p]?T.nextid++:p,l=".qtip-"+p+"-create",c=v(o(this),p,t),c===S?M:(T.api[p]=c,r=c.options,o.each(N,function(){"initialize"===this.initialize&&this(c)}),a={show:r.show.target,hide:r.hide.target},h={show:o.trim(""+r.show.event).replace(/ /g,l+" ")+l,hide:o.trim(""+r.hide.event).replace(/ /g,l+" ")+l},/mouse(over|enter)/i.test(h.show)&&!/mouse(out|leave)/i.test(h.hide)&&(h.hide+=" mouseleave"+l),a.show.bind("mousemove"+l,function(t){c._storeMouse(t),c.cache.onTarget=M}),a.show.bind(h.show,n),(r.show.ready||r.prerender)&&n(e),s)})},T.api={},o.each({attr:function(t,e){if(this.length){var i=this[0],s="title",n=o.data(i,"qtip");if(t===s&&n&&"object"==typeof n&&n.options.suppress)return 2>arguments.length?o.attr(i,ie):(n&&n.options.content.attr===s&&n.cache.attr&&n.set("content.text",e),this.attr(ie,e))}return o.fn["attr"+ee].apply(this,arguments)},clone:function(t){var e=(o([]),o.fn["clone"+ee].apply(this,arguments));return t||e.filter("["+ie+"]").attr("title",function(){return o.attr(this,ie)}).removeAttr(ie),e}},function(t,e){if(!e||o.fn[t+ee])return M;var i=o.fn[t+ee]=o.fn[t];o.fn[t]=function(){return e.apply(this,arguments)||i.apply(this,arguments)}}),o.ui||(o["cleanData"+ee]=o.cleanData,o.cleanData=function(t){for(var e,i=0;(e=o(t[i])).length;i++)if(e.attr(X))try{e.triggerHandler("removeqtip")}catch(s){}o["cleanData"+ee].apply(this,arguments)}),T.version="2.1.1",T.nextid=0,T.inactiveEvents=U,T.zindex=15e3,T.defaults={prerender:S,id:S,overwrite:M,suppress:M,content:{text:M,attr:"title",title:S,button:S},position:{my:"top left",at:"bottom right",target:S,container:S,viewport:S,adjust:{x:0,y:0,mouse:M,scroll:M,resize:M,method:"flipinvert flipinvert"},effect:function(t,e){o(this).animate(e,{duration:200,queue:S})}},show:{target:S,event:"mouseenter",effect:M,delay:90,solo:S,ready:S,autofocus:S},hide:{target:S,event:"mouseleave",effect:M,delay:0,fixed:S,inactive:S,leave:"window",distance:S},style:{classes:"",widget:S,width:S,height:S,def:M},events:{render:E,move:E,show:E,hide:E,toggle:E,visible:E,hidden:E,focus:E,blur:E}};var ae,he="margin",le="border",ce="color",pe="background-color",de="transparent",ue=" !important",fe=!!e.createElement("canvas").getContext,ge=/rgba?\(0, 0, 0(, 0)?\)|transparent|#123456/i,me={},ve=["Webkit","O","Moz","ms"];fe||(createVML=function(t,e,i){return"<qtipvml:"+t+' xmlns="urn:schemas-microsoft.com:vml" class="qtip-vml" '+(e||"")+' style="behavior: url(#default#VML); '+(i||"")+'" />'}),o.extend(x.prototype,{init:function(t){var e,i;i=this.element=t.elements.tip=o("<div />",{"class":$+"-tip"}).prependTo(t.tooltip),fe?(e=o("<canvas />").appendTo(this.element)[0].getContext("2d"),e.lineJoin="miter",e.miterLimit=100,e.save()):(e=createVML("shape",'coordorigin="0,0"',"position:absolute;"),this.element.html(e+e),t._bind(o("*",i).add(i),["click","mousedown"],function(t){t.stopPropagation()},this._ns)),t._bind(t.tooltip,"tooltipmove",this.reposition,this._ns,this),this.create()},_swapDimensions:function(){this.size[0]=this.options.height,this.size[1]=this.options.width},_resetDimensions:function(){this.size[0]=this.options.width,this.size[1]=this.options.height},_useTitle:function(t){var e=this.qtip.elements.titlebar;return e&&(t.y===B||t.y===D&&this.element.position().top+this.size[1]/2+this.options.offset<e.outerHeight(M))},_parseCorner:function(t){var e=this.qtip.options.position.my;return t===S||e===S?t=S:t===M?t=new j(e.string()):t.string||(t=new j(t),t.fixed=M),t},_parseWidth:function(t,e,i){var s=this.qtip.elements,o=le+y(e)+"Width";return(i?w(i,o):w(s.content,o)||w(this._useTitle(t)&&s.titlebar||s.content,o)||w(tooltip,o))||0},_parseRadius:function(t){var e=this.qtip.elements,i=le+y(t.y)+y(t.x)+"Radius";return 9>BROWSER.ie?0:w(this._useTitle(t)&&e.titlebar||e.content,i)||w(e.tooltip,i)||0},_invalidColour:function(t,e,i){var s=t.css(e);return!s||i&&s===t.css(i)||ge.test(s)?S:s},_parseColours:function(t){var e=this.qtip.elements,i=this.element.css("cssText",""),s=le+y(t[t.precedance])+y(ce),n=this._useTitle(t)&&e.titlebar||e.content,r=this._invalidColour,a=[];return a[0]=r(i,pe)||r(n,pe)||r(e.content,pe)||r(tooltip,pe)||i.css(pe),a[1]=r(i,s,ce)||r(n,s,ce)||r(e.content,s,ce)||r(tooltip,s,ce)||tooltip.css(s),o("*",i).add(i).css("cssText",pe+":"+de+ue+";"+le+":0"+ue+";"),a},_calculateSize:function(t){var e,i,s,o=t.precedance===R,n=this.options[o?"height":"width"],r=this.options[o?"width":"height"],a="c"===t.abbrev(),h=n*(a?.5:1),l=Math.pow,c=Math.round,p=Math.sqrt(l(h,2)+l(r,2)),d=[this.border/h*p,this.border/r*p];return d[2]=Math.sqrt(l(d[0],2)-l(this.border,2)),d[3]=Math.sqrt(l(d[1],2)-l(this.border,2)),e=p+d[2]+d[3]+(a?0:d[0]),i=e/p,s=[c(i*n),c(i*r)],o?s:s.reverse()},_calculateTip:function(t){var e=this.size[0],i=this.size[1],s=Math.ceil(e/2),o=Math.ceil(i/2),n={br:[0,0,e,i,e,0],bl:[0,0,e,0,0,i],tr:[0,i,e,0,e,i],tl:[0,0,0,i,e,i],tc:[0,i,s,0,e,i],bc:[0,0,e,0,s,i],rc:[0,0,e,o,0,i],lc:[e,0,e,i,0,o]};return n.lt=n.br,n.rt=n.bl,n.lb=n.tr,n.rb=n.tl,n[t.abbrev()]},create:function(){var t=this.corner=(fe||BROWSER.ie)&&this._parseCorner(this.options.corner);return(this.enabled=!!this.corner&&"c"!==this.corner.abbrev())&&(this.qtip.cache.corner=t.clone(),this.update()),this.element.toggle(this.enabled),this.corner},update:function(t,e){if(!this.enabled)return this;var i,s,n,r,a,h,l,c=(this.qtip.elements,this.element),p=c.children(),d=this.options,u=this.size,f=d.mimic,g=Math.round;t||(t=this.qtip.cache.corner||this.corner),f===S?f=t:(f=new j(f),f.precedance=t.precedance,"inherit"===f.x?f.x=t.x:"inherit"===f.y?f.y=t.y:f.x===f.y&&(f[t.precedance]=t[t.precedance])),s=f.precedance,t.precedance===O?this._swapDimensions():this._resetDimensions(),i=this.color=this._parseColours(t),i[1]!==de?(l=this.border=this._parseWidth(t,t[t.precedance]),d.border&&1>l&&(i[0]=i[1]),this.border=l=d.border!==M?d.border:l):this.border=l=0,r=this._calculateTip(f),h=this.size=this._calculateSize(t),c.css({width:h[0],height:h[1],lineHeight:h[1]+"px"}),a=t.precedance===R?[g(f.x===L?l:f.x===V?h[0]-u[0]-l:(h[0]-u[0])/2),g(f.y===B?h[1]-u[1]:0)]:[g(f.x===L?h[0]-u[0]:0),g(f.y===B?l:f.y===P?h[1]-u[1]-l:(h[1]-u[1])/2)],fe?(p.attr(I,h[0]).attr(k,h[1]),n=p[0].getContext("2d"),n.restore(),n.save(),n.clearRect(0,0,3e3,3e3),n.fillStyle=i[0],n.strokeStyle=i[1],n.lineWidth=2*l,n.translate(a[0],a[1]),n.beginPath(),n.moveTo(r[0],r[1]),n.lineTo(r[2],r[3]),n.lineTo(r[4],r[5]),n.closePath(),l&&("border-box"===tooltip.css("background-clip")&&(n.strokeStyle=i[0],n.stroke()),n.strokeStyle=i[1],n.stroke()),n.fill()):(r="m"+r[0]+","+r[1]+" l"+r[2]+","+r[3]+" "+r[4]+","+r[5]+" xe",a[2]=l&&/^(r|b)/i.test(t.string())?8===BROWSER.ie?2:1:0,p.css({coordsize:u[0]+l+" "+(u[1]+l),antialias:""+(f.string().indexOf(D)>-1),left:a[0]-a[2]*Number(s===O),top:a[1]-a[2]*Number(s===R),width:u[0]+l,height:u[1]+l}).each(function(t){var e=o(this);e[e.prop?"prop":"attr"]({coordsize:u[0]+l+" "+(u[1]+l),path:r,fillcolor:i[0],filled:!!t,stroked:!t}).toggle(!(!l&&!t)),!t&&e.html(createVML("stroke",'weight="'+2*l+'px" color="'+i[1]+'" miterlimit="1000" joinstyle="miter"'))})),e!==S&&this.calculate(t)},calculate:function(t){if(!this.enabled)return S;var e,i,s,n=this,r=this.qtip.elements,a=this.element,h=this.options.offset,l=(this.qtip.tooltip.hasClass("ui-widget"),{});return t=t||this.corner,e=t.precedance,i=this._calculateSize(t),s=[t.x,t.y],e===O&&s.reverse(),o.each(s,function(s,o){var a,c,p;o===D?(a=e===R?L:B,l[a]="50%",l[he+"-"+a]=-Math.round(i[e===R?0:1]/2)+h):(a=n._parseWidth(t,o,r.tooltip),c=n._parseWidth(t,o,r.content),p=n._parseRadius(t),l[o]=Math.max(-n.border,s?c:h+(p>a?p:-a)))}),l[t[e]]-=i[e===O?0:1],a.css({margin:"",top:"",bottom:"",left:"",right:""}).css(l),l},reposition:function(t,e,i){if(this.enabled){var o,n,r=e.cache,a=this.corner.clone(),h=i.adjusted,l=e.options.position.adjust.method.split(" "),c=l[0],p=l[1]||l[0],d={left:S,top:S,x:0,y:0},u={};this.corner.fixed!==M&&(c===F&&a.precedance===O&&h.left&&a.y!==D?a.precedance=a.precedance===O?R:O:c!==F&&h.left&&(a.x=a.x===D?h.left>0?L:V:a.x===L?V:L),p===F&&a.precedance===R&&h.top&&a.x!==D?a.precedance=a.precedance===R?O:R:p!==F&&h.top&&(a.y=a.y===D?h.top>0?B:P:a.y===B?P:B),a.string()===r.corner.string()||r.cornerTop===h.top&&r.cornerLeft===h.left||this.update(a,S)),o=this.calculate(a,h),o.right!==s&&(o.left=-o.right),o.bottom!==s&&(o.top=-o.bottom),o.user=this.offset,(d.left=c===F&&!!h.left)&&(a.x===D?u[he+"-left"]=d.x=o[he+"-left"]-h.left:(n=o.right!==s?[h.left,-o.left]:[-h.left,o.left],(d.x=Math.max(n[0],n[1]))>n[0]&&(i.left-=h.left,d.left=S),u[o.right!==s?V:L]=d.x)),(d.top=p===F&&!!h.top)&&(a.y===D?u[he+"-top"]=d.y=o[he+"-top"]-h.top:(n=o.bottom!==s?[h.top,-o.top]:[-h.top,o.top],(d.y=Math.max(n[0],n[1]))>n[0]&&(i.top-=h.top,d.top=S),u[o.bottom!==s?P:B]=d.y)),this.element.css(u).toggle(!(d.x&&d.y||a.x===D&&d.y||a.y===D&&d.x)),i.left-=o.left.charAt?o.user:c!==F||d.top||!d.left&&!d.top?o.left:0,i.top-=o.top.charAt?o.user:p!==F||d.left||!d.left&&!d.top?o.top:0,r.cornerLeft=h.left,r.cornerTop=h.top,r.corner=a.clone()
}},destroy:function(){this.qtip._unbind(this.qtip.tooltip,this._ns),this.qtip.elements.tip&&this.qtip.elements.tip.find("*").remove().end().remove()}}),ae=N.tip=function(t){return new x(t,t.options.style.tip)},ae.initialize="render",ae.sanitize=function(t){t.style&&"tip"in t.style&&(opts=t.style.tip,"object"!=typeof opts&&(opts=t.style.tip={corner:opts}),/string|boolean/i.test(typeof opts.corner)||(opts.corner=M))},z.tip={"^position.my|style.tip.(corner|mimic|border)$":function(){this.create(),this.qtip.reposition()},"^style.tip.(height|width)$":function(t){this.size=size=[t.width,t.height],this.update(),this.qtip.reposition()},"^content.title|style.(classes|widget)$":function(){this.update()}},o.extend(M,T.defaults,{style:{tip:{corner:M,mimic:S,width:6,height:6,border:M,offset:0}}});var ye,be,we="qtip-modal",xe="."+we;be=function(){function i(t){if(o.expr[":"].focusable)return o.expr[":"].focusable;var e,i,s,n=!isNaN(o.attr(t,"tabindex")),r=t.nodeName&&t.nodeName.toLowerCase();return"area"===r?(e=t.parentNode,i=e.name,t.href&&i&&"map"===e.nodeName.toLowerCase()?(s=o("img[usemap=#"+i+"]")[0],!!s&&s.is(":visible")):!1):/input|select|textarea|button|object/.test(r)?!t.disabled:"a"===r?t.href||n:n}function s(t){1>p.length&&t.length?t.not("body").blur():p.first().focus()}function n(t){if(l.is(":visible")){var e,i=o(t.target),n=r.tooltip,h=i.closest(G);e=1>h.length?S:parseInt(h[0].style.zIndex,10)>parseInt(n[0].style.zIndex,10),e||i.closest(G)[0]===n[0]||s(i),a=t.target===p[p.length-1]}}var r,a,h,l,c=this,p={};o.extend(c,{init:function(){function i(){var t=o(this);l.css({height:t.height(),width:t.width()})}return l=c.elem=o("<div />",{id:"qtip-overlay",html:"<div></div>",mousedown:function(){return S}}).hide(),o(t).bind("resize"+xe,i),i(),o(e.body).bind("focusin"+xe,n),o(e).bind("keydown"+xe,function(t){r&&r.options.show.modal.escape&&27===t.keyCode&&r.hide(t)}),l.bind("click"+xe,function(t){r&&r.options.show.modal.blur&&r.hide(t)}),c},update:function(t){r=t,p=t.options.show.modal.stealfocus!==S?t.tooltip.find("*").filter(function(){return i(this)}):[]},toggle:function(t,i,n){var a=(o(e.body),t.tooltip),p=t.options.show.modal,d=p.effect,u=i?"show":"hide",f=l.is(":visible"),g=o(xe).filter(":visible:not(:animated)").not(a);return c.update(t),i&&p.stealfocus!==S&&s(o(":focus")),l.toggleClass("blurs",p.blur),i&&l.css({left:0,top:0}).appendTo(e.body),l.is(":animated")&&f===i&&h!==S||!i&&g.length?c:(l.stop(M,S),o.isFunction(d)?d.call(l,i):d===S?l[u]():l.fadeTo(parseInt(n,10)||90,i?1:0,function(){i||l.hide()}),i||l.queue(function(t){l.css({left:"",top:""}),o(xe).length||l.detach(),t()}),h=i,r.destroyed&&(r=E),c)}}),c.init()},be=new be,o.extend(_.prototype,{init:function(t){var e=t.tooltip;return this.options.on?(t.elements.overlay=be.elem,e.addClass(we).css("z-index",N.modal.zindex+o(xe).length),t._bind(e,["tooltipshow","tooltiphide"],function(t,i,s){var n=t.originalEvent;if(t.target===e[0])if(n&&"tooltiphide"===t.type&&/mouse(leave|enter)/.test(n.type)&&o(n.relatedTarget).closest(overlay[0]).length)try{t.preventDefault()}catch(r){}else(!n||n&&!n.solo)&&this.toggle(t,"tooltipshow"===t.type,s)},this._ns,this),t._bind(e,"tooltipfocus",function(t,i){if(!t.isDefaultPrevented()&&t.target===e[0]){var s=o(xe),n=N.modal.zindex+s.length,r=parseInt(e[0].style.zIndex,10);be.elem[0].style.zIndex=n-1,s.each(function(){this.style.zIndex>r&&(this.style.zIndex-=1)}),s.filter("."+K).qtip("blur",t.originalEvent),e.addClass(K)[0].style.zIndex=n,be.update(i);try{t.preventDefault()}catch(a){}}},this._ns,this),t._bind(e,"tooltiphide",function(t){t.target===e[0]&&o(xe).filter(":visible").not(e).last().qtip("focus",t)},this._ns,this),s):this},toggle:function(t,e,i){return t&&t.isDefaultPrevented()?this:(be.toggle(this.qtip,!!e,i),s)},destroy:function(){this.qtip.tooltip.removeClass(we),this.qtip._unbind(this.qtip.tooltip,this._ns),be.toggle(this.qtip,S),delete this.qtip.elements.overlay}}),ye=N.modal=function(t){return new _(t,t.options.show.modal)},ye.sanitize=function(t){t.show&&("object"!=typeof t.show.modal?t.show.modal={on:!!t.show.modal}:t.show.modal.on===s&&(t.show.modal.on=M))},ye.zindex=T.zindex-200,ye.initialize="render",z.modal={"^show.modal.(on|blur)$":function(){this.destroy(),this.init(),this.qtip.elems.overlay.toggle(this.qtip.tooltip[0].offsetWidth>0)}},o.extend(M,T.defaults,{show:{modal:{on:S,effect:M,blur:M,stealfocus:M,escape:M}}}),N.viewport=function(i,s,o,n,r,a,h){function l(t,e,i,o,n,r,a,h,l){var c=s[n],d=g[t],u=m[t],f=i===F,v=-_.offset[n]+x.offset[n]+x["scroll"+n],y=d===n?l:d===r?-l:-l/2,b=u===n?h:u===r?-h:-h/2,w=T&&T.size?T.size[a]||0:0,q=T&&T.corner&&T.corner.precedance===t&&!f?w:0,C=v-c+q,j=c+l-x[a]-v+q,z=y-(g.precedance===t||d===g[e]?b:0)-(u===D?h/2:0);return f?(q=T&&T.corner&&T.corner.precedance===e?w:0,z=(d===n?1:-1)*y-q,s[n]+=C>0?C:j>0?-j:0,s[n]=Math.max(-_.offset[n]+x.offset[n]+(q&&T.corner[t]===D?T.offset:0),c-z,Math.min(Math.max(-_.offset[n]+x.offset[n]+x[a],c+z),s[n]))):(o*=i===A?2:0,C>0&&(d!==n||j>0)?(s[n]-=z+o,p.invert(t,n)):j>0&&(d!==r||C>0)&&(s[n]-=(d===D?-z:z)+o,p.invert(t,r)),v>s[n]&&-s[n]>j&&(s[n]=c,p=g.clone())),s[n]-c}var c,p,d,u=o.target,f=i.elements.tooltip,g=o.my,m=o.at,v=o.adjust,y=v.method.split(" "),b=y[0],w=y[1]||y[0],x=o.viewport,_=o.container,q=i.cache,T=i.plugins.tip,C={left:0,top:0};return x.jquery&&u[0]!==t&&u[0]!==e.body&&"none"!==v.method?(c="fixed"===f.css("position"),x={elem:x,width:x[0]===t?x.width():x.outerWidth(S),height:x[0]===t?x.height():x.outerHeight(S),scrollleft:c?0:x.scrollLeft(),scrolltop:c?0:x.scrollTop(),offset:x.offset()||{left:0,top:0}},_={elem:_,scrollLeft:_.scrollLeft(),scrollTop:_.scrollTop(),offset:_.offset()||{left:0,top:0}},("shift"!==b||"shift"!==w)&&(p=g.clone()),C={left:"none"!==b?l(O,R,b,v.x,L,V,I,n,a):0,top:"none"!==w?l(R,O,w,v.y,B,P,k,r,h):0},p&&q.lastClass!==(d=$+"-pos-"+p.abbrev())&&f.removeClass(i.cache.lastClass).addClass(i.cache.lastClass=d),C):C},N.polys={polygon:function(t,e){var i,s,o,n={width:0,height:0,position:{top:1e10,right:0,bottom:0,left:1e10},adjustable:S},r=0,a=[],h=1,l=1,c=0,p=0;for(r=t.length;r--;)i=[parseInt(t[--r],10),parseInt(t[r+1],10)],i[0]>n.position.right&&(n.position.right=i[0]),i[0]<n.position.left&&(n.position.left=i[0]),i[1]>n.position.bottom&&(n.position.bottom=i[1]),i[1]<n.position.top&&(n.position.top=i[1]),a.push(i);if(s=n.width=Math.abs(n.position.right-n.position.left),o=n.height=Math.abs(n.position.bottom-n.position.top),"c"===e.abbrev())n.position={left:n.position.left+n.width/2,top:n.position.top+n.height/2};else{for(;s>0&&o>0&&h>0&&l>0;)for(s=Math.floor(s/2),o=Math.floor(o/2),e.x===L?h=s:e.x===V?h=n.width-s:h+=Math.floor(s/2),e.y===B?l=o:e.y===P?l=n.height-o:l+=Math.floor(o/2),r=a.length;r--&&!(2>a.length);)c=a[r][0]-n.position.left,p=a[r][1]-n.position.top,(e.x===L&&c>=h||e.x===V&&h>=c||e.x===D&&(h>c||c>n.width-h)||e.y===B&&p>=l||e.y===P&&l>=p||e.y===D&&(l>p||p>n.height-l))&&a.splice(r,1);n.position={left:a[0][0],top:a[0][1]}}return n},rect:function(t,e,i,s){return{width:Math.abs(i-t),height:Math.abs(s-e),position:{left:Math.min(t,i),top:Math.min(e,s)}}},_angles:{tc:1.5,tr:7/4,tl:5/4,bc:.5,br:.25,bl:.75,rc:2,lc:1,c:0},ellipse:function(t,e,i,s,o){var n=N.polys._angles[o.abbrev()],r=i*Math.cos(n*Math.PI),a=s*Math.sin(n*Math.PI);return{width:2*i-Math.abs(r),height:2*s-Math.abs(a),position:{left:t+r,top:e+a},adjustable:S}},circle:function(t,e,i,s){return N.polys.ellipse(t,e,i,i,s)}},N.svg=function(t,s,n){for(var r,a,h,l=o(e),c=s[0],p={};!c.getBBox;)c=c.parentNode;if(!c.getBBox||!c.parentNode)return S;switch(c.nodeName){case"rect":a=N.svg.toPixel(c,c.x.baseVal.value,c.y.baseVal.value),h=N.svg.toPixel(c,c.x.baseVal.value+c.width.baseVal.value,c.y.baseVal.value+c.height.baseVal.value),p=N.polys.rect(a[0],a[1],h[0],h[1],n);break;case"ellipse":case"circle":a=N.svg.toPixel(c,c.cx.baseVal.value,c.cy.baseVal.value),p=N.polys.ellipse(a[0],a[1],(c.rx||c.r).baseVal.value,(c.ry||c.r).baseVal.value,n);break;case"line":case"polygon":case"polyline":for(points=c.points||[{x:c.x1.baseVal.value,y:c.y1.baseVal.value},{x:c.x2.baseVal.value,y:c.y2.baseVal.value}],p=[],i=-1,len=points.numberOfItems||points.length;len>++i;)next=points.getItem?points.getItem(i):points[i],p.push.apply(p,N.svg.toPixel(c,next.x,next.y));p=N.polys.polygon(p,n);break;default:if(r=c.getBBox(),mtx=c.getScreenCTM(),root=c.farthestViewportElement||c,!root.createSVGPoint)return S;point=root.createSVGPoint(),point.x=r.x,point.y=r.y,tPoint=point.matrixTransform(mtx),p.position={left:tPoint.x,top:tPoint.y},point.x+=r.width,point.y+=r.height,tPoint=point.matrixTransform(mtx),p.width=tPoint.x-p.position.left,p.height=tPoint.y-p.position.top}return p.position.left+=l.scrollLeft(),p.position.top+=l.scrollTop(),p},N.svg.toPixel=function(t,e,i){var s,o,n=t.getScreenCTM(),r=t.farthestViewportElement||t;return r.createSVGPoint?(o=r.createSVGPoint(),o.x=e,o.y=i,s=o.matrixTransform(n),[s.x,s.y]):S},N.imagemap=function(t,e,i){e.jquery||(e=o(e));var s,n,r,a=e.attr("shape").toLowerCase().replace("poly","polygon"),h=o('img[usemap="#'+e.parent("map").attr("name")+'"]'),l=e.attr("coords"),c=l.split(",");if(!h.length)return S;if("polygon"===a)result=N.polys.polygon(c,i);else{if(!N.polys[a])return S;for(r=-1,len=c.length,n=[];len>++r;)n.push(parseInt(c[r],10));result=N.polys[a].apply(this,n.concat(i))}return s=h.offset(),s.left+=Math.ceil((h.outerWidth(S)-h.width())/2),s.top+=Math.ceil((h.outerHeight(S)-h.height())/2),result.position.left+=s.left,result.position.top+=s.top,result};var _e,qe='<iframe class="qtip-bgiframe" frameborder="0" tabindex="-1" src="javascript:\'\';"  style="display:block; position:absolute; z-index:-1; filter:alpha(opacity=0); -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";"></iframe>';o.extend(q.prototype,{_scroll:function(){var e=this.qtip.elements.overlay;e&&(e[0].style.top=o(t).scrollTop()+"px")},init:function(i){var s=i.tooltip;1>o("select, object").length&&(this.bgiframe=i.elements.bgiframe=o(qe).appendTo(s),i._bind(s,"tooltipmove",this.adjustBGIFrame,this._ns,this)),this.redrawContainer=o("<div/>",{id:$+"-rcontainer"}).appendTo(e.body),i.elements.overlay&&i.elements.overlay.addClass("qtipmodal-ie6fix")&&(i._bind(t,["scroll","resize"],this._scroll,this._ns,this),i._bind(s,["tooltipshow"],this._scroll,this._ns,this)),this.redraw()},adjustBGIFrame:function(){var t,e,i=this.qtip.tooltip,s={height:i.outerHeight(S),width:i.outerWidth(S)},o=this.qtip.plugins.tip,n=this.qtip.elements.tip;e=parseInt(i.css("borderLeftWidth"),10)||0,e={left:-e,top:-e},o&&n&&(t="x"===o.corner.precedance?[I,L]:[k,B],e[t[1]]-=n[t[0]]()),this.bgiframe.css(e).css(s)},redraw:function(){if(1>this.qtip.rendered||this.drawing)return self;var t,e,i,s,o=this.qtip.tooltip,n=this.qtip.options.style,r=this.qtip.options.position.container;return this.qtip.drawing=1,n.height&&o.css(k,n.height),n.width?o.css(I,n.width):(o.css(I,"").appendTo(this.redrawContainer),e=o.width(),1>e%2&&(e+=1),i=o.css("maxWidth")||"",s=o.css("minWidth")||"",t=(i+s).indexOf("%")>-1?r.width()/100:0,i=(i.indexOf("%")>-1?t:1)*parseInt(i,10)||e,s=(s.indexOf("%")>-1?t:1)*parseInt(s,10)||0,e=i+s?Math.min(Math.max(e,s),i):e,o.css(I,Math.round(e)).appendTo(r)),this.drawing=0,self},destroy:function(){this.bgiframe&&this.bgiframe.remove(),this.qtip._unbind([t,this.qtip.tooltip],this._ns)}}),_e=N.ie6=function(t){return 6===BROWSER.ie?new q(t):S},_e.initialize="render",z.ie6={"^content|style$":function(){this.redraw()}}})})(window,document);

function initiateTip() {
	jQuery('.img_tip, .text_tip').each(function() {
		//if( jQuery(window).width() > 640 ) {
			//jQuery(this).qtip('destroy', true);
			jQuery(this).qtip({
				content: jQuery(this).attr('data-tip'),
				position: {
					my: 'top center',
					at: 'bottom center',
					viewport: jQuery(window)
				},
				show: {
					//event: 'mouseover mouseenter',
					solo: true,
				},
				hide: {
					inactive: 60000,
					fixed: true
				},
				style: {
					classes: 'qtip-dark qtip-shadow qtip-rounded qtip-wcfm-css qtip-wcfm-core-css'
				}
			});
		//} else {
			//jQuery(this).attr( 'title', jQuery(this).data('tip') );
		//}
	});
}

function GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
}

function wcfmMessageHide() {
	clearTimeout( $wcfm_message_close_timer );
	$wcfm_message_close_timer = setTimeout( function() {
		jQuery('.wcfm-message').slideUp( "slow", function() {
			jQuery('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error');
			$wcfm_is_valid_form = true;
		});
	}, 10000 );
}

function getWCFMEditorContent( editor_id ) {
	var editor_content = '';
	if( jQuery('#'+editor_id).length > 0 ) {
		if( jQuery('#'+editor_id).hasClass('rich_editor') || jQuery('#'+editor_id).hasClass('wcfm_wpeditor') ) {
			if( typeof tinymce != 'undefined' ) {
				if( tinymce.get(editor_id) != null ) editor_content = tinymce.get(editor_id).getContent({format: 'raw'});
				else editor_content = jQuery('#'+editor_id).val();
			} else {
				editor_content = jQuery('#'+editor_id).val();
			}
		} else {
			editor_content = jQuery('#'+editor_id).val();
		}
	}
	return editor_content;
}

jQuery(document).ready(function($) {
	initiateTip();
	
	// Select wrapper fix
	function unwrapSelect() {
		$('.wcfm_popup_wrapper').find('select').each(function() {
			if ( $(this).parent().is( "span" ) ) {
			  $(this).unwrap( "span" );
			}
			if ( $(this).parent().hasClass( "select-option" ) || $(this).parent().hasClass( "buddyboss-select-inner" ) || $(this).parent().hasClass( "buddyboss-select" ) ) {
				$(this).parent().find('.ti-angle-down').remove();
				$(this).parent().find('span').remove();
			  $(this).unwrap( "div" );
			}
		});
		setTimeout( function() {  unwrapSelect(); }, 500 );
	}
	
	setTimeout( function() { 
		$('.wcfm_popup_wrapper').find('select').each(function() {
			if ( $(this).parent().is( "span" ) ) {
			 $(this).css( 'padding', '5px' ).css( 'min-width', '15px' ).css( 'min-height', '35px' ).css( 'padding-top', '5px' ).css( 'padding-right', '5px' ); //.change();
			}
		});
		unwrapSelect();
	}, 500 );
	
	// Delete Product
	$('.wcfm_delete_product').each(function() {
		$(this).click(function(event) {
			event.preventDefault();
			var rconfirm = confirm(wcfm_core_dashboard_messages.product_delete_confirm);
			if(rconfirm) deleteWCFMProduct($(this));
			return false;
		});
	});
	
	function deleteWCFMProduct(item) {
		jQuery('.products').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action : 'delete_wcfm_product',
			proid : item.data('proid')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				window.location = wcfm_params.shop_url;
			}
		});
	}
	
	// WCFM Form Global Validation
	$( document.body ).on( 'wcfm_form_validate', function( event, validating_form ) {
		$proccessed_required_fileds = [];
		if( validating_form ) {
			$form = $(validating_form);
			$form.find('[data-required="1"]').each(function() {
				$data_name = $(this).attr('name');
				if( $.inArray( $data_name, $proccessed_required_fileds ) === -1 ) {
					$proccessed_required_fileds.push($data_name);
					if( !$(this).parents('.wcfm-container').hasClass('wcfm_block_hide') && !$(this).parents('.wcfm-container').hasClass('wcfm_custom_hide') && !$(this).parents('.wcfm-container').hasClass('wcfm_wpml_hide') && !$(this).parent().parent().hasClass('wcfm_block_hide') && !$(this).parent().parent().hasClass('wcfm_custom_hide') && !$(this).parent().parent().hasClass('wcfm_wpml_hide') && !$(this).parent().hasClass('wcfm_block_hide') && !$(this).parent().hasClass('wcfm_custom_hide') && !$(this).parent().hasClass('wcfm_wpml_hide') && !$(this).hasClass('wcfm_ele_hide') && !$(this).hasClass('wcfm_custom_hide') && !$(this).hasClass('wcfm_wpml_hide') ) {
						if ( $(this).is( 'input[type="checkbox"]' ) || $(this).is( 'input[type="radio"]' ) ) {
							$('[name="'+$data_name+'"]').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
							if( !$('[name="'+$data_name+'"]').is( ":checked" ) ) {
								if( $wcfm_is_valid_form ) 
									$('#' + $form.attr('id') + ' .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>' + $(this).data('required_message') ).addClass('wcfm-error').slideDown();
								else
									$('#' + $form.attr('id') + ' .wcfm-message').append( '<br /><span class="wcicon-status-cancelled"></span>' + $(this).data('required_message') );
								
								$wcfm_is_valid_form = false;
								$('[name="'+$data_name+'"]').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							}
						} else {
							$(this).removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
							$data_val = $(this).val();
							if( !$data_val ) {
								if( $wcfm_is_valid_form ) 
									$('#' + $form.attr('id') + ' .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>' + $(this).data('required_message') ).addClass('wcfm-error').slideDown();
								else
									$('#' + $form.attr('id') + ' .wcfm-message').append( '<br /><span class="wcicon-status-cancelled"></span>' + $(this).data('required_message') );
								
								$wcfm_is_valid_form = false;
								$(this).removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
							}
						}
					}
				}
			});
		
			// WP-editor validation check
			$form.find('.wcfm_wpeditor_required').each(function() {
				$data_id = $(this).attr('id');
				$data_name = $(this).attr('name');
				$form = $(this).parents('form');
				if( $.inArray( $data_name, $proccessed_required_fileds ) === -1 ) {
					$proccessed_required_fileds.push($data_name);
					if( !$(this).parents('.wcfm-container').hasClass('wcfm_block_hide') && !$(this).parents('.wcfm-container').hasClass('wcfm_custom_hide') && !$(this).parent().parent().hasClass('wcfm_block_hide') && !$(this).parent().parent().hasClass('wcfm_custom_hide') && !$(this).parent().hasClass('wcfm_block_hide') && !$(this).parent().hasClass('wcfm_custom_hide') && !$(this).hasClass('wcfm_ele_hide') && !$(this).hasClass('wcfm_custom_hide') ) {
						$data_val = wcfmstripHtml( getWCFMEditorContent( $data_id ) );
						if( $data_val.length == 1 ) {
							if( $wcfm_is_valid_form ) 
								$('#' + $form.attr('id') + ' .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>' + wcfmcapitalizeFirstLetter($data_name) + ": " + wcfm_core_dashboard_messages.required_message ).addClass('wcfm-error').slideDown();
							else
								$('#' + $form.attr('id') + ' .wcfm-message').append( '<br /><span class="wcicon-status-cancelled"></span>' + wcfmcapitalizeFirstLetter($data_name) + ": " + wcfm_core_dashboard_messages.required_message );
							
							$wcfm_is_valid_form = false;
						}
					}
				}
			});
			
			if( !$wcfm_is_valid_form ) {
				wcfmMessageHide();
			}
		}
	});
	
	// Sub-cat Required Check
	/*$( document.body ).on( 'wcfm_products_manage_form_validate', function( event, validating_form ) {
		if( validating_form ) {
			$form = $(validating_form);
			$( "#product_cats_checklist > .product_cats_checklist_item" ).each(function() {
				var count = 0;
				if ($(this).find('input:checkbox').length > 1) {
					if ($(this).find('input:checkbox:checked:visible:first').is(':checked')) {
						$(this).find('input:checkbox:checked:visible:first').parent().parent().find('.product_taxonomy_sub_checklist input:checkbox').each(function() {
							if ($(this).is(':checked')) {
								count++;
							}
						});
						if ( !count ) {
							if( $wcfm_is_valid_form ) 
								$('#' + $form.attr('id') + ' .wcfm-message').html( '<span class="wcicon-status-cancelled"></span>Please choose a sub-category before submit.').addClass('wcfm-error').slideDown();
							else
								$('#' + $form.attr('id') + ' .wcfm-message').append( '<br /><span class="wcicon-status-cancelled"></span>Please choose a sub-category before submit.' );
							
							$wcfm_is_valid_form = false;
							product_form_is_valid = false;
						}
					}
				}
			});
		}
	});*/
	
	// Catt list toggle by Main Cat select
	/*setTimeout(function() {
		$('.sub_checklist_toggler').hide();
		$('.product_cats_checklist_item').each(function() {
			if($(this).find('.wcfm-checkbox').is(':checked')){
				$(this).parent().parent().children('.sub_checklist_toggler').click();
			}
			$(this).children('.selectit').children('.wcfm-checkbox').on('click', function(){
				$(this).parent().parent().children('.sub_checklist_toggler').click();
			});
		});
	}, 100 );*/
	
	// Message Counter auto Refresher
	var messageCountRefrsherTime = '';
	function messageCountRefrsher() {
		clearTimeout(messageCountRefrsherTime);
		messageCountRefrsherTime = setTimeout(function() {
			var data = {
				action : 'wcfm_message_count'
			}	
			jQuery.ajax({
				type:		'POST',
				url: wcfm_params.ajax_url,
				data: data,
				success:	function(response) {
					$response_json = $.parseJSON(response);
					if( $response_json.status ) {
						if($response_json.notice) {
							$('.notice_count').text($response_json.notice);
						}
						if($response_json.message) {
							$('.message_count').text($response_json.message);
							if( wcfm_params.wcfm_is_desktop_notification && ( !wcfm_params.is_mobile || ( wcfm_params.is_mobile && wcfm_params.is_mobile_desktop_notification ) ) ) {
								getNewMessageNotification($response_json.message);
							}
						}
						if($response_json.enquiry) {
							$('.enquiry_count').text($response_json.enquiry);
						}
					} else {
						if( $response_json.redirect ) {
							window.location = $response_json.redirect;
						}
					}
				}
			});
			messageCountRefrsher();
		}, wcfm_params.wcfm_new_message_check_duration );
	}
	if( wcfm_params.wcfm_is_allow_wcfm && wcfm_params.wcfm_is_allow_new_message_check ) {
		messageCountRefrsher();
	}
	
	// Fetching new Message Notifications
	var notificationRefrsherTime = '';
	function getNewMessageNotification(message_count) {
		message_count = parseInt(message_count);
		var unread_message = parseInt(wcfm_params.unread_message);
		
		if( message_count > unread_message ) {
			clearTimeout(notificationRefrsherTime);
			$('.wcfm_notification_wrapper').slideDown(function() { $('.wcfm_notification_wrapper').remove(); } );
			
			var data = {
				action : 'wcfm_message_notification',
				limit  : (message_count - unread_message)
			}	
			jQuery.ajax({
				type:		'POST',
				url: wcfm_params.ajax_url,
				data: data,
				success:	function(response) {
					if( response ) {
						wcfm_params.unread_message = message_count;
						$('body').append(response);
						//initiateTip();
						wcfm_desktop_notification_sound.play();
						notificationRefrsherTime = setTimeout(function() {
						  $('.wcfm_notification_wrapper').slideDown(function() { $('.wcfm_notification_wrapper').remove(); } );
						}, 30000 );
						$('.wcfm_notification_close').click(function() {
							clearTimeout(notificationRefrsherTime);
							$('.wcfm_notification_wrapper').slideDown(function() { $('.wcfm_notification_wrapper').remove(); } );
						});
					}
				}
			});
		}
	}
	
	// WCfM Video Tutorials
	$(".wcfm_tutorials").colorbox({iframe:true, width: $large_popup_width, innerHeight:390});
	
	// Attached Links
	$(".wcfm_linked_images").colorbox({iframe:true, width: $popup_width, innerHeight:390});
	$(".wcfm_linked_attached").colorbox({iframe:true, width: $large_popup_width, innerHeight:390});
	
	// External Product View Count
	if( wcfm_params.wcfm_is_allow_external_product_analytics ) {
		$('.product_type_external').each(function() {
			$(this).click(function( event ) {
				event.preventDefault();
				$product_id = $(this).data('product_id');
				$href       = $(this).attr('href');
				$is_blank   = $(this).attr('target');
				var data = {
					action     : 'wcfm_store_external_product_view_update',
					product_id : $product_id
				}	
				jQuery.ajax({
					type:		'POST',
					url: wcfm_params.ajax_url,
					data: data,
					success:	function(response) {
						if (typeof $is_blank !== typeof undefined && $is_blank !== false) {
							window.open($href, '_blank');
						} else {
							window.location = $href;
						}
					}
				}).always(function( data ) {
					//window.location = $href;
				});
			});
		});
	}
	
	setTimeout(function() {
		$('.wcfm_datepicker').each(function() {
			if( !$(this).hasClass('hasDatepicker') ) {
				$dateFormat = $(this).data('date_format');
				if( !$dateFormat ) $dateFormat = wcfm_datepicker_params.dateFormat;
				$(this).datepicker({
					dateFormat: $dateFormat,
					closeText: wcfm_datepicker_params.closeText,
					currentText: wcfm_datepicker_params.currentText,
					monthNames: wcfm_datepicker_params.monthNames,
					monthNamesShort: wcfm_datepicker_params.monthNamesShort,
					dayNames: wcfm_datepicker_params.dayNames,
					dayNamesShort: wcfm_datepicker_params.dayNamesShort,
					dayNamesMin: wcfm_datepicker_params.dayNamesMin,
					firstDay: wcfm_datepicker_params.firstDay,
					isRTL: wcfm_datepicker_params.isRTL,
					changeMonth: true,
					changeYear: true,
					yearRange: '1920:2030'
				});
			}
		});
		
		$('.wcfm_datetimepicker').each(function() {
			$dateFormat = $(this).data('date_format');
			if( !$dateFormat ) $dateFormat = wcfm_datepicker_params.dateFormat;
			$(this).datetimepicker({
				dateFormat : $dateFormat,
				closeText: wcfm_datepicker_params.closeText,
				currentText: wcfm_datepicker_params.currentText,
				monthNames: wcfm_datepicker_params.monthNames,
				monthNamesShort: wcfm_datepicker_params.monthNamesShort,
				dayNames: wcfm_datepicker_params.dayNames,
				dayNamesShort: wcfm_datepicker_params.dayNamesShort,
				dayNamesMin: wcfm_datepicker_params.dayNamesMin,
				firstDay: wcfm_datepicker_params.firstDay,
				isRTL: wcfm_datepicker_params.isRTL,
				timeFormat: 'h:mm tt',
				changeMonth: true,
				changeYear: true,
				yearRange: '1920:2030'
			});
		});
		
		$('.wcfm_timepicker').each(function() {
			$(this).timepicker({
				timeFormat: 'h:mm tt',
			});
		});
	}, 500);
});

/*
 * WCFM Inquiry Button
 */
$wcfm_enquiry_submited = false;

jQuery(document).ready(function($) {
		
	if( $('.enquiry-form').length > 1 ) {
		$('.enquiry-form')[1].remove();
	}
	
	$inquiryFormLoaded = false;
	if( $('.add_enquiry').length > 0 ) {
		loadInquiryForm();
	}
	if( $('.wcfm_catalog_enquiry').length > 0 ) {
		if( !$inquiryFormLoaded ) loadInquiryForm();
	}
	if( $('.wcfm_store_enquiry').length > 0 ) {
		if( !$inquiryFormLoaded ) loadInquiryForm();
	}
		
	function loadInquiryForm() {
		var data = {
			action  : 'wcfm_enquiry_form_content',
			store   : 0,
			product : 0
		}	
		
		jQuery.ajax({
			type    :		'POST',
			url     : wcfm_params.ajax_url,
			data    : data,
			success :	function(response) {
				$('body').append(response);
				
				$('#enquiry_form').find('.wcfm_datepicker').each(function() {
					$(this).datepicker({
						closeText: wcfm_datepicker_params.closeText,
						currentText: wcfm_datepicker_params.currentText,
						monthNames: wcfm_datepicker_params.monthNames,
						monthNamesShort: wcfm_datepicker_params.monthNamesShort,
						dayNames: wcfm_datepicker_params.dayNames,
						dayNamesShort: wcfm_datepicker_params.dayNamesShort,
						dayNamesMin: wcfm_datepicker_params.dayNamesMin,
						firstDay: wcfm_datepicker_params.firstDay,
						isRTL: wcfm_datepicker_params.isRTL,
						dateFormat: wcfm_datepicker_params.dateFormat,
						changeMonth: true,
						changeYear: true
					});
				});
				initiateTip();
				
				$('#wcfm_enquiry_submit_button').off('click').on('click', function(event) {
					event.preventDefault();
					wcfm_enquiry_form_submit($('#wcfm_enquiry_form'));
				});
				$inquiryFormLoaded = true;
			}
		});
	}
	
	$wcfm_anr_loaded = false;
	$('.add_enquiry, .wcfm_catalog_enquiry, .wcfm_store_enquiry').each(function() {
		if( !$(this).hasClass( 'wcfm_login_popup' ) ) {
			$(this).click(function(event) {
				event.preventDefault();
				
				if( !$inquiryFormLoaded ) return false;
				
				$store   = $(this).data('store');
				$product = $(this).data('product');
				
				$.colorbox( { inline:true, href: "#enquiry_form_wrapper", width: $popup_width,
					onComplete:function() {
						
						$('#wcfm_enquiry_form').find('#enquiry_vendor_id').val($store);
						$('#wcfm_enquiry_form').find('#enquiry_product_id').val($product);
						
						if( jQuery('.anr_captcha_field').length > 0 ) {
							if (typeof grecaptcha != "undefined") {
								if( $wcfm_anr_loaded ) {
									grecaptcha.reset();
								} else {
									wcfm_anr_onloadCallback();
								}
								$wcfm_anr_loaded = true;
							}
						}
						
					}
				});
			});
		}
	});
	
	function wcfm_enquiry_form_validate($enquiry_form) {
		$is_valid = true;
		jQuery('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
		var enquiry_comment = jQuery.trim($enquiry_form.find('#enquiry_comment').val());
		if(enquiry_comment.length == 0) {
			$is_valid = false;
			$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_enquiry).addClass('wcfm-error').slideDown();
		}
		
		if( $enquiry_form.find('#enquiry_author').length > 0 ) {
			var enquiry_author = jQuery.trim($enquiry_form.find('#enquiry_author').val());
			if(enquiry_author.length == 0) {
				if( $is_valid )
					$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_name).addClass('wcfm-error').slideDown();
				else
					$enquiry_form.find('.wcfm-message').append('<br /><span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_name).addClass('wcfm-error').slideDown();
				
				$is_valid = false;
			}
		}
		
		if( $enquiry_form.find('#enquiry_email').length > 0 ) {
			var enquiry_email = jQuery.trim($enquiry_form.find('#enquiry_email').val());
			if(enquiry_email.length == 0) {
				if( $is_valid )
					$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_email).addClass('wcfm-error').slideDown();
				else
					$enquiry_form.find('.wcfm-message').append('<br /><span class="wcicon-status-cancelled"></span>' + wcfm_enquiry_manage_messages.no_email).addClass('wcfm-error').slideDown();
				
				$is_valid = false;
			}
		}
		
		$wcfm_is_valid_form = $is_valid;
		$( document.body ).trigger( 'wcfm_form_validate', $enquiry_form );
		$is_valid = $wcfm_is_valid_form;
		
		return $is_valid;
	}
	
	function wcfm_enquiry_form_submit($enquiry_form) {
		
		// Validations
		$is_valid = wcfm_enquiry_form_validate($enquiry_form);
		
		if($is_valid) {
			$('#enquiry_form_wrapper').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var data = {
				action                   : 'wcfm_ajax_controller',
				controller               : 'wcfm-enquiry-tab',
				wcfm_enquiry_tab_form    : $enquiry_form.serialize(),
				status                   : 'submit'
			}	
			jQuery.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = jQuery.parseJSON(response);
					$enquiry_form.find('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
						setTimeout(function() {
							$.colorbox.remove();
							$enquiry_form.find('#enquiry_comment').val('');
							jQuery('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
						}, 2000 );
					} else {
						$enquiry_form.find('.wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if( jQuery('.wcfm_gglcptch_wrapper').length > 0 ) {
						if (typeof grecaptcha != "undefined") {
							grecaptcha.reset();
						}
					}
					$('#enquiry_form_wrapper').unblock();
				}
			});
		}
	}
});

/*
 * WCFM Membership Subscribe Button
 */
jQuery(document).ready(function($) {
	// Choose Membership
	$('.wcfm_membership_subscribe_button').click(function(event) {
	  event.preventDefault();
	  
		$('#wcfm_membership_container').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action         : 'wcfm_choose_membership',
			membership     : $(this).data('membership'),
		}	
		$.post(wcfm_params.ajax_url, data, function(response) {
			if(response) {
				$response_json = $.parseJSON(response);
				if($response_json.status) {
					if( $response_json.redirect ) window.location = $response_json.redirect;	
				}
				$('#wcfm_membership_container').unblock();
			}
		});
	});
});

/*
 * WCFM Ultimate Core Script
 */
//var $ = jQuery.noConflict();
function intiateWCFMuQuickEdit() {
  jQuery('.wcfmu_product_quick_edit').each( function() {
  	jQuery(this).click( function( event ) {
			event.preventDefault();
			jQueryquick_edit = jQuery(this);
			jQueryproduct = jQueryquick_edit.data('product');
			
			// Ajax Call for Fetching Quick Edit HTML
			jQuery('.products').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var data = {
				action  : 'wcfmu_quick_edit_html',
				product : jQueryproduct
			}	
			
			jQuery.ajax({
				type    :		'POST',
				url     : wcfm_params.ajax_url,
				data    : data,
				success :	function(response) {
					// Intialize colorbox
					jQuery.colorbox( { html: response, width: $popup_width,
						onComplete:function() {
					
							// Intialize Quick Update Action
							jQuery('#wcfm_quick_edit_button').click(function() {
								$wcfm_is_valid_form = true;
								jQuery('#wcfm_quick_edit_form').block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});
								jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
								if( jQuery('input[name=wcfm_quick_edit_title]').val().length == 0 ) {
									//alert(wcfmu_products_manage_messages.no_title);
									jQuery('#wcfm_quick_edit_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfmu_products_manage_messages.no_title).addClass('wcfm-error').slideDown();
									wcfm_notification_sound.play();
									jQuery('#wcfm_quick_edit_form').unblock();
								} else {
									jQuery( document.body ).trigger( 'wcfm_form_validate', jQuery('#wcfm_quick_edit_form') );
									if( !$wcfm_is_valid_form ) {
										wcfm_notification_sound.play();
										jQuery('#wcfm_quick_edit_form').unblock();
									} else {
										var data = {
											action : 'wcfm_ajax_controller',
											controller : 'wcfm-products-quick-manage', 
											wcfm_quick_edit_form : jQuery('#wcfm_quick_edit_form').serialize()
										}	
										jQuery.post(wcfm_params.ajax_url, data, function(response) {
											if(response) {
												jQueryresponse_json = jQuery.parseJSON(response);
												jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
												if(jQueryresponse_json.status) {
													jQuery('#wcfm_quick_edit_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + jQueryresponse_json.message).addClass('wcfm-success').slideDown();
													jQuery('#wcfm_quick_edit_button').hide();
												} else {
													jQuery('#wcfm_quick_edit_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + jQueryresponse_json.message).addClass('wcfm-error').slideDown();
												}
												wcfm_notification_sound.play();
												jQuery('#wcfm_quick_edit_form').unblock();
												setTimeout(function() {
													if($wcfm_products_table) $wcfm_products_table.ajax.reload();
													jQuery.colorbox.remove();
												}, 2000);
											}
										} );
									}
								}
							});
						}
					});
					jQuery('.products').unblock();
				}
			});
			
			return false;
		} );
  } );
}

function intiateWCFMuScreenManager() {
	jQuery('.wcfm_screen_manager').each( function() {
  	jQuery(this).click( function( event ) {
			event.preventDefault();
			jQueryScreen_Manager = jQuery(this);
			jQueryScreen = jQueryScreen_Manager.data('screen');
			
			var data = {
				action  : 'wcfmu_screen_manager_html',
				screen  : jQueryScreen
			}	
			
			jQuery.ajax({
				type    :		'POST',
				url     : wcfm_params.ajax_url,
				data    : data,
				success :	function(response) {
					// Intialize colorbox
					jQuery.colorbox( { html: response, width: $popup_width,
						onComplete:function() {
					
							// Intialize Quick Update Action
							jQuery('#wcfm_screen_manager_button').click(function() {
								jQuery('#wcfm_screen_manager_form').block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});
								jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
								var data = {
									action : 'wcfm_ajax_controller',
									controller : 'wcfm-screen-manage', 
									wcfm_screen_manager_form : jQuery('#wcfm_screen_manager_form').serialize()
								}	
								jQuery.post(wcfm_params.ajax_url, data, function(response) {
									if(response) {
										jQueryresponse_json = jQuery.parseJSON(response);
										jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
										if(jQueryresponse_json.status) {
											jQuery('#wcfm_screen_manager_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + jQueryresponse_json.message).addClass('wcfm-success').slideDown();
										} else {
											jQuery('#wcfm_screen_manager_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + jQueryresponse_json.message).addClass('wcfm-error').slideDown();
										}
										jQuery('#wcfm_screen_manager_form').unblock();
										setTimeout(function() {
											jQuery.colorbox.remove();
											window.location = window.location.href; 
										}, 2000);
									}
								} );
							});
						}
					});
				}
			});
			
			return false;
		} );
  } );
}

jQuery( document ).ready( function( $ ) {
	intiateWCFMuQuickEdit();
	intiateWCFMuScreenManager();
	
	// Order item Mark as Received
	$('.wcfm_mark_as_recived').click(function(e) {
	  e.preventDefault();
	  $(this).hide();
	  $('.woocommerce-order-details').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action       : 'wcfm_mark_as_recived',
			orderid      : $(this).data('orderid'),
			productid    : $(this).data('productid'),
			orderitemid  : $(this).data('orderitemid'),
		}	
		$.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				window.location = window.location.href;
				$('.woocommerce-order-details').unblock();
			}
		});
	});
} );

/*
 * WCFM Colorbox Lib
 */
(function ($, document, window) {
	var
	// Default settings object.
	// See http://jacklmoore.com/colorbox for details.
	defaults = {
		// data sources
		html: false,
		photo: false,
		iframe: false,
		inline: false,

		// behavior and appearance
		transition: "elastic",
		speed: 300,
		fadeOut: 300,
		width: false,
		initialWidth: "600",
		innerWidth: false,
		maxWidth: false,
		height: false,
		initialHeight: "450",
		innerHeight: false,
		maxHeight: false,
		scalePhotos: true,
		scrolling: true,
		opacity: 0.9,
		preloading: true,
		className: false,
		overlayClose: true,
		escKey: true,
		arrowKey: true,
		top: false,
		bottom: false,
		left: false,
		right: false,
		fixed: false,
		data: undefined,
		closeButton: true,
		fastIframe: true,
		open: false,
		reposition: true,
		loop: true,
		slideshow: false,
		slideshowAuto: true,
		slideshowSpeed: 2500,
		slideshowStart: "start slideshow",
		slideshowStop: "stop slideshow",
		photoRegex: /\.(gif|png|jp(e|g|eg)|bmp|ico|webp|jxr|svg)((#|\?).*)?$/i,

		// alternate image paths for high-res displays
		retinaImage: false,
		retinaUrl: false,
		retinaSuffix: '@2x.$1',

		// internationalization
		current: "image {current} of {total}",
		previous: "previous",
		next: "next",
		close: "close",
		xhrError: "This content failed to load.",
		imgError: "This image failed to load.",

		// accessbility
		returnFocus: true,
		trapFocus: true,

		// callbacks
		onOpen: false,
		onLoad: false,
		onComplete: false,
		onCleanup: false,
		onClosed: false,

		rel: function() {
			return this.rel;
		},
		href: function() {
			// using this.href would give the absolute url, when the href may have been inteded as a selector (e.g. '#container')
			return $(this).attr('href');
		},
		title: function() {
			return this.title;
		},
		createImg: function() {
			var img = new Image();
			var attrs = $(this).data('cbox-img-attrs');

			if (typeof attrs === 'object') {
				$.each(attrs, function(key, val){
					img[key] = val;
				});
			}

			return img;
		},
		createIframe: function() {
			var iframe = document.createElement('iframe');
			var attrs = $(this).data('cbox-iframe-attrs');

			if (typeof attrs === 'object') {
				$.each(attrs, function(key, val){
					iframe[key] = val;
				});
			}

			if ('frameBorder' in iframe) {
				iframe.frameBorder = 0;
			}
			if ('allowTransparency' in iframe) {
				iframe.allowTransparency = "true";
			}
			iframe.name = (new Date()).getTime(); // give the iframe a unique name to prevent caching
			iframe.allowFullscreen = true;

			return iframe;
		}
	},

	// Abstracting the HTML and event identifiers for easy rebranding
	colorbox = 'colorbox',
	prefix = 'cbox',
	boxElement = prefix + 'Element',

	// Events
	event_open = prefix + '_open',
	event_load = prefix + '_load',
	event_complete = prefix + '_complete',
	event_cleanup = prefix + '_cleanup',
	event_closed = prefix + '_closed',
	event_purge = prefix + '_purge',

	// Cached jQuery Object Variables
	$overlay,
	$box,
	$wrap,
	$content,
	$topBorder,
	$leftBorder,
	$rightBorder,
	$bottomBorder,
	$related,
	$window,
	$loaded,
	$loadingBay,
	$loadingOverlay,
	$title,
	$current,
	$slideshow,
	$next,
	$prev,
	$close,
	$groupControls,
	$events = $('<a/>'), // $({}) would be preferred, but there is an issue with jQuery 1.4.2

	// Variables for cached values or use across multiple functions
	settings,
	interfaceHeight,
	interfaceWidth,
	loadedHeight,
	loadedWidth,
	index,
	photo,
	open,
	active,
	closing,
	loadingTimer,
	publicMethod,
	div = "div",
	requests = 0,
	previousCSS = {},
	init;

	// ****************
	// HELPER FUNCTIONS
	// ****************

	// Convenience function for creating new jQuery objects
	function $tag(tag, id, css) {
		var element = document.createElement(tag);

		if (id) {
			element.id = prefix + id;
		}

		if (css) {
			element.style.cssText = css;
		}

		return $(element);
	}

	// Get the window height using innerHeight when available to avoid an issue with iOS
	// http://bugs.jquery.com/ticket/6724
	function winheight() {
		return window.innerHeight ? window.innerHeight : $(window).height();
	}

	function Settings(element, options) {
		if (options !== Object(options)) {
			options = {};
		}

		this.cache = {};
		this.el = element;

		this.value = function(key) {
			var dataAttr;

			if (this.cache[key] === undefined) {
				dataAttr = $(this.el).attr('data-cbox-'+key);

				if (dataAttr !== undefined) {
					this.cache[key] = dataAttr;
				} else if (options[key] !== undefined) {
					this.cache[key] = options[key];
				} else if (defaults[key] !== undefined) {
					this.cache[key] = defaults[key];
				}
			}

			return this.cache[key];
		};

		this.get = function(key) {
			var value = this.value(key);
			return $.isFunction(value) ? value.call(this.el, this) : value;
		};
	}

	// Determine the next and previous members in a group.
	function getIndex(increment) {
		var
		max = $related.length,
		newIndex = (index + increment) % max;

		return (newIndex < 0) ? max + newIndex : newIndex;
	}

	// Convert '%' and 'px' values to integers
	function setSize(size, dimension) {
		return Math.round((/%/.test(size) ? ((dimension === 'x' ? $window.width() : winheight()) / 100) : 1) * parseInt(size, 10));
	}

	// Checks an href to see if it is a photo.
	// There is a force photo option (photo: true) for hrefs that cannot be matched by the regex.
	function isImage(settings, url) {
		return settings.get('photo') || settings.get('photoRegex').test(url);
	}

	function retinaUrl(settings, url) {
		return settings.get('retinaUrl') && window.devicePixelRatio > 1 ? url.replace(settings.get('photoRegex'), settings.get('retinaSuffix')) : url;
	}

	function trapFocus(e) {
		if ('contains' in $box[0] && !$box[0].contains(e.target) && e.target !== $overlay[0]) {
			e.stopPropagation();
			$box.focus();
		}
	}

	function setClass(str) {
		if (setClass.str !== str) {
			$box.add($overlay).removeClass(setClass.str).addClass(str);
			setClass.str = str;
		}
	}

	function getRelated(rel) {
		index = 0;

		if (rel && rel !== false && rel !== 'nofollow') {
			$related = $('.' + boxElement).filter(function () {
				var options = $.data(this, colorbox);
				var settings = new Settings(this, options);
				return (settings.get('rel') === rel);
			});
			index = $related.index(settings.el);

			// Check direct calls to Colorbox.
			if (index === -1) {
				$related = $related.add(settings.el);
				index = $related.length - 1;
			}
		} else {
			$related = $(settings.el);
		}
	}

	function trigger(event) {
		// for external use
		$(document).trigger(event);
		// for internal use
		$events.triggerHandler(event);
	}

	var slideshow = (function(){
		var active,
			className = prefix + "Slideshow_",
			click = "click." + prefix,
			timeOut;

		function clear () {
			clearTimeout(timeOut);
		}

		function set() {
			if (settings.get('loop') || $related[index + 1]) {
				clear();
				timeOut = setTimeout(publicMethod.next, settings.get('slideshowSpeed'));
			}
		}

		function start() {
			$slideshow
				.html(settings.get('slideshowStop'))
				.unbind(click)
				.one(click, stop);

			$events
				.bind(event_complete, set)
				.bind(event_load, clear);

			$box.removeClass(className + "off").addClass(className + "on");
		}

		function stop() {
			clear();

			$events
				.unbind(event_complete, set)
				.unbind(event_load, clear);

			$slideshow
				.html(settings.get('slideshowStart'))
				.unbind(click)
				.one(click, function () {
					publicMethod.next();
					start();
				});

			$box.removeClass(className + "on").addClass(className + "off");
		}

		function reset() {
			active = false;
			$slideshow.hide();
			clear();
			$events
				.unbind(event_complete, set)
				.unbind(event_load, clear);
			$box.removeClass(className + "off " + className + "on");
		}

		return function(){
			if (active) {
				if (!settings.get('slideshow')) {
					$events.unbind(event_cleanup, reset);
					reset();
				}
			} else {
				if (settings.get('slideshow') && $related[1]) {
					active = true;
					$events.one(event_cleanup, reset);
					if (settings.get('slideshowAuto')) {
						start();
					} else {
						stop();
					}
					$slideshow.show();
				}
			}
		};

	}());


	function launch(element) {
		var options;

		if (!closing) {

			options = $(element).data(colorbox);

			settings = new Settings(element, options);

			getRelated(settings.get('rel'));

			if (!open) {
				open = active = true; // Prevents the page-change action from queuing up if the visitor holds down the left or right keys.

				setClass(settings.get('className'));

				// Show colorbox so the sizes can be calculated in older versions of jQuery
				$box.css({visibility:'hidden', display:'block', opacity:''});

				$loaded = $tag(div, 'LoadedContent', 'width:0; height:0; overflow:hidden; visibility:hidden');
				$content.css({width:'', height:''}).append($loaded);

				// Cache values needed for size calculations
				interfaceHeight = $topBorder.height() + $bottomBorder.height() + $content.outerHeight(true) - $content.height();
				interfaceWidth = $leftBorder.width() + $rightBorder.width() + $content.outerWidth(true) - $content.width();
				loadedHeight = $loaded.outerHeight(true);
				loadedWidth = $loaded.outerWidth(true);

				// Opens inital empty Colorbox prior to content being loaded.
				var initialWidth = setSize(settings.get('initialWidth'), 'x');
				var initialHeight = setSize(settings.get('initialHeight'), 'y');
				var maxWidth = settings.get('maxWidth');
				var maxHeight = settings.get('maxHeight');

				settings.w = Math.max((maxWidth !== false ? Math.min(initialWidth, setSize(maxWidth, 'x')) : initialWidth) - loadedWidth - interfaceWidth, 0);
				settings.h = Math.max((maxHeight !== false ? Math.min(initialHeight, setSize(maxHeight, 'y')) : initialHeight) - loadedHeight - interfaceHeight, 0);

				$loaded.css({width:'', height:settings.h});
				publicMethod.position();

				trigger(event_open);
				settings.get('onOpen');

				$groupControls.add($title).hide();

				$box.focus();

				if (settings.get('trapFocus')) {
					// Confine focus to the modal
					// Uses event capturing that is not supported in IE8-
					if (document.addEventListener) {

						document.addEventListener('focus', trapFocus, true);

						$events.one(event_closed, function () {
							document.removeEventListener('focus', trapFocus, true);
						});
					}
				}

				// Return focus on closing
				if (settings.get('returnFocus')) {
					$events.one(event_closed, function () {
						$(settings.el).focus();
					});
				}
			}

			var opacity = parseFloat(settings.get('opacity'));
			$overlay.css({
				opacity: opacity === opacity ? opacity : '',
				cursor: settings.get('overlayClose') ? 'pointer' : '',
				visibility: 'visible'
			}).show();

			if (settings.get('closeButton')) {
				$close.html(settings.get('close')).appendTo($content);
			} else {
				$close.appendTo('<div/>'); // replace with .detach() when dropping jQuery < 1.4
			}

			load();
		}
	}

	// Colorbox's markup needs to be added to the DOM prior to being called
	// so that the browser will go ahead and load the CSS background images.
	function appendHTML() {
		if (!$box) {
			init = false;
			$window = $(window);
			$box = $tag(div).attr({
				id: colorbox,
				'class': $.support.opacity === false ? prefix + 'IE' : '', // class for optional IE8 & lower targeted CSS.
				role: 'dialog',
				tabindex: '-1'
			}).hide();
			$overlay = $tag(div, "Overlay").hide();
			$loadingOverlay = $([$tag(div, "LoadingOverlay")[0],$tag(div, "LoadingGraphic")[0]]);
			$wrap = $tag(div, "Wrapper");
			$content = $tag(div, "Content").append(
				$title = $tag(div, "Title"),
				$current = $tag(div, "Current"),
				$prev = $('<button type="button"/>').attr({id:prefix+'Previous'}),
				$next = $('<button type="button"/>').attr({id:prefix+'Next'}),
				$slideshow = $('<button type="button"/>').attr({id:prefix+'Slideshow'}),
				$loadingOverlay
			);

			$close = $('<button type="button"/>').attr({id:prefix+'Close'});

			$wrap.append( // The 3x3 Grid that makes up Colorbox
				$tag(div).append(
					$tag(div, "TopLeft"),
					$topBorder = $tag(div, "TopCenter"),
					$tag(div, "TopRight")
				),
				$tag(div, false, 'clear:left').append(
					$leftBorder = $tag(div, "MiddleLeft"),
					$content,
					$rightBorder = $tag(div, "MiddleRight")
				),
				$tag(div, false, 'clear:left').append(
					$tag(div, "BottomLeft"),
					$bottomBorder = $tag(div, "BottomCenter"),
					$tag(div, "BottomRight")
				)
			).find('div div').css({'float': 'left'});

			$loadingBay = $tag(div, false, 'position:absolute; width:9999px; visibility:hidden; display:none; max-width:none;');

			$groupControls = $next.add($prev).add($current).add($slideshow);
		}
		if (document.body && !$box.parent().length) {
			$(document.body).append($overlay, $box.append($wrap, $loadingBay));
		}
	}

	// Add Colorbox's event bindings
	function addBindings() {
		function clickHandler(e) {
			// ignore non-left-mouse-clicks and clicks modified with ctrl / command, shift, or alt.
			// See: http://jacklmoore.com/notes/click-events/
			if (!(e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey)) {
				e.preventDefault();
				launch(this);
			}
		}

		if ($box) {
			if (!init) {
				init = true;

				// Anonymous functions here keep the public method from being cached, thereby allowing them to be redefined on the fly.
				$next.click(function () {
					publicMethod.next();
				});
				$prev.click(function () {
					publicMethod.prev();
				});
				$close.click(function () {
					publicMethod.close();
				});
				$overlay.click(function () {
					if (settings.get('overlayClose')) {
						publicMethod.close();
					}
				});

				// Key Bindings
				$(document).bind('keydown.' + prefix, function (e) {
					var key = e.keyCode;
					if (open && settings.get('escKey') && key === 27) {
						e.preventDefault();
						publicMethod.close();
					}
					if (open && settings.get('arrowKey') && $related[1] && !e.altKey) {
						if (key === 37) {
							e.preventDefault();
							$prev.click();
						} else if (key === 39) {
							e.preventDefault();
							$next.click();
						}
					}
				});

				if ($.isFunction($.fn.on)) {
					// For jQuery 1.7+
					$(document).on('click.'+prefix, '.'+boxElement, clickHandler);
				} else {
					// For jQuery 1.3.x -> 1.6.x
					// This code is never reached in jQuery 1.9, so do not contact me about 'live' being removed.
					// This is not here for jQuery 1.9, it's here for legacy users.
					$('.'+boxElement).live('click.'+prefix, clickHandler);
				}
			}
			return true;
		}
		return false;
	}

	// Don't do anything if Colorbox already exists.
	if ($[colorbox]) {
		return;
	}

	// Append the HTML when the DOM loads
	$(appendHTML);


	// ****************
	// PUBLIC FUNCTIONS
	// Usage format: $.colorbox.close();
	// Usage from within an iframe: parent.jQuery.colorbox.close();
	// ****************

	publicMethod = $.fn[colorbox] = $[colorbox] = function (options, callback) {
		var settings;
		var $obj = this;

		options = options || {};

		if ($.isFunction($obj)) { // assume a call to $.colorbox
			$obj = $('<a/>');
			options.open = true;
		}

		if (!$obj[0]) { // colorbox being applied to empty collection
			return $obj;
		}

		appendHTML();

		if (addBindings()) {

			if (callback) {
				options.onComplete = callback;
			}

			$obj.each(function () {
				var old = $.data(this, colorbox) || {};
				$.data(this, colorbox, $.extend(old, options));
			}).addClass(boxElement);

			settings = new Settings($obj[0], options);

			if (settings.get('open')) {
				launch($obj[0]);
			}
		}

		return $obj;
	};

	publicMethod.position = function (speed, loadedCallback) {
		var
		css,
		top = 0,
		left = 0,
		offset = $box.offset(),
		scrollTop,
		scrollLeft;

		$window.unbind('resize.' + prefix);

		// remove the modal so that it doesn't influence the document width/height
		$box.css({top: -9e4, left: -9e4});

		scrollTop = $window.scrollTop();
		scrollLeft = $window.scrollLeft();

		if (settings.get('fixed')) {
			offset.top -= scrollTop;
			offset.left -= scrollLeft;
			$box.css({position: 'fixed'});
		} else {
			top = scrollTop;
			left = scrollLeft;
			$box.css({position: 'absolute'});
		}

		// keeps the top and left positions within the browser's viewport.
		if (settings.get('right') !== false) {
			left += Math.max($window.width() - settings.w - loadedWidth - interfaceWidth - setSize(settings.get('right'), 'x'), 0);
		} else if (settings.get('left') !== false) {
			left += setSize(settings.get('left'), 'x');
		} else {
			left += Math.round(Math.max($window.width() - settings.w - loadedWidth - interfaceWidth, 0) / 2);
		}

		if (settings.get('bottom') !== false) {
			top += Math.max(winheight() - settings.h - loadedHeight - interfaceHeight - setSize(settings.get('bottom'), 'y'), 0);
		} else if (settings.get('top') !== false) {
			top += setSize(settings.get('top'), 'y');
		} else {
			top += Math.round(Math.max(winheight() - settings.h - loadedHeight - interfaceHeight, 0) / 2);
		}

		$box.css({top: offset.top, left: offset.left, visibility:'visible'});

		// this gives the wrapper plenty of breathing room so it's floated contents can move around smoothly,
		// but it has to be shrank down around the size of div#colorbox when it's done.  If not,
		// it can invoke an obscure IE bug when using iframes.
		$wrap[0].style.width = $wrap[0].style.height = "9999px";

		function modalDimensions() {
			$topBorder[0].style.width = $bottomBorder[0].style.width = $content[0].style.width = (parseInt($box[0].style.width,10) - interfaceWidth)+'px';
			$content[0].style.height = $leftBorder[0].style.height = $rightBorder[0].style.height = (parseInt($box[0].style.height,10) - interfaceHeight)+'px';
		}

		css = {width: settings.w + loadedWidth + interfaceWidth, height: settings.h + loadedHeight + interfaceHeight, top: top, left: left};

		// setting the speed to 0 if the content hasn't changed size or position
		if (speed) {
			var tempSpeed = 0;
			$.each(css, function(i){
				if (css[i] !== previousCSS[i]) {
					tempSpeed = speed;
					return;
				}
			});
			speed = tempSpeed;
		}

		previousCSS = css;

		if (!speed) {
			$box.css(css);
		}

		$box.dequeue().animate(css, {
			duration: speed || 0,
			complete: function () {
				modalDimensions();

				active = false;

				// shrink the wrapper down to exactly the size of colorbox to avoid a bug in IE's iframe implementation.
				$wrap[0].style.width = (settings.w + loadedWidth + interfaceWidth) + "px";
				$wrap[0].style.height = (settings.h + loadedHeight + interfaceHeight) + "px";

				if (settings.get('reposition')) {
					setTimeout(function () {  // small delay before binding onresize due to an IE8 bug.
						$window.bind('resize.' + prefix, publicMethod.position);
					}, 1);
				}

				if ($.isFunction(loadedCallback)) {
					loadedCallback();
				}
			},
			step: modalDimensions
		});
	};

	publicMethod.resize = function (options) {
		var scrolltop;

		if (open) {
			options = options || {};

			if (options.width) {
				settings.w = setSize(options.width, 'x') - loadedWidth - interfaceWidth;
			}

			if (options.innerWidth) {
				settings.w = setSize(options.innerWidth, 'x');
			}

			$loaded.css({width: settings.w});

			if (options.height) {
				settings.h = setSize(options.height, 'y') - loadedHeight - interfaceHeight;
			}

			if (options.innerHeight) {
				settings.h = setSize(options.innerHeight, 'y');
			}

			if (!options.innerHeight && !options.height) {
				scrolltop = $loaded.scrollTop();
				$loaded.css({height: "auto"});
				settings.h = $loaded.height();
			}

			$loaded.css({height: settings.h});

			if(scrolltop) {
				$loaded.scrollTop(scrolltop);
			}

			publicMethod.position(settings.get('transition') === "none" ? 0 : settings.get('speed'));
		}
	};

	publicMethod.prep = function (object) {
		if (!open) {
			return;
		}

		var callback, speed = settings.get('transition') === "none" ? 0 : settings.get('speed');

		$loaded.remove();

		$loaded = $tag(div, 'LoadedContent').append(object);

		function getWidth() {
			settings.w = settings.w || $loaded.width();
			settings.w = settings.mw && settings.mw < settings.w ? settings.mw : settings.w;
			return settings.w;
		}
		function getHeight() {
			settings.h = settings.h || $loaded.height();
			settings.h = settings.mh && settings.mh < settings.h ? settings.mh : settings.h;
			return settings.h;
		}

		$loaded.hide()
		.appendTo($loadingBay.show())// content has to be appended to the DOM for accurate size calculations.
		.css({width: getWidth(), overflow: settings.get('scrolling') ? 'auto' : 'hidden'})
		.css({height: getHeight()})// sets the height independently from the width in case the new width influences the value of height.
		.prependTo($content);

		$loadingBay.hide();

		// floating the IMG removes the bottom line-height and fixed a problem where IE miscalculates the width of the parent element as 100% of the document width.

		$(photo).css({'float': 'none'});

		setClass(settings.get('className'));

		callback = function () {
			var total = $related.length,
				iframe,
				complete;

			if (!open) {
				return;
			}

			function removeFilter() { // Needed for IE8 in versions of jQuery prior to 1.7.2
				if ($.support.opacity === false) {
					$box[0].style.removeAttribute('filter');
				}
			}

			complete = function () {
				clearTimeout(loadingTimer);
				$loadingOverlay.hide();
				trigger(event_complete);
				settings.get('onComplete');
			};


			$title.html(settings.get('title')).show();
			$loaded.show();

			if (total > 1) { // handle grouping
				if (typeof settings.get('current') === "string") {
					$current.html(settings.get('current').replace('{current}', index + 1).replace('{total}', total)).show();
				}

				$next[(settings.get('loop') || index < total - 1) ? "show" : "hide"]().html(settings.get('next'));
				$prev[(settings.get('loop') || index) ? "show" : "hide"]().html(settings.get('previous'));

				slideshow();

				// Preloads images within a rel group
				if (settings.get('preloading')) {
					$.each([getIndex(-1), getIndex(1)], function(){
						var img,
							i = $related[this],
							settings = new Settings(i, $.data(i, colorbox)),
							src = settings.get('href');

						if (src && isImage(settings, src)) {
							src = retinaUrl(settings, src);
							img = document.createElement('img');
							img.src = src;
						}
					});
				}
			} else {
				$groupControls.hide();
			}

			if (settings.get('iframe')) {

				iframe = settings.get('createIframe');

				if (!settings.get('scrolling')) {
					iframe.scrolling = "no";
				}

				$(iframe)
					.attr({
						src: settings.get('href'),
						'class': prefix + 'Iframe'
					})
					.one('load', complete)
					.appendTo($loaded);

				$events.one(event_purge, function () {
					iframe.src = "//about:blank";
				});

				if (settings.get('fastIframe')) {
					$(iframe).trigger('load');
				}
			} else {
				complete();
			}

			if (settings.get('transition') === 'fade') {
				$box.fadeTo(speed, 1, removeFilter);
			} else {
				removeFilter();
			}
		};

		if (settings.get('transition') === 'fade') {
			$box.fadeTo(speed, 0, function () {
				publicMethod.position(0, callback);
			});
		} else {
			publicMethod.position(speed, callback);
		}
	};

	function load () {
		var href, setResize, prep = publicMethod.prep, $inline, request = ++requests;

		active = true;

		photo = false;

		trigger(event_purge);
		trigger(event_load);
		settings.get('onLoad');

		settings.h = settings.get('height') ?
				setSize(settings.get('height'), 'y') - loadedHeight - interfaceHeight :
				settings.get('innerHeight') && setSize(settings.get('innerHeight'), 'y');

		settings.w = settings.get('width') ?
				setSize(settings.get('width'), 'x') - loadedWidth - interfaceWidth :
				settings.get('innerWidth') && setSize(settings.get('innerWidth'), 'x');

		// Sets the minimum dimensions for use in image scaling
		settings.mw = settings.w;
		settings.mh = settings.h;

		// Re-evaluate the minimum width and height based on maxWidth and maxHeight values.
		// If the width or height exceed the maxWidth or maxHeight, use the maximum values instead.
		if (settings.get('maxWidth')) {
			settings.mw = setSize(settings.get('maxWidth'), 'x') - loadedWidth - interfaceWidth;
			settings.mw = settings.w && settings.w < settings.mw ? settings.w : settings.mw;
		}
		if (settings.get('maxHeight')) {
			settings.mh = setSize(settings.get('maxHeight'), 'y') - loadedHeight - interfaceHeight;
			settings.mh = settings.h && settings.h < settings.mh ? settings.h : settings.mh;
		}

		href = settings.get('href');

		loadingTimer = setTimeout(function () {
			$loadingOverlay.show();
		}, 100);

		if (settings.get('inline')) {
			var $target = $(href).eq(0);
			// Inserts an empty placeholder where inline content is being pulled from.
			// An event is bound to put inline content back when Colorbox closes or loads new content.
			$inline = $('<div>').hide().insertBefore($target);

			$events.one(event_purge, function () {
				$inline.replaceWith($target);
			});

			prep($target);
		} else if (settings.get('iframe')) {
			// IFrame element won't be added to the DOM until it is ready to be displayed,
			// to avoid problems with DOM-ready JS that might be trying to run in that iframe.
			prep(" ");
		} else if (settings.get('html')) {
			prep(settings.get('html'));
		} else if (isImage(settings, href)) {

			href = retinaUrl(settings, href);

			photo = settings.get('createImg');

			$(photo)
			.addClass(prefix + 'Photo')
			.bind('error.'+prefix,function () {
				prep($tag(div, 'Error').html(settings.get('imgError')));
			})
			.one('load', function () {
				if (request !== requests) {
					return;
				}

				// A small pause because some browsers will occasionally report a
				// img.width and img.height of zero immediately after the img.onload fires
				setTimeout(function(){
					var percent;

					if (settings.get('retinaImage') && window.devicePixelRatio > 1) {
						photo.height = photo.height / window.devicePixelRatio;
						photo.width = photo.width / window.devicePixelRatio;
					}

					if (settings.get('scalePhotos')) {
						setResize = function () {
							photo.height -= photo.height * percent;
							photo.width -= photo.width * percent;
						};
						if (settings.mw && photo.width > settings.mw) {
							percent = (photo.width - settings.mw) / photo.width;
							setResize();
						}
						if (settings.mh && photo.height > settings.mh) {
							percent = (photo.height - settings.mh) / photo.height;
							setResize();
						}
					}

					if (settings.h) {
						photo.style.marginTop = Math.max(settings.mh - photo.height, 0) / 2 + 'px';
					}

					if ($related[1] && (settings.get('loop') || $related[index + 1])) {
						photo.style.cursor = 'pointer';

						$(photo).bind('click.'+prefix, function () {
							publicMethod.next();
						});
					}

					photo.style.width = photo.width + 'px';
					photo.style.height = photo.height + 'px';
					prep(photo);
				}, 1);
			});

			photo.src = href;

		} else if (href) {
			$loadingBay.load(href, settings.get('data'), function (data, status) {
				if (request === requests) {
					prep(status === 'error' ? $tag(div, 'Error').html(settings.get('xhrError')) : $(this).contents());
				}
			});
		}
	}

	// Navigates to the next page/image in a set.
	publicMethod.next = function () {
		if (!active && $related[1] && (settings.get('loop') || $related[index + 1])) {
			index = getIndex(1);
			launch($related[index]);
		}
	};

	publicMethod.prev = function () {
		if (!active && $related[1] && (settings.get('loop') || index)) {
			index = getIndex(-1);
			launch($related[index]);
		}
	};

	// Note: to use this within an iframe use the following format: parent.jQuery.colorbox.close();
	publicMethod.close = function () {
		if (open && !closing) {

			closing = true;
			open = false;
			trigger(event_cleanup);
			settings.get('onCleanup');
			$window.unbind('.' + prefix);
			$overlay.fadeTo(settings.get('fadeOut') || 0, 0);

			$box.stop().fadeTo(settings.get('fadeOut') || 0, 0, function () {
				$box.hide();
				$overlay.hide();
				trigger(event_purge);
				$loaded.remove();

				setTimeout(function () {
					closing = false;
					trigger(event_closed);
					settings.get('onClosed');
				}, 1);
			});
		}
	};

	// Removes changes Colorbox made to the document, but does not remove the plugin.
	publicMethod.remove = function () {
		if (!$box) { return; }

		$box.stop();
		$[colorbox].close();
		$box.stop(false, true).remove();
		$overlay.remove();
		closing = false;
		$box = null;
		$('.' + boxElement)
			.removeData(colorbox)
			.removeClass(boxElement);

		$(document).unbind('click.'+prefix).unbind('keydown.'+prefix);
	};

	// A method for fetching the current element Colorbox is referencing.
	// returns a jQuery object.
	publicMethod.element = function () {
		return $(settings.el);
	};

	publicMethod.settings = defaults;

}(jQuery, document, window));

 
function wcfmstripHtml(html) {
	// Create a new div element
	var temporalDivElement = document.createElement("div");
	// Set the HTML content with the providen
	temporalDivElement.innerHTML = html;
	// Retrieve the text property of the element (cross-browser support)
	return temporalDivElement.textContent || temporalDivElement.innerText || "";
}

function wcfmcapitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

var audio = new Audio(wcfm_notification_sound);
var wcfm_notification_sound = new Audio(wcfm_notification_sound);
var wcfm_desktop_notification_sound = new Audio(wcfm_desktop_notification_sound);