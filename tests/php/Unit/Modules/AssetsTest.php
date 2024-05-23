<?php
/**
 * Test assets module class.
 */

declare( strict_types=1 );

namespace FireWork\GoogleLogin\Tests\Unit\Modules;

use WP_Mock;
use FireWork\GoogleLogin\Tests\TestCase;
use FireWork\GoogleLogin\Modules\Assets as Testee;

/**
 * Class AssetsTest
 *
 * @coversDefaultClass \FireWork\GoogleLogin\Modules\Assets
 *
 * @package FireWork\GoogleLogin\Tests\Unit\Modules
 */
class AssetsTest extends TestCase {
	/**
	 * Object in test.
	 *
	 * @var Testee
	 */
	private $testee;

	public function setUp(): void {
		$this->testee = new Testee();
	}

	/**
	 * @covers ::name
	 */
	public function testName() {
		$this->assertSame( 'assets', $this->testee->name() );
	}

	/**
	 * @covers ::init
	 */
	public function testInit() {
		WP_Mock::expectActionAdded(
			'login_enqueue_scripts',
			[
				$this->testee,
				'enqueue_login_styles'
			]
		);

		$this->testee->init();

		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register_login_styles
	 * @covers ::register_style
	 * @covers ::get_file_version
	 */
	public function testRegisterLoginStyles() {
		$this->wpMockFunction(
			'FireWork\GoogleLogin\plugin',
			[],
			2,
			function () {
				return (object) [
					'url'        => 'https://example.com/',
					'assets_dir' => 'https://example.com/assets',
				];
			}
		);

		$this->wpMockFunction(
			'wp_register_style',
			[
				'login-with-google',
				'https://example.com/assets/build/css/login.css',
				[],
				false,
				true,
			],
			1,
			true
		);

		$this->testee->register_login_styles();
		$this->assertConditionsMet();
	}

	/**
	 * @covers ::register_script
	 */
	public function testRegisterLoginScript() {
		$this->wpMockFunction(
			'FireWork\GoogleLogin\plugin',
			[],
			2,
			function () {
				return (object) [
					'url'        => 'https://example.com/',
					'assets_dir' => 'https://example.com/assets',
				];
			}
		);

		$this->wpMockFunction(
			'wp_register_script',
			[
				'login-with-google',
				'https://example.com/assets/js/login.js',
				[
					'some-other-script'
				],
				false,
				true,
			],
			1,
			true
		);

		$this->testee->register_script(
			'login-with-google',
			'js/login.js',
			[
				'some-other-script'
			]
		);

		$this->assertConditionsMet();
	}

	/**
	 * Test enqueuing style when it is already registered.
	 *
	 * @covers ::enqueue_login_styles
	 */
	public function testEnqueueLoginStyleWithStyleRegistered() {
		$this->wpMockFunction(
			'wp_style_is',
			[
				'login-with-google',
				'registered',
			],
			1,
			true
		);

		$this->wpMockFunction(
			'wp_script_is',
			[
				'login-with-google-script',
				'registered',
			],
			1,
			true
		);

		$this->wpMockFunction(
			'wp_register_style',
			[
				'login-with-google',
				'https://example.com/assets/build/css/login.css',
				[],
				false,
				true,
			],
			0,
			true
		);

		$this->wpMockFunction(
			'wp_enqueue_style',
			[
				'login-with-google',
			],
			1,
			true
		);

		$this->wpMockFunction(
			'wp_enqueue_script',
			[
				'login-with-google-script',
			],
			1,
			true
		);

		$this->testee->enqueue_login_styles();
		$this->assertConditionsMet();
	}

	/**
	 * Test enqueuing style when it is already registered.
	 *
	 * @covers ::enqueue_login_styles
	 * @covers ::get_file_version
	 */
	public function testEnqueueLoginStyleWithStyleNotRegistered() {
		$this->wpMockFunction(
			'wp_style_is',
			[
				'login-with-google',
				'registered',
			],
			1,
			false
		);

		$this->wpMockFunction(
			'wp_script_is',
			[
				'login-with-google-script',
				'registered',
			],
			1,
			false
		);

		$this->wpMockFunction(
			'FireWork\GoogleLogin\plugin',
			[],
			4,
			function () {
				return (object) [
					'url'        => 'https://example.com/',
					'assets_dir' => 'https://example.com/assets',
				];
			}
		);

		$this->wpMockFunction(
			'wp_register_style',
			[
				'login-with-google',
				'https://example.com/assets/build/css/login.css',
				[],
				false,
				true,
			],
			1,
			true
		);

		$this->wpMockFunction(
			'wp_enqueue_style',
			[
				'login-with-google',
			],
			1,
			true
		);

		$this->wpMockFunction(
			'wp_enqueue_script',
			[
				'login-with-google-script',
			],
			1,
			true
		);

		$this->testee->enqueue_login_styles();
		$this->assertConditionsMet();
	}
}
