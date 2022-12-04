<?php

	/*AoC2022 day 4
	Author(s)		: Lukas Mirow
	Date of creation	: 12/4/2022
	*/

	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');

	//Convert line from list of assignments to parsed array of elf tasks
	function line_to_tasks($line)
	{
		$i = 0;
		$linelen = strlen($line);

		//First task
		$from1 = "";
		for (; $line[$i] != "-"; $i++)
			$from1 .= $line[$i];
		$i++;
		$from1 = (int)$from1;
		$to1 = "";
		for (; $line[$i] != ","; $i++)
			$to1 .= $line[$i];
		$i++;
		$to1 = (int)$to1;

		//Second task
		$from2 = "";
		for (; $line[$i] != "-"; $i++)
			$from2 .= $line[$i];
		$i++;
		$from2 = (int)$from2;
		$to2 = "";
		for (; $i < $linelen; $i++)
			$to2 .= $line[$i];
		$to2 = (int)$to2;

		return array(array("from" => $from1, "to" => $to1), array("from" => $from2, "to" => $to2));
	}


	$full_overlaps = 0;
	$overlaps = 0;
	foreach (file(INPUT_FILE) as $line)
	{
		$tasks = line_to_tasks($line);

		//Part 1
		if ($tasks[0]["from"] <= $tasks[1]["from"] and $tasks[0]["to"] >= $tasks[1]["to"])
			$full_overlaps++;
		elseif ($tasks[1]["from"] <= $tasks[0]["from"] and $tasks[1]["to"] >= $tasks[0]["to"])
			$full_overlaps++;

		//Part 2
		if ($tasks[0]["from"] <= $tasks[1]["from"] and $tasks[0]["to"] >= $tasks[1]["from"])
			$overlaps++;
		elseif ($tasks[0]["from"] <= $tasks[1]["to"] and $tasks[0]["to"] >= $tasks[1]["to"])
			$overlaps++;
		elseif ($tasks[0]["from"] < $tasks[1]["from"] and $tasks[0]["to"] > $tasks[1]["to"])
			$overlaps++;
		elseif ($tasks[1]["from"] < $tasks[0]["from"] and $tasks[1]["to"] > $tasks[0]["to"])
			$overlaps++;

	}
	echo "Flag for part 1: $full_overlaps\n";
	echo "Flag for part 2: $overlaps\n";
?>
