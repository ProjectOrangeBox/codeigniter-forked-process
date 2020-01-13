<?php

class Forker
{
	protected $phpbin;
	protected $bootstrapFile;
	protected $workingFolder; /* where to place output from process */
	protected $maximumWait = 20; /* maximum seconds to wait for all to complete */

	protected $parentProcessId = ''; /* ForkerInput Instance ID */
	protected $exec;
	protected $running = []; /* running processes */
	protected $responseHandler;

	public function __construct(array $config = null)
	{
		if (!$config) {
			get_instance()->config->load('forker', true);

			$config = get_instance()->config->item('forker');
		}

		$this->phpbin = $config['php bin'] ?? '/usr/bin/php';
		$this->bootstrapFile = $config['bootstrap file'] ?? '/index.php';
		$this->maximumWait = $config['maximum wait'] ?? 20;

		$root = $config['root'] ?? \dirname(\dirname(__DIR__));;
		$workingFolder = $config['working folder'] ?? '/var/fork_output/';

		$this->workingFolder = \rtrim($root, '/') . '/' . \trim($workingFolder, '/') . '/';

		/* create unique page/process id */
		$this->parentProcessId = \sha1(uniqid('', true));

		$this->exec = \trim($this->phpbin) . ' ' . \rtrim($root, '/') . '/' . \trim($this->bootstrapFile, '/');

		$this->responseHandler = function (string $output, array $options = []) {
			echo $output;
		};
	}

	/**
	 * Add a shell script (endpoint) to run
	 * This is started immediately
	 *
	 * @param array $processes
	 * @param Closure $closure
	 * @return this
	 */
	public function add(string $endpoint, Closure $closure = null, array $options = []): Forker
	{
		$closure = $closure ?? $this->responseHandler;

		/* unique process id */
		$id = \sha1(\uniqid('', true));

		$processId = $this->parentProcessId . '-' . $id;

		$this->running[$id] = [$processId, $closure, $options];

		/* fork using shell do not wait for a responds */
		$forkCli = 'export CIFORKERPID="' . $processId . '";' . $this->exec . ' ' . $endpoint . ' > /dev/null 2>&1 &';

		#echo $forkCli . PHP_EOL;

		\shell_exec($forkCli);

		return $this;
	}

	public function wait(): void
	{
		$whileEnd = \time() + $this->maximumWait;

		/* wait for all of those files - with safety */
		while (\count($this->running) > 0 && \time() < $whileEnd) {
			foreach ($this->running as $id => $process) {
				/* is the process output file there yet? */
				if (\file_exists($this->workingFolder . $process[0])) {
					/* remove from checking */
					unset($this->running[$id]);

					/* capture the process output */
					$response = \file_get_contents($this->workingFolder . $process[0]);

					/* delete the process file */
					\unlink($this->workingFolder . $process[0]);

					/* call the closure to handle the response */
					$process[1]($response, $process[2]);
				}
			}
		}
	}

	public function responseHandler(Closure $closure): Forker
	{
		$this->responseHandler = $closure;

		return $this;
	}
} /* end class */
