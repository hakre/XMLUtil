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
 * Class XMLDigestTest
 *
 * @covers XMLDigest
 */
class XMLDigestTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function digest()
    {
        $digest = new XMLDigest(XMLDigest::DIGEST_SHA1);
        $this->assertInstanceOf('XMLDigest', $digest);
        $this->assertTrue($digest->hasImplementation());
        $this->assertEquals(XMLDigest::DIGEST_SHA1, $digest->getUri());
        $this->assertEquals('qZk+NkcGgWq6PiVxeFDCbJzQ2J0=', $digest->digest("abc"));
    }

    /**
     * @test
     */
    public function unsupportedHashNameInExtension()
    {
        try {
            new XMLDigest('___test_algos');
            $this->fail('expected exception not thrown');
        } catch (InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
            $this->assertStringStartsWith('Hash extension does not support the algorithm named', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function unknownHashAlgorithm()
    {
        $digest = new XMLDigest('http://example.com/unknown-digest-algorithm');
        try {
            $digest->digest('some data');
            $this->fail('expected exception not thrown');
        } catch (BadMethodCallException $e) {
            $this->addToAssertionCount(1);
            $this->assertEquals("Unknown hash algorithm 'http://example.com/unknown-digest-algorithm'", $e->getMessage());
        }
    }
}
