(function (undefined) {
	function Ajax() {
		this.xhr = (function () {
			if(window.XMLHttpRequest) return new XMLHttpRequest();
			else {
				try {
					return new ActiveXObject("MSXML2.XMLHTTP 6.0");
				} catch (e) {};
				try {
					return new ActiveXObject("MSXML2.XMLHTTP 3.0");
				} catch (e) {};
				
				try {
					return new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {};
				throw new Error("XMLHttpRequestに未対応のブラウザです。");
			}
		})();
	};
	(function (p) {
		p.getResponseHeader = function (name) {
			if(this.xhr.getAllResponseHeaders().match(name))
			{
				return this.xhr.getResponseHeader(name);
			}
			else
			{
				return undefined;
			}
		};
		p.send = function (url, method, options) {
			var callback;
			if(options && options.callback) callback = options.callback;
			else callback = function () {};
			
			var xhr = this.xhr;
			var self = this;
			
			xhr.onreadystatechange = function () {
				if(xhr.readyState == 4)
				{
					callback.call(self, xhr.responseText, xhr.status);
				}
			};
			
			var params;
			
			if(options && options.params) 
			{
				if(Object.prototype.toString.call(options.params) === "[object Object]")
				{
					params = Ajax.buildQuery(options.params, options.encoder);
				}
				else if(Object.prototype.toString.call(options.params) === "[object String]")
				{
					params = options.params;
				}
				else
				{
					throw new Error("送信パラメータの型が不正です。");
				}
			}
			
			if(method != "POST" && method != "PUT")
			{
				params = null;
				if(url.indexOf("?") != -1) url += "?" + params;
				else url += "&" + params;
			}
			
			xhr.open(method, url);
			
			var headers = (function () {
				var headers;
				if(options && options.headers && self.headers)
				{
					headers = Array.prototype.slice.call(self.headers, 0);
					for(var key in options.headers) {
						headers[key] = options.headers[key];
					}
				}
				else if(options && options.headers)
				{
					headers = options.headers;
				}
				else if(self.headers)
				{
					headers = self.headers;
				}
				else
				{
					headers = {};
				}
				
				return headers;
			})();
			
			for(var key in headers)
			{
				xhr.setRequestHeader(key, headers[key]);
			}
			
			xhr.send(params);
		};
		p.get = function (url, options) {
			return this.send(url, "GET", options);
		};
		p.post = function (url, options) {
			return this.send(url, "POST", options);
		};
	})(Ajax.prototype);
	
	Ajax.buildQuery = function (params, encoder) {
		var pairs = [];
		
		if(!encoder) encoder = encodeURIComponent;
		
		for(var key in params)
		{
			pairs.push(encoder(key) + "=" + encoder(params[key]));
		}
		
		return pairs.join("&");
	};
	
	window.Ajax = Ajax;
})();