<?php

	/*AoC2022 day 11 part 2
	Author(s)		: Lukas Mirow
	Date of creation	: 12/24/2022
	I'm using `GMP` and `sockets` here, make sure you enable that in your php.ini
	*/

	define('DEBUG', true);
	define('TEST_INPUT_FILE', 'testinput.txt');
	define('INPUT_FILE', 'input.txt');
	define('ITERATIONS', 10000);
	define('IPC_HOST', '127.0.0.1');
	define('IPC_PORT', 15151);
	define('IPC_MAX_RETRIES', 5);
	define('IPC_MSG_SIZE_MAX', 2048);

	error_reporting(E_ALL ^ E_WARNING);
	set_time_limit(0);

	function err($msg)
	{
		echo "$msg\n";
		exit(1);
	}

	function screate($address, $port)
	{
		return socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	}

	function mkserver($sock, $address, $port, $queue_size)
	{
		$ret_bind = socket_bind($sock, $address, $port);
		if ($ret_bind === false)
			return false;
		$ret_listen = socket_listen($sock, $queue_size);
		if ($ret_listen === false)
			return false;
		return $sock;
	}

	function saccept($sock)
	{
		return socket_accept($sock);
	}

	function sconnect($sock, $address, $port)
	{
		return socket_connect($sock, $address, $port);
	}

	function extract_items($itemline)
	{
		$line = explode(": ", $itemline);
		if ($line[0] != "Starting items")
			err("Expected \"Starting items\" but found \"" . $line[0] . "\"");
		return explode(", ", $line[1]);
	}

	function extract_operation($opline)
	{

		//Sanity tests
		$line = explode(": ", $opline);
		if ($line[0] != "Operation")
			err("Expected \"Operation\" but found \"" . $line[0] . "\"");
		$words = explode(" ", $line[1]);
		if ($words[0] != "new")
			err("Expected \"new\" as first word in operation string but got \"" . $words[0] . "\"");
		if ($words[1] != "=")
			err("Expected \"=\" as second word in operation string but got \"" . $words[0] . "\"");
		if ($words[2] != "old")
			err("Expected \"old\" as third word in operation string but got \"" . $words[0] . "\"");
		if (!preg_match('/[\+\-\*\/]/', $words[3]))
			err("Expected +, -, *, or / as fourth word in operation string but got \"" . $words[0] . "\"");
		if ($words[4] != "old" and !is_numeric($words[4]))
			err("Expected fifth word in operation string to be either \"old\" or a number, but got \"" . $words[0] . "\"");

		//Generate operation function
		if ($words[4] == "old")
		{
			if ($words[3] == "+")
				return function ($old) {return gmp_add($old, $old);};
			elseif ($words[3] == "-")
				return function ($old) {return gmp_sub($old, $old);};
			elseif ($words[3] == "*")
				return function ($old) {return gmp_mul($old, $old);};
			elseif ($words[3] == "/")
				return function ($old) {return gmp_div($old, $old);};
			else
				err("Failed to parse operation, \"" . $words[3] . "\" is not a valid operator");
		}
		else
		{
			$rhs = (int)$words[4];
			if ($words[3] == "+")
				return function ($old) use ($rhs) {return gmp_add($old, $rhs);};
			elseif ($words[3] == "-")
				return function ($old) use ($rhs) {return gmp_sub($old, $rhs);};
			elseif ($words[3] == "*")
				return function ($old) use ($rhs) {return gmp_mul($old, $rhs);};
			elseif ($words[3] == "/")
				return function ($old) use ($rhs) {return gmp_div($old, $rhs);};
			else
				err("Failed to parse operation, \"" . $words[3] . "\" is not a valid operator");
		}

	}

	function extract_test($testline)
	{
		$words = explode(" ", $testline);
		if ($words[0] != "Test:")
			err("Expected \"Test:\" as first word in test string but got \"" . $words[0] . "\"");
		if ($words[1] != "divisible")
			err("Expected \"divisible\" as second word in test string but got \"" . $words[1] . "\"");
		if ($words[2] != "by")
			err("Expected \"by\" as third word in test string but got \"" . $words[2] . "\"");
		$rhs = (int)$words[3];
		return function ($worry) use ($rhs) {return gmp_mod($worry, $rhs) == 0;};
	}

	function extract_on_true($trueline)
	{
		$words = explode(" ", $trueline);
		if ($words[0] != "If")
			err("Expected \"If\" as first word in true string but got \"" . $words[0] . "\"");
		if ($words[1] != "true:")
			err("Expected \"true:\" as second word in true string but got \"" . $words[1] . "\"");
		if ($words[2] != "throw")
			err("Expected \"throw\" as third word in true string but got \"" . $words[2] . "\"");
		if ($words[3] != "to")
			err("Expected \"to\" as fourth word in true string but got \"" . $words[3] . "\"");
		if ($words[4] != "monkey")
			err("Expected \"monkey\" as fifth word in true string but got \"" . $words[4] . "\"");
		return (int)$words[5];
	}

	function extract_on_false($trueline)
	{
		$words = explode(" ", $trueline);
		if ($words[0] != "If")
			err("Expected \"If\" as first word in false string but got \"" . $words[0] . "\"");
		if ($words[1] != "false:")
			err("Expected \"false:\" as second word in false string but got \"" . $words[1] . "\"");
		if ($words[2] != "throw")
			err("Expected \"throw\" as third word in false string but got \"" . $words[2] . "\"");
		if ($words[3] != "to")
			err("Expected \"to\" as fourth word in false string but got \"" . $words[3] . "\"");
		if ($words[4] != "monkey")
			err("Expected \"monkey\" as fifth word in false string but got \"" . $words[4] . "\"");
		return (int)$words[5];
	}

	function get_cpu_count()
	{
		$ret = 0;
		foreach (file("/proc/cpuinfo") as $line)
			if (trim(explode(":", $line)[0]) == "processor")
				$ret++;
		return $ret;
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
	foreach (file(TEST_INPUT_FILE) as $line)
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
	/* for ($i = 0; $i < ITERATIONS; $i++) */
	for ($i = 0; $i < 1000; $i++)
	{
		$nmonkeys = count($monkeys);
		for ($j = 0; $j < $nmonkeys; $j++)
		{
			$monkey = $monkeys[$j];
			while (count($monkey->items) > 0)
			{

				//Spawn CPU core count - 1 processes
				$cpu_count = get_cpu_count();
				echo "$cpu_count\n";
				for ($k = 0; $k < $cpu_count - 1; $k++)
				{
					$pid = pcntl_fork();
					echo "Pid: $pid\n";

					//Sub-processes
					if ($pid != 0)
					{

						//Create socket
						$sock = screate(IPC_HOST, IPC_PORT);
						$retry_counter = 0;
						while ($sock === false)
						{
							$retry_counter++;
							if ($retry_counter >= IPC_MAX_RETRIES)
								err("Process $pid: Max retries exceeded for socket creationn");
							if (DEBUG)
								echo "Process $pid failed to create socket (try $retry_counter of " . IPC_MAX_RETRIES . ")\n";
							usleep(100000);
							$sock = screate(IPC_HOST, IPC_PORT);
						}

						//Connect to server
						usleep(100000);
						$ret_conn = sconnect($sock, IPC_HOST, IPC_PORT);
						$retry_counter = 0;
						while ($ret_conn === false)
						{
							$retry_counter++;
							if ($retry_counter >= IPC_MAX_RETRIES)
								err("Process $pid: Max retries exceeded for socket connectionn");
							if (DEBUG)
								echo "Process $pid failed to connect to host `" . IPC_HOST . "`, port `" . IPC_PORT . " (try $retry_counter of " . IPC_MAX_RETRIES . ")`\n";
							usleep(100000);
							$ret_conn = sconnect($sock, IPC_HOST, IPC_PORT);
						}

						$msg = "Process `$pid` says hello\n";
						socket_write($sock, $msg, strlen($msg));
						socket_close($sock);
						exit(1);
					}
				}

				//Create socket
				$sock = screate(IPC_HOST, IPC_PORT);
				$retry_counter = 0;
				while ($sock === false)
				{
					$retry_counter++;
					if ($retry_counter >= IPC_MAX_RETRIES)
						err("Process $pid: Max retries exceeded for socket creationn");
					if (DEBUG)
						echo "Process $pid failed to create socket (try $retry_counter of " . IPC_MAX_RETRIES . ")\n";
					usleep(100000);
					$sock = screate(IPC_HOST, IPC_PORT);
				}

				//Listen for incoming connections
				$lsock = mkserver($sock, IPC_HOST, IPC_PORT, $cpu_count - 1);
				$retry_counter = 0;
				while ($lsock === false)
				{
					$retry_counter++;
					if ($retry_counter >= IPC_MAX_RETRIES)
						err("Process $pid: Max retries exceeded for socket listening");
					if (DEBUG)
						echo "Process $pid failed to listen (try $retry_counter of " . IPC_MAX_RETRIES . ")\n";
					usleep(100000);
					$lsock = mkserver($sock, IPC_HOST, IPC_PORT, $cpu_count - 1);
				}
				socket_set_nonblock($lsock);

				//Accept incoming connection and handle their input
				$conns = array();
				$data_was_received = false;
				while (!$data_was_received or count($conns) > 0)
				{

					//Accept new connection and add it to the list of connections
					$conn = saccept($lsock);
					if ($conn === false)
						continue;
					array_push($conns, $conn);
					socket_set_nonblock($conn);
					if (DEBUG)
						echo "Added new connection, new connection count: `" . count($conns) . "`, required: `" . ($cpu_count - 1) . "`\n";
					echo "Connection count: `" . count($conns) . "\n";

					//Check if new data is available on any connection
					for ($i = 0; $i < count($conns); $i++)
					{

						//Connection was closed
						$msg = socket_read($conns[$i], IPC_MSG_SIZE_MAX);
						if ($msg === false)
						{
							if (DEBUG)
								echo "A connection was closed\n";
							socket_close($conns[$i]);
							unset($conns[$i]);
							$i--;
						}

						//Nothing to read
						elseif ($msg === "")
							continue;

						//Data was read
						else
						{
							echo "Read: $msg\n";
							$data_was_received = true;
						}
					}
					//

				}

				foreach ($conns as $conn)
				{
					echo "Read: " . socket_read($conn, IPC_MSG_SIZE_MAX);
					socket_close($conn);
				}
				socket_close($sock);
				exit(1);

				//Worry increases because monkey is playing with item
				$item = array_shift($monkey->items);
				$item = ($monkey->operation)($item);

				//Monkey inspects and passes on item
				if (($monkey->test)($item) == true)
					array_push($monkeys[$monkey->on_true]->items, $item);
				else
					array_push($monkeys[$monkey->on_false]->items, $item);
				$inspections[$j]++;

			}
		}
		echo "Iteration $i of " . ITERATIONS . " (" . ($i / ITERATIONS * 100) . "%)\r";
	}
	echo "\n";

	//Calc flag
	print_r($inspections);
	rsort($inspections);
	echo "Flag for part 2: " . $inspections[0] * $inspections[1] . "\n";

?>
