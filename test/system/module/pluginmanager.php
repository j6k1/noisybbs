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
			
	    	$list = array();    // 戻り値用の配列 
			$dir = $setting->plugindir;
			
			$i=0;
			
			if( $handle = opendir( $dir ) ) 
			{ 
				while ( false !== $file = readdir( $handle ) ) 
	        	{ 
					// 自分自身と上位階層のディレクトリを除外 
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
							"プラグインクラスファイル{$files[$i]}は存在しません。");
				}
			
				$ret = require_once($files[$i]);

				if($ret == false)
				{
						return Logging::generrinfo($this,
							__FUNCTION__ , __LINE__ , 
							"{$files[$i]}の読み込みでエラーが発生しました。");
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
						"プラグイン定義ファイル{$setting->pluginlist}は存在しません。");
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
						"プラグインクラス{$class}は未定義です。");
				}
				
				$instance = new $class();
				
				if(is_subclass_of($instance, "BBSPlugin") == false)
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"クラス{$class}が基底クラスを継承していません。");
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
						"プラグイン定義ファイル{$setting->pluginlist}は存在しません。");
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
						"プラグインマスタファイル{$filepath}は存在しません。");
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
