<?php
	$SHOWCOUNT = 20;
	
	class KakoLogViewer
	{		
		function checkPostParam($year, $month, $day)
		{
			if($year == "----")
			{
				if($month != "--")
				{
					return "年を入力して下さい。";
				}
				else if($day != "--")
				{
					return "年と月を入力して下さい。";
				}
				else
				{
					return true;
				}
			}
			else if(($month == "--") && ($day != "--"))
			{
				return "月を入力して下さい。";
			}
			else
			{
				return true;
			}
		}
		
		function genPathInfoDate($year, $month, $day)
		{
			if(($year == "----") && ($month == "--") && ($day == "--"))
			{
				return "-";
			}
			else if($day != "--")
			{
				return "{$year}-" . sprintf("%02d", $month) . "-" . sprintf("%02d", $day);
			}
			else if($month != "--")
			{
				return "{$year}-" . sprintf("%02d", $month);
			}
			else
			{
				return "{$year}";
			}
		}
		
		function checkDateStrFormat($datestr)
		{
			if($datestr == "-")
			{
				return true;
			}
			
			if(preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = $match[2];
				$day = $match[3]; 
			}
			else if(preg_match('/^(\d{4})-(\d{2})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = $match[2];
				$day = null;
			}
			else if(preg_match('/^(\d{4})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = null;
				$day = null;
			}
			else
			{
				return "日付指定文字列のフォーマットが不正です。";
			}
			
			if(isset($month))
			{
				if(($month < 1) || ($month > 12))
				{
					return "月の指定が不正です。";
				}
			}
			
			if( (isset($month)) && (isset($day)) )
			{
				if( ($day < 1) || 
					($day > (date("t", strtotime("{$year}-{$month}-01"))) ) )
				{
					return "日の指定が不正です。";
				}
			}
			
			return true;
		}
		
		function DateStrtoTimeStamp($datestr, $mode)
		{
			if($datestr == "-")
			{
				return null;
			}
			
			if(preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = $match[2];
				$day = $match[3]; 
			}
			else if(preg_match('/^(\d{4})-(\d{2})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = $match[2];
				$day = null;
			}
			else if(preg_match('/^(\d{4})$/', $datestr, $match))
			{
				$year = $match[1];
				$month = null;
				$day = null;
			}
			
			if($mode == "start")
			{
				if(!isset($month))
				{
					$month = "01";
				}
				
				if(!isset($day))
				{
					$day = "01";
				}
				return strtotime("{$year}-{$month}-{$day}");
			}
			else if($mode == "end")
			{
				if(!isset($month))
				{
					$month = "12";
				}
				
				if(!isset($day))
				{
					$day = (date("t", strtotime("{$year}-{$month}-01")));
				}
				return (strtotime("{$year}-{$month}-{$day}") + (60 * 60 * 24));
			}
			else
			{
				return false;
			}
		}
		
		function OutPutErrHtml($msg, $backlink = null)
		{
			if(isset($backlink))
			{
				$backhtml = "<div><a href={$backlink}>戻る</a></div>";
			}
			else
			{
				$backhtml = "<div>ブラウザの「戻る」ボタンで戻って下さい。</div>";
			} 
			echo <<<EOM
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>エラー</title>
</head>
<body>
<div>{$msg}</div>
{$backhtml}
</body>
</html>

EOM;
			return true;
		}
	}
			
	require_once("inclueds.php");
	
	BBSList::getInstance()->Init();
	
	if(!isset($_SERVER["PATH_INFO"]))
	{
		KakoLogViewer::OutPutErrHtml("URLの形式が不正です。");
		exit;
	}
	
	$baseurl = Util::getBaseUrl();
	$rooturl = Util::getRootUrl();
	
	$pathinfo = explode("/", $_SERVER["PATH_INFO"]);
	$pathinfo_count = count($pathinfo);
	
	if($pathinfo_count < 2)
	{
		KakoLogViewer::OutPutErrHtml("URLの形式が不正です。");
		exit;
	}
	
	$bbs  = $pathinfo[1];
	
	if(in_array($bbs, Util::getBBSList()) == false)
	{
		KakoLogViewer::OutPutErrHtml("存在しない板キーが指定されました。");
		exit;
	}
	
	$page = ($pathinfo_count > 2) ? $page = $pathinfo[2] : $page = 1;
	
	if( ($page != "") && (preg_match('/^\d+$/', $page) === false) )
	{
		KakoLogViewer::OutPutErrHtml("URLの形式が不正です。", "{$rooturl}/{$bbs}/index.html");
		exit;
	}
	
	if(($page == "") || ($page < 1))
	{
		$page = 1;
	}
	
	$ret = SettingInfo::getInstance()->Init($bbs);

	if(ErrInfo::IsErr($ret))
	{
		KakoLogViewer::OutPutErrHtml($ret->sysmsg, "{$rooturl}/{$bbs}/index.html");
		exit;
	}

	$setting = SettingInfo::getInstance();
	
	$ret = HtmlList::getInstance()->Init();

	if(ErrInfo::IsErr($ret))
	{
		KakoLogViewer::OutPutErrHtml($ret->sysmsg, "{$rooturl}/{$bbs}/index.html");
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if( (!isset($_POST["start_year"])) || 
			(!isset($_POST["start_month"])) ||
			(!isset($_POST["start_day"])) )
		{
			KakoLogViewer::OutPutErrHtml("フォーム情報が不正です。", "{$rooturl}/{$bbs}/index.html");
			exit;
		}

		if( (!isset($_POST["end_year"])) || 
			(!isset($_POST["end_month"])) ||
			(!isset($_POST["end_day"])) )
		{
			KakoLogViewer::OutPutErrHtml("フォーム情報が不正です。", "{$rooturl}/{$bbs}/index.html");
			exit;
		}
		
		$start_date_errmsg = null;
		
		$result = KakoLogViewer::checkPostParam($_POST["start_year"], $_POST["start_month"], 
			$_POST["start_day"]);
		
		if($result !== true)
		{
			$start_date_errmsg = $result;
		}

		$end_date_errmsg = null;
		
		$result = KakoLogViewer::checkPostParam($_POST["end_year"], $_POST["end_month"], 
			$_POST["end_day"]);
		
		if($result !== true)
		{
			$end_date_errmsg = $result;
		}
		
		if((!isset($start_date_errmsg)) && (!isset($end_date_errmsg)))
		{
			$start_date = KakoLogViewer::genPathInfoDate(
				$_POST["start_year"], $_POST["start_month"], $_POST["start_day"]);
			$end_date = KakoLogViewer::genPathInfoDate(
				$_POST["end_year"], $_POST["end_month"], $_POST["end_day"]);
			Util::Redirect(
				"{$baseurl}/kako.php/{$bbs}/1/{$start_date}/{$end_date}");
			exit;
		}
		
		$search_path = "";
	}
	else
	{
		HtmlList::getInstance()->Krsort();
		
		$thread_list = HtmlList::getInstance()->getRows();
		$count = count($thread_list);
		
		if($pathinfo_count <= 3)
		{
			$show_thread_list = $thread_list;
			$search_path = "";
		}
		else if($pathinfo_count < 5)
		{
			KakoLogViewer::OutPutErrHtml("URLの形式が不正です。", "{$baseurl}/kako.php/{$bbs}/");
			exit;
		}
		else
		{
			$start_date = $pathinfo[3];
			$end_date = $pathinfo[4];
			
			$ret = KakoLogViewer::checkDateStrFormat($start_date);
			
			if($ret !== true)
			{
				KakoLogViewer::OutPutErrHtml("開始年月の{$ret}", "{$baseurl}/kako.php/{$bbs}/");
				exit;
			}
			
			$ret = KakoLogViewer::checkDateStrFormat($end_date);
			
			if($ret !== true)
			{
				KakoLogViewer::OutPutErrHtml("終了年月の{$ret}", "{$baseurl}/kako.php/{$bbs}/");
				exit;
			}
			
			$starttime = KakoLogViewer::DateStrtoTimeStamp($start_date, "start");
			$endtime = KakoLogViewer::DateStrtoTimeStamp($end_date, "end");
			
			$search_path = "/{$start_date}/{$end_date}";
			
			$show_thread_list = array();
			
			foreach($thread_list as $key => $val)
			{
				if(($endtime !== null) && ($key >= $endtime))
				{
					continue;
				}
				
				if(($starttime !== null) && ($key < $starttime))
				{
					break;
				}
				
				$show_thread_list[$key] = $val;
			}
		}
	}
	
	$form = new FormUtil();

	$year_array = array();
	$month_array = array();
	$day_array = array();
	
	$year_now = date("Y");
	
	$year_array["----"] = "----";
	for($i = $year_now; $i >= 1970 ; $i--)
	{
		$year_array["{$i}"] = sprintf("%04d", $i);
	}
	
	$month_array["--"] = "--";
	for($i = 1; $i <= 12 ; $i++)
	{
		$month_array["{$i}"] = sprintf("%02d", $i);
	}
	
	$day_array["--"] = "--";
	for($i = 1; $i <= 31 ; $i++)
	{
		$day_array["{$i}"] = sprintf("%02d", $i);
	}
	
	$form->addElement("start_year", null, null, $year_array);
	$form->addElement("start_month", null, null, $month_array);
	$form->addElement("start_day", null, null, $day_array);

	$form->addElement("end_year", null, null, $year_array);
	$form->addElement("end_month", null, null, $month_array);
	$form->addElement("end_day", null, null, $day_array);
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script type="text/javascript" src="<?php echo $baseurl; ?>/kakolog.js"></script>
<style>
* {
	font-family:arial,sans-serif;
	margin: 0px;
	padding: 0px;
}
body {
	background-color: <?php echo Util::valid_css_color_val($setting->BBS_BG_COLOR); ?>;
}
#container {
	width: 800px;
}
#box {
	width: 700px;
}
@media screen and (max-width: 320px) {
	#container {
		width: 630px;
	}
	#box {
		width: 550px;
	}
}
a:link {
	color: <?php echo Util::valid_css_color_val($setting->BBS_LINK_COLOR); ?>;
	
}
a:visited {
	color: <?php echo Util::valid_css_color_val($setting->BBS_VLINK_COLOR); ?>;
	
}
a:hover {
	color: <?php echo Util::valid_css_color_val($setting->BBS_ALINK_COLOR); ?>;
	
}
.form {
	padding-top: 8px;
	padding-bottom: 8px;
	border-style: double;
	border-color: black;
	border-top-width: 0px;
	border-bottom-width: 4px;
	border-left-width: 0px;
	border-right-width: 0px;
	margin-top: 12px;
}
div.line {
	border-style: double;
	border-color: black;
	border-top-width: 0px;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
	margin-top: 12px;
	margin-bottom: 12px;
}
div.head {
	padding-top: 2px;
	padding-bottom: 2px;
	padding-left: 4px;
	padding-right: 4px;
	font-weight: bold;
	border-style: solid;
	border-color: black;
	border-width: 1px;
}
#threads div.line {
	text-align: center;
	margin-top: 4px;
	margin-bottom: 4px;
	border-style: double;
	border-color: black;
	border-top-width: 0px;
	border-bottom-width: 3px;
	border-left-width: 0px;
	border-right-width: 0px;
}
#threads div.thread {
	text-align: left;
}
#pagelink {
	text-align: center;
}
#back {
	text-align: left;
	margin-right: 12px;
	margin-bottom: 20px;
}
</style>
<title><?php echo htmlspecialchars($setting->BBS_TITLE, ENT_QUOTES); ?> - 過去ログ一覧</title>
</head>
<body>
<center>
<div id="container">
	<div id="box">
		<div id="back"><a href="<?php echo "{$rooturl}/{$bbs}/index.html"; ?>">板TOPへ戻る</a></div>
		<div class="head"><?php echo htmlspecialchars($setting->BBS_TITLE, ENT_QUOTES); ?> - 過去ログ一覧</div>
		<div class="form">
			<form name="searchdate" method="post" action="">
				<?php echo $form->SelectBox("start_year", 5, array("id" => "start_year")); ?> / 
				<?php echo $form->SelectBox("start_month", 5, array("id" => "start_month")); ?> / 
				<?php echo $form->SelectBox("start_day", 5, array("id" => "start_day")); ?> 
				～
				<?php echo $form->SelectBox("end_year", 5, array("id" => "end_year")); ?> / 
				<?php echo $form->SelectBox("end_month", 5, array("id" => "end_month")); ?> / 
				<?php echo $form->SelectBox("end_day", 5, array("id" => "end_day")); ?> 
			<input type="submit" value="検索" />
			</form>
		</div>
		<div class="line"></div>
		<div id="threads">
			<?php $i=0; ?>
			<?php foreach($show_thread_list as $key => $val) : ?>
			<?php if($i < ($page - 1) * $SHOWCOUNT) { $i++; continue; } ?>
			
			<div class="thread">
				<div>
					<?php 
						$dir1 = substr($key, 0, 4);
						$dir2 = substr($key, 0, 5);
						$path = "{$rooturl}/{$bbs}/kako/{$dir1}/{$dir2}/{$key}.html";
					?>
					<a href="<?php echo $path ;?>"><?php echo $val; ?></a>
				</div>
			</div>
			<div class="line"></div>
			<?php $i++; ?>
			<?php if($i >= $page * $SHOWCOUNT) break; ?>
			<?php endforeach ; ?>
			<div id="pagelink">
				<?php if($page > 1) { ?>
				<a href="<?php echo $baseurl; ?>/kako.php/<?php echo $bbs; ?>/<?php echo $page - 1; echo $search_path; ?>">
					&lt;&lt;前へ
				</a>
				<?php } else { ?>
						&lt;&lt;前へ
				<?php } ?>
				
				<?php if(count($show_thread_list) > ($page * $SHOWCOUNT)) { ?>
					<a href="<?php echo $baseurl; ?>/kako.php/<?php echo $bbs; ?>/<?php echo $page + 1; echo $search_path; ?>">
						次へ&gt;&gt;
					</a>
				<?php } else { ?>
						次へ&gt;&gt;
				<?php } ?>
			</div>
		</div>
	</div>
</div>
</center>
</body>
</html>
