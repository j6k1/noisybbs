<?php
//2012 03/19 �d�l�ύX
//�N���̃g���K�����[���A�h���X����"!htmltag"�ɕύX�B
//[color=(params)]{body}[/color]
//[size=(params)]{body}[/size]�̋L�q��<span style="params...">{body}</span>
//�ɐ��K�\���Œu�������������A���X�g�ɂ���html�^�O�݂̂�
///on...��������菜���Ă��̂܂ܔ��f����������ɕύX�B
//�Ȃ��A�l�X�g�����������i�^�O�����Ă��Ȃ��j�^�O�̓G�X�P�[�v����d�l�Ƃ����B
//2012 03/19 end
//--------------
//2012 04/20 IE���L��XSS�Ǝ㐫�ɑ΂���΍��ǉ��B
//style�����̊e�p�����[�^�̒l�ɂ��āA
//�R�����g�������폜������A���l�����Q�Ƃƃo�b�N�X���b�V���R�[�h��
//�o�C�i���ɕϊ��A����ɒʏ��SJIS����������UTF-16BE�ɕϊ���A
//�S�p�p���𔼊p�p���iUTF-16BE�j�ɕϊ����A
//expression��������url�Ŏn�܂��Ă����炻�̒l�ƃL�[�̃y�A���󕶎����
//�u������悤�ɂ����B
//�Ȃ��A�o�b�N�X���b�V���R�[�h�ɂ�CSS1�d�l��CSS2�d�l������A
//�����Ɍ݊������Ȃ����߁A�����̃��[���ŉ��߂��Ăǂ��炩����̌��ʂ�
//expression��������url�Ŏn�܂��Ă�����NG�Ƃ���悤�ɁB
//�܂��AIE�̈ꕔ�ɂ���}���`�o�C�g�����̈ꕔ�Ƀo�b�N�X���b�V��(\x5c)���܂�
//�R�[�h��16�i���������������o�b�N�X���b�V���R�[�h�Ƃ݂Ȃ��d�l�ɑΉ����邽�߁A
//���̓���d�l�ɂ��ƂÂ���͌��ʂ�expression��������url�Ŏn�܂��Ă����ꍇ��
//NG�Ƃ���悤�ɂ����B
//2012 04/20 end
//2012 12/01
//html���l�����Q�Ƌy�уo�b�N�X���b�V���R�[�h��
//�T���Q�[�g�y�A�͈͂̃R�[�h�ɂ��āA3�o�C�g�o�C�i��������ɕϊ����Ă��̂�
//UTF-16�T���Q�[�g�y�A��4�o�C�g�����ɕϊ�����悤�ɏC���B
//decodeToUnicodeStringIEBug�̃R�[���o�b�N��decodeToUnicodeStringInnerIEBug
//���w�肵�Ă��Ȃ������̂��C���B
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
			//�u���b�N��index��������
			$index = 0;
			//�^�O�u���b�N�ԍ��X�^�b�N������
			$tagstack = array();
			//�u���b�N�z�񏉊���
			$blocks = array();
			$i = 0;
			//�^�O���K�\��
			$tag_reg ="\\<((/?[^\"'\\>\\<\\s/]+)\\s*((?:{$this->stringreg_dquoets}|{$this->stringreg_squoets}|[^\\>])*))\\>";
			//�^�O�̒��̃^�O�J�n�L����T�����K�\��
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
		
		//unicode(UTF-16BE)�����̑S�p�p���𔼊p�p���ɕϊ�
		function mbAlphaNumericToAscii($str)
		{
			static $utf16be_valid_char = '(?:(?:[\x00-\xD7\xE0-\xFF][\x00-\xFF])|(?:[\xD8-\xDF][\x00-\xFF][\x00-\xFF]))';
		
			return preg_replace_callback(
				"#{$utf16be_valid_char}#",
				array($this, "mbAlphaNumericToAsciiInner"),
				$str);
		}

		//unicode(UTF-16BE)�����̑S�p�p���𔼊p�p���ɕϊ�����ۂ�
		//preg_replace_callback�ɓn�����R�[���o�b�N���\�b�h
		function mbAlphaNumericToAsciiInner($match)
		{
			static $convtable_unicode = null;
			static $convtable = array(
				"�`" => "A",
				"�a" => "B",
				"�b" => "C",
				"�c" => "D",
				"�d" => "E",
				"�e" => "F",
				"�f" => "G",
				"�g" => "H",
				"�h" => "I",
				"�i" => "J",
				"�j" => "K",
				"�k" => "L",
				"�l" => "M",
				"�m" => "N",
				"�n" => "O",
				"�o" => "P",
				"�p" => "Q",
				"�q" => "R",
				"�r" => "S",
				"�s" => "T",
				"�t" => "U",
				"�u" => "V",
				"�v" => "W",
				"�w" => "X",
				"�x" => "Y",
				"�y" => "Z",
				"��" => "a",
				"��" => "b",
				"��" => "c",
				"��" => "d",
				"��" => "e",
				"��" => "f",
				"��" => "g",
				"��" => "h",
				"��" => "i",
				"��" => "j",
				"��" => "k",
				"��" => "l",
				"��" => "m",
				"��" => "n",
				"��" => "o",
				"��" => "p",
				"��" => "q",
				"��" => "r",
				"��" => "s",
				"��" => "t",
				"��" => "u",
				"��" => "v",
				"��" => "w",
				"��" => "x",
				"��" => "y",
				"��" => "z",
				"�O" => "0",
				"�P" => "1",
				"�Q" => "2",
				"�R" => "3",
				"�S" => "4",
				"�T" => "5",
				"�U" => "6",
				"�V" => "7",
				"�W" => "8",
				"�X" => "9"
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
