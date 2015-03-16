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
 * Class XMLErrorTest
 *
 * @covers XMLError
 */
class XMLErrorTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $error = new LibXMLError();
        $this->assertInstanceOf('LibXMLError', $error);
        $subject = new XMLError($error);
        $this->assertInstanceOf('XMLError', $subject);
    }

    /**
     * @test
     */
    public function standardGetters() {
        $error   = new LibXMLError();
        $subject = new XMLError($error);

        $this->assertEquals($error, $subject->getError());
        $this->assertSame($error, $subject->getError());

        $this->assertEquals(LIBXML_ERR_NONE, $subject->getLevel());
        $this->assertEquals('NONE', $subject->getSeverity());
        $this->assertSame('NONE', $subject->getSeverity());
        $this->assertEquals(null, $subject->getCode());
        $this->assertSame(0, $subject->getCode());
        $this->assertEquals(null, $subject->getMessage());
        $this->assertSame("", $subject->getMessage());
        $this->assertEquals(null, $subject->getFile());
        $this->assertSame("", $subject->getFile());
        $this->assertEquals(null, $subject->getLine());
        $this->assertEquals(null, $subject->getColumn());
        $this->assertSame(null, $subject->getColumn());

        $expected = "[NONE] ( 0)  (0:0)\n";
        $this->assertEquals($expected, $subject->__toString());
        $this->expectOutputString($expected);
        $subject::out($error);

        $error->level = 7654321;
        $this->assertSame(null, $subject->getSeverity());
    }
}

