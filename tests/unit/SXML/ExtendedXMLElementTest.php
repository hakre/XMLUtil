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
 * Class ExtendedXMLElementTest
 *
 * @covers ExtendedXMLElement
 */
class ExtendedXMLElementTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $xml = new ExtendedXMLElement('<doc/>');
        $this->assertInstanceOf('ExtendedXMLElement', $xml);
    }

    /**
     * @test
     */
    public function countable() {
        $xml = new SimpleXMLElement('<doc/>');
        for ($i = 0; $i < 5; $i++) {
            $xml->addChild('child');
        }

        $this->assertEquals(5, $xml->child->count());
        $this->assertNotInstanceOf('Countable', $xml);

        $this->assertCount(5, SXML::cast('ExtendedXMLElement', $xml)->child);
    }

    /**
     * @test
     */
    public function addXML() {
        $xml = new ExtendedXMLElement('<doc/>');

        $child = $xml->addXML('<child/>');
        $this->assertInstanceOf(get_class($xml), $child);

        $child = $xml->addXML('<child><grandchild>Hello World</grandchild></child>');
        $this->assertInstanceOf(get_class($xml), $child->grandchild);
        $this->assertEquals('Hello World', $child->grandchild);

        $this->assertEquals($child[0], $xml->child[1]);
        $this->assertTrue($child[0] == $xml->child[1]);
        $this->assertTrue($child == $xml->child[1]);

        $child = $xml->addXML('<child/><child/><child>Foo</child>');
        $this->assertTrue($child == $xml->child[2]);
        $this->assertEquals(5, $xml->child->count());
        $this->assertCount(5, $xml->child);
    }
}
