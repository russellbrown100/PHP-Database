<?php



	function get_database_path()
	{
		return 'C:\Users\Russell Brown\Documents\Database Code\database.dat';


	}

	function database_exists()
	{
		$result = "true";

		$path = get_database_path(); 
		
		if (file_exists($path))  
		{ 
			$result = "true";
		} 
		else 
		{ 
			$result = "false";
		} 

		return $result;
	}

	function table_exists($table_name)
	{
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");	

		
		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		$has_table = "false";
		
		while (true)
		{
			$line = fgets($f);
			
			if (contains_string($line, $table_name) == "true")
			{
				$has_table = "true";
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		return $has_table;
	}

	function create_database()
	{			
		
		$path = get_database_path(); 

		$f = fopen($path,"w+");
				
		fwrite($f, "tables:\n");
		fwrite($f, "data:\n");
		
			
		fclose($f);
		

	}


	function delete_database()
	{			
		
		$path = get_database_path(); 

		unlink($path);
		

	}




	function create_table($table_name, $columns, $column_names, $column_types)
	{
		$path = get_database_path(); 
		
		
		$f = fopen($path,"r+");
		
		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
		
		
		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);	
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		
		$exists = "false";
		$starting_position = 0;
		while (true)
		{
			$starting_position = ftell($f);
			$line = fgets($f);
			if (contains_string($line, $table_name) == "true") 
			{
				$exists = "true";
				break;			
			}
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
			
		}
		
		
		if ($exists == "false")
		{		
			$string = $table_name.",".$columns.",";
			
			for ($i = 0; $i < $columns; $i++)
			{
				$string = $string.strlen($column_names[$i]).",";
				$string = $string.$column_names[$i].",";
			}
			
			
			for ($i = 0; $i < $columns; $i++)
			{
				$string = $string.strlen($column_types[$i]).",";
				$string = $string.$column_types[$i];
				
				if ($i < $columns - 1) $string = $string.",";
			}
			
			
			$string = $string."\n";
			
			move_everything_down("false", "create_table", $f, 0, $starting_position, $string);
						
			fseek($f, $starting_position, SEEK_SET);
			
			fwrite($f, $string);
		
		}
		

		fclose($f);
	}
		
		
	
	
	function split_string($string, $separator)
	{

		$text = "";
		$open = "true";
		
		$list = array();
		
		for ($i = 0; $i < strlen($string); $i++)
		{
			
			if ($string[$i] == $separator)
			{
				if ($open == "true")
				{
					array_push($list, $text);
					$text = "";
				}
			}
			else
			{
				if ($string[$i] == '"')
				{
					if ($open == "true") $open = "false";
					else if ($open == "false") $open = "true";
					
				}
				
				$text = $text.$string[$i];
			}
		}
		
		array_push($list, $text);

		
		return $list;
		
	}


	function reverse_string($string)
	{
		$result = "";
		
		for ($i2 = strlen($string) - 1; $i2 >= 0; $i2--)
		{
			$result = $result.$string[$i2];
		}
		
		return $result;
	}
		
	function contains_string($source, $target)
	{
		
		$result = "false";
		
		for ($i = 0; $i <= strlen($source) - strlen($target); $i++)
		{			
			$result2 = "true";
			
			for ($i2 = 0; $i2 < strlen($target); $i2++)
			{
				
				if ($source[$i + $i2] != $target[$i2])
				{
					$result2 = "false";
					break;
				}
			}
			
			
			if ($result2 == "true")
			{
				$result = "true";
				break;
			}
			
		}
		
		return $result;
	}

	function get_beginning_of_line($f)
	{
		$position = ftell($f);
		
		if ($position > 0)
		{
			while (true)
			{
				fseek($f, $position, SEEK_SET);
				
				$c = fgetc($f);
				
				if ($c == "\n") break;
				
				$position--;
				
				if ($position <= 0) break;
			}
			
			if ($position > 0)
			{
				fseek($f, $position + 1, SEEK_SET);
			}
			else
			{
				fseek($f, $position, SEEK_SET);
			}
		}
	}

	function get_table_info($table_name)
	{
		$column_names = array();
		$column_types = array();
		
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");				

		fseek($f, 0, SEEK_SET);
		
		$look_at_tables = "false";
		
		while (true)
		{
			$line = fgets($f);
			
			
			
			if (contains_string($line, "data:") == "true")
			{
				$look_at_tables = "false";
				break;
			}
			
			if ($look_at_tables == "true")
			{
				$array2 = explode(",", $line);
				
				if ($array2[0] == $table_name)
				{					
					$cols = $array2[1];
					$ci = 0;
					$start = 0;
					
					for ($i = 0; $i < strlen($line); $i++)
					{
						if ($line[$i] == ',')
						{
							$ci++;
							if ($ci == 2)
							{
								$start = $i+1;
								break;
							}
						}
					}
					
					$c = $start;
					$ci = $start;
					
					// column names
					
					for ($i = 0; $i < $cols; $i++)
					{
						
						$length = "";
						
						while (true)
						{
							if ($line[$ci] == ',')
							{
								break;
							}
							
							$length = $length.$line[$ci];
							$c++;
							$ci++;
							if ($ci >= strlen($line)) break;							
						}
						
						$pl = $ci + 1;
						
						$ci += $length + 1;
						$slen = $ci - $pl;
						
						$value = substr($line, $pl, $slen);
						array_push($column_names, $value);
						
						$ci++;
					}
					
					
					// column types
					
					for ($i = 0; $i < $cols; $i++)
					{
						$length = "";
						
						while (true)
						{
							if ($line[$ci] == ',')
							{
								break;
							}
							
							$length = $length.$line[$ci];
							$c++;
							$ci++;
							if ($ci >= strlen($line)) break;							
						}
						
						$pl = $ci + 1;
						
						$ci += $length + 1;
						$slen = $ci - $pl;
						
						$value = substr($line, $pl, $slen);
						array_push($column_types, $value);
						
						$ci++;
					}
				}
			}
			
			
			
			if (contains_string($line, "tables:"))
			{
				$look_at_tables = "true";
			}
		}
		
				
		fclose($f);
		
		
		
		
		return array($column_names, $column_types);
		
	}

	function get_number_of_columns($table_name)
	{
		
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");		


		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);

		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		while (true)
		{
			$line = fgets($f);
			
			if (contains_string($line, $table_name) == "true")
			{
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		$line_arr = explode(",", $line);
		$cols = $line_arr[1];
		
		fclose($f);
		
		return $cols;
		
	}


	function get_column_id($table_name, $column_name)
	{
		
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");		


		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);

		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		while (true)
		{
			$line = fgets($f);
					
			if (contains_string($line, $table_name) == "true")
			{
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		
		$line_arr = explode(",", $line);
		$cols = $line_arr[1];
		
		$id = 3;
		$count = 0;
		$id2 = -1;
		
		for ($i = 0; $i < $cols; $i++)
		{
			
			if ($line_arr[$id] == $column_name)
			{
				$id2 = $count;
				break;
			}
			
			$id += 2;
			$count++;
		}
		
		fclose($f);
		
		return $id2;
		
	}
	
		

	function get_column_value($table_name, $column_name, $array)
	{	
		$id = get_column_id($table_name, $column_name);
		
		$cols = get_number_of_columns($table_name);

		$pos = 0;
		
		for ($i2 = 0; $i2 < $id; $i2++)
		{
			$pos += $array[2 + $i2];
		}
		
		$len = $array[2 + $id];
		
		$val = substr($array[$cols + 2], $pos, $len);
		
		return $val;
							
	}



	function move_everything_up($debug, $operation, $f, $starting_position, $ending_position)
	{
		
		$result = "true";
		$end_of_data = "false";
		$length = 1000;
		$next_write_pos = $starting_position;
		$next_read_pos = $ending_position;
			
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
			
		if ($debug == "true")
		{
			$n = 0;
		}
		
		
		while (true)
		{
			
			// read the data
			
			fseek($f, $next_read_pos, SEEK_SET);
			
			if (ftell($f) + $length > $size)
			{
				$length = $size - ftell($f);
				$end_of_data = "true";
			}
			
			$data = fread($f, $length);
			
			$next_read_pos = ftell($f);
			
			// save the data		
			
			
			fseek($f, 0, SEEK_END);
			
			$saved_length = ftell($f);
			
			$starting_pos = ftell($f);
			
			$result = "true";

			try
			{
				
				fwrite($f, $operation.":".$length.":".$next_write_pos.":".$next_read_pos."\n");
				fwrite($f, $data);
				
				if ($data[$length - 1] != '\n')
				{
					fwrite($f, "\n");
				}
			
			
				fwrite($f, "recovery point".":".$starting_pos."\n");
		
		
			}
			catch (Exception $e)
			{
				$result = "false";
				echo $e->GetMessage();
			}
			
			if ($debug == "true")
			{
				if ($n == 1)
				{
					$result = "false";
					break;
				}
			}
			
			
			// write the data			
			
			if ($result == "true")
			{
				try
				{				
					fseek($f, $next_write_pos, SEEK_SET);
					
					fwrite($f, $data);
					
					
				}
				catch (Exception $e)
				{
					$result = "false";
					echo $e->GetMessage();
				}
			}
			
			if ($result == "true")
			{
				ftruncate($f, $saved_length);
				
				if ($end_of_data == "true") break;
				
				$next_write_pos = ftell($f);
			}
			else
			{
				break;
			}
			
			if ($debug == "true")
			{
				$n++;
			}
			
			
		}
		
		if ($result == "true")
		{
			ftruncate($f, ftell($f));
		}
		
		return $result;
	}

		
		
	function move_everything_down($debug, $operation, $f, $position, $target_position, $string)
	{
		try
		{
			$path = get_database_path(); 
			
			$length = 1000;
			$end_of_data = "false";
			$saved_length = 0;
			$result = "true";
			
			if ($position == 0)
			{			
				// set the position to the end
		
		
				fseek($f, 0, SEEK_END);
				
				if (ftell($f) - $length < $target_position)
				{
					$length = ftell($f) - $target_position;
					$end_of_data = "true";
				}
				
				$position = ftell($f) - $length;
				
			}
			
			if ($debug == "true")
			{
				$n = 0;
			}
				
			while (true)
			{
				
				//$next_write_pos = $position;		//??????
				
				$next_write_pos = $position + strlen($string);   //??????
				
				// read the data
				
				fseek($f, $position, SEEK_SET);
				$data = fread($f, $length);
				
				// save the data
				
				
				
				if ($length > 0)
				{
					$blanks = "";
					for ($i = 0; $i < strlen($string) - 1; $i++)
					{
						$blanks = $blanks."_";
					}
					$blanks = $blanks."\n";
					fwrite($f, $blanks);
					
					fseek($f, 0, SEEK_END);
					$saved_length = ftell($f);
					$starting_pos = ftell($f);
					fwrite($f, $operation.":".$length.":".$next_write_pos.":".$target_position.":".$string);
					fwrite($f, $data);
					
					
					if (substr($data, $length - 1, 1) != "\n")
					{
						fwrite($f, "\n");
					}
					
					fwrite($f, "recovery point".":".$starting_pos."\n");
				}
				else
				{
					break;
				}
				
					
				if ($debug == "true")
				{
					if ($n == 0)
					{
						$result = "false";
						break;
					}
				}
					
				
				// write the data
				
				
				try
				{					
					
					fseek($f, $position + strlen($string), SEEK_SET);
					fwrite($f, $data);
					ftruncate($f, $saved_length);
				
				}
				catch (Exception $e)
				{
					$result = "false";
					echo $e->GetMessage();
				}
				
				if ($result == "true")
				{
					if ($end_of_data == "true") break;
					
					if ($position - $length < $target_position)
					{
						$length = $position - $target_position;
						$position = $target_position;
						$end_of_data = "true";
					}
					else
					{
						$position -= $length;
					}
				}
				
					
				if ($debug == "true")
				{
					$n++;
				}
				
			}
			
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
		return $result;
	}

		
	function IsInteger($string)
	{
		$result = "true";
		
		if (is_numeric($string) == false)
		{
			$result = "false";
		}
		
		if (contains_string($string, ".") == "true")
		{
			$result = "false";
		}
		
		
		return $result;
	}


	function IsString($string)
	{
		return "true";
	}

	function IsNumericDateTime($string)
	{
		$result = "true";
		
		if (is_numeric($string) == false)
		{
			$result = "false";
		}
		
		if (contains_string($string, ".") == "true")
		{
			$result = "false";
		}
		
		
		return $result;
	}

	function IsDouble($string)
	{
		$result = "true";
		
		if (is_numeric($string) == false)
		{
			$result = "false";
		}
				
		
		return $result;
	}

	function check_input($table_name, $array)
	{
		$arr = get_table_info($table_name);
		$column_types = $arr[1];		
		$result = "true";		
		$cols = get_number_of_columns($table_name);
			
		
		if (sizeof($array) != $cols)
		{
			$result = "false";
		}
		else
		{
			for ($i = 0; $i < sizeof($column_types); $i++)
			{
				$oarr = $column_types[$i];
				
				if (oarr[1] == "datetime")
				{
					if (IsNumericDateTime($array[$i]) == "false")
					{
						$result = "false";
						break;
					}
				}
				else if (oarr[1] == "string")
				{
					if (IsString($array[$i]) == "false")
					{
						$result == "false";
						break;
					}
				}
				else if (oarr[1] == "int")
				{
					if (IsInteger($array[$i]) == "false")
					{
						$result = "false";
						break;
					}
				}
				else if (oarr[1] == "double")
				{
					if (IsDouble($array[$i]) == "false")
					{
						$result = "false";
						break;
					}
				}
			}
		}
		
		return $result;
		
	}

	
	//------------------------------------------------------------
	
			
			
	function update_raw_record($record_number, $array2)
	{
		
		
		
		$path = get_database_path(); 


		$f = fopen($path,"r+");	
		
		
		if (flock($f, LOCK_EX | LOCK_NB) == true)
		{
		
			
			// get the file size
			
			fseek($f, 0, SEEK_END);
			$size = ftell($f);	
			
			
			// get the beginning of the data section

			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);		
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			
			// initialize variables
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			$beginning_of_line = "true";
			$line_position = 0;
			
			
			
			

			while (true)
			{			

				if (ftell($f) + $length >= $size)
				{
					$length = $size - ftell($f);
					$end_of_data = "true";
				}

				// read the data

				$starting_position = ftell($f);
				$data = fread($f, $length);
				
				// loop through the data
				
				for ($i = 0; $i < $length; $i++)
				{
					if ($data[$i] == "\n")
					{
												
						$array = explode(",", $line);	
						
							
						// check if the record number equals the target record number
						if ($array[0] == $record_number)
						{		
							// Note:  for updating transactions, we are (1) moving everything up then (2) moving everything down
								
							$end_of_data = "true";				
							
							$result = move_everything_up("false", "move_everything_up", $f, $line_position, $line_position + strlen($line) + 1);
							
							
							if ($result == "true")
							{
								// format the line with the new data
									
										
								$line = "";
								for ($i2 = 0; $i2 < sizeof($array2); $i2++)
								{
									$line = $line.$array2[$i2];
									if ($i2 < sizeof($array2) - 1)
									{
										$line = $line.",";
									}
								}
								
								$line = $line."\n";
								
								// move everything down
								
								$result = move_everything_down("false", "move_everything_down", $f, 0, $line_position, $line);
												
								// write the new line
												
								if ($result == "true")
								{
													
									fseek($f, $line_position, SEEK_SET);
									
									fwrite($f, $line);
								
								}
								else if ($result == "false")
								{
									print "error occured\n";
								}
								
							}
												
								
							
							break;
							
							
						}
						
						// new line from file
						
						$line = "";
						$beginning_of_line = "true";
						
						
					}
					else if ($data[$i] != "\r")
					{					
						// save the line position
						
						if ($beginning_of_line == "true")
						{
							$line_position = $starting_position + $i;
						}
						
						// append the line
						
						$line = $line.$data[$i];
						$beginning_of_line = "false";
					}
				}
				
				if ($end_of_data == "true")
				{
					break;
				}
				
			}

			flock($f, LOCK_UN);
			
		}
			
			
		fclose($f);
		
		
		
		
		
	}




	function normalize_records()
	{

		
		$path = get_database_path(); 


		$f = fopen($path,"r+");	

		if (flock($f, LOCK_EX | LOCK_NB) == true)
		{
			
			
			fseek($f, 0, SEEK_END);
			$size = ftell($f);	

			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);		
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			$beginning_of_line = "true";
			$line_position = 0;
			
			$record_number = 0;

			while (true)
			{			

				if (ftell($f) + $length >= $size)
				{
					$length = $size - ftell($f);
					$end_of_data = "true";
				}


				$starting_position = ftell($f);
				$data = fread($f, $length);
				
				
				for ($i = 0; $i < $length; $i++)
				{
					if ($data[$i] == "\n")
					{
												
						$array = explode(",", $line);	
						
						$raw_record_number = $array[0];
						
						$array[0] = $record_number;
						
						update_raw_record($raw_record_number, $array);
						
						$record_number++;					
						
						$line = "";
						$beginning_of_line = "true";
						
						
					}
					else if ($data[$i] != "\r")
					{					
						if ($beginning_of_line == "true")
						{
							$line_position = $starting_position + $i;
						}
						$line = $line.$data[$i];
						$beginning_of_line = "false";
					}
				}
				
				if ($end_of_data == "true")
				{
					break;
				}
				
			}

			
			flock($f, LOCK_UN);
			
		}	
		
			
			
		fclose($f);
		
		
		
		
			
			
	}

	
		
	function insert_record($table_name, $array)
	{
		
		
		try
		{
			
			$path = get_database_path(); 

			$f = fopen($path,"r+");		
			
			if (flock($f, LOCK_EX | LOCK_NB) == true)
			{
				
					
				// get the file size
				
				fseek($f, 0, SEEK_END);
				$file_size = ftell($f);
				
				// save the position for the beginning of the data section
				
				fseek($f, 0, SEEK_SET);
				
				while (true)
				{
					$line = fgets($f);
					
					
					if (contains_string($line, "data:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
							
				$size = ftell($f);
				
				// check to make sure that the table exists
				
				
				fseek($f, 0, SEEK_SET);
							
				while (true)
				{
					$line = fgets($f);
					if (contains_string($line, "tables:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
				
				$exists = "false";			
				while (true)
				{
					$string = fgets($f);				
					
					if (contains_string($string, $table_name) == "true")
					{
						$exists = "true";
						break;
					}
					
					if (contains_string($line, "data:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
				
				if ($exists == "true")
				{
					// increment the record number
					
					if ($file_size > $size)
					{				
						fseek($f, $file_size - 3, SEEK_SET);
						get_beginning_of_line($f);
						$line = fgets($f);			

						$line_array = explode(",", $line);
						
						
						$record_number = $line_array[0] + 1;
						
					}
					else
					{
						$record_number = "0";
					}
									
					// format the string
					
					$line = $record_number.",".$table_name.",";
					for ($i = 0; $i < sizeof($array); $i++)
					{
						$line = $line.strlen($array[$i]);
						$line = $line.",";
					}
					for ($i = 0; $i < sizeof($array); $i++)
					{
						$line = $line.$array[$i];
					}
					
					$line = $line."\n";
								
							
					fseek($f, $file_size, SEEK_SET);
					
					$blanks = "";
					
					for ($i = 0; $i < strlen($line) - 1; $i++)
					{
						$blanks = $blanks."_";
					}
					
					$blanks = $blanks."\n";
				
					// write the blank spaces first

					$saved_position = ftell($f);
					fwrite($f, $blanks);
					
					// write the formatted line for recovery
					
					$recovery_position = ftell($f);
					fwrite($f, "insert-recovery:".$line);
					
					
					// write the formatted line 
					
					fseek($f, $saved_position, SEEK_SET);
					fwrite($f, $line);
		
		
	
					// read the recovery string
					
					fseek($f, $recovery_position, SEEK_SET);
					$recovery_string = fgets($f);
		
		
		
					$arr = split_string($recovery_string, ':');
					
					// check to make sure that the whole string is written
					
					$error = "false";									
					
					if (strlen($arr[1]) != strlen($line))
					{
						$error = "true";
					}
					
					if ($error == "false")
					{
						// make sure that the data written matches the recovery string
				
						if ($arr[1] != $line)
						{
							$error = "true";
						}
				
					}
										
					
					// discard the recovery string
					
					ftruncate($f, $recovery_position);


					// throw exception message if an error occurd
					
					if ($error == "true")
					{
						throw Exception("error durig insert");
					}
					
					
					
				}
				
				
				flock($f, LOCK_UN);
			
			}
			
			fclose($f);
			
		}
		catch (Exception $e)
		{
			print $e->getMessage();
		}
	}
		
		


		
	function test_insert_record($table_name, $array)
	{
		
		
		try
		{
			
			$path = get_database_path(); 

			$f = fopen($path,"r+");		
			
			if (flock($f, LOCK_EX | LOCK_NB) == true)
			{
				
					
				// get the file size
				
				fseek($f, 0, SEEK_END);
				$file_size = ftell($f);
				
				// save the position for the beginning of the data section
				
				fseek($f, 0, SEEK_SET);
				
				while (true)
				{
					$line = fgets($f);
					
					
					if (contains_string($line, "data:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
							
				$size = ftell($f);
				
				// check to make sure that the table exists
				
				
				fseek($f, 0, SEEK_SET);
							
				while (true)
				{
					$line = fgets($f);
					if (contains_string($line, "tables:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
				
				$exists = "false";			
				while (true)
				{
					$string = fgets($f);				
					
					if (contains_string($string, $table_name) == "true")
					{
						$exists = "true";
						break;
					}
					
					if (contains_string($line, "data:") == "true") break;
					if (ftell($f) >= $file_size) break;
				}
				
				if ($exists == "true")
				{
					// increment the record number
					
					if ($file_size > $size)
					{				
						fseek($f, $file_size - 3, SEEK_SET);
						get_beginning_of_line($f);
						$line = fgets($f);			

						$line_array = explode(",", $line);
						
						
						$record_number = $line_array[0] + 1;
						
					}
					else
					{
						$record_number = "0";
					}
									
					// format the string
					
					$line = $record_number.",".$table_name.",";
					for ($i = 0; $i < sizeof($array); $i++)
					{
						$line = $line.strlen($array[$i]);
						$line = $line.",";
					}
					for ($i = 0; $i < sizeof($array); $i++)
					{
						$line = $line.$array[$i];
					}
					
					$line = $line."\n";
								
							
					fseek($f, $file_size, SEEK_SET);
					
					$blanks = "";
					
					for ($i = 0; $i < strlen($line) - 1; $i++)
					{
						$blanks = $blanks."_";
					}
					
					$blanks = $blanks."\n";
				
					// write the blank spaces first

					$saved_position = ftell($f);
					fwrite($f, $blanks);
					
					// write the formatted line for recovery
					
					$recovery_position = ftell($f);
					fwrite($f, "insert-recovery:".$line);
					
					
					// write the formatted line 
					
					fseek($f, $saved_position, SEEK_SET);
					fwrite($f, $line);
		
		
	
					// read the recovery string
					
					fseek($f, $recovery_position, SEEK_SET);
					$recovery_string = fgets($f);
		
		
		
					$arr = split_string($recovery_string, ':');
					
					// check to make sure that the whole string is written
					
					$error = "false";									
					
					if (strlen($arr[1]) != strlen($line))
					{
						$error = "true";
					}
					
					if ($error == "false")
					{
						// make sure that the data written matches the recovery string
				
						if ($arr[1] != $line)
						{
							$error = "true";
						}
				
					}
										
					
					
					
				}
				
				
				flock($f, LOCK_UN);
			
			}
			
			fclose($f);
			
		}
		catch (Exception $e)
		{
			print $e->getMessage();
		}
	}
		
		
		
		

	function delete_record($record_number)
	{

		
		$path = get_database_path(); 


		$f = fopen($path,"r+");	
				
		if (flock($f, LOCK_EX | LOCK_NB) == true)
		{
					
			// get the file size
			
			fseek($f, 0, SEEK_END);
			$size = ftell($f);	

			// get the beginning of the data section
			
			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);		
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			// initialize variables
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			$beginning_of_line = "true";
			$line_position = 0;
			
			

			while (true)
			{			

				if (ftell($f) + $length >= $size)
				{
					$length = $size - ftell($f);
					$end_of_data = "true";
				}

				// read the data

				$starting_position = ftell($f);
				$data = fread($f, $length);
				
				// loop through the data
				
				for ($i = 0; $i < $length; $i++)
				{
					if ($data[$i] == "\n")
					{
												
						$array = explode(",", $line);	
						
						// check if the record number equals the target record number
							
						if ($array[0] == $record_number)
						{		
									
							$end_of_data = "true";
							
							// move everything up from this point
							
							$result = move_everything_up("true", "move_everything_up", $f, $line_position, $line_position + strlen($line) + 1);
							
														
							if ($result == "false")
							{
								print "error occured\n";
							}
						
							
							
							break;
							
							
						}
						
						// new line
						
						$line = "";
						$beginning_of_line = "true";
						
						
					}
					else if ($data[$i] != "\r")
					{					
						// save the line position
				
						if ($beginning_of_line == "true")
						{
							$line_position = $starting_position + $i;
						}
						
						// append to line
						
						$line = $line.$data[$i];
						$beginning_of_line = "false";
					}
				}
				
				if ($end_of_data == "true")
				{
					break;
				}
				
			}
			
			flock($f, LOCK_UN);

		}
		
			
			
		fclose($f);
		
		
		
		
			
			
	}

			
			
	function update_record($record_number, $array2)
	{
		
		
		
		$path = get_database_path(); 


		$f = fopen($path,"r+");	
		
		
		if (flock($f, LOCK_EX | LOCK_NB) == true)
		{
		
			
			// get the file size
			
			fseek($f, 0, SEEK_END);
			$size = ftell($f);	
			
			
			// get the beginning of the data section

			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);		
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			
			// initialize variables
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			$beginning_of_line = "true";
			$line_position = 0;
			
			
			
			

			while (true)
			{			

				if (ftell($f) + $length >= $size)
				{
					$length = $size - ftell($f);
					$end_of_data = "true";
				}

				// read the data

				$starting_position = ftell($f);
				$data = fread($f, $length);
				
				// loop through the data
				
				for ($i = 0; $i < $length; $i++)
				{
					if ($data[$i] == "\n")
					{
												
						$array = explode(",", $line);	
						
							
						// check if the record number equals the target record number
						if ($array[0] == $record_number)
						{		
							// Note:  for updating transactions, we are (1) moving everything up then (2) moving everything down
								
							$end_of_data = "true";				
							
							$result = move_everything_up("false", "move_everything_up", $f, $line_position, $line_position + strlen($line) + 1);
							
							
							if ($result == "true")
							{
								// format the line with the new data
								
								$line = "";
								for ($i2 = 0; $i2 < 2; $i2++)
								{
									$line = $line.$array[$i2];
									$line = $line.",";
								}
								
								for ($i2 = 0; $i2 < sizeof($array2); $i2++)
								{
									$line = $line.strlen($array2[$i2]);
									$line = $line.",";
								}							
								
								for ($i2 = 0; $i2 < sizeof($array2); $i2++)
								{
									$line = $line.$array2[$i2];
								}
								$line = $line."\n";
								
								
								
								
								// move everything down
								
								$result = move_everything_down("false", "move_everything_down", $f, 0, $line_position, $line);
												
												
												
								// write the new line
												
								if ($result == "true")
								{
													
									fseek($f, $line_position, SEEK_SET);
									
									fwrite($f, $line);
								
								}
								else if ($result == "false")
								{
									print "error occured\n";
								}
								
							}
												
								
							
							break;
							
							
						}
						
						// new line from file
						
						$line = "";
						$beginning_of_line = "true";
						
						
					}
					else if ($data[$i] != "\r")
					{					
						// save the line position
						
						if ($beginning_of_line == "true")
						{
							$line_position = $starting_position + $i;
						}
						
						// append the line
						
						$line = $line.$data[$i];
						$beginning_of_line = "false";
					}
				}
				
				if ($end_of_data == "true")
				{
					break;
				}
				
			}

			flock($f, LOCK_UN);
			
		}
			
			
		fclose($f);
		
		
		
		
		
	}



		

	function perform_recovery()
	{

		
		$path = get_database_path(); 


		$f = fopen($path,"r+");	
		
		
		if (flock($f, LOCK_EX | LOCK_NB) == true)
		{

			// get the file size
			
			fseek($f, 0, SEEK_END);
			$file_size = ftell($f) - 2;
			
			// point to the beginning of the last line in the file
			
			fseek($f, $file_size, SEEK_SET);
			get_beginning_of_line($f);
			$saved_position = ftell($f);					
			$line = fgets($f);
			
			// check to see if a recovery point has been saved
	
			// for insert operation
			if (contains_string($line, "insert-recovery"))
			{
				$arr = split_string($line, ':');
				fseek($f, $saved_position - 2, SEEK_SET);
				
				// write the recovery string to the previous line
				get_beginning_of_line($f);
				fwrite($f, $arr[1]);
				
				// discard the recovery string
				ftruncate($f, ftell($f));
				
			}
			// for update and delete operations
			else if (contains_string($line, "recovery point"))
			{
				// get the recovery info
				
				$arr = split_string($line, ':');
				
				$position = $arr[1];			
				
				fseek($f, $position, SEEK_SET);
				$line = fgets($f);
				
				$arr = split_string($line, ':');
				
				$operation = $arr[0];
				$length = $arr[1];
				$next_write_pos = $arr[2];
				
				
				// read from the recovery point
				
				$next_read_pos2 = $arr[2] - $length - strlen($arr[4]);
							
				$data = fread($f, $length);
				
				
				
				// write the data			
				
				$result = "true";
				
				if ($result == "true")
				{
					try
					{				
						fseek($f, $next_write_pos, SEEK_SET);
						
						fwrite($f, $data);
						
						$next_write_pos = ftell($f);
						
					}
					catch (Exception $e)
					{
						$result = "false";
						echo $e->GetMessage();
					}
				}
				
				
				if ($result == "true")
				{
					ftruncate($f, $position);
					
				}
					
				
				
				// read-write from saved position
				
				if ($operation == "move_everything_up")
				{			
					$next_read_pos = $arr[3];
					$result = move_everything_up("false", "move_everything_up", $f, $next_write_pos, $next_read_pos);
				}
				else if ($operation == "move_everything_down")
				{
					$target_position = $arr[3];
					$string = $arr[4];
					
					
					$result = move_everything_down("false", "move_everything_down", $f, $next_read_pos2, $target_position, $string);

					if ($result == "true")
					{
						fseek($f, $target_position, SEEK_SET);
						
						fwrite($f, $string);
						
					}
									
							
				}
				
				if ($result == "false")
				{
					print "error occured\n";
				}
							
				
			}
			
			
			flock($f, LOCK_UN);
			
		}
		
			
		fclose($f);
		
		
		
		
	}
	
	
	


	function select_data($record_number)
	{
		
		$array2 = null;
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");	

		
		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		$has_table = "false";
		
		while (true)
		{
			$line = fgets($f);
			
			if (contains_string($line, $table_name) == "true")
			{
				$has_table = "true";
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		
		if ($has_table == "true")
		{
			
			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);
				
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			
			
			
			
			$array2 = array();
			
			
			
			
			{
				
				while (true)
				{			

					if (ftell($f) + $length >= $size)
					{
						$length = $size - ftell($f);
						$end_of_data = "true";
					}



					$data = fread($f, $length);
					
					
					for ($i = 0; $i < $length; $i++)
					{
						if ($data[$i] == "\n")
						{
													
							$array = explode(",", $line);		
							

							if ($array[0] == $record_number)
							{
								
								$cols = get_number_of_columns($array[1]);
								
								
								array_push($array2, strval($cols));
								
								array_push($array2, ":");
								
								$pos = 0;	
																
								for ($i2 = 0; $i2 < $cols; $i2++)
								{
									$len2 = $array[2 + $i2];
									
									$val = substr($array[$cols + 2], $pos, intval($len2));
								
									
									array_push($array2, $val);			

									if ($i2 < $cols - 1)
									{
										array_push($array2, ":");
									}
									
									$pos += intval($array[2 + $i2]);	
								}
								
								$end_of_data = "true";
								break;
								
							
							}
							
							
							
							
							
							$line = "";
							
							
						}
						else if ($data[$i] != "\r")
						{					
							$line = $line.$data[$i];
						}
					}
					
					if ($end_of_data == "true")
					{
						break;
					}
					
				}
				
			}
			
			
			
		}
			
			
		fclose($f);
		
		
		return $array2;
		
		
	}


	

	function select_records($table_name, $starting_result_number, $number_of_results, $criteria)
	{
		$results = array();
		
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");	

		
		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		$has_table = "false";
		
		while (true)
		{
			$line = fgets($f);
			
			if (contains_string($line, $table_name) == "true")
			{
				$has_table = "true";
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		
		if ($has_table == "true")
		{
			
			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);
				
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			
			
			
			$cols = get_number_of_columns($table_name);
			
			$result_number = 0;
			
			

			{
				
				while (true)
				{			

					if (ftell($f) + $length >= $size)
					{
						$length = $size - ftell($f);
						$end_of_data = "true";
					}



					$data = fread($f, $length);
					
					
					for ($i = 0; $i < $length; $i++)
					{
						if ($data[$i] == "\n")
						{
													
							$array = explode(",", $line);	
							if ($array[1] == $table_name)
							{
								$c = 0;
								for ($i2 = 0; $i2 < sizeof($criteria); $i2++)
								{
									$cond = $criteria[$i2];
									$val = get_column_value($table_name, $cond[0], $array);
									if ($cond[1] == "==" && $val == $cond[2])
									{										
										$c++;
									}																	
								}								
								if ($c == sizeof($criteria))
								{
									if ($result_number >= $starting_result_number && sizeof($results) < $number_of_results)
									{
										$pos = 0;		
										$array2 = array();										
										for ($i2 = 0; $i2 < $cols; $i2++)
										{
											$len2 = $array[2 + $i2];											
											$val = substr($array[$cols + 2], $pos, intval($len2));											
											array_push($array2, $val);		
											$pos += intval($array[2 + $i2]);	
										}										
										array_push($results, $array2);
									}
									else if (sizeof($results) >= $number_of_results)
									{
										$end_of_data = "true";
										break;
									}
									$result_number++;
								}
								
							}
							
							
							/*
							if ($end_of_data == "true")
							{
								break;
							}*/
							
							
							$line = "";
							
							
						}
						else if ($data[$i] != "\r")
						{					
							$line = $line.$data[$i];
						}
					}
					
					if ($end_of_data == "true")
					{
						break;
					}
					
				}
				
			}
			
			
			
		}
			
			
		fclose($f);
		
		
		return $results;
		
		
	}
	
	
	

	function select_records_backwards($table_name, $starting_result_number, $number_of_results, $criteria)
	{
		$results = array();
		
		
		$path = get_database_path(); 

		$f = fopen($path,"r+");	

		
		
		fseek($f, 0, SEEK_END);
		$size = ftell($f);
		
		fseek($f, 0, SEEK_SET);
		
		while (true)
		{
			$line = fgets($f);
			if (contains_string($line, "tables:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		$has_table = "false";
		
		while (true)
		{
			$line = fgets($f);
			
			if (contains_string($line, $table_name) == "true")
			{
				$has_table = "true";
				break;
			}
			
			if (contains_string($line, "data:") == "true") break;
			if (ftell($f) >= $size) break;
		}
		
		
		if ($has_table == "true")
		{
			
			fseek($f, 0, SEEK_SET);
			
			
			while (true)
			{
				$line = fgets($f);
				
				
				if (contains_string($line, "data:") == "true") break;
				if (ftell($f) >= $size) break;
			}
			
			$starting_position = ftell($f);
			
			
			$length = 1000;		
			$end_of_data = "false";
			$line = "";
			
			
			
			$cols = get_number_of_columns($table_name);
			
			$result_number = 0;
			
			
			fseek($f, 0, SEEK_END);
			
			$position = ftell($f);
			
			$line = "";
			
			while (true)
			{
				$prev_position = $position;
				$position -= $length;
				
				if ($position < $starting_position) 
				{
					$position = $starting_position;
					$length = $prev_position - $starting_position;
				}
				
				fseek($f, $position, SEEK_SET);			
				
				$data = fread($f, $length);
				
				if ($position > $starting_position)
				{
				
					
					for ($i = $length - 1; $i >= 0; $i--)
					{
						if ($data[$i] == "\n")
						{
							$line = reverse_string($line);
							
							
							$array = explode(",", $line);	
							if ($array[1] == $table_name)
							{
								$c = 0;
								for ($i2 = 0; $i2 < sizeof($criteria); $i2++)
								{
									$cond = $criteria[$i2];
									$val = get_column_value($table_name, $cond[0], $array);																		
									if ($cond[1] == "==" && $val == $cond[2])
									{										
										$c++;
									}			
								}												
								if ($c > 0)
								{
									if ($result_number >= $starting_result_number && sizeof($results) < $number_of_results)
									{
										$pos = 0;		
										$array2 = array();										
										for ($i2 = 0; $i2 < $cols; $i2++)
										{
											$len2 = $array[2 + $i2];											
											$val = substr($array[$cols + 2], $pos, intval($len2));											
											array_push($array2, $val);		
											$pos += intval($array[2 + $i2]);	
										}										
										array_push($results, $array2);
									}
									else if (sizeof($results) >= $number_of_results)
									{
										$end_of_data = "true";
										break;
									}
									$result_number++;
								}
								
							}
							
							
							
							
							//for ($i2 = 0; $i2 < strlen($line); $i2++)
							//{
							//	print $line[$i2];
							//}
							//print "\n";
							
							$line = "";
						}
						else if ($data[$i] != "\r")
						{
							$line = $line.$data[$i];
						}
					}
				
				}
				else
				{
					
					
					
					for ($i = $length - 1; $i >= 0; $i--)
					{
						if ($data[$i] == "\n")
						{
							$line = reverse_string($line);
							
							$array = explode(",", $line);	
							if ($array[1] == $table_name)
							{
								$c = 0;
								for ($i2 = 0; $i2 < sizeof($criteria); $i2++)
								{
									$cond = $criteria[$i2];
									$val = get_column_value($table_name, $cond[0], $array);
									if ($cond[1] == "==" && $val == $cond[2])
									{										
										$c++;
									}																	
								}								
								if ($c > 0)
								{
									if ($result_number >= $starting_result_number && sizeof($results) < $number_of_results)
									{
										$pos = 0;		
										$array2 = array();										
										for ($i2 = 0; $i2 < $cols; $i2++)
										{
											$len2 = $array[2 + $i2];											
											$val = substr($array[$cols + 2], $pos, intval($len2));											
											array_push($array2, $val);		
											$pos += intval($array[2 + $i2]);	
										}										
										array_push($results, $array2);
									}
									else if (sizeof($results) >= $number_of_results)
									{
										$end_of_data = "true";
										break;
									}
									$result_number++;
								}
								
							}
							
							
							//for ($i2 = 0; $i2 < strlen($line); $i2++)
							//{
							//	print $line[$i2];
							//}
							//print "\n";
							
							
							$line = "";
						}
						else if ($data[$i] != "\r")
						{
							$line = $line.$data[$i];
						}
					}
					
					
					$line = reverse_string($line);
					
					$array = explode(",", $line);	
					if ($array[1] == $table_name)
					{
						$c = 0;
						for ($i2 = 0; $i2 < sizeof($criteria); $i2++)
						{
							$cond = $criteria[$i2];
							$val = get_column_value($table_name, $cond[0], $array);
							if ($cond[1] == "==" && $val == $cond[2])
							{										
								$c++;
							}																	
						}								
						if ($c > 0)
						{
							if ($result_number >= $starting_result_number && sizeof($results) < $number_of_results)
							{
								$pos = 0;		
								$array2 = array();										
								for ($i2 = 0; $i2 < $cols; $i2++)
								{
									$len2 = $array[2 + $i2];											
									$val = substr($array[$cols + 2], $pos, intval($len2));											
									array_push($array2, $val);		
									$pos += intval($array[2 + $i2]);	
								}										
								array_push($results, $array2);
							}
							else if (sizeof($results) >= $number_of_results)
							{
								$end_of_data = "true";
								break;
							}
							$result_number++;
						}
						
					}
							
					
					//for ($i2 = strlen($line) - 1; $i2 >= 0; $i2--)
					//{
					//	print $line[$i2];
					//}
					//print "\n";
					
					
					
					
					break;
				}
				
			
			}
			
			
			
			
		}
			
			
		fclose($f);
		
		
		return $results;
		
		
	}
	
	
	
	
	
	//------------------------------------------------------------
	//main
	
	function test1()
	{
		
		
		
		
		if (database_exists() == "true")
		{
			delete_database();
		}
		
		
		
		if (database_exists() == "false")
		{
			create_database();
			
			
			$column_names = array("first name", "last name");
			$column_types = array("string", "string");				
			
			create_table("contacts", sizeof($column_names), $column_names, $column_types);

			
		
			print "inserting records\n";
			
			for ($i = 0; $i < 400; $i++)
			{			
				$array = array("john".$i, "smith".$i);
				
				if (check_input("contacts", $array) == "true")
				{
					insert_record("contacts", $array);
				}
			}
			
			print "done\n";
			
			
			//print "deleting record\n";		
			
			//delete_record("100");
			
			print "updating record\n";
				
			$array = array("davidfrom toronto", "jonesfromottawa");
			
			update_record("200", $array);
		
			
			print "done\n";
				
			print "performing recovery\n";
			
			perform_recovery();
		}
		
		print "done\n";
		
		
	}
	
	
	function test2()
	{
		
		
		
		if (database_exists() == "true")
		{
			delete_database();
		}
		
		
		
		if (database_exists() == "false")
		{
			create_database();
			
			
			$column_names = array("first name", "last name");
			$column_types = array("string", "string");				
			
			create_table("contacts", sizeof($column_names), $column_names, $column_types);

			
		
			print "inserting records\n";
			
			for ($i = 0; $i < 400; $i++)
			{			
				$array = array("john".$i, "smith".$i);
				
				if (check_input("contacts", $array) == "true")
				{					
					insert_record("contacts", $array);
				}
			}
			
			print "done\n";
			
			
			$array = array("john-test", "smith-test");
			test_insert_record("contacts", $array);
					
					
			//print "deleting record\n";		
			
			
			print "performing recovery\n";
			
			perform_recovery();
			
		}
		
		
		
		
		
	}
	
	function test3()
	{
		
		$path = 'C:\Users\Russell Brown\Documents\Database Code\test_data.dat';
		
		$f = fopen($path, "w+");
		
		if (flock($f, LOCK_EX | LOCK_NB))
		{
			print "writing\n";

			fwrite($f, "hello world");
			
			
			$f2 = fopen($path, "w+");
			
			if (flock($f2, LOCK_EX | LOCK_NB) == false)
			{				
				print "unable to obtain lock\n";
			}
			else
			{
				print "lock obtained\n";
			}
			
		
			flock($f, LOCK_UN);
			
			
			$f2 = fopen($path, "w+");
			
			if (flock($f2, LOCK_EX | LOCK_NB) == false)
			{				
				print "unable to obtain lock\n";
			}
			else
			{
				print "lock obtained\n";
			}
			
		}
		
		fclose($f);
	}
	
	
	
	
	function test4()
	{
		
		
		
		if (database_exists() == "true")
		{
			delete_database();
		}
		
		
		
		if (database_exists() == "false")
		{
			create_database();
			
			
			$column_names = array("first name", "last name");
			$column_types = array("string", "string");				
			
			create_table("contacts", sizeof($column_names), $column_names, $column_types);

			
		
			print "inserting records\n";
			
			for ($i = 0; $i < 200; $i++)
			{			
				$array = array("john".$i, "smith".$i);
				
				if (check_input("contacts", $array) == "true")
				{					
					insert_record("contacts", $array);
				}
			}
			
			
						/*
			$arr = select_data("100");
			
			for ($i = 0; $i < sizeof($arr); $i++)
			{
				print $arr[$i];
			}
			
			print "\n";
			
			*/
				
			print "done\n";
				
		}
		
		
		
		
		
	}
	
	
	
	function test5()
	{
		
		
		
		if (database_exists() == "true")
		{
			
			print "selecting records\n";

			$criteria = array();
			array_push($criteria, array("first name", "==", "john101"));

			$results = select_records("contacts", 0, 10000, $criteria);
			
			
			print "results:\n";
			
			
			if (sizeof($results) > 0)
			{


				$cols = get_number_of_columns("contacts");


				for ($i = 0; $i < sizeof($results); $i++)
				{
					$arr = $results[$i];
					
					for ($i2 = 0; $i2 < sizeof($arr); $i2++)
					{
						print $arr[$i2];
					}
					
					print "\n";
				}
			}

			
		}
		
			
		print "done\n";
	}
	
	
	
	function test6()
	{
		
		
		
		if (database_exists() == "true")
		{
			
			print "selecting records\n";

			$criteria = array();
			array_push($criteria, array("first name", "==", "john199"));
			array_push($criteria, array("first name", "==", "john197"));
			array_push($criteria, array("first name", "==", "john0"));

			$results = select_records_backwards("contacts", 0, 10000, $criteria);
			
			
			print "results:\n";
			
			
			if (sizeof($results) > 0)
			{


				$cols = get_number_of_columns("contacts");


				for ($i = 0; $i < sizeof($results); $i++)
				{
					$arr = $results[$i];
					
					for ($i2 = 0; $i2 < sizeof($arr); $i2++)
					{
						print $arr[$i2];
					}
					
					print "\n";
				}
			}

			
		}
		
			
		print "done\n";
	}
	
	
	
	
	function test7()
	{
		
		
		
		if (database_exists() == "true")
		{
			delete_database();
		}
		
		
		
		if (database_exists() == "false")
		{
			create_database();
			
			
			$column_names = array("first name", "last name");
			$column_types = array("string", "string");				
			
			create_table("contacts", sizeof($column_names), $column_names, $column_types);

			
		
			print "inserting records\n";
			
			for ($i = 0; $i < 200; $i++)
			{			
				$array = array("john".$i, "smith".$i);
				
				if (check_input("contacts", $array) == "true")
				{					
					insert_record("contacts", $array);
				}
			}
			
			
			//print "updating record\n";
			
			//$array = array("davidfrom toronto of canada", "jonesfromottawa");
			
			//update_record("100", $array);
			
			//print "normalizing records\n";
			//normalize_records();
			
			print "done\n";
				
		}
		
		
		
		
		
	}
	
	function test8()
	{
			
		print "normalizing records\n";
		normalize_records();
		
		print "done\n";
	
	}
	
	
	//test7();
	
	
	
	
	//test6();
	
	

	//test4();
	
	

?>



