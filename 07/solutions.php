<?php

	/*AoC2022 day 7
	Author(s)		: Lukas Mirow
	Date of creation	: 12/7/2022
	*/

	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');
	define('FS_SIZE', 70000000);
	define('FS_SPACE_REQUIRED', 30000000);


	function calc_size($fs)
	{
		$res = 0;
		foreach ($fs as $node)
			if (is_numeric($node))
				$res += $node;
			else
				$res += calc_size($node);
		return $res;
	}

	function get_all_dirs($fs)
	{
		$ret = array();
		foreach ($fs as $node)
			if (!is_numeric($node))
			{
				array_push($ret, $node);
				$dirs_to_add = get_all_dirs($node);
				foreach ($dirs_to_add as $dir_to_add)
					array_push($ret, $dir_to_add);
			}
		return $ret;
	}

	function calc_flag1($fs)
	{
		$res = 0;
		foreach ($fs as $node)
		{
			if (is_numeric($node))
				continue;
			$nsize = calc_size($node);
			if ($nsize <= 100000)
				$res += $nsize;
			$res += calc_flag1($node);
		}
		return $res;
	}

	$fs = array();
	$in_ls = false;
	$cwd = "/";
	$dirptr = &$fs;
	foreach (file(INPUT_FILE) as $line)
	{
		$words = explode(" ", trim($line));

		//Handle file printed by `ls`
		if ($in_ls and $line[0] != "$")
		{
			if (is_numeric($words[0]))
				$dirptr[$words[1]] = (int)$words[0];
			else
				$dirptr[$words[1]] = array();
		}

		//Handle command
		else
		{
			$in_ls = false;
			switch ($words[1])
			{

				//Handle `cd`
				case "cd":

					//cd to /
					if ($words[2] == "/")
					{
						$cwd = "/";
						$dirptr = &$fs;
					}

					//cd one up
					elseif ($words[2] == "..")
					{
						$dirparts = explode("/", $cwd);
						$cwd = "";
						$dirptr = &$fs;
						array_shift($dirparts); //Remove empty string before first slash
						if (count($dirparts) == 0)
						{
							$cwd = "/";
							$dirptr = &$fs;
						}
						else
						{
							array_pop($dirparts); //Remove last dir, cause we want to go one up
							foreach ($dirparts as $dirpart)
							{
								$cwd .= "/" . $dirpart;
								$dirptr = &$dirptr[$dirpart];
							}
						}
					}

					//cd one down
					else
					{
						$dirptr = &$dirptr[$words[2]];
						if ($cwd != "/")
							$cwd .= "/";
						$cwd .= $words[2];
					}
					break;

				//Handle `ls`
				case "ls":
					$in_ls = true;
					break;
			}
		}
	}

	//Calculate flag1
	echo "Flag for part 1: " . calc_flag1($fs) . "\n";

	//Calculate flag2
	$fs_space_used = calc_size($fs);
	assert($fs_space_used <= FS_SIZE, "FS is already overfull, something must have gone wrong");
	$flag2 = 0;
	$free_space = FS_SIZE - $fs_space_used;
	if ($free_space < FS_SPACE_REQUIRED)
	{
		$dirs = get_all_dirs($fs);
		$to_free = FS_SPACE_REQUIRED - $free_space;
		$large_enough_dir_sizes = array();
		foreach ($dirs as $dir)
		{
			$dsize = calc_size($dir);
			if ($dsize >= $to_free)
				array_push($large_enough_dir_sizes, $dsize);
		}
		sort($large_enough_dir_sizes);
		if (count($large_enough_dir_sizes) == 0)
			$flag2 = $fs_space_used;
		else
			$flag2 = $large_enough_dir_sizes[0];
	}
	echo "Flag for part 2: $flag2\n";

?>
