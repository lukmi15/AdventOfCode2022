<?php

	/*AoC2022 day 8
	Author(s)		: Lukas Mirow
	Date of creation	: 12/10/2022
	*/

	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');

	function is_visible($i, $j, $forrest)
	{
		$height = $forrest[$i][$j];

		//Check north
		$tmpmax = 0;
		for ($k = 0; $k < $i; $k++)
			if ($forrest[$k][$j] > $tmpmax)
				$tmpmax = $forrest[$k][$j];
		if ($tmpmax < $height)
			return true;

		//Check south
		$crows = count($forrest);
		$tmpmax = 0;
		for ($k = $i+1; $k < $crows; $k++)
			if ($forrest[$k][$j] > $tmpmax)
				$tmpmax = $forrest[$k][$j];
		if ($tmpmax < $height)
			return true;

		//Check west
		$tmpmax = 0;
		for ($k = 0; $k < $j; $k++)
			if ($forrest[$i][$k] > $tmpmax)
				$tmpmax = $forrest[$i][$k];
		if ($tmpmax < $height)
			return true;

		//Check east
		$tmpmax = 0;
		$ccols = count($forrest[0]);
		for ($k = $j+1; $k < $ccols; $k++)
			if ($forrest[$i][$k] > $tmpmax)
				$tmpmax = $forrest[$i][$k];
		if ($tmpmax < $height)
			return true;

		return false;
	}

	function calc_scenic_score($i, $j, $forrest)
	{
		$height = $forrest[$i][$j];

		//Check north
		$cnorth = 0;
		for ($k = $i - 1; $k >= 0; $k--)
		{
			$cnorth++;
			if ($forrest[$k][$j] >= $height)
				break;
		}

		//Check south
		$crows = count($forrest);
		$csouth = 0;
		for ($k = $i+1; $k < $crows; $k++)
		{
			$csouth++;
			if ($forrest[$k][$j] >= $height)
				break;
		}

		//Check west
		$cwest = 0;
		for ($k = $j - 1; $k >= 0; $k--)
		{
			$cwest++;
			if ($forrest[$i][$k] >= $height)
				break;
		}

		//Check east
		$ceast = 0;
		$ccols = count($forrest[0]);
		for ($k = $j+1; $k < $ccols; $k++)
		{
			$ceast++;
			if ($forrest[$i][$k] >= $height)
				break;
		}

		return $cnorth * $ceast * $csouth * $cwest;
	}

	//Parse input
	$forrest = array();
	foreach (file(INPUT_FILE) as $line)
	{
		$tree_row = array();
		$line = trim($line);
		$linelen = strlen($line);
		for ($i = 0; $i < $linelen; $i++)
			array_push($tree_row, (int)$line[$i]);
		array_push($forrest, $tree_row);
	}

	//Count visible trees
	$crows = count($forrest);
	$ccols = count($forrest[0]);
	$cvisible = $crows*2 + 2*($ccols) - 4; //Corner trees are always visible
	for ($i = 1; $i < $crows - 1; $i++) //For each row (except first and last)
		for ($j = 1; $j < $ccols - 1; $j++) //For each column (except first and last)
			if (is_visible($i, $j, $forrest))
				$cvisible++;
	echo "Flag for part 1: $cvisible\n";

	//Calculate max scenic score
	$smax = 0;
	for ($i = 1; $i < $crows - 1; $i++) //For each row (except first and last)
		for ($j = 1; $j < $ccols - 1; $j++) //For each column (except first and last)
		{
			$sscore = calc_scenic_score($i, $j, $forrest);
			if ($sscore > $smax)
				$smax = $sscore;
		}
	echo "Flag for part 2: $smax\n";


?>
