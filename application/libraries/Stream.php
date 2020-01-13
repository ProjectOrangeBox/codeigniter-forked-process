<?php

class Stream extends CI_Output
{
	protected $functionName = '_i';

	protected $headerSent = false;
	protected $injectorSent = false;

	public function __construct()
	{
		parent::__construct();

		ini_set('implicit_flush', 1);
		ob_implicit_flush(1);
	}

	public function send(string $name, string $output = null)
	{
		if (!$this->headerSent) {
			$this->headerSent = true;

			/* The parent _display will build correct header and send it */
			parent::_display('');
		}

		if (!$this->injectorSent) {
			$this->injectorSent = true;

			if (!is_cli()) {
				echo '<script>function ' . $this->functionName . '(i,c){let e=document.getElementById(i);if(e){e.outerHTML=c}}</script>';
			}
		}

		/* Output String */
		if ($output && $name) {
			echo '<script>' . $this->functionName . '("' . $name . '",' . json_encode($output) . ');</script>';
		} else {
			echo $name;
		}

		/* Send the output buffer & Flush system output buffer */
		@ob_flush();
		flush();

		return $this;
	}
} /* end class */
