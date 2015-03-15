<?php
/*
 * This file is part of the XMLUtil package.
 *
 * Copyright (C) 2015 hakre <http://hakre.wordpress.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author hakre <http://hakre.wordpress.com>
 * @license AGPL-3.0 <http://spdx.org/licenses/AGPL-3.0>
 */

/**
 * Class XMLRecoderTest
 *
 * @covers XMLRecoder
 */
class XMLRecoderTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function getEncodingDeclaration() {
        $recoder = new XMLRecoder();

        $xml      = $this->getFixtureString('decl15477999.xml');
        $encoding = $recoder->getEncodingDeclaration($xml);
        $this->assertEquals('UTF-8', $encoding);
    }

    /**
     * @see bomDetection
     * @return array
     */
    public function provideBomDetection() {
        return array(
            array("", null),
            array("sail this ship", null),
            array("\xEF\xBB\xBFsail this ship", 'UTF-8'),
            array("\xEF\xBB\xBF", 'UTF-8'),
            array("\xEF\xBB\xBFs", 'UTF-8'),
            array("\xEF\xBBs", null),
            array("\xFF\xFE\x00\x00sail this ship", 'UTF-32LE'),
            array("\xFF\xFEsail this ship", 'UTF-16LE'),
            array("\xFF\xFE", 'UTF-16LE'),
            array("\x00\x00\xFE\xFFsail this ship", 'UTF-32BE'),
            array("\xFE\xFFsail this ship", 'UTF-16BE'),
            array("\xFE\xFF", 'UTF-16BE'),
            array("\xFE\xFFs", 'UTF-16BE'),
            array("\xFE\xFFsa", 'UTF-16BE'),
        );
    }

    /**
     * @test
     * @param string $input
     * @param string $expected
     * @dataProvider provideBomDetection
     */
    public function bomDetection($input, $expected) {
        $recoder = new XMLRecoder();
        $actual  = $recoder->detectEncodingViaBom($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function recodeXMLString() {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-7\"?>\n<title>alpha: \xE1</title>";

        $recoder = new XMLRecoder();

        $this->assertFalse(preg_match('//u', $xml));

        $xmlUTF8 = $recoder->recodeXMLString('ISO-8859-7', 'UTF-8', $xml);

        $this->assertSame(1, preg_match('/^.{10,}$/us', $xmlUTF8));

        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xmlUTF8));
        $this->assertEquals('alpha: α', $doc->getElementsByTagName('title')->item(0)->nodeValue);


        $this->assertEquals("", $recoder->recodeXMLString(null, 'UTF-8', ""));

        $actual = $recoder->recodeXMLString(null, 'UTF-8', "<?xml version=\"1.0\" encoding=\"ISO-8859-7\"?>\n");
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n", $actual);
    }

    /**
     * @test
     */
    public function readWriteEncodingInDeclaration() {
        $recoder = new XMLRecoder();

        $declaration = '<?xml version="1.0" encoding="UTF-16" standalone="no"?>';

        $this->assertEquals('UTF-16', $recoder->getEncodingDeclaration($declaration));
        $changed = $recoder->setEncodingDeclaration($declaration, 'TTY-NONE');
        $this->assertEquals('<?xml version="1.0" encoding="TTY-NONE" standalone="no"?>', $changed);
        $this->assertEquals('TTY-NONE', $recoder->getEncodingDeclaration($changed));

        try {
            $recoder->setEncodingDeclaration($declaration, '"\'@@@@!=?"§\'"');
            $this->fail('An expected exception was not thrown');
        } catch (InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
            $this->assertStringStartsWith('Invalid target encoding for XML declaration:', $e->getMessage());
        }

        $declaration = '<?xml version="1.0"?>';

        $this->assertEquals(null, $recoder->getEncodingDeclaration($declaration));
        $changed = $recoder->setEncodingDeclaration($declaration, 'TTY-NONE');
        $this->assertEquals('TTY-NONE', $recoder->getEncodingDeclaration($changed));

        $declaration = '';

        $this->assertEquals(null, $recoder->getEncodingDeclaration($declaration));
        $changed = $recoder->setEncodingDeclaration($declaration, 'TTY-NONE');
        $this->assertEquals('TTY-NONE', $recoder->getEncodingDeclaration($changed));
    }
}
