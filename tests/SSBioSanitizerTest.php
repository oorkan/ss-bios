<?php

/**
 * Class SSBioSanitizerTest
 *
 * @package Ss_Bios
 */

/**
 * SSBio sanitizer test case.
 */

final class SSBioSanitizerTest extends WP_UnitTestCase {

    public function setUp(): void 
    {
    	$this->ssbio = new SSBio();
    }

    /**
     * @dataProvider SanitizeData
 	 */
    public function testSanitize(string $input, $expected, string $type = 'name'): void
    {
        $sanitized_input = $this->ssbio->sanitize($input, $type);

        $this->assertSame($sanitized_input, $expected);
    }

    public function SanitizeData(): array
    {
        return [
            [ 'John Doe', 'John Doe'],
            [ '', false ],
            [ '<script>let a = 5;</script>', false ],
            [ '<p>hello</p>', false ],
            [ '<p><span>hello</span></p>', false ],
            [ '<p><span>hello </span>world</p>', false ],
            [ '<p><span>hello </span><span>world</span></p>', false ],
            [ 
                'John Doe+James Smith',
                ['John Doe', 'James Smith']
            ],
            [ 
                'John Doe+',
                ['John Doe']
            ],
            [ 
                '+John Doe',
                ['John Doe']
            ],
            [ 
                '!John Doe',
                [':exclude:', ['John Doe']]
            ],
            [ 
                '!John Doe!James Smith',
                [':exclude:', ['John Doe', 'James Smith']]
            ],
            [ 'John Doe+James Smith!Jane Black', false ],
            [ 
                'My Category',
                false,
                'category'
            ],
            [ 
                'my-category+my-another-category',
                [ 'my-category', 'my-another-category' ],
                'category' 
            ],
            [ 
                '!my-category!my-another-category',
                [ ':exclude:', ['my-category', 'my-another-category']],
                'category' 
            ]
        ];
    }
}