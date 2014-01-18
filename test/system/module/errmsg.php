<?php
	class ErrMessage
	{
		var $bbskey_null = "�L�[���w�肳��Ă��܂���B";
		var $bbs_notfound = "�w�肵���͌�����܂���ł����B";
		var $urlformat = "URL�̌`�����s���ł��B";
		var $resno_null = "���X�ԍ����w�肳��Ă��܂���B";
		var $bbskey_invalid = "�L�[���s���ł��B";
		var $threadkey_invalid = "�X���b�h�L�[���s���ł��B";
		var $thread_stateread = "�ǂ݂��񂾃X���b�h��ԏ�񂪕s���ł��B";
		var $denyhosts_notinit = "�K���z�X�g���X�g������������Ă��܂���B";
		
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
			$this->res_size_over = "���������������܂�";
			$this->res_linecnt_over = "���s�̐����������܂��B";
			$this->res_linelen_over = "��s���������܂��B";
			
			$this->ngword_found      = "NG���[�h���܂܂�Ă��܂�";
			$this->noname = "���̔ł͖��O�����󗓂ɂ��邱�Ƃ͂ł��܂���B";
			$this->name_size_over = "���O�����������܂��B";
			$this->mail_size_over = "���[�������������܂��B";
			$this->nullmsg = "�{������ł��B";
			$this->nosubject = "�X���b�h�^�C�g������ł��B";
			$this->subject_size_over = "�X���b�h�^�C�g�����������܂��B";

			$this->invalidprm = "���͒l���s���ł��B";
			
			$this->postedlimit = "���e�Ԋu���Z�����܂��B";

			$this->res_max = "���̃X���b�h�ɂ͂��������܂���B";

			$this->not_openedfile = "�t�@�C�����J����Ă��܂���B";
			$this->fileopen_ng    = "�w�肵���t�@�C�����J���܂���B";
			$this->fileclose_ng    = "�w�肵���t�@�C�����N���[�Y�ł��܂���B";
			$this->filelock_ng	= "�t�@�C�������b�N�ł��܂���ł����B";
			$this->fileunlock_ng	= "�t�@�C���̃��b�N�������ł��܂���ł����B";
			$this->filetruncate_ng = "�t�@�C�����e���N���A�ł��܂���ł����B";
			$this->fopenmode_notsupport = "���Ή��̃t�@�C���I�[�v�����[�h���w�肳��܂����B";
			$this->fwrite_ng = "�t�@�C���ɏ������߂܂���ł����B";
			$this->filechmod_ng = "�t�@�C���̃p�[�~�b�V������ύX�ł��܂���ł����B";
			
			$this->threadstop	= "�X���b�h�X�g�b�v����Ă��܂��B";
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
			
			$this->rentou = "���e�Ԋu���Z�����܂��B{$this->resinterval}�b�ȏ�Ԋu���J���ē��e���Ă�������<br />�B\n";
			$this->threcre_cnt_over = "���Ȃ���{$this->threcer_interval}���Ԉȓ���{$this->threcer_max}" . 
			 "�X���b�h�����łɗ��Ă����߁A����ȏ㗧�Ă��܂���B<br />
�����Ԃ��҂��Ă��痧�ĂĂ��������B";

		}
	}
?>
