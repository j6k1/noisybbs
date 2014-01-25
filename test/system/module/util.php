<?php
	class Util
	{
		var $weekday;
		var $id_cnv_tbl;
		
		function Util()
		{
			$this->weekday = array("<font color=red>日</font>", "月", "火", "水", "木", "金", "<font color=blue>土</font>");

			$id_cnv_tbl_val = 0x30;
			$this->id_cnv_tbl = array();
		
			for($i=0; $i < 64 ; $i++){
				if($i == 10){
					$id_cnv_tbl_val = 0x41;
				}else if($i == 36){
					$id_cnv_tbl_val = 0x61;
				}else if($i == 62){
					$id_cnv_tbl_val = 0x2E;
				}
				$this->id_cnv_tbl[$i] = $id_cnv_tbl_val;
				$id_cnv_tbl_val++;
			}
		}

		function &getInstance()
		{
			return Singleton::getInstance("Util");
		}
		
		function file_lock($fp)
		{
			$ret = flock($fp,LOCK_EX);
			return $ret;
		}
		
		function file_unlock($fp)
		{
			$ret = flock($fp,LOCK_UN);
			return $ret;
		}
		
		function getline_count($txt)
		{
			if($txt == "")
			{
				return 0;
			}
			
			$cnt = preg_match('/\n/',$txt);
			$cnt++;
			
			return $cnt;
		}
		
		function isoneline($txt)
		{
			if(preg_match('/\n|\r/',$txt) > 0){
				return false;
			}
			return true;
		}
		
		function Isintstring($val)
		{
			if(preg_match('/^\d+$/', $val) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function readenv($key, $envlist, $charset, $ifnotfound_to_exit = false)
		{
			if(preg_match('/^'. $key . ' *= *([^\n]*)$/um', $envlist, $match))
			{
				if(preg_match('/^\d*$/', $match[1]) == 0)
				{
					$match[1] = mb_convert_encoding($match[1], $charset, 'UTF-8');
				}
				return $match[1];
			}
			else
			{
				if($ifnotfound_to_exit == false)
				{
					return null;
				}
				else
				{
					echo "キー{$key}が見つかりませんでした。";
					exit;
				}
			}
		}
	
		function readenvs($envlist, $charset, &$result)
		{
			$envlines = explode("\n", $envlist);
			
			foreach($envlines as $line)
			{
				if(preg_match('/^([^ =]+) *= *([^\n]*)$/u', $line, $match) == 0)
				{
					continue;
				}
				else
				{
					$result[$match[1]] = $match[2];
				}
				
				if(count($match) > 0)
				{
					if(preg_match('/^\d*$/', $result[$match[1]]) == 0)
					{
						$result[$match[1]] = mb_convert_encoding($result[$match[1]], $charset, 'UTF-8');
					}
				}
				
				$match = null;
			}
		}
	
		function updateenv($key, $val, &$envlist)
		{
			if(preg_match('/^'. $key . ' *= *([^\n]*)$/um', $envlist))
			{
				$envlist = preg_replace('/^'. $key . ' *= *([^\n]*)$/um', $key . '=' . $val, $envlist);
			}
			else
			{
				return false;
			}
			return true;
		}
		
		function addenv($key, $val, &$envlist)
		{
			$envlist .= "{$key}={$val}\n";
			return true;
		}

		function cnv_bbs_date($datetime, $usec = null)
		{
			$Util = Util::getInstance();
	
			if(isset($usec))
			{
				$usec = substr($usec, 1, 3);
			}
			else
			{
				$usec = "";
			}
			
			$date = date("Y/m/d", $datetime)."(".$Util->weekday[date("w", $datetime)].")".date(" H:i:s", $datetime).$usec;
		
			return $date;
		}
		
		function getcappass(&$mail)
		{
			$pass = strstr($mail, '#');
			
			if(!isset($pass))
			{
				return null;
			}
			
			if($pass == null)
			{
				return null;
			}
			
			if($pass == "")
			{
				return null;
			}
			
			$pass = substr($pass, 1);
			$mail = substr($mail, 0, strpos($mail, '#'));
			
			return $pass;
		}
		
		function bbshtmlspecialchars($str)
		{
			$str = str_replace('<', '&lt;',$str);	
			$str = str_replace('>', '&gt;',$str);	
			$str = str_replace('\'','&#39;',$str);
			$str = str_replace('"','&quot;',$str);
		
			return $str;
		}
	
		function adminhtmlspecialchars($str)
		{
			$str = str_replace('&', '&amp;', $str);
			$str = Util::bbshtmlspecialchars($str);
			
			return $str;
		}
		
		function datspecialchars($msgbody)
		{
			$msgbody = str_replace('<>', '&lt;&gt;', $msgbody);
			$msgbody = str_replace(array("\r\n", "\n"), "<br>", $msgbody);
			
			return $msgbody;
		}
		
		function msgbody_escape($msgbody)
		{
			$msgbody = Util::bbshtmlspecialchars($msgbody);	
			$msgbody = preg_replace('/\r\n/','<br>',$msgbody);
			$msgbody = preg_replace('/\r/','<br>',$msgbody);
			$msgbody = preg_replace('/\n/','<br>',$msgbody);
			$msgbody = str_replace(",", "&#44;", $msgbody);				//カンマを変換
	
			return $msgbody;
		}
	
		function adminResHtmlSpecialChars($str)
		{
			$sjis_valid_none_ctrl_char_reg = '(?:[\x20-\x7E\xA1-\xDF]|(?:[\x81-\x9F\xE0-\xEF](?:[\x40-\x7E\x80-\xFC])))';
			$sjischar_reg = '(?:[\x00-\x1F\x20-\x7E\x7F\xA1-\xDF]|(?:[\x81-\x9F\xE0-\xEF](?:[\x40-\x7E\x80-\xFC]|[\x00-\x3F\x7F\xFD-\xFF]))|(?:[\x80\xA0\xF0-\xFF][\x00-\xFF]))';
			$stringreg_dquoets = "(?:\"(?:\\x5c\\x5c|\\x5c{$sjischar_reg}|[^\"])*\")";
			$stringreg_squoets = "(?:'(?:\\x5c\\x5c|\\x5c{$sjischar_reg}|[^'])*')";

			if(preg_match('/^([^\<]+)\z/', $str))
			{
				return preg_replace('/^([^\<]+)\z/',
					create_function('$match', 
						'return Util::bbshtmlspecialchars($match[0]);'),
					$str);
			}
			
			$str = preg_replace_callback('/^([^\<]+)\</', 
				create_function('$match', 
					'return Util::bbshtmlspecialchars($match[1]) . "<";'),
				$str);

			$str = preg_replace_callback(
				"/\\<((?:{$stringreg_dquoets}|{stringreg_squoets}|[^\\>])*)\\</",
				create_function('$match', 
					'return "&lt;" .Util::bbshtmlspecialchars($match[1]) . "<";'),
				$str);
			
			$str = preg_replace_callback(
				"/\\<((?:{$stringreg_dquoets}|{stringreg_squoets}|[^\\>])*)\\z/",
				create_function('$match', 
					'return "&lt;" .Util::bbshtmlspecialchars($match[1]);'),
				$str);

			$str = preg_replace_callback(
				"/\\<((?:{$stringreg_dquoets}|{stringreg_squoets}|[^\\>])*)\\>/",
				array("Util", "adminResHtmlSpecialCharsInner"),
				$str);

			return $str;
		}
		
		function adminResHtmlSpecialCharsInner($match)
		{
			$sjis_valid_none_ctrl_char_reg = '(?:[\x20-\x7E\xA1-\xDF]|(?:[\x81-\x9F\xE0-\xEF](?:[\x40-\x7E\x80-\xFC])))';

			if(preg_match("/^{$sjis_valid_none_ctrl_char_reg}+\z/",
				$match[0]) == 0)
			{
				return Util::bbshtmlspecialchars($match[0]);
			}

			if($match[1] == "br" || $match[1] == "br/")
			{
				return "<{$match[1]}>";
			}
			else
			{
				$tagbody = Util::adminhtmlspecialchars($match[1]);
				return "&lt;{$tagbody}&gt;";
			}
		}
		
		function gen_id($str_base)
		{
			$Util = Util::getInstance();
	
			$hash = 0;
			$out_str = "";
			$wk_str = md5($str_base);
			
			for($i=0 ; $i < 16 ; $i++){
				$key[$i] = substr($wk_str,$i*2,2);
			}
			
			for($i=0; $i < 8 ; $i++){
				$hash = ($hash * 37) + (int)$key[$i];
			}
	
			$in_str_h = $hash & 0xffffff;
			
			$hash = 0;
			
			for($i=8; $i < 16 ; $i++){
				$hash = ($hash * 37) + (int)$key[$i];
			}
	
			$in_str_l = $hash & 0xffffff;
			
			for($i=0; $i < 4 ; $i++){
				$out_str .=  chr($Util->id_cnv_tbl[$in_str_h & 0x3F]);
				$in_str_h = $in_str_h >> 6;
			}
			
			for($i=0; $i < 4 ; $i++){
				$out_str .=  chr($Util->id_cnv_tbl[$in_str_l & 0x3F]);
				$in_str_l = $in_str_l >> 6;
			}		
				
			return $out_str;
		}

		function gen_trip($key)
		{
			$salt=substr("{$key}H.", 1, 2);
			$salt=strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
			$salt=preg_replace("/[^\.\/0-9A-Za-z]/", ".", $salt);
			$trip = substr(crypt($key, $salt), -10);
			
			return $trip;
		}

		function gen_trip_nver($key)
		{
			$salt=substr("{$key}HG", 1, 2);
			$salt=strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");

			$salt = preg_replace_callback('/[\x80-\xFF]/',
				create_function('$match', '$chr = unpack("C", $match[0]); return chr($chr[1] - 0x80);'), 
				$salt);
			
			$salt = preg_replace_callback('/[\x7B-\x7F]/', 
				create_function('$match', '$chr = unpack("C", $match[0]); return chr($chr[1] - 0x4D);'), 
				$salt);
		
			$salt = preg_replace_callback('/[\x14-\x2D]/', 
				create_function('$match', '$chr = unpack("C", $match[0]); return chr($chr[1] + 0x4D);'), 
				$salt);
		
			$salt = preg_replace_callback('/[\x00-\x13]/', 
				create_function('$match', '$chr = unpack("C", $match[0]); return chr($chr[1] + 0x47);'), 
				$salt);
			
			$trip = substr(crypt($key, $salt), -10);
			
			return $trip;
		}
		
		function appendfwrite($fname, $data)
		{
			$errmsg = ErrMessage::getInstance();
			$Util = Util::getInstance();
			
			if(file_exists("{$fname}") == false)
			{
				return Logging::generrinfo($Util,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$fname}が見つかりません。");
			}
			
			$fp = @fopen($fname, "a");
			
			if($fp === false)
			{
				return Logging::generrinfo($Util,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileopen_ng} File={$fname}");
			}
			
			if(@fwrite($fp, $data) === false)
			{	
				return Logging::generrinfo($Util,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fwrite_ng} File={$fname}");
			}

			if(@fclose($fp) === false)
			{
				return Logging::generrinfo($Util,
					__FUNCTION__ , __LINE__ , 
					"{$errmsg->fileclose_ng} File={$fname}");
			}
			
			return true;
		}

		function getfilelist( $dir , &$i = 0 ) 
		{ 
	    	if( !is_dir( $dir ) ) {   // ディレクトリでなければ false を返す 
	        	return false; 
			}
	
	    	$list = array();    // 戻り値用の配列 
	
			if( $handle = opendir( $dir ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if( is_dir( "$dir/$file" ) ) {
	                    	// ディレクトリならcontinue 
	                    	continue;
						} else {
	      					// ファイルならばパスを格納 
	                    	$list[$i] = "$dir/$file";
							$i++; 
	            		} 
	        		} 
	    		}
	       		closedir( $handle ); 
			}
			
			return $list;
		}
	
		function cnvpreghostid($host)
		{
			$preg_host = Util::cnvloghostid($host);
					
			return preg_quote($preg_host, "/");
		}
		
		function cnvloghostid($host)
		{
			$host = htmlspecialchars($host);
			
			return $host;
		}
		
		function getP2UserId($ipaddr, $useragent)
		{
			$setting = SettingInfo::getInstance();
	
			if(isset($setting->p2ipaddrs) == false)
			{
				return null;
			}
			
			if(!in_array($ipaddr, $setting->p2ipaddrs))
			{
				return null;
			}
			
			if(preg_match('/p2-user-hash: ([0-9a-z]+)/' ,
				 $useragent , $match)) {
				
				return $match[1];
			}
			
			return null;
		}
		
		function getP2IP($ipaddr, $useragent)
		{
			$setting = SettingInfo::getInstance();
	
			if(isset($setting->p2ipaddrs) == false)
			{
				return null;
			}
			
			if(!in_array($ipaddr, $setting->p2ipaddrs))
			{
				return null;
			}
			
			if(preg_match('/p2-client-ip: ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/' ,
				 $useragent , $match)) {
				
				return $match[1];
			}
			
			return null;
		}
		
		function getP2BBM($ipaddr, $useragent)
		{
			$setting = SettingInfo::getInstance();
	
			if(isset($setting->p2ipaddrs) == false)
			{
				return null;
			}
			
			if(!in_array($ipaddr, $setting->p2ipaddrs))
			{
				return null;
			}
			
			if(preg_match('/p2-bbm: ([^ \)]+)/' ,
				$useragent , $match)) {
				
				return $match[1];
			}
			
			return null;
		}
		
		function getmoblieid($useragent, $carrier)
		{
			$mobileid = array();
			$mobileid["uniq"] = null;
			$mobileid["model"] = null;
			
			if($carrier == "A") {
				if(isset($_SERVER['HTTP_X_UP_SUBNO']))
				{
					$mobileid["uniq"] = $_SERVER['HTTP_X_UP_SUBNO'];
				}
			}
			else if($carrier == "D") {
				if(isset($_SERVER['HTTP_X_DCMGUID']))
				{
					$mobileid["uniq"] = $_SERVER['HTTP_X_DCMGUID'];
				}
				else if(preg_match('/; *icc([^\)]+)/', 
					 $useragent , $match)) {
					$mobileid["uniq"] = $match[1];	 
				}
				else if(preg_match('/\/ser([^;]+)/', 
					 $useragent , $match)) {
					$mobileid["uniq"] = $match[1];	 
				}
			}
			else if($carrier == "S") {
				if(isset($_SERVER['HTTP_X_JPHONE_UID']))
				{
					$mobileid["uniq"] = $_SERVER['HTTP_X_JPHONE_UID'];
				}
				else if(isset($_SERVER['x-jphone-uid']))
				{
					$mobileid["uniq"] = $_SERVER['x-jphone-uid'];
				}
				else if(isset($_SERVER['HTTP_X_JPHONE_MSNAME']))
				{
					$mobileid["model"] = $_SERVER['HTTP_X_JPHONE_MSNAME'];
				}
			}
			else if(Util::use_cookie_id($carrier))
			{
				if(isset($_COOKIE['uniqid']))
				{
					$mobileid["uniq"] = $_COOKIE['uniqid'];
				}
				else
				{
					$mobileid["uniq"] = Util::gen_id_seed_cookie();
				}
			}
			return $mobileid;
		}
		
		function use_cookie_id($carrier)
		{
			if( ($carrier = "SI") ||
				($carrier = "E") )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function cnvip_to_int($strip)
		{
			$ipinfo = explode('/', $strip);
			
			$strip = $ipinfo[0];
			
			if(count($ipinfo) > 1)
			{
				$msk = (int)$ipinfo[1];
			} 
			else
			{
				$msk = 32;
			}
			
			$ipbyte = explode('.', $strip);
			
			$ip = (int)$ipbyte[0];
			
			for($i=1 ; $i < 4 ; $i++) {
				$ip = $ip << 8;
				$ip |= $ipbyte[$i];
			}
			
			$result = array("ip" => $ip, "msk" => $msk);
			
			return $result;
		}
		
		function intip_cmp($sip, $dip, $ipmsk)
		{
			$ipmskval = 0x80000000;			
			
			for($i=1 ; $i < (int)$ipmsk; $i++) {
				$ipmskval = $ipmskval >> 1;
				$ipmskval |= 0x80000000;
			}
			$sip &= $ipmskval;
			$dip &= $ipmskval;
			
			if($sip == $dip) {
				return true;
			} else {
				return false;
			}
			
			return false;
		}
		
		function chkip_and_subnet($sip ,$dip)
		{
			$sipinfo = Util::cnvip_to_int($sip);
			$dipinfo = Util::cnvip_to_int($dip);
			
			$sip = $sipinfo["ip"];
			$dip = $dipinfo["ip"];
			
			if($sipinfo["msk"] < $dipinfo["msk"])
			{
				$msk = $sipinfo["msk"];
			}
			else
			{
				$msk = $dipinfo["msk"];
			}
			
			if(Util::intip_cmp($sip, $dip, $msk) == true) {
				return true;
			} else {
				return false;
			}
			
			return false;
		}
		
		function is_air_phone($intip, $agent)
		{
			if($agent == null)
			{
				return false;
			}
			
			$setting = SettingInfo::getInstance();

			$sip = $intip;
			$ipinfo = null;
			
			if(preg_match(
				'/((WILLCOM)(DDIPOCKET);)|(SHARP)|(WS\d+SH)/', 
				$agent) > 0)
			{
				if($setting->AIRPHONEIP_CHK != "1")
				{
					return true;
				}
			}
			else
			{
				return false;
			}
			
			if(isset($setting->air_phone_iplist) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					'air_phone_iplistが初期化されていません。。');				
			}
			
			$air_array = $setting->air_phone_iplist;
			
			foreach($air_array as $dip)
			{
				$ipinfo = cnvip_to_int($dip);
				
				if(Util::intip_cmp($sip, 
					$ipinfo["ip"], $ipinfo["msk"]) == true)
				{
					return true;
				}
			}
			
			return false;
		}
		
		function get_carrier_id($hostname)
		{
			if(preg_match('/.+docomo\.ne\.jp$/', $hostname) > 0)
			{
				return "D";
			}
			else if(preg_match('/.+ezweb\.ne\.jp$/', $hostname) > 0)
			{
				return "A";
			}
			else if( (preg_match('/.+jp-.\.ne\.jp$/', $hostname) > 0) ||
					 (preg_match('/.+\.vodafone\.ne\.jp$/', $hostname) > 0) ||
					 (preg_match('/.+disney\.ne\.jp$/', $hostname) > 0) )
			{
				return "S";
			}
			else if( (preg_match('/.+i\.softbank\.jp$/', $hostname) > 0) ||
					 (preg_match('/.+\.panda-world\.ne\.jp$/', $hostname) > 0) )
			{
				return "SI";
			}
			else if( (preg_match('/.+emobile\.ad\.jp$/', $hostname) > 0) ||
					 (preg_match('/.+e-mobile\.ne\.jp$/', $hostname) > 0) )
			{
				return "E";
			}
			
			return null;
		}
		
		function get_carrier_idfromip($intip)
		{
			$moblieiptbl = MobileIpTbl::getInstance();
			
			$docomo_array = $moblieiptbl->docomo;
			$au_array = $moblieiptbl->au;
			$sbm_array = $moblieiptbl->sbm;
			$air_array = $moblieiptbl->air;
			
			$sip = $intip;
			$ipinfo = null;
			
			foreach($docomo_array as $dip)
			{
				$ipinfo = Util::cnvip_to_int($dip);
				
				if(Util::intip_cmp($sip, 
					$ipinfo["ip"], $ipinfo["msk"]) == true)
				{
					return "D";
				}
			}

			foreach($au_array as $dip)
			{
				$ipinfo = cnvip_to_int($dip);
				
				if(Util::intip_cmp($sip, 
					$ipinfo["ip"], $ipinfo["msk"]) == true)
				{
					return "A";
				}
			}
			
			foreach($sbm_array as $dip)
			{
				$ipinfo = cnvip_to_int($dip);
				
				if(Util::intip_cmp($sip, 
					$ipinfo["ip"], $ipinfo["msk"]) == true)
				{
					return "S";
				}
			}
			
			foreach($air_array as $dip)
			{
				$ipinfo = cnvip_to_int($dip);
				
				if(Util::intip_cmp($sip, 
					$ipinfo["ip"], $ipinfo["msk"]) == true)
				{
					return "H";
				}
			}
			
			return null;
		}
		
		function gen_id_seed_cookie()
		{
			return uniqid(time(), true);
		}
		
		function Redirect($url)
		{
			header('Location: ' . $url);
			exit;
		}	
		
		function getBaseUrl()
		{
			preg_match('/^(.*\/test)\//', 
				$_SERVER['REQUEST_URI'], $match);
				
			return "http://{$_SERVER['HTTP_HOST']}{$match[1]}";
		}
		
		function getRootUrl()
		{
			$baseurl = Util::getBaseUrl();
			return preg_replace('/\/test$/', '', $baseurl);
		}
		
		function ConvertMobileLink($text)
		{
			$hostptn = '(:\/\/[-_.!~*\'()a-zA-Z0-9;?:\@&=+\$,%#]+)';
			return preg_replace('/((https?)' . $hostptn . ')[-_.!~*\'()a-zA-Z0-9;\/?:|\@&=+\$,%#]+/',
				'<a href="\0">\1</a>', $text);
		}
		
		function addLink($text)
		{
			$hostptn = '(:\/\/[-_.!~*\'()a-zA-Z0-9;?:\@&=+\$,%#]+)';
			return preg_replace('/((https?)' . $hostptn . ')[-_.!~*\'()a-zA-Z0-9;\/?:|\@&=+\$,%#]+/',
				'<a href="\0">\0</a>', $text);
		}
		
		function getBBSList()
		{
			return BBSList::getInstance()->getRows();
		}
		
		function getBBSNameList($bbskeys)
		{
			$result = array();
			
			foreach($bbskeys as $bbs)
			{
				$data = file_get_contents("../{$bbs}/SETTING.TXT");
				$data = mb_convert_encoding($data, "UTF-8", "SJIS");
				$result[$bbs] = Util::readenv("BBS_TITLE", $data, "SJIS");
			}
			
			return $result;
		}
		
		function ExtractFilesSub($root, $src, $dst)
		{
			if( $handle = opendir( "{$root}/{$src}" ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if(is_dir( "{$root}/{$src}/{$file}" ))
						{
							if(file_exists("{$dst}/{$src}/{$file}") == false)
							{
								
								if(mkdir("{$dst}/{$src}/{$file}", 0755) == false)
								{
									return Logging::generrinfo(Util::getInstance(),
										__FUNCTION__ , __LINE__ , 
										"ディレクトリ{$dst}/{$src}/{$file}の作成でエラーが発生しました。");
								}
								
								chmod("{$dst}/{$src}/{$file}", 0777);
								Util::ExtractFilesSub($root, "{$src}/{$file}", $dst);
							}
						}
						else
						{
							if(copy("{$root}/{$src}/{$file}", "{$dst}/{$src}/{$file}") == false)
							{
								return Logging::generrinfo(Util::getInstance(),
									__FUNCTION__ , __LINE__ , 
									"ファイル{$root}/{$src}/{$file}のコピーでエラーが発生しました。");
							}
						}
	        		} 
	    		}
	       		closedir( $handle );
			}
			
			return true;
		}
		
		function ExtractFiles($root, $dst)
		{
			if( $handle = opendir( $root ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if(is_dir( "{$root}/{$file}" ))
						{
							if(file_exists("{$dst}/{$file}") == false)
							{
								if(mkdir("{$dst}/{$file}", 0755) == false)
								{
									return Logging::generrinfo(Util::getInstance(),
										__FUNCTION__ , __LINE__ , 
										"ディレクトリ{$dst}/{$file}の作成でエラーが発生しました。");
								}
								chmod("{$dst}/{$file}", 0777);
								Util::ExtractFilesSub($root, $file, $dst);
							}
						}
						else
						{
							if(copy("{$root}/{$file}", "{$dst}/{$file}") == false)
							{
								return Logging::generrinfo(Util::getInstance(),
									__FUNCTION__ , __LINE__ , 
									"ファイル{$root}/{$file}のコピーでエラーが発生しました。");
							}
						}
	        		} 
	    		}
	       		closedir( $handle ); 
			}
			
			return true;
		}
		
		function RemoveFiles($dir)
		{
			if( $handle = opendir( $dir ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if(is_dir( "{$dir}/{$file}" ))
						{
							Util::RemoveFilesSub("{$dir}/{$file}");
						}
						else
						{
							if(unlink("{$dir}/{$file}") == false)
							{
								return Logging::generrinfo(Util::getInstance(),
									__FUNCTION__ , __LINE__ , 
									"ファイル{$dir}/{$file}の削除でエラーが発生しました。");
							}
						}
	        		} 
	    		}
	       		closedir( $handle );
			}
			
			return true;
		}
		
		function RemoveFilesSub($dir)
		{
			if( $handle = opendir( $dir ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if(is_dir( "{$dir}/{$file}" ))
						{
							Util::RemoveFilesSub("{$dir}/{$file}");
						}
						else
						{
							if(unlink("{$dir}/{$file}") == false)
							{
								return Logging::generrinfo(Util::getInstance(),
									__FUNCTION__ , __LINE__ , 
									"ファイル{$dir}/{$file}の削除でエラーが発生しました。");
							}
						}
	        		} 
	    		}
	       		closedir( $handle );
				
				if(rmdir($dir) == false)
				{
					return Logging::generrinfo(Util::getInstance(),
						__FUNCTION__ , __LINE__ , 
						"ディレクトリ{$dir}の削除でエラーが発生しました。");
				}
				
				return true;
			}
		}
		
		function EmptyString($str)
		{
			if((!isset($str)) || ((is_string($str)) && ($str == "")))
			{
				return true;
			}
			
			return false;
		}
		
		function convUnicodeToUTF16Surrogate($code)
		{
			$code = $code - 0x10000;
			$h = ($code >> 10) & 0x3FF;
			$l = $code & 0x3FF;
			$h |= (0x36 << 10);
			$l |= (0x37 << 10);
			
			return (($h << 16) | $l);
		}
		
		function valid_css_color_regexp()
		{
			static $regexp = null;
			if(isset($regexp)) return $regexp;
			
			$percent = '(?:100|[1-9]\d||\d)%';
			$digit = '(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)';
			$hex = '(?:[a-fA-F\d])';
			
			$percent_3 = strtr('(?: *{percent} *, *{percent} *, *{percent} *)', array("{percent}" => $percent));
			$digit_3 = strtr('(?: *{digit} *, *{digit} *, *{digit} *)', array("{digit}" => $digit));
			$percent_3_a = strtr('(?: *{percent} *, *{percent} *, *{percent} *, *(?:0\.\d|1(?:\.0)?|0) *)', array("{percent}" => $percent));
			$digit_3_a = strtr('(?: *{digit} *, *{digit} *, *{digit} *, *(?:0\.\d|1(?:\.0)?|0) *)', array("{digit}" => $digit));
			$angle = '(?:360|3[0-5]\d|[1-2]\d{2}|[1-9]\d|\d)';
			$hsl_args = strtr('(?: *{angle} *, *{percent} *, *{percent} *)', array("{angle}" => $angle, "{percent}" => $percent));
			$hsl_args_a = strtr('(?: *{angle} *, *{percent} *, *{percent} *, *(?:0\.\d|1(?:\.0)?|0))', array("{angle}" => $angle, "{percent}" => $percent));
			
			$regexp = <<<EOM
/^([a-zA-Z]+|#{hex}{3}|#{hex}{6}|rgb\({digit_3}\)|rgb\({percent_3}\)|rgba\({digit_3_a}\)|rgba\({percent_3_a}\)|hsl\({hsl_args}\)|hsla\({hsl_args_a}\))\z/
EOM;
			$regexp = strtr($regexp, array(
				"{hex}" => $hex,
				"{digit_3}" => $digit_3,
				"{percent_3}" => $percent_3,
				"{digit_3_a}" => $digit_3_a,
				"{percent_3_a}" => $percent_3_a,
				"{hsl_args}" => $hsl_args,
				"{hsl_args_a}" => $hsl_args_a
			));
			return $regexp;
		}
		
		function valid_css_color_val($cssColor)
		{
			if(preg_match(Util::valid_css_color_regexp(), $cssColor) == 0)
			{
				return "none";
			}
			else
			{
				return $cssColor;
			}
		}
	}
?>