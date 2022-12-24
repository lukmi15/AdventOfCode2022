<?php

	/*AoC2022 day 10
	Author(s)		: Lukas Mirow
	Date of creation	: 12/23/2022
	*/

	define('DEBUG', false);
	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');
	define('SCREEN_WIDTH', 40);
	define('SCREEN_HEIGHT', 6);


	function calc_part1flag($xvals)
	{
		$ret = 0;
		foreach (array(20, 60, 100, 140, 180, 220) as $cycle)
			$ret += $cycle * $xvals[$cycle];
		return $ret;
	}

	function noop(&$xvals, &$cycle)
	{
		array_push($xvals, $xvals[$cycle - 1]);
		$cycle++;
	}

	function addx(&$xvals, &$cycle, $val)
	{
		noop($xvals, $cycle);
		array_push($xvals, $xvals[$cycle - 1] + (int)$val);
		$cycle++;
	}

	function pixel_to_draw($xpixel, $xval)
	{
		if (abs($xpixel - $xval) <= 1)
			return "#";
		return ".";
	}


	$xvals = array();
	array_push($xvals, 1);
	array_push($xvals, 1);
	$cycle = 2;
	foreach (file(INPUT_FILE) as $line)
	{
		$line = trim($line);
		$words = explode(" ", $line);
		if ($words[0] == "noop")
			noop($xvals, $cycle);
		elseif ($words[0] == "addx")
			addx($xvals, $cycle, $words[1]);
	}

	echo "Flag for part 1: " . calc_part1flag($xvals) . "\n";
	echo "Flag for part 2:\n";
	$npixel = SCREEN_HEIGHT * SCREEN_WIDTH;
	array_shift($xvals);
	for ($i = 0; $i < $npixel; $i++)
	{
		if ($i % SCREEN_WIDTH == 0)
			echo "\n";
		$xpixel = $i % SCREEN_WIDTH;
		echo pixel_to_draw($xpixel, $xvals[$i]);
	}
	echo "\n";

?>
