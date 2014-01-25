var isMSIE = /*@cc_on!@*/false;
var KeyState = {
		message : { shiftkey : false } 
	};
	
String.prototype.genresline = function (num) {
	
	var fileds = this.split("<>");
	
	if(fileds.length < 4)
	{
		for(var i=0; i < 4; i++)
		{
			fileds[i] = "ここ壊れてます"
		}
	}
	
	var resline = "";
	resline += "<dt id=\"a" + num + "\">" + num + " ： ";
	resline += "<span class=\"name\"><b>" + fileds[0] + "</b></span> ";
	resline += "<span class=\"info\">" + "[" + fileds[1] + "]";
	resline += "：" + fileds[2] + "</span></dt>";
	resline += "<dd>" + fileds[3] + "</dd>";
	resline += "\n";
	
	return resline;
}

String.prototype.getreslines = function () {
	return this.split("\n");
}
function DatInfo () {
		this.datdata = "";
		this.datrange = 0;
		this.lastmodified = "Thu, 01 Jun 1970 00:00:00 GMT";
		
		this.init = function () {
			this.datdata = "";
			this.datrange = 0;
			this.lastmodified = "Thu, 01 Jun 1970 00:00:00 GMT";
		};

		this.set = function (buff, length) {
			if(buff != "")
			{
				this.datrange = length;
				this.datdata = buff;
			}
		};

		this.append = function (buff, length) {
			
			if((buff.length > 0) && (buff.charCodeAt(0) != 0x0a) && (this.datdata.length > 0))
			{
				alert(buff);
				this.ifdelres();
			}
			else if(this.datdata.length == 0)
			{
				this.datrange = length;
				this.datdata = buff;
			}
			else if((buff.length > 2) && (buff.charCodeAt(0) == 0x0a))
			{
				this.datrange += length - 1;
				this.datdata += buff.substr(1, length - 1);
			}
			else if(buff.length == 2)
			{
				this.datrange += 1;
				this.datdata += buff;
			}
		};

		this.ifdelres = function () {
			window.alert("あぼ～んがあったようです。取得し直します。");
			this.init();
			this.ajax_getdat(true);
		};

		this.getrequset_range = function () {
			if(this.datrange > 0)
			{
				return  (this.datrange - 1) ;
			}
			else
			{
				return this.datrange;
			}
	
		};

		this.getrenderhtml = function (options) {
			var htmlresbodys = "";
			var reslines = this.datdata.getreslines();
			var rescnt;
			
			if(options.end == null)
			{
				options.end = reslines.length;
			}
			
			rescnt = reslines.length;
		
			if((options.first == true) && (options.start > 1))
			{
				htmlresbodys += reslines[0].genresline(1);
			}
			
			for(i= (options.start - 1) ; ((i < options.end) && (i < rescnt)) ; i++)
			{
				if(reslines[i] == "")
				{
					break;
				}
				
				htmlresbodys += reslines[i].genresline(i+1);
			}
			
			return htmlresbodys;
		};
		
		this.update_dat = function (xmlhttp, callback_args)
		{
			var threadinfo = callback_args[0];
			var parseopt = callback_args[1];
			
			window.clearInterval(threadinfo.timerid);
			document.getElementById("resbodys").innerHTML = "";
			
			threadinfo.requestcomp = true;
			
			if(XmlHttpHelper.getLastModefied(xmlhttp) != null)
			{
				this.lastmodified = XmlHttpHelper.getLastModefied(xmlhttp);
			}
			
			var length = XmlHttpHelper.getContentLength(xmlhttp);	
			
			if(length != null)
			{
				length = parseInt(length);
				if(length.toString() == "NaN")
				{
					window.alert("Content-Lengthのパースに失敗しました。");
					this.init();
					return;
				}
			}
			else if((xmlhttp.status == 0) || (xmlhttp.status == 404) || (xmlhttp.status == 302))
			{
				window.alert("該当するスレッドは削除されたか、存在しません。");
				return;
			}
			else if((xmlhttp.status != 416) && (xmlhttp.status != 304))
			{
				window.alert("Content-Lengthが取得できませんでした。");
				this.init();
				return;
			}
			
			if(xmlhttp.status == 200)
			{
				this.set(XmlHttpHelper.getResponseText(xmlhttp), length);
			}
			else if(xmlhttp.status == 206)
			{	
				this.append(XmlHttpHelper.getResponseText(xmlhttp), length);
			}
			
			else if(xmlhttp.status == 416)
			{
				this.ifdelres();
			}
			else if(xmlhttp.status == 304)
			{
				//処理なし
			}
			
			if(parseopt)
			{
				threadinfo.parse_option();
			}
			threadinfo.rendering();
		};
	}
var threadinfo = {
	option : null,
	options : { first : null, start : null, end : null},
	urlbase : null,
	bbs : null,
	thread : null,
	pastfrom : null,
	pastto : null,
	nextfrom : null,
	nextto : null,
	timerid : null,
	requestcomp : true,
	datInfo : new DatInfo(),
	
	parse_option : function() {
		var reslines = this.datInfo.datdata.getreslines();
		
		if(/^\|(\d+)n?$/.test(this.option))
		{
			var match = this.option.match(/\d+/g);
			
			if(/n$/.test(this.option))
			{
				this.options.first = false;
			}
			else
			{
				this.options.first = true;
			}
			
			this.options.start = reslines.length - match[0];
			
			if(this.options.start < 1)
			{
				this.options.start = 1;
			}
			
			this.options.end = reslines.length;
		}
		else if(/^(\d+)-(\d+)n?$/.test(this.option))
		{
			var match = this.option.match(/\d+/g);
			
			if(/n$/.test(this.option))
			{
				this.options.first = false;
			}
			else
			{
				this.options.first = true;
			}
			
			this.options.start = match[0];
			this.options.end   = match[1];
		}
		else if(/^-(\d+)n?$/.test(this.option))
		{
			var match = this.option.match(/\d+/g);
			
			if(/n$/.test(this.option))
			{
				this.options.first = false;
			}
			else
			{
				this.options.first = true;
			}
			
			this.options.start = 1;
			this.options.end   = match[0];
		}
		else if(/^(\d+)-n?$/.test(this.option))
		{
			var match = this.option.match(/\d+/g);
			
			if(/n$/.test(this.option))
			{
				this.options.first = false;
			}
			else
			{
				this.options.first = true;
			}
			
			this.options.start = match[0];
			this.options.end   = reslines.length;
		}
		else if(/^(\d+)n?$/.test(this.option))
		{
			var match = this.option.match(/\d+/g);
			
			if(/n$/.test(this.option))
			{
				this.options.first = false;
			}
			else
			{
				this.options.first = true;
			}
			
			this.options.start = match[0];
			this.options.end   = match[0];
		}
		else if(/^$/.test(this.option))
		{
			this.options.first = false;
			this.options.start = 1;
			this.options.end   = reslines.length;
		}
		else
		{
			window.alert("オプション指定が不正です。");
		}
		
		this.pastfrom = this.options.start - 100;
		if(this.pastfrom < 1)
		{
			this.pastfrom = 1;
		}
	
		this.pastto = this.options.start - 1;
		if(this.pastto < 1)
		{
			this.pastto = 1;
		}
	
		this.nextfrom = this.options.end + 1;
		this.nextto   = this.options.end + 100;
		
	},
	
	update_dat : function(xmlhttp, callback_args) {
		var threadinfo = callback_args[0];
		threadinfo.datInfo.update_dat(xmlhttp, callback_args);
		window.location.href = "#footer";
	},
	
	resdisp : function(option) {
		this.option = option;
		this.ajax_getdat(true);
	},
	
	rendering : function() {
		document.getElementById("resbodys").innerHTML = this.datInfo.getrenderhtml(this.options);
		var reslines = this.datInfo.datdata.getreslines();
		var subject = reslines[0].split("<>")[4];
		
		if(subject == undefined)
		{
			subject = 'スレタイ不明';
		}
		
		document.title = subject;
		document.getElementById("subject").innerHTML = subject;
		var date = new Date();
		var speed = (reslines.length - 1) / (Math.floor(date.getTime() / 60000) - Math.floor(this.thread / 60)) * 60 * 24;
		document.getElementById("speed").innerHTML = "<strong>Speed:" + speed + "</strong>";
		document.getElementById("size").innerHTML = this.datInfo.datrange;
	},
	
	ajax_reload : function(xmlhttp, callback_args) {
		var threadinfo = callback_args[0];
		window.clearInterval(threadinfo.timerid);
	
		var param = document.getElementById("resparams");
		if( (xmlhttp.status == 200) && (/<!-- 2ch_X:error -->/.test(xmlhttp.responseText)) )
		{
			msgwindow = window.open();
			msgwindow.document.write(xmlhttp.responseText);
		}
		param.elements["MESSAGE"].value = "";
		
		threadinfo.options.end = null;
		
		threadinfo.ajax_getdat(false);
	},
	
	res_post : function(iskeybord) {
		var info = document.getElementById("info");	
	
		var param = document.getElementById("resparams");
	
		var bbs = this.bbs;
		var key = this.thread
		
		var message = param.elements["MESSAGE"];
		
		var url = this.urlbase + "test/bbs.cgi?guid=On";
	
		var headers = new Array();
		
		headers["Referer"] = this.urlbase + "/" + bbs + "/";	
		headers["Content-Type"] = "application/x-www-form-urlencoded";
		
		var postprm = new Array();
	
		postprm["bbs"] = bbs;
		postprm["key"] = key;
		postprm["FROM"] = param.elements["FROM"].value;
		postprm["mail"] = param.elements["mail"].value;
		postprm["MESSAGE"] = param.elements["MESSAGE"].value;
		
		postprm["submit"] = "書き込む";
		postprm["utn"] = "utn";
		postprm["time"] = "1";
		postprm["suka"] = "pontan";
		
		var count = 3;
		
		this.timerid = window.setInterval( function () {
			count++;
			var str = "書き込み中";
			for(var i=0; i < count; i++)
			{
				str += ".";
			}
			
			if(count > 20)
			{
				count = 0;
			}
			
			document.getElementById("resbodys").innerHTML = "<span id='waiting'><b>" + str + "</b></span>";
		}, 100);
		
		http_post(this.ajax_reload, [this], url, null, headers, postprm, EscapeSJIS);
		
		return false;
	},
	
	ajax_getdat : function(parseopt) {
		if(this.requestcomp == false)
		{
			window.alert("通信中です...");
			return;
		}
		
		this.requestcomp = false;
		
		var param = document.getElementById("resparams");
	
		var bbs = this.bbs;
		var key = this.thread
		
		var url = this.urlbase + bbs + "/dat/" + key + ".dat?" + (new Date()).getTime();
	
		var headers = new Array();
		var mimetype = "text/plain; charset=shift_jis";

		headers["Content-Type"] = "text/plain; charset=x-sjis";
		headers["If-Modified-Since"] = this.datInfo.lastmodified;
		
		//データの差分取得時にはAccept-Encodingにgzipなどの圧縮してデータを返す指定がなされていてはならず、
		//identiferなどを指定する必要があるが、XMLHTTPRequestは勝手にgzip, deflateとつけてしまい、
		//また、XMLHTTPRequestのsetRequestHeaderはAccept-Encodingの変更が不可能らしい。
		//よって、差分取得は未対応とする。
		//headers["Accept-Encoding"] = "identity";
		//headers["Range"] = "bytes=" + this.datInfo.getrequset_range() + "-";
		
		document.getElementById("resbodys").innerHTML = "<span id='waiting'><b>読み込み中...</b></span>";
		var count = 3;
		
		this.timerid = window.setInterval(function () {
			count++;
			var str = "読み込み中";
			for(var i=0; i < count; i++)
			{
				str += ".";
			}
			
			if(count > 20)
			{
				count = 0;
			}
			
			document.getElementById("resbodys").innerHTML = "<span id='waiting'><b>" + str + "</b></span>";
		}, 100);
		
		http_get(this.update_dat, [this, parseopt], url, mimetype, headers);
	}
};
window.onload = function () { init(); };

function init()
{	
	var message = document.getElementById("message");
	standardize(message);
	message.addEventListener("keydown", 
		function (evt) {
					if(!evt)
					{
						var evt = event;
					}
					if(evt.keyCode == 16){ KeyState.message.shiftkey = true; } 
					if((evt.keyCode == 13)  && (KeyState.message.shiftkey == true))
					{ 
						evt.preventDefault();
						threadinfo.res_post(true);
						return false;
					} }, true);
	message.addEventListener("keyup",
		function (evt) { 
					if(!evt)
					{
						var evt = event;
					}
					if(evt.keyCode == 16) {KeyState.message.shiftkey = false; } }, true);
					

	var match = location.pathname.match(/^.*(\/test\/read.html)/g);
	threadinfo.urlbase = match[0].replace("test/read.html", "");
	
	var thisFile = location.pathname.match(/^.*\/test\/read.html/);
	var pathstr = location.pathname.replace(thisFile, "");
	var pathinfo = pathstr.split("/");

	if(pathinfo.length < 4)
	{
		window.alert("URLの形式が不正です。");
		return;
	}
	
	threadinfo.bbs = pathinfo[1];
	threadinfo.thread = pathinfo[2];
	threadinfo.option = decodeURIComponent(pathinfo[3]);
	
	document.getElementById("name").value = Cookie.get("FROM");
	document.getElementById("e-mail").value = Cookie.get("mail");
	
	threadinfo.ajax_getdat(true);
}
