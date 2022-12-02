<?php

	/*AoC2022 day 2
	Author(s)		: Lukas Mirow
	Date of creation	: 12/2/2022
	*/

	define('TEST_INPUT_FILE', 'testinput1.txt');
	define('INPUT_FILE', 'input.txt');
	define
	(
		'SCORES',
		array
		(
			'A' => 1, //Rock
			'B' => 2, //Paper
			'C' => 3, //Scissors
			'X' => 1, //Rock/Lose
			'Y' => 2, //Paper/Draw
			'Z' => 3, //Scissors/Win
			'LOSE' => 0,
			'DRAW' => 3,
			'WIN' => 6
		)
	);

	function calc_score1($opponents_move, $my_move)
	{
		$score = SCORES[$my_move];
		if ($opponents_move == 'A')
		{
			if ($my_move == 'X')
				$score += SCORES['DRAW'];
			elseif ($my_move == 'Y')
				$score += SCORES['WIN'];
			else
				$score += SCORES['LOSE'];
		}
		elseif ($opponents_move == 'B')
		{
			if ($my_move == 'X')
				$score += SCORES['LOSE'];
			elseif ($my_move == 'Y')
				$score += SCORES['DRAW'];
			else
				$score += SCORES['WIN'];
		}
		else
		{
			if ($my_move == 'X')
				$score += SCORES['WIN'];
			elseif ($my_move == 'Y')
				$score += SCORES['LOSE'];
			else
				$score += SCORES['DRAW'];
		}
		return $score;
	}

	function calc_score2($opponents_move, $outcome)
	{
		$score = 0;
		if ($opponents_move == 'A')
		{
			if ($outcome == 'X')
				$score += SCORES['LOSE'] + SCORES['C'];
			elseif ($outcome == 'Y')
				$score += SCORES['DRAW'] + SCORES['A'];
			else
				$score += SCORES['WIN'] + SCORES['B'];
		}
		elseif ($opponents_move == 'B')
		{
			if ($outcome == 'X')
				$score += SCORES['LOSE'] + SCORES['A'];
			elseif ($outcome == 'Y')
				$score += SCORES['DRAW'] + SCORES['B'];
			else
				$score += SCORES['WIN'] + SCORES['C'];
		}
		if ($opponents_move == 'C')
		{
			if ($outcome == 'X')
				$score += SCORES['LOSE'] + SCORES['B'];
			elseif ($outcome == 'Y')
				$score += SCORES['DRAW'] + SCORES['C'];
			else
				$score += SCORES['WIN'] + SCORES['A'];
		}
		return $score;
	}

	$score1 = 0;
	$score2 = 0;
	foreach (file(INPUT_FILE) as $line)
	{
		$score1 += calc_score1($line[0], $line[2]);
		$score2 += calc_score2($line[0], $line[2]);
	}
	echo "Flag for day 1: $score1\n";
	echo "Flag for day 2: $score2\n";

?>
