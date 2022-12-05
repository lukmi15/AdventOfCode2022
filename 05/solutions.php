<?php

	/*AoC2022 day 5
	Author(s)		: Lukas Mirow
	Date of creation	: 12/5/2022
	*/

	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');
	define('CRATE_OFFSET', 4);

	//Append line from input to stacks
	function append_line_to_stacks($line, $stacks)
	{
		$linelen = strlen($line);
		for ($i = 1; $i < $linelen; $i += CRATE_OFFSET)
		{
			$crate = $line[$i];
			if ($crate != " ")
				array_push($stacks[(int)($i / CRATE_OFFSET)], $crate);
		}
		return $stacks;
	}

	//Append line from input to moves
	function append_line_to_moves($line, $moves)
	{
		$words = explode(" ", $line);
		$cwords = count($words);
		array_push($moves, array("amount" => (int)$words[1], "from" => (int)($words[3] - 1), "to" => (int)($words[5] - 1)));
		return $moves;
	}

	//Move `amount` crates from stack `$stack[$from_index]` to stack `$stack[$to_index]` for CrateMover9000
	function move($amount, $from_index, $to_index, $stacks)
	{
		$tmp = array();
		for ($i = 0; $i < $amount; $i++)
			array_push($tmp, array_pop($stacks[$from_index]));
		foreach ($tmp as $el)
			array_push($stacks[$to_index], $el);
		return $stacks;
	}

	//Move `amount` crates from stack `$stack[$from_index]` to stack `$stack[$to_index]` for CrateMover9001
	function move9k1($amount, $from_index, $to_index, $stacks)
	{
		$tmp = array();
		for ($i = 0; $i < $amount; $i++)
			array_push($tmp, array_pop($stacks[$from_index]));
		foreach (array_reverse($tmp) as $el)
			array_push($stacks[$to_index], $el);
		return $stacks;
	}

	//Init stacks
	$stacks = array();
	$lines = file(INPUT_FILE);
	$cstacks = (int)((strlen($lines[0]) + 1) / CRATE_OFFSET);
	for ($i = 0; $i < $cstacks; $i++)
		array_push($stacks, array());

	//Parse moves and stacks
	$moves = array();
	$stacks_done = false;
	foreach ($lines as $line)
	{
		if ($line == "\n" or ctype_digit($line[1]))
		{
			$stacks_done = true;
			continue;
		}
		if ($stacks_done == false)
			$stacks = append_line_to_stacks($line, $stacks);
		else
			$moves = append_line_to_moves($line, $moves);
	}

	//Reverse stacks for better maintainability
	for ($i = 0; $i < $cstacks; $i++)
		$stacks[$i] = array_reverse($stacks[$i]);

	//Do moves for CrateMover9000
	$stacks9k = array_slice($stacks, 0);
	foreach ($moves as $move)
		$stacks9k = move($move["amount"], $move["from"], $move["to"], $stacks9k);

	//Do moves for CrateMover9001
	$stacks9k1 = $stacks;
	foreach ($moves as $move)
		$stacks9k1 = move9k1($move["amount"], $move["from"], $move["to"], $stacks9k1);

	//Print flags
	echo "Flag for part 1: ";
	foreach ($stacks9k as $stack)
		echo end($stack);
	echo "\n";
	echo "Flag for part 2: ";
	foreach ($stacks9k1 as $stack)
		echo end($stack);
	echo "\n";
?>
