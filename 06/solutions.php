<?php

	/*AoC2022 day 6
	Author(s)		: Lukas Mirow
	Date of creation	: 12/6/2022
	*/

	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');

	//Detect start-of-packet marker
	//Returns index pointing to last byte of SOP marker
	//Returns -1 on error
	function detect_sop_marker($recvbuffer)
	{
		$buflen = strlen($recvbuffer);
		for ($i = 3; $i < $buflen; $i++)
			if ($recvbuffer[$i - 3] != $recvbuffer[$i - 2])
				if ($recvbuffer[$i - 3] != $recvbuffer[$i - 1])
					if ($recvbuffer[$i - 3] != $recvbuffer[$i])
						if ($recvbuffer[$i - 2] != $recvbuffer[$i - 1])
							if ($recvbuffer[$i - 2] != $recvbuffer[$i])
								if ($recvbuffer[$i - 1] != $recvbuffer[$i])
									return $i + 1;
		return -1;
	}

	//Detect start-of-message marker
	//Returns index pointing to last byte of SOM marker
	//Returns -1 on error
	function detect_som_marker($recvbuffer)
	{
		$buflen = strlen($recvbuffer);
		for ($i = 13; $i < $buflen; $i++)
		{
			$bytes_seen = array();
			$duplicate = false;
			array_push($bytes_seen, $recvbuffer[$i]);
			for ($j = 1; $j < 14; $j++)
				if (in_array($recvbuffer[$i - $j], $bytes_seen))
				{
					$duplicate = true;
					break;
				}
				else
					array_push($bytes_seen, $recvbuffer[$i - $j]);
			if (!$duplicate)
				return $i + 1;
		}
		return -1;
	}

	foreach (file(INPUT_FILE) as $line)
	{
		echo "Flag for part 1: " . detect_sop_marker($line) . "\n";
		echo "Flag for part 2: " . detect_som_marker($line) . "\n";
	}

?>
