<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{    
	protected function assertStatusOrDump($response, $status)
	{
		try {
			$response->assertStatus($status);
		} catch (\Exception $e) {
			dump('failed to assert status ' . $status . ' for response:');
			dump(json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			throw $e;
		}
		return $response;
	}
}
