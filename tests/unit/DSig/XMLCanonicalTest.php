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
 * Class XMLCanonicalTest
 *
 * @covers XMLCanonical
 */
class XMLCanonicalTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $digest = new XMLCanonical('');
        $this->assertInstanceOf('XMLCanonical', $digest);
    }

    /**
     * @test
     */
    public function canonicalization() {
        $canonical = new XMLCanonical(XMLCanonical::CANON_XML10_OMIT_COMMENTS);
        $input     = $this->getFixtureString('sig29050349.php#raw');
        $expected  = $this->getFixtureString('sig29050349.php#canon');
        $this->assertNotEquals($input, $expected);
        $canon = $canonical->C14N($input);
        $this->assertEquals($expected, $canon);
        $this->assertEquals($expected, $canonical->C14N($expected));
    }

    /**
     * @test
     */
    public function information() {
        $uri       = XMLCanonical::CANON_XML10_OMIT_COMMENTS;
        $canonical = new XMLCanonical($uri);
        $this->assertEquals($uri, $canonical->getUri());
        $this->assertTrue($canonical->hasImplementation());
        $this->assertEquals(XMLCanonical::CANONICAL_XML_VERSION_1_0, $canonical->getName());
        $this->assertFalse($canonical->hasComments());

        $canonical = new XMLCanonical("invalid URI");
        $this->assertFalse($canonical->hasImplementation());
        $this->assertNull($canonical->getName());
        $this->assertNull($canonical->hasComments());
    }

    /**
     * @test
     */
    public function C14N() {

        $canonical = new XMLCanonical("invalid URI");
        try {
            $canonical->C14N("invalid XML");
            $this->fail('expected exception not thrown');
        } catch (BadMethodCallException $e) {
            $this->addToAssertionCount(1);
            $this->assertEquals('Unknown canonicalize algorithm invalid URI', $e->getMessage());
        }

        $uri       = XMLCanonical::CANON_XML10_OMIT_COMMENTS;
        $canonical = new XMLCanonical($uri);

        try {
            @$canonical->C14N("invalid XML");
            $this->fail('expected exception not thrown');
        } catch (InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
            $this->assertEquals("Invalid XML given 'invalid XML'", $e->getMessage());
        }

        try {
            $canonical->C14N(new SimpleXMLElement('<xml/>'));
            $this->fail('expected exception not thrown');
        } catch (InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
            $this->assertEquals("Invalid XML argument given", $e->getMessage());
        }

        $this->assertEquals('<xml></xml>', $canonical->C14N("<xml/>"));
        $this->assertEquals('<xml></xml>', $canonical->C14N("<?xml version=\"1.0\"?><!-- comment --><xml/>"));
    }
}
