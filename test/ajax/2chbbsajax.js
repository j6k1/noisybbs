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
			var lines = this.data.getResLines();
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
			var headers = {};
			
			headers["Content-Type"] = "text/plain; charset=x-sjis";
			headers["If-Modified-Since"] = this.lastmodified;
			
			this.ajax.get(url, {
				callback: function (data, status) {
					if(status === 404 || status === 302)
					{
						callback.call(self.thread, false, "�Y������X���b�h�͍폜���ꂽ���A���݂��܂���B");
					}
					else if(status !== 200 && status !== 304)
					{
						callback.call(self.thread, false, "�ʐM�G���[���������܂����B");
					}
					else if(status === 200)
					{
						self.data = data;
						var lastmodified;
						
						if(!(lastmodified = self.ajax.getResponseHeader("Last-Modified")))
						{
							callback.call(self.thread, false, "dat�̍ŏI�X�V�������擾�ł��܂���ł����B");
						}
						else
						{
							self.lastmodified = lastmodified;
							callback.call(self.thread, true, "");
						}
					}
					else
					{
						callback.call(self.thread, true, "");
					}
				},
				headers: headers
			});
		};
	})(BBSDat.prototype);
	
	function Rooter () {
		
	};
	
	Rooter.dispatch = function (thread, paths) {
		var	bbs = pathinfo[1],
			key = pathinfo[2],
			options = pathinfo[3] || "";
		
		if(thread.bbs != bbs || thread.key != key) thread.datdata.init();
		
		thread.bbs = bbs;
		thread.key = key;
		
		thread.loadThread(function () {
			this.terminateLoadingView();
			this.parseOptions(decodeURIComponent(options));
			this.render();
		}, function () {
			if(this.requestcomp == false)
			{
				window.alert("�ʐM���ł�...");
				return false;
			}
			return true;
		});
	};
	
	Rooter.readPath = function (src) {
		if(src.test(/^\/(:bbs)\/index.html$/)) return src;
		else if(src.test(/^#!ID\/(\d+)(-\d+)?$/)) return src.substr(2);
		return src;
	};
	
	(function (p) {
		
	})(Rooter.prototype);
	
	function BBSThread (paths) {
		this.requestcomp = true;
		this.shiftkey = false;

		var match = window.location.pathname.match(/^.*(\/test\/read.html)/g);
		
		this.urlbase = match[0].replace("test/read.html", "");
		
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
		
		$("#resparams").submit(function () {
			e.preventDefault();
			self.postRes();
			
			return false;
		});
		
		$("#name").val(Cookie.get("FROM"));
		$("#e-mail").val(Cookie.get("mail"));
		
		this.datdata = new BBSDat(this);
		this.ajax = new Ajax();
		
		this.startLoadingView();
		Rooter.dispatch(this, paths.split("/"));
		
		$("a").on("click", function (e) {
			var m,
				path = Rooter.readPath($(this).attr("src"));
				
			if((m = path.match(/^ID\/(\d+)(-\d+)?$/)))
			{
				if(!m[2]) window.location.hash = "a" + m[2];
			}
			else if((m = path.match(/^\/(:bbs)\/index.html$/)))
			{
				e.preventDefault();
				window.location.href = self.urlbase + self.bbs + "/index.html";
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/(:pastfrom)-(:pastto)?$/)))
			{
				e.preventDefault();
				var bbs = self.bbs,
					key = self.key,
					options = self.pastfrom + "-" + self.pastto;
				Rooter.dispatch(self, m);
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/(:nextfrom)-(:nextto)?$/)))
			{
				e.preventDefault();
				var bbs = self.bbs,
					key = self.key,
					options = self.nextfrom + "-" + self.nextto;
				Rooter.dispatch(self, m);
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/([^\/]+)?$/)))
			{
				e.preventDefault();
				var bbs = self.bbs,
					key = self.key,
					options = m[3];
				Rooter.dispatch(self, m);
			}
			else if((m = path.match(/^\/([^\/]+)\/([^\/]+)\/([^\/]+)?$/)))
			{
				e.preventDefault();
				var bbs = m[1],
					key = m[2],
					options = m[3];
				Rooter.dispatch(self, m);
			}
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
			return (this.urlbase + this.bbs + "/dat/" + this.key + ".dat?" + (new Date()).getTime());
		};
		p.createPostURL = function () {
			return (this.urlbase + "test/bbs.cgi?guid=On");
		};
		p.loadThread = function (callback, beforeLoad) {
			if(beforeLoad && !beforeLoad.call(this)) return;
			
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
		
			this.startSendingView();
			
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
					self.loadThread(function () {
						this.render();
					});
				},
				params: {
					bbs: self.bbs,
					key: self.key,
					FROM: params.find("input[name='FROM']").val(),
					mail: params.find("input[name='mail']").val(),
					MESSAGE: params.find("#message").val(),
					submit: "��������",
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
		};
		p.reload = function () {
			this.startLoadingView();
			this.loadThread(function () {
				this.terminateLoadingView();
				this.render();
			});
		};
		p.startLoadingView = function () {
			var count = 3;
			
			this.timerid = window.setInterval(function () {
				count++;
				var str = "�ǂݍ��ݒ�";
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
		p.startSendingView = function () {
			var count = 3;
			
			this.timerid = window.setInterval( function () {
				count++;
				var str = "�������ݒ�";
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
		p.render = function() {
			$("#resbodys").html(this.datdata.renderHTML(this.options));
			var lines = this.datdata.data.getResLines();
			var subject = lines[0].split("<>")[4];
			
			if(subject == undefined)
			{
				subject = '�X���^�C�s��';
			}
			
			$("title").html(subject);
			$("#subject").html(subject);
			var date = new Date();
			var speed = (lines.length - 1) / (Math.floor(date.getTime() / 60000) - Math.floor(this.key / 60)) * 60 * 24;
			$("#speed").html("<strong>Speed:" + speed + "</strong>");
			$("#size").html(this.datdata.data.length - 1);
		};
		p.parseOptions = function(options) {
			var reslines = this.datdata.data.getResLines();
			
			this.options = {};
			
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
				window.alert("�I�v�V�����w�肪�s���ł��B");
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
