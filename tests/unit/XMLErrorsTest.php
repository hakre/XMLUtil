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
 * Class XMLErrorsTest
 *
 * @covers XMLErrors
 */
class XMLErrorsTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $this->getFixtureDoc('inv29080588.xml');
        $errors = new XMLErrors();
        $this->assertInstanceOf('XMLErrors', $errors);
    }

    /**
     * @test
     */
    public function lineHandling() {
        $this->getFixtureDoc('inv29080588.xml');
        $errors = new XMLErrors($this->getLastFixtureErrors());

        $there = $errors->atLine(4);
        $this->assertCount(3, $there);

        $there = $errors->atLine(11);
        $this->assertCount(3, $there);

        $set = array(
            array(18, "[FATAL] (73) expected '>' (11:18)\n"),
            array(18, "[FATAL] (76) Opening and ending tag mismatch: Body line 3 and Consult (11:18)\n"),
            array(43, "[FATAL] (76) Opening and ending tag mismatch: Envelope line 1 and Body (11:43)\n"),
        );

        $there->rewind();
        foreach ($set as $data) {
            list($column, $string) = $data;
            $this->assertEquals($column, $there->current()->getColumn());
            $this->assertEquals($string, $there->current()->__toString());
            $there->next();
        }

        $there = $there->columnDesc();

        $set = array(
            array(43, "[FATAL] (76) Opening and ending tag mismatch: Envelope line 1 and Body (11:43)\n", array(18)),
            array(18, "[FATAL] (73) expected '>' (11:18)\n", array()),
            array(18, "[FATAL] (76) Opening and ending tag mismatch: Body line 3 and Consult (11:18)\n", array()),
        );


        $there->rewind();
        foreach ($set as $data) {
            list($column, $string, $followUpColumns) = $data;
            $this->assertEquals($column, $there->current()->getColumn());
            $this->assertEquals($string, $there->current()->__toString());
            $this->assertEquals($followUpColumns, $there->followUpColumns());
            $there->next();
        }

    }

    /**
     * @test
     */
    public function out()
    {
        $this->expectOutputString($this->getFixtureString('inv29080588.out'));
        $buffer = $this->getFixtureString('inv29080588.xml');

        $saved = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($buffer);
        XMLErrors::out($buffer);
        libxml_use_internal_errors($saved);
    }

    /**
     * @test
     */
    public function item() {

        $errors = new XMLErrors();
        $this->assertNull($errors->item(0));
        $this->assertNull($errors->current());
        $this->assertFalse($errors->valid());

        $this->getFixtureDoc('inv29080588.xml');
        $errors = new XMLErrors($this->getLastFixtureErrors());

        $this->assertInstanceOf('XMLError', $errors->item(0));
        $this->assertInstanceOf('XMLError', $errors->item(6));
        $this->assertNull($errors->item(7));
    }

    /**
     * @test
     */
    public function countAndIteration() {

        $errors = new XMLErrors();
        $this->assertCount(0, $errors);

        $this->getFixtureDoc('inv29080588.xml');
        $errors = new XMLErrors($this->getLastFixtureErrors());
        $this->assertCount(7, $errors);

        $this->assertInstanceOf('Iterator', $errors);
        $this->assertTrue($errors->valid()); // XMLErrors auto-rewinds on ctor

        $array = iterator_to_array($errors);
        $this->assertEquals(count($array), count($errors));

        $this->assertNull($errors->current());
        $this->assertFalse($errors->valid());

        foreach ($errors as $error) {
            $this->assertInstanceOf('XMLError', $error);
        }

        $this->assertNull($errors->current());
        $this->assertFalse($errors->valid());
    }
}

