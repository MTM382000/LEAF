<?php

declare(strict_types = 1);

include '../../libs/php-commons/XSSHelpers.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests libs/php-commons/XSSHelpers.php
 */
final class XSSHelpersTest extends TestCase
{

    /**
     * Tests XSSHelpers::sanitizer($in, $allowedTags)
     * 
     * Tests sanitizing HTML with anchor elements.
     */
    public function testSanitizer_links() : void
    {
        $in1 = "<a href='http://google.com'>Google</a>";
        $in2 = "<a href='#' onclick='alert(\"gotcha\")'>Hello</a>";

        $this->assertEquals(
            '&lt;a href=&#039;http://google.com&#039;&gt;Google</a>',
            XSSHelpers::sanitizer($in1, ['a'])
        );
        $this->assertEquals(
            '&lt;a href=&#039;#&#039; onclick=&#039;alert(&quot;gotcha&quot;)&#039;&gt;Hello</a>',
            XSSHelpers::sanitizer($in2, ['a'])
        );

        $this->assertEquals(
            '&lt;a href=&#039;#&#039; onclick=&#039;alert(&quot;gotcha&quot;)&#039;&gt;Hello&lt;/a&gt;',
            XSSHelpers::sanitizer($in2, [])
        );
        $this->assertEquals(
            '&lt;a href=&#039;http://google.com&#039;&gt;Google&lt;/a&gt;',
            XSSHelpers::sanitizer($in1, [''])
        );
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests processing line breaks (\n, \r) within the HTML input
     */
    public function testSanitizeHTML_LineBreaks() : void
    {
        $linebreaks = "text\nwith\nbreaks";
        $linebreaks2 = "text\rwith\rbreaks";
        $linebreaks3 = "text\r\nwith\r\nbreaks";
        $linebreaks4 = "text\n\rwith\n\rbreaks";
        $linebreaks5 = "text\n\nwith\n\nbreaks";

        $expectedOutput = 'text<br>with<br>breaks';
        $expectedOutput2 = 'text<br><br>with<br><br>breaks';

        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks2));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks3));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks4));

        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($linebreaks5));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests processing line breaks (\n, \r) in a paragraph (<p>) within the HTML input
     */
    public function testSanitizeHTML_LineBreaks_Paragraphs() : void
    {
        $str1 = "<p>text\nwith\nbreaks\nin\nparagraph</p>";
        $out1 = '<p>textwithbreaksinparagraph</p>';
        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests the length of the HTML input
     */
    public function testSanitizeHTML_Length() : void
    {
        $shortStr = str_repeat('.', 12345);
        $maxStr = str_repeat('.', 65535);
        $tooLongStr = str_repeat('.', 70000);

        $this->assertEquals(12345, strlen(XSSHelpers::sanitizeHTML($shortStr)));
        $this->assertEquals(65535, strlen(XSSHelpers::sanitizeHTML($maxStr)));

        // Any string over 65535 should be shortened to 65535
        $this->assertEquals(65535, strlen(XSSHelpers::sanitizeHTML($tooLongStr)));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Ordered Lists within the HTML input
     */
    public function testSanitizeHTML_OL() : void
    {
        $str1 = '<ol><li>an</li><li>ordered</li><li>list</li></ol>';
        $out1 = '<ol><li>an</li><li>ordered</li><li>list</li></ol>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests scrubbing SSNs
     */
    public function testSanitizeHTML_SSN() : void
    {
        $str1 = '123-45-6789';
        $out1 = '###-##-####';

        $str2 = '<tr><td>123-45-6789</td></tr>';
        $out2 = '<tr><td>###-##-####</td></tr>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
        $this->assertEquals($out2, XSSHelpers::sanitizeHTML($str2));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Tables within the HTML input
     */
    public function testSanitizeHTML_Table() : void
    {
        $str1 = '<table><tr><td></td></tr></table>';
        $out1 = '<table class="table"><tr><td></td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests formatting tabs (<b><i><u>) within the HTML input
     */
    public function testSanitizeHTML_TextFormatting() : void
    {
        $str1 = '<b>Some <i>formatted</i> <u>text</u>.'; // closing </b> left out intentionally
        $out1 = '<b>Some <i>formatted</i> <u>text</u>.</b>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests any unclosed element tags within the HTML input
     */
    public function testSanitizeHTML_UnclosedTags() : void
    {
        $str1 = '<table><tr><td><p><b>unclosed<i>tags';
        $out1 = '<table class="table"><tr><td><p><b>unclosed<i>tags</i></b></p></td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests any unclosed inner element tags within the HTML input
     */
    public function testSanitizeHTML_UnclosedTags_Inner() : void
    {
        $str1 = '<table><tr><td>unclosed inner tr</td></table>';
        $out1 = '<table class="table"><tr><td>unclosed inner tr</td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Unordered Lists within the HTML input
     */
    public function testSanitizeHTML_UL() : void
    {
        $str1 = '<ul><li>an</li><li>unordered</li><li>list</li></ul>';
        $out1 = '&lt;ul&gt;<li>an</li><li>unordered</li><li>list</li>&lt;/ul&gt;';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTMLRich()
     *
     * Tests processing images within the HTML input
     */
    public function testSanitizeHTMLRich_Img() : void
    {
        $img = '<img src="hello.jpg">';
        $img2 = '<img src="hello.jpg"/>';
        $img3 = '<img src="hello.jpg" />';
        $img4 = '<img src="hello.jpg" alt="world">';
        $img5 = '<img src="hello.jpg" alt="world"/>';
        $img6 = '<img src="hello.jpg" alt="world" />';
        $img7 = '<img src="javascript:alert(\'hello.jpg\')">';
        
        $expectedOutput = '<img src="hello.jpg" alt="" />"';
        $expectedOutput2 = '<img src="hello.jpg" alt="world" />"';
        $expectedOutput3 = '&lt;img src=&quot;javascript:alert(&#039;hello.jpg&#039;)&quot;&gt;';
        
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img2));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img3));
        
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img4));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img5));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img6));
        
        $this->assertEquals($expectedOutput3, XSSHelpers::sanitizeHTML($img7));
    }
    

    /**
     * Tests XSSHelpers::xscrub()
     *
     * Tests escaping HTML tags
     */
    public function testXscrub_tags() : void
    {
        $str1 = '<table><tr><td></td></tr></table>';
        $out1 = '&lt;table&gt;&lt;tr&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;';

        $this->assertEquals($out1, XSSHelpers::xscrub($str1));
    }

    /**
     * Tests XSSHelpers::xscrub()
     *
     * Tests escaping HTML tags
     */
    public function testXscrub_InlineJS() : void
    {
        $str1 = "<a onmouseover=\"alert('xss')\">test</a>";
        $out1 = '&lt;a onmouseover=&quot;alert(&apos;xss&apos;)&quot;&gt;test&lt;/a&gt;';

        $this->assertEquals($out1, XSSHelpers::xscrub($str1));
    }
}
