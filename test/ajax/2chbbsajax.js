(function (undefined) {
	var isMSIE = /*@cc_on!@*/false;
	
	function BBSDat (thread) {
		this.thread = thread;
		this.ajax = new Ajax();
		this.init();
	}
	
	(function (p) {
		p.init = function () {
			this.data = "";
			this.lastmodified = "Thu, 01 Jun 1970 00:00:00 GMT";
		};
		p.renderHTML = function (options) {
			var html = [];
			var lines = this.datdata.getResLines();
			var rescnt;
			
			if(options.end == null)
			{
				options.end = lines.length;
			}
			
			rescnt = lines.length;
		
			if((options.first == true) && (options.start > 1))
			{
				html.push(lines[0].createResLine(1));
			}
			
			for(var i = (options.start - 1) ; ((i < options.end) && (i < rescnt)) ; i++)
			{
				if(lines[i] == "")
				{
					break;
				}
				
				html.push(lines[i].createResLine(i+1));
			}
			
			return html.join("\n");
		};
		
		p.requestDat = function (callback) {
			var url = this.thread.createDatURL();
			var self = this;
			
			headers["Content-Type"] = "text/plain; charset=x-sjis";
			headers["If-Modified-Since"] = this.lastmodified;
			
			this.ajax.get(function (url,) {
				callback: function (data, status) {
					if(status !== 200 && status !== 304)
					{
						callback.call(self.thread, false, "通信エラーが発生しました。");
					}
					else if(status === 200)
					{
						self.data = data;
						var lastmodified;
						
						if(!(lastmodified = ajax.getResponseHeader("Last-Modified")))
						{
							callback.call(self.thread, false, "datの最終更新日時が取得できませんでした。");
						}
						else
						{
							self.lastmodified = lastmodified;
							callback.call(self.thread, true, "");
						}
					}
				},
				headers: headers
			});
		};
	})(BBSDat.prototype);
	
	function BBSThread (paths) {
		this.requestcomp = true;
		this.shiftkey = false;

		var pathinfo = paths.split("/");

		var match = window.location.pathname.match(/^.*(\/test\/read.html)/g);
		
		this.urlbase = match[0].replace("test/read.html", "");
		this.bbs = pathinfo[1];
		this.key = pathinfo[2];
		this.options = this.parseOptions(decodeURIComponent(pathinfo[3]));
		
		var self = this;
		
		var onKeyDown = function (e) {
			var key = self.keyCode(e);
			
			if(key === 16) self.shiftkey = true;
			if(key === 13 && self.shiftkey)
			{
				e.preventDefault();
				self.postRes();
			}
		};
		
		if(window.opera)
		{
			$("#message").on("keypress", function (e) {
				return onKeyDown(e);
			});
		}
		else
		{
			$("#message").on("keydown", function (e) {
				return onKeyDown(e);
			});
		}
		
		$("#message").on("keyup", function (e) {
			var key = self.keyCode(e);
			if(key === 16) self.shiftkey = false;
		});
		
		$("#name").val(Cookie.get("FROM"));
		$("#e-mail").val(Cookie.get("mail"));
		
		this.datdata = new BBSDat(this);
		this.ajax = new Ajax();
		
		this.loadThread(function () {
			this.terminateLoadingView();
			this.render();
		});
	}
	
	(function (p) {
		p.keyCode = function (e) {
			if(document.all) return e.originalEvent.keyCode;
			else if(document.getElementById) return (e.originalEvent.keyCode) ? e.originalEvent.keyCode : e.originalEvent.charCode;
			else if(document.layers) return e.originalEvent.which;
			else return 0;
		};
		p.createDatURL = function () {
			return (this.bbs + "/dat/" + this.key + ".dat?" + (new Date()).getTime());
		};
		p.createPostURL = function () {
			return (this.urlbase + "test/bbs.cgi?guid=On";);
		};
		p.loadThread = function (callback) {
			if(this.requestcomp == false)
			{
				window.alert("通信中です...");
				return;
			}
			
			this.requestcomp = false;
			
			
			this.datdata.requestDat(function (result, message) {
				if(!result) {
					alert(message);
				}
				else
				{
					callback.call(this);
				}
			});
		};
		p.postRes = function () {
			var self = this;
			var params = $("#resparams");
		
			this.startSendingPage();
			
			this.ajax.post(this.createPostURL(), {
				callback: function (data, status) {
					self.terminateSendingView();
				
					var params = $("#resparams");
					if( (status == 200) && (/<!-- 2ch_X:error -->/.test(data)) )
					{
						msgwindow = window.open();
						msgwindow.document.write(data);
					}
					params.find("#message").val("");
					
					self.options.end = null;
				},
				params: {
					bbs: bbs,
					key: key,
					FROM: params.find("input[name='FROM']").val(),
					mail: params.find("input[name='mail']").val(),
					MESSAGE: params.find("#message").val(),
					submit: "書き込む",
					utn: "utn",
					time: params.find("input[name='time']").val(),
					suka: params.find("input[name='suka']").val()
				},
				headers: {
					"Referer": this.urlbase + "/" + this.bbs + "/",
					"Content-Type": "application/x-www-form-urlencoded"
				},
				encoder: EscapeSJIS
			});
			
			this.loadThread(function () {
				this.render();
			});
		};
		p.reload = function () {
			this.loadThread():
		};
		p.startLoadingView = function () {
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
				
				$("#resbodys").html("<span id='waiting'><b>" + str + "</b></span>");
			}, 100);
		};
		p.terminateLoadingView = function () {
			window.clearInterval(this.timerid);
		};
		p.startSendingPage = function () {
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
				
				$("#resbodys").html("<span id='waiting'><b>" + str + "</b></span>");
			}, 100);
		};
		p.terminateSendingView = function () {
			window.clearInterval(this.timerid);
		};
		render : function() {
			$("#resbodys").html(this.datdata.renderHTML(this.options);
			var lines = this.datdata.getResLines();
			var subject = lines[0].split("<>")[4];
			
			if(subject == undefined)
			{
				subject = 'スレタイ不明';
			}
			
			$("title").html(subject);
			$("#subject").html(subject);
			var date = new Date();
			var speed = (lines.length - 1) / (Math.floor(date.getTime() / 60000) - Math.floor(this.key / 60)) * 60 * 24;
			$("#speed").html("<strong>Speed:" + speed + "</strong>");
			$(#"size").html(this.datdata.data.length - 1);
		};
		p.parseOptions = function(options) {
			var reslines = this.datdata.data.getResLines();
			
			if(/^\|(\d+)n?$/.test(options))
			{
				var match = options.match(/\d+/g);
				
				if(/n$/.test(options))
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
			else if(/^(\d+)-(\d+)n?$/.test(options))
			{
				var match = options.match(/\d+/g);
				
				if(/n$/.test(options))
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
			else if(/^-(\d+)n?$/.test(options))
			{
				var match = options.match(/\d+/g);
				
				if(/n$/.test(options))
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
			else if(/^(\d+)-n?$/.test(options))
			{
				var match = options.match(/\d+/g);
				
				if(/n$/.test(options))
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
			else if(/^(\d+)n?$/.test(options))
			{
				var match = options.match(/\d+/g);
				
				if(/n$/.test(options))
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
			else if(/^$/.test(options))
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
			
		};
	})(BBSThread.prototype);
	
	window.BBSDat = BBSDat;
	window.BBSThread = BBSThread;
})();
