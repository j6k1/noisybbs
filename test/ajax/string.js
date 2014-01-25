(function (undefined) {
	String.fromTemplate = function () {
		var lines = Array.prototype.slice.call(arguments, 0),
			params = args.pop();
		
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
		
		var fileds = this.split("<>");
		
		if(fileds.length < 4)
		{
			for(var i=0; i < 4; i++)
			{
				fileds[i] = "ここ壊れてます"
			}
		}
		
		return String.fromTemplate(
			'<dt id="a{num}">{num} ： <span class="name"><b>{name}</b></span> <span class="info">：{mail}</span></dt>',
			'<dd>{body}</dd>
		{
			num: num,
			name: fileds[0],
			mail: fileds[1],
			body: fileds[2]
		});
	};
	
	String.prototype.getResLines = function () {
		return this.split("\n");
	};
})();
