function dbexec(c){
		empty("dbRes");
		append("dbRes", "<div class='loading'></div>");
		ajax(serialize(d.forms[0]) + '&code=' + c, function(r){
			empty("dbRes");
			append("dbRes", r);
			uiUpdateControls();
		});
	}	
	
	function dbengine(t){
		d.getElementById("su").className = "hide";
		d.getElementById("sp").className = "hide";
		d.getElementById("so").className = "hide";
		
		if ((t.value === "odbc") || (t.value === "pdo")){
			d.getElementById("sh").innerHTML = "DSN/Connection String";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
		} else if ((t.value === "sqlite") || (t.value === "sqlite3")){
			d.getElementById("sh").innerHTML = "DB File";
		} else {
			d.getElementById("sh").innerHTML = "Host";
			d.getElementById("su").className = "";
			d.getElementById("sp").className = "";
			d.getElementById("so").className = "";
		}
	}
	
	function dbhistory(a){
		if (a == "s"){
			o = {history: []};
			if (sessionStorage.getItem( config.consNames.sqlclog ) != null)
				o = JSON.parse(sessionStorage.getItem( config.consNames.sqlclog ));
				
			o.history.push({"type": d.getElementById("type").value, "host": d.getElementById("host").value, 
				"port": d.getElementById("port").value, "user": d.getElementById("user").value, "pass": d.getElementById("pass").value});
			sessionStorage.setItem( config.consNames.sqlclog , JSON.stringify(o));
		} else if (sessionStorage.getItem( config.consNames.sqlclog ) != null) {
			s = "";
			o = JSON.parse(sessionStorage.getItem( config.consNames.sqlclog ));
			for (i = 0; i < o.history.length; i++){
				u = "me=sql&host=" + o.history[i].host + "&port=" + o.history[i].port + "&user=" + o.history[i].user + "&pass=" + o.history[i].pass + "&type=" + o.history[i].type;
				s += "[" + o.history[i].type.toUpperCase() + "] " + o.history[i].user + "@" + o.history[i].host + "<span style='float:right;'><a href='#' onclick='ajaxLoad(&quot;" + u + "&quot;)'>' . tText('go', 'Go!') . '</a></span><br>";
			}
			
			if (s != "") prepend("content", "<div id='uires' class='uires'>" + s + "</div>");
		}//TODO add delete a entry
	}