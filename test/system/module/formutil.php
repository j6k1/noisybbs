<?php
	class FormUtil
	{
		var $element;
		var $haserr;
		
		function FormUtil()
		{
			$this->elements = array();
			$this->haserr = false;			
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("FormUtil");
		}			
		
		function addElement($key, $val, $chkptn = null, $list = null)
		{
			$this->elements[$key] = new FormUtilElement($key, $val, $chkptn, $list);
			return true;
		}
		
		function setElementValue($key, $val, $index = null)
		{
			if(!isset($this->elements[$key]))
			{
				return false;
			}
			
			if($index !== null)
			{
				$this->elements[$key]->val[$index] = $val;
			}
			else
			{
				$this->elements[$key]->val = $val;
			}
			
			return true;
		}
		
		function getElementKeys()
		{
			$result = array();
			
			foreach($this->elements as $key => $elem)
			{
				$result[] = $key;
			}
			
			return $result;
		}
		
		function HasError()
		{
			return $this->haserr;
		}
		
		function getElementValue($key)
		{
			if(!isset($this->elements[$key]))
			{
				return null;
			}
			
			return $this->elements[$key]->val;
		}
		
		function getAllElementValue()
		{
			$result = array();
			
			foreach($this->elements as $key => $elem)
			{
				$result[$key] = $elem->val;
			}
			
			return $result;
		}
		
		function ErrMessage($key, $msg, $index = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			if($this->elements[$key]->err === false)
			{
				return "";
			}
			
			if($index === null)
			{
				if($this->elements[$key]->err == true)
				{
					return $msg;
				}
			}
			else
			{
				if($this->elements[$key]->err[$index] == true)
				{
					return $msg;
				}
			}
			
			return "";
		}
		
		function ValidateAll()
		{
			foreach($this->elements as $key => $elmval)
			{
				FormUtil::ValidateElement($key);
			}
			
			return $this->haserr;
		}
		
		function ValidateElement($key)
		{
			if( (!isset($this->elements[$key])) || 
			    (!isset($this->elements[$key]->chkptn)) )
			{
				return true;
			}
			
			if(is_array($this->elements[$key]->val))
			{
				$this->elements[$key]->err = array();

				foreach($this->elements[$key]->val as $index => $val)
				{
					$err = true;
		
					if(is_array($this->elements[$key]->chkptn))
					{
						foreach($this->elements[$key]->chkptn as $ptn)
						{
							if($ptn == "valid_ereg_expression")
							{
								$values = explode("\n", $val);
								$err = false;
								
								foreach($values as $value)
								{
									$value = mb_convert_encoding($value, "UTF-8", "SJIS");
									if(@preg_match($value, "") === false)
									{
										$err = true;
										continue 2;
									}
								}
								
								break;
							}
							else if(preg_match($ptn, $val))
							{
								$err = false;
								break;
							}
						}

						$this->elements[$key]->err[$index] = $err;
					}
					else
					{
						$ptn = $this->elements[$key]->chkptn;
						$err = true;
	
						if($ptn == "valid_ereg_expression")
						{
							$values = explode("\n", $val);
							$err = false;
							
							foreach($values as $value)
							{
								$value = mb_convert_encoding($value, "UTF-8", "SJIS");
								if(@preg_match($value, "") === false)
								{
									$err = true;
									break;
								}
							}
						}
						else if(preg_match($ptn, $val))
						{
							$err = false;
						}
						
						$this->elements[$key]->err[$index] = $err;
					}
				}
			}
			else 
			{
				if(is_array($this->elements[$key]->chkptn))
				{
					$err = true;

					foreach($this->elements[$key]->chkptn as $ptn)
					{
						$val = $this->elements[$key]->val;
						
						if($ptn == "valid_ereg_expression")
						{
							$values = explode("\n", $val);
							$err = false;
							
							foreach($values as $value)
							{
								$value = mb_convert_encoding($value, "UTF-8", "SJIS");
								if(@preg_match($value, "") === false)
								{
									$err = true;
									continue 2;
								}
							}
							
							break;
						}
						else if(preg_match($ptn, $val))
						{
							$err = false;
							break;
						}
						
					}

					$this->elements[$key]->err = $err;
				}
				else
				{
					$ptn = $this->elements[$key]->chkptn;
					$err = true;

					if($ptn == "valid_ereg_expression")
					{
						$values = explode("\n", $this->elements[$key]->val);
						$err = false;
						
						foreach($values as $value)
						{
							$value = mb_convert_encoding($value, "UTF-8", "SJIS");
							if(@preg_match($value, "") === false)
							{
								$err = true;
								break;
							}
						}
					}
					else if(preg_match($ptn, $this->elements[$key]->val))
					{
						$err = false;
					}

					$this->elements[$key]->err = $err;
				}
			}
			
			if($err)
			{
				$this->haserr = true;
			}
			
			return true;
		}
		
		function Text($key, $options = null, $subkey = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			$html = "";
			
			if(isset($subkey))
			{
				$value = Util::adminhtmlspecialchars($this->elements[$key]->val[$subkey]);
			
				$html .= <<<EOM
<input type="text" name="{$key}[{$subkey}]" value="{$value}" 
EOM;
			}
			else
			{
				$value = Util::adminhtmlspecialchars($this->elements[$key]->val);
				
				$html .= <<<EOM
<input type="text" name="{$key}" value="{$value}" 
EOM;
			}
			
			if(isset($options))
			{
				foreach($options as $optkey => $optval)
				{
					$optval = str_replace('"', '\"', $optval);
					$html .= <<<EOM
 {$optkey}="{$optval}" 
EOM;
				}
			}
			
			$html .= " />";
			
			return $html;
		}

		function TextArea($key, $indent, $options = null, $subkey = null)
		{
			$html = "";
			
			for($i=0 ; $i < $indent ; $i++)
			{
				$html .= "\t";
			}
			
			if(isset($subkey))
			{
			$html .= <<<EOM
<textarea name="{$key}[{$subkey}]"
EOM;
			}
			else
			{
			$html .= <<<EOM
<textarea name="{$key}"
EOM;
			}
			
			if(isset($options))
			{
				foreach($options as $optkey => $optval)
				{
					$optval = str_replace('"', '\"', $optval);
					$html .= <<<EOM
 {$optkey}="{$optval}"
EOM;
				}
			}
			$html .= ">";
			if(isset($subkey))
			{
				$html .= Util::adminhtmlspecialchars("{$this->elements[$key]->val[$subkey]}");
			}
			else
			{
				$html .= Util::adminhtmlspecialchars("{$this->elements[$key]->val}");
			}
			
			$html .= "</textarea>";
			
			return $html;
		}

		function Password($key, $options = null, $subkey = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			$html = "";
			
			if(isset($subkey))
			{
				$value = Util::adminhtmlspecialchars($this->elements[$key]->val[$subkey]);
			
				$html .= <<<EOM
<input type="password" name="{$key}[{$subkey}]" value="{$value}" 
EOM;
			}
			else
			{
				$value = Util::adminhtmlspecialchars($this->elements[$key]->val);
				
				$html .= <<<EOM
<input type="password" name="{$key}" value="{$value}" 
EOM;
			}
			
			if(isset($options))
			{
				foreach($options as $optkey => $optval)
				{
					$optval = str_replace('"', '\"', $optval);
					$html .= <<<EOM
 {$optkey}="{$optval}" 
EOM;
				}
			}
			
			$html .= " />";
			
			return $html;
		}

		function SelectBox($key, $indent, $options = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			$html = "";
			
			$html .= <<<EOM
<select name="{$key}"
EOM;

			if(isset($options))
			{
				foreach($options as $optkey => $optval)
				{
					$optval = str_replace('"', '\"', $optval);
					$html .= <<<EOM
 {$optkey}="{$optval}"
EOM;
				}
			}
			
			$html .= ">\n";
			
			foreach($this->elements[$key]->list as $listkey => $listval)
			{
				for($i=0 ; $i <= $indent ; $i++)
				{
					$html .= "\t";
				}

				if( isset($this->elements[$key]->val) && 
					($this->elements[$key]->val == $listkey) )
				{
					$listval = Util::adminhtmlspecialchars($listval);
					$listkey = Util::adminhtmlspecialchars($listkey);
					$html .= <<<EOM
<option value="{$listkey}" selected="selected">{$listval}</option>

EOM;
				}
				else
				{
					$listval = Util::adminhtmlspecialchars($listval);
					$listkey = Util::adminhtmlspecialchars($listkey);
					$html .= <<<EOM
<option value="{$listkey}">{$listval}</option>

EOM;
				}
			}

			for($i=0 ; $i < $indent ; $i++)
			{
				$html .= "\t";
			}
			
			$html .= <<<EOM
</select>

EOM;

			return $html;
		}
		
		function RadioButton($key, $indent, $options = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			$html = "\n";
			
			foreach($this->elements[$key]->list as $listkey => $listval)
			{
				for($i=0 ; $i < $indent ; $i++)
				{
					$html .= "\t";
				}
				
				$listval = Util::adminhtmlspecialchars($listval);
				$html .= <<<EOM
<input type="radio" name="{$key}" value="{$listval}"
EOM;
				if(isset($options))
				{
					foreach($options as $optkey => $optval)
					{
						$optval = str_replace('"', '\"', $optval);
						$html .= <<<EOM
 {$optkey}="{$optval}"
EOM;
					}
				}
				
				if($this->elements[$key]->val == $listval)
				{
					$html .= " checked";
				}
				
				$listkey = Util::adminhtmlspecialchars($listkey);
				$html .= ">{$listkey}<br>\n";
			}

			return $html;
		}
		
		function CheckBoxList($key, $indent, $options = null)
		{
			if(!isset($this->elements[$key]))
			{
				return "";
			}
			
			$html = "";
			
			if(isset($this->elements[$key]->list))
			{
				foreach($this->elements[$key]->list as $listkey => $listval)
				{
	
					for($i=0 ; $i < $indent ; $i++)
					{
						$html .= "\t";
					}
					
					$listkey = Util::adminhtmlspecialchars($listkey);
					$listvalescape = Util::adminhtmlspecialchars($listval);
					$html .= <<<EOM
{$listvalescape}<input type="checkbox" name="{$key}[]" value="{$listkey}" 
EOM;
					if(isset($options))
					{
						foreach($options as $optkey => $optval)
						{
							$optval = str_replace('"', '\"', $optval);
							$html .= <<<EOM
 {$optkey}="{$optval}"
EOM;
						}
					}
					
					if(isset($this->elements[$key]->val) == true)
					{
						if(is_array($this->elements[$key]->val))
						{
							if(in_array($listval, $this->elements[$key]->val))
							{
								$html .= " checked";
							}
						}
						else
						{
							if($listval == $this->elements[$key]->val)
							{
								$html .= " checked";
							}
						}
					}
				
					$html .= "><br>\n";
				
				}
			}
			
			return $html;
		}
		
		function CheckBox($key, $val, $options = null)
		{
			$val = Util::adminhtmlspecialchars($val);
			if(is_array($this->elements[$key]->val))
			{
				$html .= <<<EOM
<input type="checkbox" name="{$key}[]" value="{$val}" 
EOM;
			}
			else
			{
				$html .= <<<EOM
<input type="checkbox" name="{$key}" value="{$val}" 
EOM;
			}

			if(isset($options))
			{
				foreach($options as $optkey => $optval)
				{
					$optval = str_replace('"', '\"', $optval);
					$html .= <<<EOM
 {$optkey}="{$optval}" 
EOM;
				}
			}
			
			if(isset($this->elements[$key]->val) == true)
			{
				if(is_array($this->elements[$key]->val))
				{
					if(in_array($val, $this->elements[$key]->val))
					{
						$html .= " checked";
					}
				}
				else if($this->elements[$key]->val == $val)
				{
					$html .= " checked";
				}
			}
			
			$html .= ">";
		
			return $html;
		}
	}
?>
