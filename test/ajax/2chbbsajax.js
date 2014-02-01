(function (undefined) {
	var isMSIE = /*@cc_on!@*/false;
	
    (function () {
        var lastTime = (new Date()).getTime();;
        var vendors = ["ms", "moz", "webkit", "o"];
        for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
            window.requestAnimationFrame = window[vendors[x] + "RequestAnimationFrame"];
            window.cancelRequestAnimationFrame = window[vendors[x] + "CancelRequestAnimationFrame"]
        }
        if (!window.requestAnimationFrame) {
            window.requestAnimationFrame = function (callback, element) {
                var currTime = (new Date()).getTime();
                var timeToCall = 16 - (currTime - lastTime);
                if (timeToCall < 0) {
                    timeToCall = 0;
                }
                timeToCall = timeToCall % 16;
                var id = window.setTimeout(function () {
                    callback(currTime + timeToCall)
                }, timeToCall);
                lastTime = currTime + timeToCall;
                return id
            }
        }
        if (!window.cancelAnimationFrame) {
            window.cancelAnimationFrame = function (id) {
                clearTimeout(id)
            }
        }
    })();

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
						callback.call(self.thread, false, "該当するスレッドは削除されたか、存在しません。");
					}
					else if(status !== 200 && status !== 304)
					{
						callback.call(self.thread, false, "通信エラーが発生しました。");
					}
					else if(status === 200)
					{
						self.data = data;
						var lastmodified;
						
						if(!(lastmodified = self.ajax.getResponseHeader("Last-Modified")))
						{
							callback.call(self.thread, false, "datの最終更新日時が取得できませんでした。");
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
		var	bbs = paths[1],
			key = paths[2],
			options = paths[3] || "",
			anchor = paths[4] && /^!(.+)/.test(paths[4]) && paths[4].substr(1) || "";
		
		if(thread.bbs != bbs || thread.key != key) thread.datdata.init();
		
		thread.bbs = bbs;
		thread.key = key;
		
		thread.loadThread(function () {
			this.terminateLoadingView();
			this.parseOptions(decodeURIComponent(options));
			this.render();
			if(anchor) this.moveToAnchor(anchor);
		}, function () {
			if(this.requestcomp == false)
			{
				window.alert("通信中です...");
				return false;
			}
			return true;
		});
	};
	
	Rooter.readPath = function (src) {
		if(/^\/(:bbs)\/index.html$/.test(src)) return src;
		else if(/^#!ID\/(\d+)(-\d+)?$/.test(src)) return src.substr(2);
		else if(/^#!\/.*$/.test(src)) return src.substr(2);
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
		
		$("#resparams").submit(function (e) {
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
		
		$("body").on("click", "a", function (e) {
			var m,
				path = Rooter.readPath(
					this.getAttribute && this.getAttribute("href", 2) || $(this).attr("href")
				);

			if(path === "/:reload")
			{
				e.preventDefault();
				self.reload();
			}
			else if((m = path.match(/^ID\/(\d+)(-\d+)?$/)))
			{
				e.preventDefault();
				if(!m[2]) window.location.hash = "a" + m[1];
			}
			else if((m = path.match(/^\/(:bbs)\/index.html$/)))
			{
				e.preventDefault();
				window.location.href = self.urlbase + self.bbs + "/index.html";
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/(:pastfrom)-(:pastto)?$/)))
			{
				e.preventDefault();
				if(!self.options) return;
				var bbs = self.bbs,
					key = self.key,
					options = self.pastfrom + "-" + self.pastto;
				window.location.hash = "#!/" + bbs + "/" + key + "/" + options;
				Rooter.dispatch(self, ["", bbs, key, options]);
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/(:nextfrom)-(:nextto)?$/)))
			{
				e.preventDefault();
				if(!self.options) return;
				var bbs = self.bbs,
					key = self.key,
					options = self.nextfrom + "-" + self.nextto;
				window.location.hash = "#!/" + bbs + "/" + key + "/" + options;
				Rooter.dispatch(self, ["", bbs, key, options]);
			}
			else if((m = path.match(/^\/(:bbs)\/(:key)\/([^\/]+)?$/)))
			{
				e.preventDefault();
				var bbs = self.bbs,
					key = self.key,
					options = m[3] || "";
				window.location.hash = "#!/" + bbs + "/" + key + "/" + options;
				Rooter.dispatch(self, ["", bbs, key, options]);
			}
			else if((m = path.match(/^\/([^\/]+)\/([^\/]+)\/([^\/]+)?$/)))
			{
				var bbs = m[1],
					key = m[2],
					options = m[3] || "" ;
				window.location.hash = "#!/" + bbs + "/" + key + "/" + options;
				Rooter.dispatch(self, ["", bbs, key, options]);
			}
			else if(path === "#")
			{
				e.preventDefault();
			}
			else if((m = path.match(/^#(.+)/)))
			{
				e.preventDefault();
				if(typeof self.orgOptions === "undefined") return;
				var bbs = self.bbs,
					key = self.key,
					options = self.orgOptions || ":";
				window.location.hash = "#!/" + bbs + "/" + key + "/" + options + "/!" + m[1];
				Rooter.dispatch(self, ["", bbs, key, options, "!" + m[1]]);
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
		p.moveToAnchor = function (id) {
			if($("#" + id).length === 0) return;
			var p = $("#" + id).offset().top;
			$("html, body").animate({ scrollTop: p }, "fast");
		};
		p.createDatURL = function () {
			return (this.urlbase + this.bbs + "/dat/" + this.key + ".dat?" + (new Date()).getTime());
		};
		p.createPostURL = function () {
			return (this.urlbase + "test/bbs.cgi?guid=On");
		};
		p.openResponseWindow = function (message) {
			var self = this;
			$("#response-window-wrapper .inner").html($("#response-window-template").html());
			var wnd = $("#response-window");
			var m = message.match(/<body[^>]*>((.|\n|\r)*?)<\/body>/);
			$("#response-html").html(m[1]);
			var x = ($("#message").width() - wnd.width()) / 2;
			var y = ($("html").height() - ($("body").hasClass("ie6") ? wnd.innerHeight() : wnd.height())) / 2;
			var h = $("body").hasClass("ie6") ? wnd.innerHeight() : wnd.height();
			wnd.css({
				left: x,
				top: y
			});
			wnd.css("height", "0px");
			wnd.css("top", (y + h / 2) + "px");
			wnd.css("display", "block");
			
			var frame = 0;
			
			requestAnimationFrame(function enterFrame () {
				frame+=10;
				wnd.css({
					left: x,
					top: ((y + h / 2) - (h / 2 / (60 / frame)) + "px"),
					height: (h * (frame / 60)) + "px"
				});

				if(frame == 60)
				{
					$("#response-window-close").on("click", function (e) {
						self.closeResponseWindow();
					});
					var onKeyDown = function (e) {
						var key = self.keyCode(e);
						
						if(key === 13)
						{
							e.preventDefault();
							self.closeResponseWindow();
						}
					};
					
					if(window.opera)
					{
						$(document).one("keypress", function (e) {
							return onKeyDown(e);
						});
					}
					else
					{
						$(document).one("keydown", function (e) {
							return onKeyDown(e);
						});
					}
				}
				else
				{
					requestAnimationFrame(enterFrame);
				}
			});
		};
		p.closeResponseWindow = function () {
			var wnd = $("#response-window");
			var x = ($("#message").width() - wnd.width()) / 2;
			var y = ($(window).height() - wnd.height()) / 2;
			var h = wnd.height();
			var frame = 60;
			requestAnimationFrame(function enterFrame() {
				frame-=10;
				wnd.css({
					top: ((y + h / 2) - (h / 2 / (60 / frame)) + "px"),
					height: (h * (frame / 60)) + "px"
				});

				if(frame == 0)
				{
					$("#response-window").css("display", "none");
					$("#response-window").remove();
					$("#response-window").empty();
				}
				else
				{
					requestAnimationFrame(enterFrame);
				}
			});
		};
		p.loadThread = function (callback, beforeLoad) {
			if(beforeLoad && !beforeLoad.call(this)) return;
			
			this.requestcomp = false;
			
			
			this.datdata.requestDat(function (result, message) {
				this.requestcomp = true;
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
			if(this.requestcomp == false)
			{
				window.alert("通信中です...");
				return false;
			}
			
			var self = this;
			var params = $("#resparams");
		
			this.startSendingView();
			
			this.ajax.post(this.createPostURL(), {
				callback: function (data, status) {
					self.terminateSendingView();
				
					var params = $("#resparams");
					if( (status == 200) && (/<!-- 2ch_X:error -->/.test(data)) )
					{
						self.openResponseWindow(data);
					}
					params.find("#message").val("");
					
					self.options.end = null;
					self.loadThread(function () {
						this.render();
						var p = $("#footer").offset().top;
						$("html, body").css("scrollTop", p);
					});
				},
				params: {
					bbs: self.bbs,
					key: self.key,
					FROM: params.find("input[name='FROM']").val(),
					mail: params.find("input[name='mail']").val(),
					MESSAGE: params.find("#message").val(),
					submit: "書き込む",
					utn: "utn",
					time: params.find("input[name='time']").val(),
					suka: params.find("input[name='suka']").val()
				},
				headers: {
					//"Referer": this.urlbase + "/" + this.bbs + "/",
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
			}, function () {
				if(this.requestcomp == false)
				{
					window.alert("通信中です...");
					return false;
				}
				return true;
			});
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
		p.startSendingView = function () {
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
		p.render = function() {
			$("#resbodys").html(this.datdata.renderHTML(this.options));
			var lines = this.datdata.data.getResLines();
			var subject = lines[0].split("<>")[4];
			
			if(subject == undefined)
			{
				subject = 'スレタイ不明';
			}
			
			document.title = subject;
			$("#subject").html(subject);
			var date = new Date();
			var speed = (lines.length - 1) / (Math.floor(date.getTime() / 60000) - Math.floor(this.key / 60)) * 60 * 24;
			$("#speed").html("<strong>Speed:" + speed + "</strong>");
			$("#size").html(this.datdata.data.length - 1);
		};
		p.parseOptions = function(options) {
			var reslines = this.datdata.data.getResLines();
			this.orgOptions = options;
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
				
				this.options.start = Number(match[0]);
				this.options.end   = Number(match[1]);
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
				this.options.end   = Number(match[0]);
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
				
				this.options.start = Number(match[0]);
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
				
				this.options.start = Number(match[0]);
				this.options.end   = Number(match[0]);
			}
			else if(/^:?$/.test(options))
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
