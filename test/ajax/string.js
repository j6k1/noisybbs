(function (undefined) {
	String.fromTemplate = function () {
		var lines = Array.prototype.slice.call(arguments, 0),
			params = lines.pop();
		
		if(Object.prototype.toString.call(params) === "[object String]")
		{
			throw new Error("プレースホルダ置換用のパラメータが渡されていないか、型が不正です。");
		}
		else if(!params)
		{
			return lines.join("\n").replace(/{!(!*(\w|\d+))}/g, function (m, m1) {
				return "{" + m1 + "}";
			});
		}
		else if(Object.prototype.toString.call(params) === "[object Array]")
		{
			return lines.join("\n").replace(/{!(!*(\w|\d+))}|{(\w+)}|{(\d+)}/g, function (m, m1, m2, m3, m4) {
				if(m2) return "{" + m2 + "}";
				else if(m3) return "";
				else if(m4) return params[parseInt(m4)];
				else throw new Error("ProgramError.");
			});
		}
		else if(Object.prototype.toString.call(params) === "[object Object]")
		{
			return lines.join("\n").replace(/{!(!*(\w|\d+))}|{(\w+)}|{(\d+)}/g, function (m, m1, m2, m3, m4) {
				if(m2) return "{" + m2 + "}";
				else if(m3) return params[m3];
				else if(m4) return "";
				else throw new Error("ProgramError.");
			});
		}
	};
	String.prototype.createResLine = function (num) {
		
		var fields = this.split("<>");
		
		if(fields.length < 4)
		{
			for(var i=0; i < 4; i++)
			{
				fields[i] = "ここ壊れてます"
			}
		}
		
		return String.fromTemplate(
			'<dt id="a{num}">{num} ： <span class="name"><b>{name}</b></span> <span class="info">[{mail}] ： {dateid}</span></dt>',
			'<dd>{body}</dd>',
		{
			num: num,
			name: fields[0],
			mail: fields[1],
			dateid: fields[2],
			body: fields[3].replace(
				/(<[^>]*>)|(((https?:)(\/\/[-_.!~*\'()a-zA-Z0-9;?:\@&=+\$,%#]+))[-_.!~*\'()a-zA-Z0-9;\/?:|\@&=+\$,%#]+)/g,
				function (str, tag, url) {
					if(tag) return tag;
					url = url.replace(/&/g, "&amp;");
					return '<a href="' + url + '">' + url + '</a>';
				}
			).replace(/&gt;&gt;((\d+)(\-\d+)?)/, function (m, m1, m2, m3) {
				return '<a href="ID/' + m1 + '">' + m + '</a>';
			})
		});
	};
	
	String.prototype.getResLines = function () {
		return this.split("\n");
	};
})();
