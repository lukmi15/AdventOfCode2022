<?php

	/*AoC2022 day 11 part 1
	Author(s)		: Lukas Mirow
	Date of creation	: 12/24/2022
	*/

	define('DEBUG', false);
	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');
	define('ITERATIONS', 20);


	function extract_items($itemline)
	{
		$line = explode(": ", $itemline);
		assert($line[0] == "  Starting items", "Expected \"  Starting items\" but found \"" . $line[0] . "\"");
		return explode(", ", $line[1]);
	}

	function extract_operation($opline)
	{

		//Sanity tests
		$line = explode(": ", $opline);
		assert($line[0] == "  Operation", "Expected \"  Operation\" but found \"" . $line[0] . "\"");
		$words = explode(" ", $line[1]);
		assert($words[0] == "new", "Expected \"new\" as first word in operation string but got \"" . $words[0] . "\"");
		assert($words[1] == "=", "Expected \"=\" as second word in operation string but got \"" . $words[0] . "\"");
		assert($words[2] == "old", "Expected \"old\" as fourth word in operation string but got \"" . $words[0] . "\"");
		assert(in_array($words[3], array("+", "-", "*", "/"), "Expected +, -, *, or / as fifth word in operation string but got \"" . $words[0] . "\""));
		assert($words[4] == "old" or is_numeric($words[3]), "Expected sixth word in operation string to be either \"old\" or a number, but got \"" . $words[0] . "\"");

		//Generate operation function
		if ($words[4] == "old")
		{
			if ($words[3] == "+")
				return function ($old) {return $old + $old;};
			elseif ($words[3] == "-")
				return function ($old) {return $old - $old;};
			elseif ($words[3] == "*")
				return function ($old) {return $old * $old;};
			elseif ($words[3] == "/")
				return function ($old) {return $old / $old;};
			else
				assert(false, "Failed to parse operation, \"" . $words[3] . "\" is not a valid operator");
		}
		else
		{
			$rhs = (int)$words[4];
			if ($words[3] == "+")
				return function ($old) use ($rhs) {return $old + $rhs;};
			elseif ($words[3] == "-")
				return function ($old) use ($rhs) {return $old - $rhs;};
			elseif ($words[3] == "*")
				return function ($old) use ($rhs) {return $old * $rhs;};
			elseif ($words[3] == "/")
				return function ($old) use ($rhs) {return $old / $rhs;};
			else
				assert(false, "Failed to parse operation, \"" . $words[3] . "\" is not a valid operator");
		}

	}

	function extract_test($testline)
	{
		$words = explode(" ", $testline);
		assert($words[0] == "Test:", "Expected \"Test:\" as first word in test string but got \"" . $words[0] . "\"");
		assert($words[1] == "divisible", "Expected \"divisible\" as second word in test string but got \"" . $words[1] . "\"");
		assert($words[2] == "by", "Expected \"by\" as third word in test string but got \"" . $words[2] . "\"");
		$rhs = (int)$words[3];
		return function ($worry) use ($rhs) {return $worry % $rhs == 0;};
	}

	function extract_on_true($trueline)
	{
		$words = explode(" ", $trueline);
		assert($words[0] == "If:", "Expected \"If\" as first word in true string but got \"" . $words[0] . "\"");
		assert($words[1] == "true:", "Expected \"true:\" as second word in true string but got \"" . $words[1] . "\"");
		assert($words[2] == "throw", "Expected \"throw\" as third word in true string but got \"" . $words[2] . "\"");
		assert($words[3] == "to", "Expected \"to\" as fourth word in true string but got \"" . $words[3] . "\"");
		assert($words[4] == "monkey", "Expected \"monkey\" as fifth word in true string but got \"" . $words[4] . "\"");
		return (int)$words[5];
	}

	function extract_on_false($trueline)
	{
		$words = explode(" ", $trueline);
		assert($words[0] == "If:", "Expected \"If\" as first word in false string but got \"" . $words[0] . "\"");
		assert($words[1] == "false:", "Expected \"false:\" as second word in false string but got \"" . $words[1] . "\"");
		assert($words[2] == "throw", "Expected \"throw\" as third word in false string but got \"" . $words[2] . "\"");
		assert($words[3] == "to", "Expected \"to\" as fourth word in false string but got \"" . $words[3] . "\"");
		assert($words[4] == "monkey", "Expected \"monkey\" as fifth word in false string but got \"" . $words[4] . "\"");
		return (int)$words[5];
	}

	class Monkey
	{

		var $items;
		var $operation;
		var $test;
		var $on_true;
		var $on_false;

		public function __construct($monkey_string)
		{
			$lines = explode("\n", trim($monkey_string));

			//Header is being ignored

			//Parse starting items
			$line = trim($lines[1]);
			$this->items = extract_items($line);

			//Parse operation
			$line = trim($lines[2]);
			$this->operation = extract_operation($line);

			//Parse test
			$line = trim($lines[3]);
			$this->test = extract_test($line);

			//Parse on true
			$line = trim($lines[4]);
			$this->on_true = extract_on_true($line);

			//Parse on false
			$line = trim($lines[5]);
			$this->on_false = extract_on_false($line);

		}

	};


	//Parse and generate monkeys
	$monkeystring = "";
	$monkeys = array();
	$inspections = array();
	foreach (file(INPUT_FILE) as $line)
	{
		if ($line == "\n")
		{
			array_push($monkeys, new Monkey($monkeystring));
			array_push($inspections, 0);
			$monkeystring = "";
		}
		$monkeystring .= $line;
	}
	array_push($monkeys, new Monkey($monkeystring));
	array_push($inspections, 0);

	//Play game
	for ($i = 0; $i < ITERATIONS; $i++)
	{
		$nmonkeys = count($monkeys);
		for ($j = 0; $j < $nmonkeys; $j++)
		{
			$monkey = $monkeys[$j];
			while (count($monkey->items) > 0)
			{

				//Worry increases because monkey is playing with item
				$item = array_shift($monkey->items);
				$item = ($monkey->operation)($item);

				//Worry declines because item did not break
				$item = (int)($item / 3);

				//Monkey inspects and passes on item
				if (($monkey->test)($item) == true)
					array_push($monkeys[$monkey->on_true]->items, $item);
				else
					array_push($monkeys[$monkey->on_false]->items, $item);
				$inspections[$j]++;

			}
		}
	}

	//Calc flag
	rsort($inspections);
	echo "Flag for part 1: " . $inspections[0] * $inspections[1] . "\n";

?>
