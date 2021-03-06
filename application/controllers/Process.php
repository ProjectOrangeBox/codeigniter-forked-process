<?php

class Process extends CI_Controller
{
	/* this is the end point called by another script */
	public function endpoint1(string $letters, string $when): void
	{
		$this->_sameThingForExample(__METHOD__, $letters, $when);
	}

	public function endpoint2(string $letters, string $when): void
	{
		$this->_sameThingForExample(__METHOD__, $letters, $when);
	}

	public function endpoint3(string $letters, string $when): void
	{
		$this->_sameThingForExample(__METHOD__, $letters, $when);
	}

	public function endpoint4(string $letters, string $when): void
	{
		$this->_sameThingForExample(__METHOD__, $letters, $when);
	}

	public function endpoint5(string $letters, string $when): void
	{
		$this->_sameThingForExample(__METHOD__, $letters, $when);
	}

	protected function _sameThingForExample(string $method, string $letters, string $when): void
	{
		$start = date('H:i:s');

		$sleep = mt_rand(2, 5);

		/* simulate work */
		sleep($sleep);

		$this->forker->response->send('We slept for ' . $sleep . ' seconds. From ' . $start . ' to ' . date('H:i:s'));
	}
}
