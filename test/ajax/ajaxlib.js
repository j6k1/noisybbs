var XmlHttpHelper = {
	getResponseText : function(xmlhttp) { return xmlhttp.responseText; },
	getLastModefied : function (xmlhttp) {
		if(xmlhttp.status == 0)
		{
			return null;
		}
		
		if(xmlhttp.getAllResponseHeaders().match("Last-Modified"))
		{
			return xmlhttp.getResponseHeader("Last-Modified");
		}	
		else
		{
			return null;
		}
	}
	,
	getContentLength : function (xmlhttp) {
		if(xmlhttp.status == 0)
		{
			return null;
		}
		
		if(xmlhttp.getAllResponseHeaders().match("Content-Length"))
		{
			return xmlhttp.getResponseHeader("Content-Length");
		}
		else
		{
			return null;
		}
	}
};
function standardize(node) {
    if (!node.addEventListener)
        node.addEventListener = function(t, l, c) { this["on"+t] = l; };
    if (!node.dispatchEvent)
        node.dispatchEvent = function(e) { this["on"+e.type](e); };
}
if(!window.XMLHttpRequest)
{
	if(window.ActiveXObject)
	{
		XMLHttpRequest = function() {
			try{
				return new ActiveXObject("Msxml2.XMLHTTP");
			}catch(e){
				try{
					return new ActiveXObject("Microsoft.XMLHTTP");
				}catch(e){
					return null;
				}
			}
		} 
	}
	else
	{
		XMLHttpRequest = function () { return null; };
	}
}
function createXmlHttp()
{	
	return new XMLHttpRequest();
}
function http_get(callback, args, url, mimetype, headers)
{	
	var x = createXmlHttp();
	if(x == null){
		window.alert("XMLHttpRequest非対応のブラウザです。");
		return;
	}
	
	x.onreadystatechange=function(){
		if(x.readyState == 4){
			if(callback != null)
			{
				callback(x, args);
			}
		}
	}
	
	if(mimetype != null)
	{
		try {
			x.overrideMimeType(mimetype);
		}catch(e){
		
		}
	}
	
	x.open("GET", url , true);
	
	for (key in headers)
	{
		x.setRequestHeader(key,  headers[key]);//, false);
	}
	
	x.send(null);
}
function http_post(callback, args, url, mimetype, headers, data, encoder)
{	
	var x = createXmlHttp();
	
	if(encoder == null)
	{
		encoder = function (src) { return src; }
	}
	
	if(x == null){
		window.alert("XMLHttpRequest非対応のブラウザです。");
		return;
	}

	x.onreadystatechange=function(){
		if(x.readyState == 4){
			if(callback != null)
			{
				callback(x, args);
			}
		}
	}
	
	var postdata = "";
	var delim = "";
	
	for (postkey in data)
	{
		postdata +=  delim + postkey + "=" + encoder(data[postkey]);
		delim = "&";
	}
	
	if(mimetype != null)
	{
		try {
			x.overrideMimeType(mimetype);
		}catch(e){
		
		}
	}
	
	x.open("POST", url , true);
	
	for (key in headers)
	{
		x.setRequestHeader(key,  headers[key]);//, false);
	}

	x.send(postdata);
}
function ajax_uriencode(str)
{
	var uriencodestr = "";
	var atchar = "";
	var work = "";
	var buff = "";
	
	for (i=0; i < str.length; i++) {
		atchar = str.charCodeAt(i);
		if(atchar < 16)
		{
			uriencodestr += "%0" + atchar.toString(16).toUpperCase();
		}
		else if(
			((atchar != 45) && /* - */ (atchar != 95) && /* _ */ (atchar != 46)) && /* . */
		     (  ((atchar < 48) || (atchar > 57)) && ((atchar < 65) || (atchar > 90)) &&
		    	((atchar < 97) || (atchar > 122))
		  	 )
		  )
		{
			work = atchar.toString(16).toUpperCase();

			buff = "";
			for(j=0; j < work.length; j+= 2)
			{
				buff += "%" + work.substr(j, 2);
			}
			uriencodestr += buff;
		}
		else
		{
			uriencodestr += str.charAt(i);
		}
	}
	
	return uriencodestr
}
