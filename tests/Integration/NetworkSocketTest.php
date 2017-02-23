<?php declare(strict_types = 1);
/*
 * Copyright (c) 2010-2014 Pierrick Charron
 * Copyright (c) 2016 Holger Woltersdorf
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace hollodotme\FastCGI\Tests\Integration;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;

/**
 * Class NetworkSocketTest
 * @package hollodotme\FastCGI\Tests\Integration
 */
class NetworkSocketTest extends \PHPUnit\Framework\TestCase
{
	public function testCanSendAsyncRequestAndReceiveRequestId()
	{
		$connection = new NetworkSocket( '127.0.0.1', 9000 );
		$client     = new Client( $connection );
		$content    = http_build_query( [ 'test-key' => 'unit' ] );

		$requestId = $client->sendAsyncRequest(
			[
				'GATEWAY_INTERFACE' => 'FastCGI/1.0',
				'REQUEST_METHOD'    => 'POST',
				'SCRIPT_FILENAME'   => __DIR__ . '/Workers/worker.php',
				'SERVER_SOFTWARE'   => 'hollodotme/fast-cgi-client',
				'REMOTE_ADDR'       => '127.0.0.1',
				'REMOTE_PORT'       => '9985',
				'SERVER_ADDR'       => '127.0.0.1',
				'SERVER_PORT'       => '80',
				'SERVER_NAME'       => 'your-server',
				'SERVER_PROTOCOL'   => 'HTTP/1.1',
				'CONTENT_TYPE'      => 'application/x-www-form-urlencoded',
				'CONTENT_LENGTH'    => strlen( $content ),
			],
			$content
		);

		$this->assertGreaterThanOrEqual( 1, $requestId );
		$this->assertLessThanOrEqual( 65535, $requestId );
	}

	public function testCanSendAsyncRequestAndWaitForResponse()
	{
		$connection       = new NetworkSocket( '127.0.0.1', 9000 );
		$client           = new Client( $connection );
		$content          = http_build_query( [ 'test-key' => 'unit' ] );
		$expectedResponse = "X-Custom: Header\nContent-Type: text/plain; charset=UTF-8\n\nunit";

		$requestId = $client->sendAsyncRequest(
			[
				'GATEWAY_INTERFACE' => 'FastCGI/1.0',
				'REQUEST_METHOD'    => 'POST',
				'SCRIPT_FILENAME'   => __DIR__ . '/Workers/worker.php',
				'SERVER_SOFTWARE'   => 'hollodotme/fast-cgi-client',
				'REMOTE_ADDR'       => '127.0.0.1',
				'REMOTE_PORT'       => '9985',
				'SERVER_ADDR'       => '127.0.0.1',
				'SERVER_PORT'       => '80',
				'SERVER_NAME'       => 'your-server',
				'SERVER_PROTOCOL'   => 'HTTP/1.1',
				'CONTENT_TYPE'      => 'application/x-www-form-urlencoded',
				'CONTENT_LENGTH'    => strlen( $content ),
			],
			$content
		);

		$response = $client->waitForResponse( $requestId );

		$this->assertEquals( $expectedResponse, $response );
	}

	public function testCanSendSyncRequestAndReceiveResponse()
	{
		$connection       = new NetworkSocket( '127.0.0.1', 9000 );
		$client           = new Client( $connection );
		$content          = http_build_query( [ 'test-key' => 'unit' ] );
		$expectedResponse = "X-Custom: Header\nContent-Type: text/plain; charset=UTF-8\n\nunit";

		$response = $client->sendRequest(
			[
				'GATEWAY_INTERFACE' => 'FastCGI/1.0',
				'REQUEST_METHOD'    => 'POST',
				'SCRIPT_FILENAME'   => __DIR__ . '/Workers/worker.php',
				'SERVER_SOFTWARE'   => 'hollodotme/fast-cgi-client',
				'REMOTE_ADDR'       => '127.0.0.1',
				'REMOTE_PORT'       => '9985',
				'SERVER_ADDR'       => '127.0.0.1',
				'SERVER_PORT'       => '80',
				'SERVER_NAME'       => 'your-server',
				'SERVER_PROTOCOL'   => 'HTTP/1.1',
				'CONTENT_TYPE'      => 'application/x-www-form-urlencoded',
				'CONTENT_LENGTH'    => strlen( $content ),
			],
			$content
		);

		$this->assertEquals( $expectedResponse, $response );
	}
}