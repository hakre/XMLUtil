<?php
/*
 * This file is part of the XMLUtil package.
 *
 * Copyright (C) 2012, 2013, 2014, 2015 hakre <http://hakre.wordpress.com>
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
 * Class SXML
 *
 * SimpleXMLElement related functions
 */
abstract class SXML
{
    /**
     * appends the XML as children to parent
     *
     * @param SimpleXMLElement $parent
     * @param string           $xml
     */
    public static function appendXML(SimpleXMLElement $parent, $xml) {
        $self = dom_import_simplexml($parent);
        $doc  = $self->ownerDocument;

        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML($xml);

        while ($child = $fragment->firstChild) {
            $self->appendChild($child);
        }
    }

    /**
     * Cast a SimpleXMLElement to the type of another
     *
     * @param string|SimpleXMLElement $className
     * @param SimpleXMLElement        $element
     *
     * @return SimpleXMLElement
     */
    public static function cast($className, SimpleXMLElement $element) {
        if ($element instanceof $className) {
            return $element;
        }

        return simplexml_import_dom(dom_import_simplexml($element), $className);
    }

    /**
     * @param DOMNode $node
     * @param string  $class
     * @return null|SimpleXMLElement
     */
    public static function domImport(DOMNode $node, $class = 'SimpleXMLElement') {
        if ($node instanceof DOMComment) {
            return null;
        }

        if ($node instanceof DOMEntityReference) {
            return null;
        }

        if ($node instanceof DOMCharacterData) {
            return null;
        }

        if ($node instanceof DOMDocument) {
            return null;
        }

        if ($node instanceof DOMAttr) {
            $element = simplexml_import_dom($node->parentNode, $class);

            return $element->attributes($node->namespaceURI)->{$node->nodeName};
        }

        if ($node instanceof DOMElement and !$node->ownerDocument) {
            $doc  = new DomDocument();
            $node = $doc->importNode($node, true);
        }

        return simplexml_import_dom($node, $class);
    }

    /**
     * @param SimpleXMLElement $parent
     * @param SimpleXMLElement $element
     *
     * @return SimpleXMLElement|false on error
     *
     * @throws DOMException
     */
    public static function import(SimpleXMLElement $parent, SimpleXMLElement $element) {

        if (null === $element[0]) {
            return false;
        }

        return self::importDOMNode($parent, dom_import_simplexml($element));
    }

    /**
     * import a DOMNode as child
     *
     * @param SimpleXMLElement $parent
     * @param DOMNode          $node
     * @return SimpleXMLElement
     *
     * @throws DOMException
     */
    public static function importDOMNode(SimpleXMLElement $parent, DOMNode $node) {

        if ($node instanceof DOMDocumentType) {
            trigger_error('dropped a DTD');

            return false;
        }

        if ($node instanceof DOMDocument) {
            return SXML::importDOMNodes($parent, $node->childNodes);
        }

        $parentNode = dom_import_simplexml($parent);

        if (($node->ownerDocument ?: $node) !== $parentNode->ownerDocument) {
            $newNode = $parentNode->ownerDocument->importNode($node, true);
        } else {
            $newNode = $node->cloneNode(true);
            if (!$newNode) {
                throw new DOMException(
                    sprintf(
                        'unable to clone node %s (result: %s)', var_export($node, true), var_export($newNode, true)
                    )
                );
            }
        }

        $firstChild = $parentNode->appendChild($newNode);

        if (!$firstChild instanceof DOMNode) {
            trigger_error(sprintf(
                "Unable to append %s as child (result: %s) ", $newNode->ownerDocument->saveXML($newNode), var_export($firstChild, true)
            ));
        }

        return self::domImport($firstChild, get_class($parent));
    }

    /**
     * import a DOMNode as child
     *
     * @param SimpleXMLElement    $parent
     * @param DOMNode|DOMNodeList $nodes
     *
     * @return SimpleXMLElement
     */
    public static function importDOMNodes(SimpleXMLElement $parent, DOMNodeList $nodes) {

        $count = 0;
        $first = false;

        foreach ($nodes as $node) {
            $added = self::importDOMNode($parent, $node);
            if ($added !== false && !$count++) {
                $first = $added;
            }
        }

        return $first;
    }

}
