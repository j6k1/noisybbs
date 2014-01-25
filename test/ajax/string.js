(function () {
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
})();
