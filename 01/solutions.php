<?php

	/*AoC2022 day 1
	Author(s)		: Lukas Mirow
	Date of creation	: 12/1/2022
	*/

	define('TEST_INPUT_FILE', 'testinput1.txt');
	define('INPUT_FILE', 'input.txt');

	//Count calories for elves
	$i = 0;
	$elfcals = array();
	$elfcals[0] = 0;
	foreach (file(INPUT_FILE) as $line)
	{

		//Handle elf separator
		if ($line == "\n")
		{
			$i++;
			$elfcals[$i] = 0;
			continue;
		}

		//Count up
		$elfcals[$i] += intval($line);

	}
	rsort($elfcals, SORT_NUMERIC);
	echo "Flag for part 1: $elfcals[0]\n";
	echo "Flag for part 2: " . $elfcals[0] + $elfcals[1] + $elfcals[2] . "\n";

?>
