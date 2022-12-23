<?php

	/*AoC2022 day 9
	Author(s)		: Lukas Mirow
	Date of creation	: 12/13/2022

	Coordinate system:
	     x
	 +---->
	 |
	 |
	y|
	 v

	*/

	define('DEBUG', false);
	define('TEST_INPUT_FILE', 'testinput.txt');
	define('TEST_INPUT_FILE2', 'testinput2.txt');
	define('INPUT_FILE', 'input.txt');
	define('KNOT_COUNT', 10);

	//Debug print
	function print_board($knotposs)
	{

		//Determine min and max values to know how to big the board must be
		$xvals = array();
		foreach ($knotposs as $knotpos)
			array_push($xvals, $knotpos[0]);
		$xmin = min($xvals);
		$xmax = max($xvals);
		$yvals = array();
		foreach ($knotposs as $knotpos)
			array_push($yvals, $knotpos[1]);
		$ymin = min($yvals);
		$ymax = max($yvals);

		//Draw board
		for ($y = $ymin; $y <= $ymax; $y++)
		{
			for ($x = $xmin; $x <= $xmax; $x++)
			{
				if ($x == $knotposs[0][0] and $y == $knotposs[0][1])
					echo "H";
				elseif ($x == $knotposs[1][0] and $y == $knotposs[1][1])
					echo "1";
				elseif ($x == $knotposs[2][0] and $y == $knotposs[2][1])
					echo "2";
				elseif ($x == $knotposs[3][0] and $y == $knotposs[3][1])
					echo "3";
				elseif ($x == $knotposs[4][0] and $y == $knotposs[4][1])
					echo "4";
				elseif ($x == $knotposs[5][0] and $y == $knotposs[5][1])
					echo "5";
				elseif ($x == $knotposs[6][0] and $y == $knotposs[6][1])
					echo "6";
				elseif ($x == $knotposs[7][0] and $y == $knotposs[7][1])
					echo "7";
				elseif ($x == $knotposs[8][0] and $y == $knotposs[8][1])
					echo "8";
				elseif ($x == $knotposs[9][0] and $y == $knotposs[9][1])
					echo "9";
				else
					echo ".";
			}
			echo "\n";
		}
		echo "=====================================================\n";
	}

	function is_touching($tailpos, $headpos)
	{
		if (abs($tailpos[0] - $headpos[0]) > 1)
			return false;
		if (abs($tailpos[1] - $headpos[1]) > 1)
			return false;
		return true;
	}

	function recalc_tailpos(&$tailpos, $headpos)
	{
		if (is_touching($tailpos, $headpos))
			return;
		if ($tailpos[0] - $headpos[0] > 1 and $tailpos[1] - $headpos[1] > 1) //If the head moved to the left and up
			$tailpos = array($headpos[0] + 1, $headpos[1] + 1);
		elseif ($tailpos[0] - $headpos[0] > 1 and $tailpos[1] - $headpos[1] < -1) //If the head moved to the left and down
			$tailpos = array($headpos[0] + 1, $headpos[1] - 1);
		elseif ($tailpos[0] - $headpos[0] < -1 and $tailpos[1] - $headpos[1] > 1) //If the head moved to the right and up
			$tailpos = array($headpos[0] - 1, $headpos[1] + 1);
		elseif ($tailpos[0] - $headpos[0] < -1 and $tailpos[1] - $headpos[1] < -1) //If the head moved to the right and down
			$tailpos = array($headpos[0] - 1, $headpos[1] - 1);
		elseif ($tailpos[0] - $headpos[0] > 1) //If the head moved to the left
			$tailpos = array($headpos[0] + 1, $headpos[1]);
		elseif ($tailpos[0] - $headpos[0] < -1) //If the head moved to the right
			$tailpos = array($headpos[0] - 1, $headpos[1]);
		elseif ($tailpos[0] - $headpos[0] < -1) //If the head moved to the right
			$tailpos = array($headpos[0] - 1, $headpos[1]);
		elseif ($tailpos[1] - $headpos[1] > 1) //If the head moved up
			$tailpos = array($headpos[0], $headpos[1] + 1);
		else //If the head moved down
			$tailpos = array($headpos[0], $headpos[1] - 1);
	}

	function do_move(&$knotposs, $move, $dist, &$tail1_visited, &$tail9_visited)
	{

		//Determine move direction
		$dx = 0;
		$dy = 0;
		if ($move == "U")
			$dy = -1;
		elseif ($move == "R")
			$dx = 1;
		elseif ($move == "D")
			$dy = 1;
		else
			$dx = -1;

		//Do move
		for ($i = 0; $i < $dist; $i++)
		{

			//Move head
			$knotposs[0][0] += $dx;
			$knotposs[0][1] += $dy;

			//Make tails follow
			for ($j = 0; $j < KNOT_COUNT - 1; $j++)
				recalc_tailpos($knotposs[$j + 1], $knotposs[$j]);

			//Update list of visited locations
			if (!in_array($knotposs[1], $tail1_visited))
				array_push($tail1_visited, $knotposs[1]);
			if (!in_array($knotposs[9], $tail9_visited))
				array_push($tail9_visited, $knotposs[9]);

			//Print board if debugging is enabled
			if (DEBUG)
				print_board($knotposs);

		}

	}


	$knotposs = array();
	for ($i = 0; $i < KNOT_COUNT; $i++)
		array_push($knotposs, array(0, 0));
	$tail1_visited = array();
	$tail9_visited = array();
	foreach (file(INPUT_FILE) as $line)
	{
		$move = explode(" ", trim($line)); //Parse input line
		do_move($knotposs, $move[0], (int)$move[1], $tail1_visited, $tail9_visited); //Execute line
	}

	echo "Flag for part 1: " . count($tail1_visited) . "\n";
	echo "Flag for part 2: " . count($tail9_visited) . "\n";

?>
