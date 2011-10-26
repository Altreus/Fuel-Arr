<?php

namespace Arr;

/**
 * Arr class tests
 *
 * @group Arr
 * @group Arr
 */
class Tests_Arr extends \Fuel\Core\TestCase {
    /**
	 * Test construction
	 *
     * @test
     */
    public function test_1() {
		$normal_array = array(1, 2, 3, 4, 5);
		$arr = \Arr\Arr::forge($normal_array);

		$this->assertCount($arr, 5);
    }
}
