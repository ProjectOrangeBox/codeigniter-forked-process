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

	public $response;

	public function __construct(array $config = null)
	{
		if (!$config) {
			get_instance()->config->load('forker', true);

			$config = get_instance()->config->item('forker');
		}

		require __DIR__ . '/Forker_response.php';

		$this->phpbin = $config['php bin'] ?? '/usr/bin/php';
		$this->bootstrapFile = $config['bootstrap file'] ?? '/index.php';
		$this->maximumWait = $config['maximum wait'] ?? 20;

		$root = $config['root'] ?? \dirname(\dirname(__DIR__));;
		$workingFolder = $config['working folder'] ?? '/var/fork_output/';

		$this->workingFolder = \rtrim($root, '/') . '/' . \trim($workingFolder, '/') . '/';

		$this->response = new Forker_response($this->workingFolder, $config['auto capture'] || true);

		/* create unique page/process id */
		$this->parentProcessId = \md5(uniqid('', true));

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
		$options['processId'] = $this->parentProcessId . '-' . \md5(\uniqid('', true));

		$this->running[] = [$options['processId'], $closure, $options];

		/* fork using shell do not wait for a responds set up CIFORKERPID for the response */
		\shell_exec('export CIFORKERPID="' . $options['processId'] . '";' . $this->exec . ' ' . $endpoint . ' > /dev/null 2>&1 &');

		return $this;
	}

	public function wait(): void
	{
		$whileEnd = \time() + $this->maximumWait;

		/* wait for all of those files - with safety */
		while (\count($this->running) > 0 && \time() < $whileEnd) {
			foreach ($this->running as $key => $process) {
				/* is the process output file there yet? */
				if (\file_exists($this->workingFolder . $process[0])) {
					/* remove from checking */
					unset($this->running[$key]);

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
