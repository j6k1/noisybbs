var Cookie = (function () {
	var Cookie = function () {
	};

	(function () {
		var cookies = document.cookie;
		
		cookies = cookies.split(";");
		
		Cookie.values = {};
		
		for(var i=0, len = cookies.length ; i < len; i++)
		{
			var pair = cookies[i].replace(/^ +| +$/, "").split("=");
			
			Cookie.values[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
		}
	})();
	
	Cookie.get = function (name) {
		if(typeof Cookie.values[name] === "undefined") return "";
		else return Cookie.values[name];
	};
	
	Cookie.set = function (name, value, expires) {
		if(typeof expires === "undefined") expires = 0;
		if(!(expires instanceof Date)) 
		{
			var expires = (function (sec) {
				var expires = new Date();
				expires.setTime(sec);
				return expires;
			})(expires);
		}
		
		document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + "; " + "expires=" + expire.toUTCString();
	}
	
	return Cookie;
})();
