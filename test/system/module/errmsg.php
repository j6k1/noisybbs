<?php
	class ErrMessage
	{
		var $bbskey_null = "板キーが指定されていません。";
		var $bbs_notfound = "指定した板は見つかりませんでした。";
		var $urlformat = "URLの形式が不正です。";
		var $resno_null = "レス番号が指定されていません。";
		var $bbskey_invalid = "板キーが不正です。";
		var $threadkey_invalid = "スレッドキーが不正です。";
		var $thread_stateread = "読みこんだスレッド状態情報が不正です。";
		var $denyhosts_notinit = "規制ホストリストが初期化されていません。";
		
		var $res_size_over;
		var $res_linecnt_over;
		var $res_linelen_over;
		var $ngword_found;
		var $noname;
		var $name_size_over;
		var $mail_size_over;
		var $nullmsg;
		
		var $nosubject;
		var $subject_size_over;
		
		var $invalidprm;

		var $postedlimit;
		
		var $res_max;

		var $fileopen_ng;
		var $fileclose_ng;
		var $filelock_ng;
		var $fileunlock_ng;

		var $thredstop;
		
		var $rentou;
		var $threcre_cnt_over;
		
		function ErrMessage()
		{
			$this->res_size_over = "文字数が多すぎます";
			$this->res_linecnt_over = "改行の数が多すぎます。";
			$this->res_linelen_over = "一行が長すぎます。";
			
			$this->ngword_found      = "NGワードが含まれています";
			$this->noname = "この板では名前欄を空欄にすることはできません。";
			$this->name_size_over = "名前欄が長すぎます。";
			$this->mail_size_over = "メール欄が長すぎます。";
			$this->nullmsg = "本文が空です。";
			$this->nosubject = "スレッドタイトルが空です。";
			$this->subject_size_over = "スレッドタイトルが長すぎます。";

			$this->invalidprm = "入力値が不正です。";
			
			$this->postedlimit = "投稿間隔が短すぎます。";

			$this->res_max = "このスレッドにはもう書けません。";

			$this->not_openedfile = "ファイルが開かれていません。";
			$this->fileopen_ng    = "指定したファイルが開けません。";
			$this->fileclose_ng    = "指定したファイルをクローズできません。";
			$this->filelock_ng	= "ファイルをロックできませんでした。";
			$this->fileunlock_ng	= "ファイルのロックを解除できませんでした。";
			$this->filetruncate_ng = "ファイル内容をクリアできませんでした。";
			$this->fopenmode_notsupport = "未対応のファイルオープンモードが指定されました。";
			$this->fwrite_ng = "ファイルに書き込めませんでした。";
			$this->filechmod_ng = "ファイルのパーミッションを変更できませんでした。";
			
			$this->threadstop	= "スレッドストップされています。";
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("ErrMessage");
		}			

		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			$this->resinterval = $this->setting->RES_INTERVAL;
			$this->threcer_interval = $setting->THRECRE_INTERVAL;
			$this->threcer_max = $setting->THRECRE_MAX;
			
			$this->rentou = "投稿間隔が短すぎます。{$this->resinterval}秒以上間隔を開けて投稿してください<br />。\n";
			$this->threcre_cnt_over = "あなたは{$this->threcer_interval}時間以内に{$this->threcer_max}" . 
			 "スレッドをすでに立てたため、これ以上立てられません。<br />
何時間か待ってから立ててください。";

		}
	}
?>
