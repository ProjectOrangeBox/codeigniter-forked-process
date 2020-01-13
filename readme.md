# Process Forker & Streaming Output

### Plain unmodified CodeIgniter 3.1.11

This is a example which requests a controller which first send a standard CodeIgniter Template directly to the screen for output (stream). This also automatcally sends the CodeIgniter Header (if any) as well as the streaming Javascript Snippet (about 71 bytes).

It then sets up the Forker to request 9 sub requests.
These have been setup to output some simple text with a sleep of between 2 and 5 seconds (see Process.php Controller) this will simulate work being done by each sub-process. This is done Asynchronous so even if each takes 5 seconds to complete they all won't take more than 5 seconds to run instead of 45 seconds (9 x 5).

We than wait for each of these to complete (with timeout).

Finally send a Controller Finished Messesage which again should take no longer than 5 seconds to complete (not the combined time of all sub-processors).

Since this is using Streaming each piece of output is sent to the screen as soon as it's available. This makes our  time to first byte microseonds instead of 5 seconds or even 45 seconds for the entire page to render!

***So we are doing 18-45 seconds worth of work in 2-5 seconds!***


```
./serve.sh
```

[http://localhost:8080/
](http://localhost:8080/)


* application/config/forker.php - config file

```
$config['root'] - application root folder (dirname(dirname(__DIR__)))
$config['maximum wait'] - maximum number of seconds to wait for all processes (20)
$config['working folder'] - read/write working folder (some place in var usually /var/fork_output/)
$config['php bin'] - location of PHP bin for basic forking (/usr/bin/php)
$config['bootstrap file'] - based on applicaton root where is index.php? (include index.php) (/index.php)
$config['auto capture'] - turn on output capture automatically in the process handler (true)
```
	
	
* controllers/Main.php - example controller
* controllers/Process.php - examples "working" processes

* libraries/Forker.php - Process Forker

```
$forker->add('/endpoint/foo/bar',function ($output,$options) {
	echo $output.PHP_EOL;
},['option1'=>true]);
$forker->wait();
$forker->responseHandler(function ($output,$options) {
	echo $output.PHP_EOL;
});
$forker->response->capture();
$forker->response->send('Welcome Home');
```

* libraries/Stream.php - Output Streamer

```
$stream->send('id','<i>Hello World</i>');
$stream->send('<b>Welcome</b> Home');
```

Controller Example

```
/* stream directly out - flushing output instead of buffering it */
$this->stream->send($this->load->view('template.html', ['start' => 'Start Controller ' . date('H:i:s')], true));

/* each added process will sleep for a random 2-5 seconds but run Asynchronous */
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
```
