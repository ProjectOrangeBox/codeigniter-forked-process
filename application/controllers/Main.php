<?php

class Main extends CI_Controller
{

	public function index()
	{
		echo 'Start ' . date('H:i:s') . PHP_EOL;

		echo '<script>function _i(i,c){var _e=document.getElementById(i);if(_e){_e.outerHTML=c}}</script>';

		for ($i = 1; $i <= 9; $i++) {
			echo '<div id="div' . $i . '">Div ' . $i . '</div>';
		}

		$this->load->library('forker');

		ini_set('implicit_flush', 1);
		ob_implicit_flush(1);

		$this->forker->responseHandler(function (string $output, array $options = []) {
			echo '<script>_i("' . $options['div'] . '",' . json_encode($output) . ');</script>';

			@ob_flush();
			flush();
		});

		$this->forker
			->add('/process/endpoint1/abc/1st', null, ['div' => 'div1'])
			->add('/process/endpoint2/def/2nd', null, ['div' => 'div2'])
			->add('/process/endpoint3/ghi/3rd', null, ['div' => 'div3'])
			->add('/process/endpoint3/jkl/4th', null, ['div' => 'div4'])
			->add('/process/endpoint5/mno/5th', null, ['div' => 'div5'])
			->add('/process/endpoint1/pqr/6th', null, ['div' => 'div6'])
			->add('/process/endpoint2/stu/7th', null, ['div' => 'div7'])
			->add('/process/endpoint3/vwx/8th', null, ['div' => 'div8'])
			->add('/process/endpoint4/yz/9th', null, ['div' => 'div9'])
			->wait();

		echo 'End ' . date('H:i:s') . PHP_EOL;
	}
}
