<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\console;

use lithium\console\Request;
use lithium\tests\mocks\console\MockCommand;
use lithium\tests\mocks\console\command\MockCommandHelp;

class CommandTest extends \lithium\test\Unit {

	public $request;

	public function setUp() {
		$this->request = new Request(array('input' => fopen('php://temp', 'w+')));
		$this->classes = array('response' => 'lithium\tests\mocks\console\MockResponse');
	}

	public function testConstruct() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = $this->request->env();
		$result = $command->request->env();
		$this->assertEqual($expected, $result);

		$this->request->params = array(
			'case' => 'lithium.tests.cases.console.CommandTest'
		);
		$command = new MockCommand(array('request' => $this->request));
		$this->assertTrue(!empty($command->case));
	}

	public function testInvoke() {
		$command = new MockCommand(array('request' => $this->request));
		$response = $command('testRun');

		$result = $response;
		$expected = 'lithium\console\Response';
		$this->assertTrue(is_a($result, $expected));

		$expected = 'testRun';
		$result = $response->testAction;
		$this->assertEqual($expected, $result);
	}

	public function testInvokeSettingResponseStatus() {
		$command = new MockCommand(array('request' => $this->request));

		$expected = 0;
		$result = $command('testReturnNull')->status;
		$this->assertEqual($expected, $result);

		$expected = 0;
		$result = $command('testReturnTrue')->status;
		$this->assertEqual($expected, $result);

		$expected = 1;
		$result = $command('testReturnFalse')->status;
		$this->assertEqual($expected, $result);

		$expected = -1;
		$result = $command('testReturnNegative1')->status;
		$this->assertEqual($expected, $result);

		$expected = 1;
		$result = $command('testReturn1')->status;
		$this->assertEqual($expected, $result);

		$expected = 3;
		$result = $command('testReturn3')->status;
		$this->assertEqual($expected, $result);

		$expected = 'this is a string';
		$result = $command('testReturnString')->status;
		$this->assertEqual($expected, $result);

		$expected = 1;
		$result = $command('testReturnEmptyArray')->status;
		$this->assertEqual($expected, $result);

		$expected = 0;
		$result = $command('testReturnArray')->status;
		$this->assertEqual($expected, $result);
	}

	public function testOut() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "ok\n";
		$result = $command->out('ok');
		$this->assertEqual($expected, $result);
	}

	public function testOutArray() {
		$command = new MockCommand(array('request' => $this->request));

		$expected = "line 1\nline 2\n";
		$command->out(array('line 1', 'line 2'));
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testError() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "ok\n";
		$result = $command->error('ok');
		$this->assertEqual($expected, $result);
	}

	public function testErrorArray() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "line 1\nline 2\n";
		$command->error(array('line 1', 'line 2'));
		$result = $command->response->error;
		$this->assertEqual($expected, $result);
	}

	public function testNl() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "\n\n\n";
		$result = $command->nl(3);
		$this->assertEqual($expected, $result);
	}

	public function testHr() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "----\n";
		$command->hr(4);
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testHeader() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "----\nheader\n----\n";
		$command->header('header', 4);
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testColumns() {
		$command = new MockCommand(array('request' => $this->request));
		$expected = "data1\t\ndata2\t\n";
		$command->columns(array('col1' => 'data1', 'col2' => 'data2'));
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testHelp() {
		$command = new MockCommand(array('request' => $this->request));
		$return = $command->__invoke('_help');

		$this->assertTrue($return);

		$expected = "li3 mock-command --case=CASE --face=FACE ";
		$expected .= "--mace=MACE --race=RACE -lace [ARGS]";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);

		$command = new MockCommand(array('request' => $this->request));
		$return = $command->__invoke('_help');

		$expected = "testRun";
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/m", $result);
	}

	public function testAdvancedHelp() {
		$command = new MockCommandHelp(array('request' => $this->request));
		$return = $command->__invoke('_help');

		$expected = "li3 mock-command-help --long=LONG -s [ARGS]";
		$expected = preg_quote($expected);
		$result = $command->response->output;
		$this->assertPattern("/{$expected}/", $result);
	}

	public function testIn() {
		$command = new MockCommand(array('request' => $this->request));
		fwrite($command->request->input, 'nada mucho');
		rewind($command->request->input);

		$expected = "nada mucho";
		$result = $command->in('What up dog?');
		$this->assertEqual($expected, $result);

		$expected = "What up dog?  \n > ";
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testInWithDefaultOption() {
		$command = new MockCommand(array('request' => $this->request));
		fwrite($command->request->input, '  ');
		rewind($command->request->input);

		$expected = "y";
		$result = $command->in('What up dog?', array('default' => 'y'));
		$this->assertEqual($expected, $result);

		$expected = "What up dog?  \n [y] > ";
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}

	public function testInWithOptions() {
		$command = new MockCommand(array('request' => $this->request));
		fwrite($command->request->input, 'y');
		rewind($command->request->input);

		$expected = "y";
		$result = $command->in('Everything Cool?', array('choices' => array('y', 'n')));
		$this->assertEqual($expected, $result);

		$expected = "Everything Cool? (y/n) \n > ";
		$result = $command->response->output;
		$this->assertEqual($expected, $result);
	}
}

?>