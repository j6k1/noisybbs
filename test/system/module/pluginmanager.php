<?php
	class PluginManager
	{
		var $plugins;
		var $masterdata;
		
		function PluginManager()
		{
			$this->plugins = null;
			$this->masterdata = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("PluginManager");
		}			
		
		function FindPlugins()
		{
			$setting =& SettingInfo::getInstance();
			
	    	$list = array();    // �߂�l�p�̔z�� 
			$dir = $setting->plugindir;
			
			$i=0;
			
			if( $handle = opendir( $dir ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// �������g�Ə�ʊK�w�̃f�B���N�g�������O 
	            	if( $file != "." && $file != ".." ) 
	          		{ 
	                	if( !is_dir( "{$dir}/{$file}" ) && (preg_match('/\.php$/', $file)) )
						{
	                    	$list[$i] = "{$dir}/{$file}";
							$i++; 
						}
	        		} 
	    		}
	       		closedir( $handle ); 
			}
			
			return $list;
				
		}
		
		function IncludePlugins()
		{
			$files = $this->FindPlugins();
			$count = count($files);
			
			for($i=0; $i < $count ; $i++)
			{
				if(file_exists($files[$i]) == false)
				{
						return Logging::generrinfo($this,
							__FUNCTION__ , __LINE__ , 
							"�v���O�C���N���X�t�@�C��{$files[$i]}�͑��݂��܂���B");
				}
			
				$ret = require_once($files[$i]);

				if($ret == false)
				{
						return Logging::generrinfo($this,
							__FUNCTION__ , __LINE__ , 
							"{$files[$i]}�̓ǂݍ��݂ŃG���[���������܂����B");
				}
			}
			
			return true;
		}
		
		function LoadPlugins()
		{
			$setting =& SettingInfo::getInstance();
			
			if(file_exists($setting->pluginlist) == false)
			{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"�v���O�C����`�t�@�C��{$setting->pluginlist}�͑��݂��܂���B");
			}
			
			$data = FileReader::Read($setting->pluginlist);
			
			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data != "")
			{
				$plugins = explode("\n", $data);
				array_pop($plugins);
			}
			else
			{
				$plugins = array();
			}
			
			$count = count($plugins);

			for($i=0; $i < $count ; $i++)
			{
				$class = $plugins[$i];
				
				if(class_exists($class) == false)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"�v���O�C���N���X{$class}�͖���`�ł��B");
				}
				
				$instance = new $class();
				
				if(is_subclass_of($instance, "BBSPlugin") == false)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"�N���X{$class}�����N���X���p�����Ă��܂���B");
				}
				
				$this->plugins[$i] = $instance;
			}
			
			return true;
		}
		
		function getEnablePlugins()
		{
			$setting =& SettingInfo::getInstance();
			
			if(file_exists($setting->pluginlist) == false)
			{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"�v���O�C����`�t�@�C��{$setting->pluginlist}�͑��݂��܂���B");
			}
			
			$data = FileReader::Read($setting->pluginlist);
			
			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data != "")
			{
				$plugins = explode("\n", $data);
				array_pop($plugins);
			}
			else
			{
				$plugins = array();
			}
			
			return $plugins;
		}
		
		function InitPluginMaster()
		{
			$setting =& SettingInfo::getInstance();
			$filepath = "system/sysdata/{$setting->pluginmaster}";
			
			if(file_exists($filepath) == false)
			{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"�v���O�C���}�X�^�t�@�C��{$filepath}�͑��݂��܂���B");
			}
			
			$data = FileReader::Read($filepath);
			
			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data == "")
			{
				$data = array();
			}
			else
			{
				$data = explode("\n", $data);
				array_pop($data);
			}
			
			foreach($data as $val)
			{
				list($classname, $name) = explode("<>", $val);
				$this->masterdata[$classname] = $name;
			}
			
			return true;
		}
		
		function getMasterData()
		{
			return $this->masterdata;
		}
	}
?>
