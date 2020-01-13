<?php

class Main extends CI_Controller
{

	public function index()
	{
		$this->load->library('forker');

		get_instance()->output->append_output('<p>Start Controller ' . date('H:i:s') . '</p>');

		get_instance()->output->append_output($this->load->view('template.html', [], true));

		$this->forker->responseHandler(function (string $output, array $options = []) {
			get_instance()->output->inject($options['div'], $output);
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

		get_instance()->output->append_output('<p>End Controller ' . date('H:i:s') . '</p>');
	}

	public function cli()
	{
		$this->load->library('forker');

		get_instance()->output->append_output('Start Controller ' . date('H:i:s') . PHP_EOL);

		$this->forker->responseHandler(function (string $output, array $options = []) {
			get_instance()->output->append_output($output);
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

		get_instance()->output->append_output('End Controller ' . date('H:i:s') . PHP_EOL);
	}
}
