<?php

/**
 * Class SSBioSanitizerTest
 *
 * @package Ss_Bios
 */

/**
 * SSBio sanitizer test case.
 */

final class SSBioTransformHTMLTest extends WP_UnitTestCase {

    public function setUp(): void 
    {
    	$this->ssbio = new SSBio();
    }

    /**
     * @dataProvider TransformHTMLData
 	 */
    public function testTransformHTML($html = '', $tag = '', $delimiter = '', $strip = true, $expected): void
    {
        $transformed_html = $this->ssbio->transform_html($html, $tag, $delimiter, $strip);

        $this->assertSame($transformed_html, $expected);
    }

    public function TransformHTMLData(): array
    {
        return [
            [ '', '', '', true, false ],
            [ '<li>John Doe</li>', '', '', true, false ],
            [ '<li>John Doe</li>', 'li', '', true, 'John Doe' ],
            [ '<li>John Doe</li>', 'li', '+', true, '+John Doe' ],
            [ '<li>John Doe</li>', 'li', '!', true, '!John Doe' ],
            [ '<li>John Doe</li><li>Jane Smith</li>', 'li', '+', true, '+John Doe+Jane Smith' ],
            [ '<li>John Doe</li><li>Jane Smith</li>', 'li', '!', true, '!John Doe!Jane Smith' ],
            [ '<li>John Doe<span> is human</span>', 'li', '', true, 'John Doe is human' ],
            [ '<li>John Doe <img src="unknown">', 'li', '', true, 'John Doe' ]

        ];
    }
}