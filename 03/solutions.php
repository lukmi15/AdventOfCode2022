<?php

	/*AoC2022 day 3
	Author(s)		: Lukas Mirow
	Date of creation	: 12/4/2022
	*/

	define('TEST_INPUT_FILE', 'testinput1.txt');
	define('INPUT_FILE', 'input.txt');

	//Calculates priority from item
	//Returns false on error
	function item_to_priority($item)
	{
		if ($item >= "a" and $item <= "z")
			return ord($item) - ord('a') + 1;
		if ($item >= "A" and $item <= "Z")
			return ord($item) - ord('A') + 27;
		return false;
	}

	//Part 1
	$flag = 0;
	foreach (file(INPUT_FILE) as $rucksack)
	{
		$rucksack_size = strlen($rucksack);
		$compartment_size = (int)($rucksack_size/2);
		for ($i = 0; $i < $compartment_size; $i++)
			for ($j = $compartment_size; $j < $rucksack_size; $j++)
				if ($rucksack[$i] == $rucksack[$j])
				{
					$flag += item_to_priority($rucksack[$i]);
					$i = $compartment_size;
					$j = $rucksack_size;
				}
	}
	echo "Flag for part 1: $flag\n";

	//Part 2
	$flag = 0;
	$NRUCKS = 3;
	$rucks = array(null, null, null);
	$rucksack_sizes = array(null, null, null);
	foreach (file(INPUT_FILE) as $rucksack)
	{

		//Ret rucksacks for this group
		for ($i = 0; $i < $NRUCKS; $i++)
			if ($rucks[$i] == null)
			{
				$rucks[$i] = $rucksack;
				$rucksack_sizes[$i] = strlen($rucksack);
				break;
			}

		//Check rucksacks for badges
		for ($i = 0; $i < $rucksack_sizes[0]; $i++)
			for ($j = 0; $j < $rucksack_sizes[1]; $j++)
				for ($k = 0; $k < $rucksack_sizes[2]; $k++)
					if ($rucks[0][$i] == $rucks[1][$j])
						if ($rucks[1][$j] == $rucks[2][$k])
						{
							$flag += item_to_priority($rucks[0][$i]);
							$i = $rucksack_sizes[0];
							$j = $rucksack_sizes[1];
							$k = $rucksack_sizes[2];
							$rucks = array(null, null, null);
							$rucksack_sizes = array(null, null, null);
						}

	}
	echo "Flag for part 2: $flag\n";
?>
