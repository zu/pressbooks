<?php

class SanitizeTest extends \WP_UnitTestCase {

	public function test_html5_to_xhtml11() {

		$html = '<article style="font-weight:bold;">Foo</article><h1>Hello!</h1><command>Bar</command>';
		$html = \Pressbooks\Sanitize\html5_to_xhtml11( $html );
		$this->assertEquals(
			'<div class=\'bc-article article\'  style="font-weight:bold;">Foo</div><h1>Hello!</h1><div class=\'bc-command command\' >Bar</div>',
			$html
		);
	}

	public function test_html5_to_epub3() {

		$html = '<article style="font-weight:bold;">Foo</article><h1>Hello!</h1><command>Bar</command>';
		$html = \Pressbooks\Sanitize\html5_to_epub3( $html );
		$this->assertEquals(
			'<article style="font-weight:bold;">Foo</article><h1>Hello!</h1><div class=\'bc-command command\' >Bar</div>',
			$html
		);
	}

	public function test_fix_audio_shortcode() {

		\Pressbooks\Sanitize\fix_audio_shortcode();
		$this->assertTrue( has_filter( 'wp_audio_shortcode' ) );

		// Verify that style attribute is empty.
		$var = wp_audio_shortcode( [ 'src' => 'http://foo/audio.mp3' ] );
		$this->assertFalse( strpos( $var, 'style=' ) );
	}

	public function test_sanitize_xml_attribute() {

		$var = 'Hello-World!';
		$this->assertEquals( $var, \Pressbooks\Sanitize\sanitize_xml_attribute( $var ) );

		$var = "\t <Hello&World!> ";
		$this->assertEquals( '&lt;Hello&amp;World!&gt;', \Pressbooks\Sanitize\sanitize_xml_attribute( $var ) );

		$var = " te\fst";
		$this->assertEquals( 'test', \Pressbooks\Sanitize\sanitize_xml_attribute( $var ) );
	}

	public function test_sanitize_xml_id() {

		$var = 'Hello-World!';
		$test = $this->_generateControlCharacters() . $var;
		$test = \Pressbooks\Sanitize\sanitize_xml_id( $test );
		$this->assertEquals( 'Hello-World', $test );

		$var = ' Héllö Wôrld! ';
		$test = \Pressbooks\Sanitize\sanitize_xml_id( $var );
		$this->assertEquals( 'HelloWorld', $test );

		$var = '123';
		$test = \Pressbooks\Sanitize\sanitize_xml_id( $var );
		$this->assertStringStartsWith( 'slug-123', $test );

		$var = 'こんにちは世界!';
		$test = \Pressbooks\Sanitize\sanitize_xml_id( $var );
		$this->assertStringStartsWith( 'slug-', $test );
	}

	public function test_remove_control_characters() {

		$var = 'Hello-World!';
		$test = $this->_generateControlCharacters() . $var;
		$test = \Pressbooks\Sanitize\remove_control_characters( $test );
		$this->assertEquals( 12, strlen( $test ) );

		$var = 'Héllö Wôrld!';
		$test = \Pressbooks\Sanitize\remove_control_characters( $var );
		$this->assertEquals( 12, mb_strlen( $test, 'UTF-8' ) );

		$var = 'こんにちは世界!';
		$test = \Pressbooks\Sanitize\remove_control_characters( $var );
		$this->assertEquals( 8, mb_strlen( $test, 'UTF-8' ) );
	}

	public function test_force_ascii() {

		$var = 'Hello-World!';
		$test = $this->_generateControlCharacters() . $var;
		$test = \Pressbooks\Sanitize\force_ascii( $test );
		$this->assertEquals( 12, strlen( $test ) );

		$var = 'Héllö Wôrld!';
		$test = \Pressbooks\Sanitize\force_ascii( $var );
		$this->assertEquals( 9, strlen( $test ) );

		$var = 'こんにちは世界!';
		$test = \Pressbooks\Sanitize\force_ascii( $var );
		$this->assertEquals( 1, strlen( $test ) );
	}


	/**
	 * Generate a string containing all the ASCII control characters
	 *
	 * @return string
	 */
	private function _generateControlCharacters() {

		$controlCharacters = chr( 127 );
		for ( $i = 0; $i < 32; ++$i ) {
			$controlCharacters .= chr( $i );
		}

		return $controlCharacters;
	}

	public function test_decode() {

		$test = '&#48;&#49;&#50;&#51;&#52;&#53;&#038;&#54;&#55;&#56;&#57;';
		$test = \Pressbooks\Sanitize\decode( $test );
		$this->assertEquals( '012345&#038;6789', $test );

		$test = '&#48;&#49;&#50;&#51;&#52;&#53;&amp;&#54;&#55;&#56;&#57;';
		$test = \Pressbooks\Sanitize\decode( $test );
		$this->assertEquals( '012345&#038;6789', $test );
	}

	public function test_strip_br() {

		$test = 'Hello <br /> World!';
		$test = \Pressbooks\Sanitize\strip_br( $test );
		$this->assertEquals( 'Hello   World!', $test );

		$test = 'Hello <br/><br   /> World!';
		$test = \Pressbooks\Sanitize\strip_br( $test );
		$this->assertEquals( 'Hello    World!', $test );

		$test = 'Hello &lt;br /&gt; World!';
		$test = \Pressbooks\Sanitize\strip_br( $test );
		$this->assertEquals( 'Hello   World!', $test );

		$test = 'Hello &lt;br/&gt;&lt;br   /&gt; World!';
		$test = \Pressbooks\Sanitize\strip_br( $test );
		$this->assertEquals( 'Hello    World!', $test );
	}

	public function test_filter_title() {

		// Acceptable Tags: <br />, <span> with class, <em>, and <strong>.

		$test = '<h1><em>Hello</em><br/><strong>World!</strong></h1>';
		$test = \Pressbooks\Sanitize\filter_title( $test );
		$this->assertEquals( '<em>Hello</em><br /><strong>World!</strong>', $test );

		$test = '<span class="pb" style="font-weight:bold;"><i><b>Foobar</b></i></span><p /><div>Foobaz</div>';
		$test = \Pressbooks\Sanitize\filter_title( $test );
		$this->assertEquals( '<span class="pb">Foobar</span>Foobaz', $test );

		$test = '<del><strike>Keep me</strike></del>';
		$test = \Pressbooks\Sanitize\filter_title( $test );
		$this->assertEquals( '<del>Keep me</del>', $test );
	}

	public function test_canonicalize_url() {

		$url = 'pressbooks.com/';
		$this->assertEquals( 'http://pressbooks.com', \Pressbooks\Sanitize\canonicalize_url( $url ) );

		$url = 'https://pressbooks.com/';
		$this->assertEquals( 'https://pressbooks.com', \Pressbooks\Sanitize\canonicalize_url( $url ) );

		$url = 'HTTPS://PRESSBOOKS.COM/FOO/BAR/';
		$this->assertEquals( 'https://pressbooks.com/FOO/BAR', \Pressbooks\Sanitize\canonicalize_url( $url ) );

		$url = 'ftp://PRESSBOOKS.COM/foo/BAR�/?hello=world&TESTING=��123';
		$this->assertEquals( 'http://pressbooks.com/foo/BAR/?hello=world&TESTING=123', \Pressbooks\Sanitize\canonicalize_url( $url ) );

		$url = 'MAILTO:^accepts�!mostly,garb@ge.../';
		$this->assertEquals( 'MAILTO:^accepts!mostly,garb@ge...', \Pressbooks\Sanitize\canonicalize_url( $url ) );

		$url = 'mailto:miranda@yourcompany.com?bcc=eventsteam@yourcompany.com&subject=Excited%20to%20meet%20at%20the%20event!&body=Hi%20Miranda,';
		$this->assertEquals( $url, \Pressbooks\Sanitize\canonicalize_url( $url ) );
	}

	public function test_maybe_https() {

		if ( isset( $_SERVER['HTTPS'] ) ) {
			$old = $_SERVER['HTTPS'];
		}

		$_SERVER['HTTPS'] = true;

		$url = 'http://pressbooks.com';
		$url = \Pressbooks\Sanitize\maybe_https( $url );
		$this->assertStringStartsWith( 'https://', $url );

		$url = 'http://https.org';
		$url = \Pressbooks\Sanitize\maybe_https( $url );
		$this->assertEquals( 'https://https.org', $url );

		$_SERVER['HTTPS'] = false;

		$url = 'http://pressbooks.com';
		$url = \Pressbooks\Sanitize\maybe_https( $url );
		$this->assertStringStartsNotWith( 'https://', $url );

		$url = 'https://http.org';
		$url = \Pressbooks\Sanitize\maybe_https( $url );
		$this->assertEquals( 'https://http.org', $url );

		if ( isset( $old ) ) {
			$_SERVER['HTTPS'] = $old;
		} else {
			unset( $_SERVER['HTTPS'] );
		}
	}

	public function test_normalize_css_urls() {
		// Relative font
		$css = '@font-face { font-family: "Bergamot Ornaments"; src: url(themes-book/pressbooks-book/fonts/Bergamot-Ornaments.ttf) format("truetype"); font-weight: normal; font-style: normal; }';
		$css = \Pressbooks\Sanitize\normalize_css_urls( $css );
		$template_directory_uri = get_template_directory_uri();
		$this->assertContains( $template_directory_uri . '/assets/book/typography/fonts/Bergamot-Ornaments.ttf', $css );

		// Uploaded font
		$fullpath_font = get_theme_root( 'pressbooks-book' ) . '/pressbooks-book/assets/book/typography/fonts/Bergamot-Ornaments.ttf';
		$uploaded_font = WP_CONTENT_DIR . '/uploads/assets/fonts/Bergamot-Ornaments.ttf';
		copy( $fullpath_font, $uploaded_font );
		$css = '@font-face { font-family: "Bergamot Ornaments"; src: url(uploads/assets/fonts/Bergamot-Ornaments.ttf) format("truetype"); font-weight: normal; font-style: normal; }';
		$css = \Pressbooks\Sanitize\normalize_css_urls( $css );
		$this->assertContains( set_url_scheme( WP_CONTENT_URL ), $css );
		$css = '@font-face { font-family: "Bergamot Ornaments"; src: url(uploads/assets/fonts/garbage.ttf) format("truetype"); font-weight: normal; font-style: normal; }';
		$css = \Pressbooks\Sanitize\normalize_css_urls( $css );
		$this->assertNotContains( set_url_scheme( WP_CONTENT_URL ), $css );
		unlink( $uploaded_font );

		// Can't find, no change
		$css = 'url(/fonts/foo.garbage)';
		$this->assertEquals( $css, \Pressbooks\Sanitize\normalize_css_urls( $css ) );
	}

	public function test_allow_post_content() {

		global $allowedposttags;

		\Pressbooks\Sanitize\allow_post_content();

		$this->assertTrue( $allowedposttags['h1']['xml:lang'] );
	}

	public function test_clean_filename() {

		$file = '../../hacker.php';
		$file = \Pressbooks\Sanitize\clean_filename( $file );
		$this->assertEquals( $file, 'hacker.php' );

		$file = '../../hacker.php;../../~more-hacks.php...';
		$file = \Pressbooks\Sanitize\clean_filename( $file );
		$this->assertEquals( $file, 'hacker.php;~more-hacks.php' );

		$file = 'フランス語.txt'; // UTF-8
		$file = \Pressbooks\Sanitize\clean_filename( $file );
		$this->assertEquals( $file, 'フランス語.txt' );
	}

	public function test_strip_container_tags() {

		$test = '<HTML><div id="title-page"><h1 class="title">My Test Book</h1></div></HTML>';
		$result = \Pressbooks\Sanitize\strip_container_tags( $test );
		$this->assertEquals( '<div id="title-page"><h1 class="title">My Test Book</h1></div>', $result );

		$test = '<html xmlns="http://www.w3.org/1999/xhtml"><div id="title-page"><h1 class="title">My Test Book</h1></div></html>';
		$result = \Pressbooks\Sanitize\strip_container_tags( $test );
		$this->assertEquals( '<div id="title-page"><h1 class="title">My Test Book</h1></div>', $result );

		$test = '<?xml version="1.0"?><!DOCTYPE html>';
		$result = \Pressbooks\Sanitize\strip_container_tags( $test );
		$this->assertEquals( '', $result );

		$test = <<< TERRIBLE
<html
lang="en-US"
xmlns="http://www.w3.org/1999/xhtml"
><body
><p>:(</p></body
></html
>
TERRIBLE;

		$result = \Pressbooks\Sanitize\strip_container_tags( $test );
		$this->assertEquals( '<p>:(</p>', $result );

		$test = '<p>No change</p>';
		$result = \Pressbooks\Sanitize\strip_container_tags( $test );
		$this->assertEquals( '<p>No change</p>', $result );
	}

	public function test_cleanup_css() {
		$css = "body { font-family: 'Comic Sans' !important; }";
		$this->assertEquals( $css, \Pressbooks\Sanitize\cleanup_css( $css ) );

		$css = "body { font-family: '<em>Doing It Wrong</em>' !important; \\}";
		$this->assertEquals( "body { font-family: 'Doing It Wrong' !important; }", \Pressbooks\Sanitize\cleanup_css( $css ) );

		$css = '\\\\\A0';
		$this->assertEquals( '\\\A0', \Pressbooks\Sanitize\cleanup_css( $css ) );
	}

}
