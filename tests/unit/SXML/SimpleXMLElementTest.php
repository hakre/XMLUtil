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
 * Class SimpleXMLElementTest
 *
 * @covers SimpleXMLElement
 */
class SimpleXMLElementTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function createEmpty() {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" ?>\n<tag/>");
        $this->assertInstanceOf('SimpleXMLElement', $xml);
    }

    /**
     * @test
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage simplexml_import_dom() expects parameter 2 to be a class name derived from
     */
    public function import_class_parameter() {
        $doc = new DOMDocument();
        $doc->loadXML('<doc/>');
        $object = new ExtendedXMLElement('<ext/>');
        $result = simplexml_import_dom($doc->documentElement, $object);
        $this->assertInstanceOf($object, $result);
    }
}
