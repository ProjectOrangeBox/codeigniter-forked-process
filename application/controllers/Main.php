<?php

class Main extends CI_Controller
{

	public function index()
	{
		/* stream directly out - flushing output instead of buffering it */
		$this->stream->send($this->load->view('template.html', ['start' => 'Start Controller ' . date('H:i:s')], true));

		$this->forker
			->responseHandler(function (string $output, array $options = []) {
				get_instance()->stream->send($options['div'], $output);
			})
			->add('/process/endpoint1/abc/1st', null, ['div' => 'div1'])
			->add('/process/endpoint2/def/2nd', null, ['div' => 'div2'])
			->add('/process/endpoint3/ghi/3rd', null, ['div' => 'div3'])
			->add('/process/endpoint3/jkl/4th', null, ['div' => 'div4'])
			->add('/process/endpoint5/mno/5th', null, ['div' => 'div5'])
			->add('/process/endpoint1/pqr/6th', null, ['div' => 'div6'])
			->add('/process/endpoint2/stu/7th', null, ['div' => 'div7'])
			->add('/process/endpoint3/vwx/8th', null, ['div' => 'div8'])
			->add('/process/endpoint4/yz/9th', null, ['div' => 'div9'])
			->wait(); /* now we need to wait for everything to get done or the timeout */

		$this->stream->send('endController', 'End Controller ' . date('H:i:s'));
	}

	public function cli()
	{
		echo 'Start Controller ' . date('H:i:s') . PHP_EOL;

		$this->forker
			->responseHandler(function (string $output) {
				echo $output . PHP_EOL;
			})
			->add('/process/endpoint1/abc/1st')
			->add('/process/endpoint2/def/2nd')
			->add('/process/endpoint3/ghi/3rd')
			->add('/process/endpoint3/jkl/4th')
			->add('/process/endpoint5/mno/5th')
			->add('/process/endpoint1/pqr/6th')
			->add('/process/endpoint2/stu/7th')
			->add('/process/endpoint3/vwx/8th')
			->add('/process/endpoint4/yz/9th')
			->wait();

		echo 'End Controller ' . date('H:i:s') . PHP_EOL;
	}
}
