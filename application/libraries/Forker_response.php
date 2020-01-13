<?php

class Forker_response
{
	protected $workingFolder;
	protected $capture = false;

	public function __construct(array $config = null)
	{
		if (!$config) {
			get_instance()->config->load('forker', true);

			$config = get_instance()->config->item('forker');
		}

		$root = $config['root'] ?? \dirname(\dirname(__DIR__));;
		$workingFolder = $config['working folder'] ?? '/var/fork_output/';

		$this->workingFolder = \rtrim($root, '/') . '/' . \trim($workingFolder, '/') . '/';

		$this->capture = $config['auto capture'] || true;

		if ($this->capture) {
			$this->capture();
		}
	}

	public function capture(): bool
	{
		$this->capture = true;

		return ob_start();
	}

	public function response(string $output = '', string $outputFile = null): int
	{
		if ($this->capture) {
			/* Return the contents of the output buffer */
			$output = \ob_get_contents() . $output;

			/* Clean (erase) the output buffer and turn off output buffering */
			\ob_end_clean();
		}

		/* if nothing sent in use the last url segment */
		if (!$outputFile) {
			$outputFile = $_ENV['CIFORKERPID'];
		}

		$filename = $this->workingFolder . $outputFile;

		/* get the path where you want to save this file so we can put our file in the same file */
		$dirname = \dirname($filename);

		/* is the directory writeable */
		if (!\is_writable($dirname)) {
			throw new Exception('atomic file put contents folder "' . $dirname . '" not writable');
		}

		/* create file with unique file name with prefix */
		$tmpfname = \tempnam($dirname, 'afpc_');

		/* did we get a temporary filename */
		if ($tmpfname === false) {
			throw new Exception('atomic file put contents could not create temp file');
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($tmpfname, $output);

		/* did we write anything? */
		if ($bytes === false) {
			throw new Exception('atomic file put contents could not file put contents');
		}

		/* changes file permissions so I can read/write and everyone else read */
		if (\chmod($tmpfname, 0644) === false) {
			throw new Exception('atomic file put contents could not change file mode');
		}

		/* move it into place - this is the atomic function */
		if (\rename($tmpfname, $filename) === false) {
			throw new Exception('atomic file put contents could not make atomic switch');
		}

		/* return the number of bytes written */
		return (int) $bytes;
	}
} /* end class */
