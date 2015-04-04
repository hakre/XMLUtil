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
 * Class SXMLTest
 *
 * @covers SXML
 */
class SXMLTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $refl = new ReflectionClass('SXML');
        $this->addToAssertionCount(1);
        $this->assertTrue($refl->isAbstract());
    }

    /**
     * @test
     */
    public function import() {
        $xml = new SimpleXMLElement('<doc><child attr="value"/></doc>');
        $att = $xml->child['attr'];
        $this->assertInstanceOf(get_class($xml), $att);
        $this->assertEquals('attr', $att->getName());

        $result = SXML::import($xml, $att);
        $this->assertInstanceOf(get_class($xml), $result);
        $this->assertStringStartsWith("<?xml version=\"1.0\"?>\n<doc attr=\"value\"><child ", $xml->asXML());
    }

    /**
     * @test
     */
    public function domImportAttribute() {
        $xml = new SimpleXMLElement('<doc><child attr="value"/></doc>');
        $att = dom_import_simplexml($xml->child['attr']);
        $this->assertInstanceOf('DOMAttr', $att);
        $this->assertEquals('attr', $att->nodeName);

        $result = SXML::domImport($att);
        $this->assertInstanceOf(get_class($xml), $result);
        $this->assertEquals('attr', $result->getName());
        $this->assertEquals($result, $xml->child['attr']);
        $result['attr'] = "kitten";
        $this->assertEquals("<child attr=\"value\"/>", $xml->child->asXML());
    }

    /**
     * @test
     */
    public function domImportDocument() {
        $doc = new DOMDocument();
        $xml = SXML::domImport($doc);
        $this->assertNull($xml);
    }

    /**
     * @test
     */
    public function domImportFreedElement() {
        $reader = new XMLReader();
        $reader->open('data://text/xml,<root/>');
        $reader->read();
        $node = $reader->expand();
        $this->assertInstanceOf('DOMElement', $node);
        $result = SXML::domImport($node);
        $this->assertInstanceOf('SimpleXMLElement', $result);
    }

    /**
     * @test
     */
    public function importTemporaryElement() {
        $xml    = new SimpleXMLElement("<root/>");
        $result = SXML::import($xml, $xml->newChild);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function casting() {
        $xml = new SimpleXMLElement('<doc/>');

        $casted = SXML::cast($xml, $xml);
        $this->assertInstanceOf('SimpleXMLElement', $casted);
        $this->assertEquals($casted, $xml);

        $casted = SXML::cast('ExtendedXMLElement', $xml);
        $this->assertInstanceOf('ExtendedXMLElement', $casted);

        $xml    = $casted;
        $casted = SXML::cast($casted, $xml);
        $this->assertInstanceOf('ExtendedXMLElement', $casted);
        $this->assertEquals($casted, $xml);
    }

    /**
     * @test
     */
    public function importDOMNode() {

        $xml = new SimpleXMLElement('<doc><parent/></doc>');
        $yml = new SimpleXMLElement('<child><grandchild/></child>');

        // DOMElement in same document
        $result = SXML::appendDOMNode($xml, dom_import_simplexml($xml->parent));
        $class  = get_class($xml);
        $this->assertInstanceOf($class, $result);
        $this->assertFalse($xml == $result);
        $this->assertCount(2, $xml->xpath('//parent'));

        // DOMElement in a different document
        $result = SXML::appendDOMNode($xml->parent[1], dom_import_simplexml($yml));
        $this->assertInstanceOf($class, $result);
        $this->assertInstanceOf($class, $xml->parent->child);
        $this->assertNull($xml->parent->child->grandchild);

        $this->assertEquals("child", $result->getName());
    }

    /**
     * @test
     */
    public function importDOMDocument() {
        $xml   = new SimpleXMLElement('<?xml version="1.0" ?><doc><child/></doc>');
        $class = get_class($xml);

        // DOMDocument into the same document
        $doc    = dom_import_simplexml($xml)->ownerDocument;
        $result = SXML::appendDOMNode($xml, $doc);
        $this->assertInstanceOf($class, $result);
        $this->assertEquals('doc', $result->getName());
        list($parent) = $result->xpath('..');
        $this->assertInstanceOf($class, $parent);
        $this->assertEquals('doc', $parent->getName());

        // DOMDocument in a different document
        $doc = $this->getFixtureDoc('features.xml');

        $xml = new SimpleXMLElement('<doc/>');
        SXML::appendDOMNode($xml, $doc);
        $out = $xml->asXML();
        $this->assertStringStartsWith("<?xml version=\"1.0\"?>\n<doc><!--\n    This document contains some of the XML features\n--><root xmlns:prefix=\"uri:for-prefix\">", $out);
        $this->assertEquals("TextNode", trim($xml->root->element));

        $out = $xml->root->element->children[2]->asXML();
        $this->assertEquals("<children>Plain and <![CDATA[Cdata]]></children>", $out);
    }

    /**
     * @test
     */
    public function importDOMFragment() {
        $xml      = new SimpleXMLElement('<doc/>');
        $doc      = new DOMDocument();
        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML('<child>1</child><child>2</child><child>3</child>');

        $result = SXML::appendDOMNode($xml, $fragment);
        $this->assertInstanceOf('SimpleXMLElement', $result);
        $this->assertEquals("child", $result->getName());
    }

    /**
     * @test
     */
    public function importNoElementNodes() {
        $doc = $this->getFixtureDoc('features-dtd.xml');
        $xml = new SimpleXMLElement('<doc/>');

        // comment
        $comment = $doc->childNodes->item(0);
        $this->assertInstanceOf('DOMComment', $comment);

        $result = SXML::appendDOMNode($xml, $comment);
        $this->assertNull($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc><!--\n    This document contains a variety of XML features\n--></doc>\n", $xml->asXML());


        // DTD (different document)
        $xml = new SimpleXMLElement('<doc/>');

        $dtd = $doc->childNodes->item(1);
        $this->assertInstanceOf('DOMDocumentType', $dtd);

        $this->allowErrors(function () use (&$result, $xml, $dtd) {
            $result = SXML::appendDOMNode($xml, $dtd);
        }, array('type' => 1024, 'message' => 'dropped a DTD'));

        $this->assertFalse($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc/>\n", $xml->asXML());


        // DTD (same document)
        $this->allowErrors(function () use (&$result, $doc, $dtd) {
            $result = SXML::appendDOMNode(simplexml_import_dom($doc), $dtd);
        }, array('type' => 1024, 'message' => 'dropped a DTD'));

        $this->assertFalse($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc/>\n", $xml->asXML());

        // entityReference
        $entity = $doc->createEntityReference('entity');
        $this->assertInstanceOf('DOMEntityReference', $entity);

        $result = SXML::appendDOMNode($xml, $entity);

        $this->assertNull($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc>&entity;</doc>\n", $xml->asXML());
        $this->assertInstanceOf('DOMEntityReference', dom_import_simplexml($xml)->childNodes->item(0));


        // CDATA
        $xml = new SimpleXMLElement('<doc/>');

        $cdata = $doc->getElementsByTagName('children')->item(1)->childNodes->item(0);
        $this->assertInstanceOf('DOMCharacterData', $cdata);
        $this->assertInstanceOf('DOMCdataSection', $cdata);

        $result = SXML::appendDOMNode($xml, $cdata);
        $this->assertNull($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc><![CDATA[Cdata Node]]></doc>\n", $xml->asXML());


        // Text
        $xml  = new SimpleXMLElement('<doc/>');
        $text = $doc->createTextNode('Text');
        $this->assertInstanceOf('DOMCharacterData', $text);
        $this->assertInstanceOf('DOMText', $text);

        $result = SXML::appendDOMNode($xml, $text);
        $this->assertNull($result);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<doc>Text</doc>\n", $xml->asXML());

    }

    /**
     * @param       $function
     * @param array $lastError ex: array('type' => 1024, 'message' => 'dropped a DTD')
     * @return mixed
     */
    private function allowErrors($function, array $lastError = null) {
        set_error_handler('var_dump', 0);
        /** @noinspection PhpUndefinedVariableInspection */
        @$undef_var;
        $saved = ini_set('display_errors', false);

        $result = $function();

        ini_set('display_errors', $saved);
        restore_error_handler();

        if ($lastError) {
            $expected = $lastError;
            $this->assertEquals($expected, array_intersect_key(error_get_last(), $expected));
        }

        return $result;
    }

}
