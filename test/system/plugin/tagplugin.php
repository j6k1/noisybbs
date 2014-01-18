<?php
//2012 03/19 仕様変更
//起動のトリガをメールアドレス欄に"!htmltag"に変更。
//[color=(params)]{body}[/color]
//[size=(params)]{body}[/size]の記述を<span style="params...">{body}</span>
//に正規表現で置換する方式から、リストにあるhtmlタグのみを
///on...属性を取り除いてそのまま反映させる方式に変更。
//なお、ネストがおかしい（タグが閉じていない）タグはエスケープする仕様とした。
//2012 03/19 end
//--------------
//2012 04/20 IE特有のXSS脆弱性に対する対策を追加。
//style属性の各パラメータの値について、
//コメント部分を削除した後、数値文字参照とバックスラッシュコードを
//バイナリに変換、さらに通常のSJIS文字部分をUTF-16BEに変換後、
//全角英数を半角英数（UTF-16BE）に変換し、
//expressionもしくはurlで始まっていたらその値とキーのペアを空文字列に
//置換するようにした。
//なお、バックスラッシュコードにはCSS1仕様とCSS2仕様があり、
//微妙に互換性がないため、両方のルールで解釈してどちらか一方の結果が
//expressionもしくはurlで始まっていたらNGとするように。
//また、IEの一部にあるマルチバイト文字の一部にバックスラッシュ(\x5c)を含む
//コードに16進数が続く部分をバックスラッシュコードとみなす仕様に対応するため、
//この動作仕様にもとづく解析結果がexpressionもしくはurlで始まっていた場合も
//NGとするようにした。
//2012 04/20 end
//2012 12/01
//html数値文字参照及びバックスラッシュコードの
//サロゲートペア範囲のコードについて、3バイトバイナリ文字列に変換してたのを
//UTF-16サロゲートペアの4バイト文字に変換するように修正。
//decodeToUnicodeStringIEBugのコールバックにdecodeToUnicodeStringInnerIEBug
//を指定していなかったのを修正。
//
//--------------
	class TagPlugin extends BBSPlugin
	{
		var $enable;
		var $enable_tags = array(
			"p",
			"span",
			"b",
			"del",
			"em",
			"hr",
			"ins",
			"strong"
		);
		
		var $sjis_valid_none_ctrl_char_reg;
		var $sjischar_reg;
		var $stringreg_dquoets;
		var $stringreg_squoets;
		var $html_entity_reg;
		var $backslash_code_css1_reg;
		var $backslash_code_css2_reg;
		
		var $MESSAGE;
		
		function TagPlugin()
		{
			$this->enable = false;
			$this->sjis_valid_none_ctrl_char_reg = '(?:[\x20-\x7E\xA1-\xDF]|(?:[\x81-\x9F\xE0-\xEF](?:[\x40-\x7E\x80-\xFC])))';
			$this->sjischar_reg = '(?:[\x00-\x1F\x20-\x7E\x7F\xA1-\xDF]|(?:[\x81-\x9F\xE0-\xEF](?:[\x40-\x7E\x80-\xFC]|[\x00-\x3F\x7F\xFD-\xFF]))|(?:[\x80\xA0\xF0-\xFF][\x00-\xFF]))';
			$this->stringreg_dquoets = "(?:\"(?:\\x5c\\x5c|\\x5c{$sjischar_reg}|[^\"])*\")";
			$this->stringreg_squoets = "(?:'(?:\\x5c\\x5c|\\x5c{$sjischar_reg}|[^'])*')";
			$this->html_entity_reg = '(?:(?:&#x([\da-fA-F]+);)|(?:&#(\d+);))';
			$this->backslash_code_css1_reg = '(?:\x5c([\da-fA-F]{1,4}))';
			$this->backslash_code_css2_reg = '(?:\x5c([\da-fA-F]{1,6}) ?)';
			
			$this->MESSAGE = "";
		}
		
		function ExecWriteBefore(&$writedata)
		{
			if(preg_match('/!htmltag/', $writedata->mail))
			{
				$this->enable = true;
			}
			
			$this->MESSAGE = $writedata->MESSAGE;
			
			return true;
		}
		
		function ExecWriteAfter(&$writedata)
		{
			if($this->enable == false)
			{
				return true;
			}
			
			$body = $this->MESSAGE;
			$len = strlen($body);
			//ブロックのindexを初期化
			$index = 0;
			//タグブロック番号スタック初期化
			$tagstack = array();
			//ブロック配列初期化
			$blocks = array();
			$i = 0;
			//タグ正規表現
			$tag_reg ="\\<((/?[^\"'\\>\\<\\s/]+)\\s*((?:{$this->stringreg_dquoets}|{$this->stringreg_squoets}|[^\\>])*))\\>";
			//タグの中のタグ開始記号を探す正規表現
			$tagstart_reg ="((?:{$this->stringreg_dquoets}|{$this->stringreg_squoets}|[^\\<])*)\\<";
			
			while($i < $len)
			{
				if(preg_match(
					"#{$tag_reg}#m", 
					$body, $match, PREG_OFFSET_CAPTURE, $i))
				{					
					if($match[0][1] > $i)
					{
						$blocks[$index++] = Util::msgbody_escape(
							substr($body, $i, $match[0][1] - $i));
					}
					
					$i = $match[0][1];
					
					if(preg_match("/^({$this->sjis_valid_none_ctrl_char_reg}+)\z/",
							$match[0][0]))
					{
						if(preg_match("#^{$tagstart_reg}#", $match[1][0], $submatch))
						{
							$blocks[$index++] = Util::msgbody_escape("<{$submatch[1]}");
							$i += strlen($submatch[1]) + 1;
							continue;
						}
						
						if(preg_match('#^\</([^\'"\>\<\s/]+)\>$#', $match[0][0], $namematch))
						{
							if(count($tagstack) == 0)
							{
								$blocks[$index++] = Util::msgbody_escape($match[0][0]);
							}
							else
							{
								$lastelm = array_pop($tagstack);
								
								$elmvalue = $blocks[$lastelm["index"]];
								if($lastelm["name"] != $namematch[1])
								{
									$escapelm = Util::msgbody_escape($elmvalue);
									$blocks[$lastelm["index"]] = $escapelm;
									$blocks[$index++] = Util::msgbody_escape($match[0][0]);
								}
								else if(strlen($elmvalue) > strlen($lastelm["name"] + 2))
								{
									$elm = substr($elmvalue,
										 1 + strlen($lastelm["name"]), 
										 strlen($elmvalue) - 2 - strlen($lastelm["name"])
									);
									$elm = $this->removeCssID($elm);
									$elm = strtr($this->removeJSEvent($elm), array("<" => "&lt;", ">" => "&gt;"));
									$blocks[$lastelm["index"]] = "<{$lastelm['name']}{$elm}>";
									$blocks[$index++] = $match[0][0];
								}
							}
						}
						else if(in_array($match[2][0], $this->enable_tags))
						{
							if(preg_match('#^\<' . preg_quote($match[2][0], "#") . '\s*/\>$#', $match[0][0]))
							{
								$blocks[$index++] = $match[0][0];
							}
							else
							{
								$tagstack[] = array(
									"name" => $match[2][0], "index" => $index);
								$blocks[$index++] = $match[0][0];
							}
						}
						else
						{
							$blocks[$index++] = Util::msgbody_escape($match[0][0]);
						}
					}
					else
					{
						$blocks[$index++] = Util::msgbody_escape($match[0][0]);
					}
					
					$i += strlen($match[0][0]);
				}
				else
				{
					break;
				}
			}
			
			if(count($tagstack) > 0)
			{
				foreach($tagstack as $elm)
				{
					$blocks[$elm["index"]] = Util::msgbody_escape($blocks[$elm["index"]]);
				}
			}
			
			if($i < $len)
			{
				$blocks[$index] = Util::msgbody_escape(
					substr($body, $i, $len - $i));
			}
			
			$writedata->MESSAGE = implode("", $blocks);
				
			return true;
		}
		
		function removeCssID($tagbody)
		{
			$regexp = "((?:(\\s*)([^='\"\\>\\<\\s]+)(?:\\s*=\\s*)";
			$regexp .= "(?:({$this->stringreg_dquoets}|{$this->stringreg_squoets})|";
			$regexp .= "(?:[^\\s/]+))))|";
			$regexp .= "({$this->stringreg_dquoets}|{$this->stringreg_squoets})|";
			$regexp .= "(\\s*[^'\"\\>\\<\\s]\\s*)";
			
			return preg_replace_callback(
				"#{$regexp}#",
				array($this, "removeCssIDInner"),
				$tagbody);
		}
		
		function removeCssIDInner($match)
		{
			if(!Util::EmptyString($match[3]))
			{
				if(preg_match('/^[iI][dD]/', $match[3]))
				{
					return "";
				}
			}
			
			return $match[0];
		}
		
		function removeJSEvent($tagbody)
		{
			$regexp = "((?:(\\s*)([^='\"\\>\\<\\s]+)(?:\\s*=\\s*)";
			$regexp .= "(?:({$this->stringreg_dquoets}|{$this->stringreg_squoets})|";
			$regexp .= "(?:[^\\s/]+))))|";
			$regexp .= "({$this->stringreg_dquoets}|{$this->stringreg_squoets})|";
			$regexp .= "(\\s*[^'\"\\>\\<\\s]\\s*)";
			
			return preg_replace_callback(
				"#{$regexp}#",
				array($this, "removeJSEventInner"),
				$tagbody);
		}

		function removeJSEventInner($match)
		{
			if(!Util::EmptyString($match[3]))
			{
				if(preg_match('/^[oO][nN]/', $match[3]))
				{
					return "";
				}
				else if($match[3] == "style")
				{
					if($match[4] == '""' || $match[4] == "''")
					{
						return "";
					}
					else if(preg_match('/^[\'"]/', $match[4]))
					{
						$quote_len = 1;
					}
					else
					{
						$quote_len = 0;
					}
					
					$regexp_list = array();
					$regexp_list[] = $this->html_entity_reg;
					$regexp_list[] = '(?:[\x00-\x3A\x3C-\x7F\xA1-\xDF])';
					$regexp_list[] = "(?:[\x81-\x9F\xE0-\xEF][\x40-\x7E\x80-\xFC])";
					
					if(preg_match_all('/(' . implode("|", $regexp_list) . ')+/', 
						substr($match[4], $quote_len, strlen($match[4]) - (1 + $quote_len)), $match_prms) == 0)
					{
						return "";
					}
					
					$params = $match_prms[0];
					
					foreach($params as $i => $param)
					{
						if(strpos($param, ":") === false)
						{
							$params[$i] = "";
						}	
						else
						{
							$params[$i] = $this->removeIEXSSValue($param);
						}
					}
					
					if(!Util::EmptyString(implode("", $params)))
					{
						$stylevalue = implode(";", $params);
					
						return $match[2] . 'style="' . $stylevalue . '"';
					}
					else
					{
						return "";
					}
				}
			}
			else if(!Util::EmptyString($match[5]))
			{
				return "";
			}
			
			return $match[0];
		}
		
		function removeIEXSSValue($str)
		{
			list(, $value) = explode(":", $str);
			
			$value = trim($value);
			$value = preg_replace('#/\*(?:(?:(?:\*(?!/))|[^\*])*)\*/#', '', $value);
			
			$cnvvalue = $this->decodeToUnicodeStringIEBug(
				$value,
				$this->backslash_code_css1_reg);
				
			$cnvvalue = $this->mbAlphaNumericToAscii($cnvvalue);

			$cnvvalue = $this->mbAlphaStrLower($cnvvalue);
			
			$expression_reg = preg_quote(mb_convert_encoding(
				"expression", "UTF-16BE", "SJIS-win"), "/");

			$url_reg = preg_quote(mb_convert_encoding(
				"url", "UTF-16BE", "SJIS-win"), "/");
			
			if(preg_match("/^(?:{$expression_reg}|{$url_reg})/", $cnvvalue))
			{
				return "";
			}
			
			$cnvvalue = $this->decodeToUnicodeStringIEBug(
				$value,
				$this->backslash_code_css2_reg);
				
			$cnvvalue = $this->mbAlphaNumericToAscii($cnvvalue);
			
			$cnvvalue = $this->mbAlphaStrLower($cnvvalue);

			if(preg_match("/^(?:{$expression_reg}|{$url_reg})/", $cnvvalue))
			{
				return "";
			}

			$cnvvalue = $this->decodeToUnicodeString(
				$value,
				$this->backslash_code_css1_reg);
				
			$cnvvalue = $this->mbAlphaNumericToAscii($cnvvalue);
			
			$cnvvalue = $this->mbAlphaStrLower($cnvvalue);

			if(preg_match("/^(?:{$expression_reg}|{$url_reg})/", $cnvvalue))
			{
				return "";
			}
			
			$cnvvalue = $this->decodeToUnicodeString(
				$value,
				$this->backslash_code_css2_reg);
				
			$cnvvalue = $this->mbAlphaNumericToAscii($cnvvalue);
			
			$cnvvalue = $this->mbAlphaStrLower($cnvvalue);

			if(preg_match("/^(?:{$expression_reg}|{$url_reg})/", $cnvvalue))
			{
				return "";
			}
			
			return $str;
		}
		
		function decodeToUnicodeStringIEBug($str, $backslash_code_reg)
		{
			$regexp_list = array();
			$regexp_list[] = $this->html_entity_reg;
			$regexp_list[] = $backslash_code_reg;
			$regexp_list[] = '([\x00-\x7F\xA1-\xDF])';
			$regexp_list[] = "(([\x81-\x9F\xE0-\xEF])(?:{$backslash_code_reg}))";
			$regexp_list[] = "([\x81-\x9F\xE0-\xEF][\x40-\x7E\x80-\xFC])";
			
			return preg_replace_callback(
				"/" . implode("|", $regexp_list) . "/",
				array($this, "decodeToUnicodeStringInnerIEBug"),
				$str);
		}
		
		function decodeToUnicodeStringInnerIEBug($match)
		{
			if(!Util::EmptyString($match[1]))
			{
				$unicode = hexdec($match[1]);
				
				if(!is_int($unicode))
				{
					return "";
				}
				else if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}
			else if(!Util::EmptyString($match[2]))
			{
				$unicode = intval($match[2]);
				
				if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}
			else if(!Util::EmptyString($match[3]))
			{
				$unicode = hexdec($match[3]);
				
				if(!is_int($unicode))
				{
					return "";
				}
				else if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}	
			else if(!Util::EmptyString($match[5]))
			{
				$sjis_first = $match[6];
				$unicode = hexdec($match[7]);

				if($unicode <= 0xFFFF)
				{
					$unicode = hexdec(sprintf("%04X", $unicode));
					
					$unicode = pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else  if($unicode <= 0x10FFFF)
				{
					$unicode = hexdec(sprintf("%06X", $unicode));

					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				}
				else
				{
					return "";
				}
				
				return (mb_convert_encoding(
					$sjis_first, "UTF-16BE", "SJIS-win") . $unicode);
			}
			else
			{
				return mb_convert_encoding($match[0], "UTF-16BE", "SJIS-win");
			}
		}

		function decodeToUnicodeString($str, $backslash_code_reg)
		{
			$regexp_list = array();
			$regexp_list[] = $this->html_entity_reg;
			$regexp_list[] = $backslash_code_reg;
			$regexp_list[] = '([\x00-\x7F\xA1-\xDF])';
			$regexp_list[] = "([\x81-\x9F\xE0-\xEF][\x40-\x7E\x80-\xFC])";
			
			return preg_replace_callback(
				"/" . implode("|", $regexp_list) . "/",
				array($this, "decodeToUnicodeStringInner"),
				$str);
		}
		
		function decodeToUnicodeStringInner($match)
		{
			if(!Util::EmptyString($match[1]))
			{
				$unicode = hexdec($match[1]);
				
				if(!is_int($unicode))
				{
					return "";
				}
				else if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}
			else if(!Util::EmptyString($match[2]))
			{
				$unicode = intval($match[2]);
				
				if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}
			else if(!Util::EmptyString($match[3]))
			{
				$unicode = hexdec($match[3]);
				
				if(!is_int($unicode))
				{
					return "";
				}
				else if($unicode <= 0xFFFF)
				{
					return pack('C2', ($unicode >> 8), ($unicode & 0xFF));
				}
				else if($unicode <= 0x10FFFF)
				{
					$utf16bin = Util::convUnicodeToUTF16Surrogate($unicode);
					return pack('C4', 
						(($utf16bin >> 24) & 0xFF), 
						(($utf16bin >> 16) & 0xFF), 
						(($utf16bin >> 8) & 0xFF), ($utf16bin & 0xFF));
				
				}
				else
				{
					return "";
				}
			}
			else
			{
				return mb_convert_encoding($match[0], "UTF-16BE", "SJIS-win");
			}
		}
		
		//unicode(UTF-16BE)文字の全角英数を半角英数に変換
		function mbAlphaNumericToAscii($str)
		{
			static $utf16be_valid_char = '(?:(?:[\x00-\xD7\xE0-\xFF][\x00-\xFF])|(?:[\xD8-\xDF][\x00-\xFF][\x00-\xFF]))';
		
			return preg_replace_callback(
				"#{$utf16be_valid_char}#",
				array($this, "mbAlphaNumericToAsciiInner"),
				$str);
		}

		//unicode(UTF-16BE)文字の全角英数を半角英数に変換する際に
		//preg_replace_callbackに渡されるコールバックメソッド
		function mbAlphaNumericToAsciiInner($match)
		{
			static $convtable_unicode = null;
			static $convtable = array(
				"Ａ" => "A",
				"Ｂ" => "B",
				"Ｃ" => "C",
				"Ｄ" => "D",
				"Ｅ" => "E",
				"Ｆ" => "F",
				"Ｇ" => "G",
				"Ｈ" => "H",
				"Ｉ" => "I",
				"Ｊ" => "J",
				"Ｋ" => "K",
				"Ｌ" => "L",
				"Ｍ" => "M",
				"Ｎ" => "N",
				"Ｏ" => "O",
				"Ｐ" => "P",
				"Ｑ" => "Q",
				"Ｒ" => "R",
				"Ｓ" => "S",
				"Ｔ" => "T",
				"Ｕ" => "U",
				"Ｖ" => "V",
				"Ｗ" => "W",
				"Ｘ" => "X",
				"Ｙ" => "Y",
				"Ｚ" => "Z",
				"ａ" => "a",
				"ｂ" => "b",
				"ｃ" => "c",
				"ｄ" => "d",
				"ｅ" => "e",
				"ｆ" => "f",
				"ｇ" => "g",
				"ｈ" => "h",
				"ｉ" => "i",
				"ｊ" => "j",
				"ｋ" => "k",
				"ｌ" => "l",
				"ｍ" => "m",
				"ｎ" => "n",
				"ｏ" => "o",
				"ｐ" => "p",
				"ｑ" => "q",
				"ｒ" => "r",
				"ｓ" => "s",
				"ｔ" => "t",
				"ｕ" => "u",
				"ｖ" => "v",
				"ｗ" => "w",
				"ｘ" => "x",
				"ｙ" => "y",
				"ｚ" => "z",
				"０" => "0",
				"１" => "1",
				"２" => "2",
				"３" => "3",
				"４" => "4",
				"５" => "5",
				"６" => "6",
				"７" => "7",
				"８" => "8",
				"９" => "9"
			);
			
			if(!isset($convtable_unicode))
			{
				foreach($convtable as $key => $val)
				{
					$key = mb_convert_encoding($key , "UTF-16BE", "SJIS-win");
					$val = mb_convert_encoding($val , "UTF-16BE", "SJIS-win");
					
					$convtable_unicode[$key] = $val;
				}
			}
			
			if(isset($convtable_unicode[$match[0]]))
			{
				return $convtable_unicode[$match[0]];
			}
			
			return $match[0];
		}
		
		function mbAlphaStrLower($str)
		{
			static $utf16be_valid_char = '(?:(?:[\x00-\xD7\xE0-\xFF][\x00-\xFF])|(?:[\xD8-\xDF][\x00-\xFF][\x00-\xFF]))';
		
			return preg_replace_callback(
				"#{$utf16be_valid_char}#",
				array($this, "mbAlphaStrLowerInner"),
				$str);
		}

		function mbAlphaStrLowerInner($match)
		{
			static $convtable_unicode = null;
			static $convtable = array(
				"A" => "a",
				"B" => "b",
				"C" => "c",
				"D" => "d",
				"E" => "e",
				"F" => "f",
				"G" => "g",
				"H" => "h",
				"I" => "i",
				"J" => "j",
				"K" => "k",
				"L" => "l",
				"M" => "m",
				"N" => "n",
				"O" => "o",
				"P" => "p",
				"Q" => "q",
				"R" => "r",
				"S" => "s",
				"T" => "t",
				"U" => "u",
				"V" => "v",
				"W" => "w",
				"X" => "x",
				"Y" => "y",
				"Z" => "z"
			);

			if(!isset($convtable_unicode))
			{
				foreach($convtable as $key => $val)
				{
					$key = mb_convert_encoding($key , "UTF-16BE", "SJIS-win");
					$val = mb_convert_encoding($val , "UTF-16BE", "SJIS-win");

					$convtable_unicode[$key] = $val;
				}
			}
			
			if(isset($convtable_unicode[$match[0]]))
			{
				return $convtable_unicode[$match[0]];
			}
			
			return $match[0];
		}
	}
?>
