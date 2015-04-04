<?php
/*
 * XMLUtil - Utility Classes for XML Standard Applications in PHP
 *
 * Copyright (C) 2013, 2015 hakre <http://hakre.wordpress.com>
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
 * Class ExtendedXMLElement
 */
class ExtendedXMLElement extends SimpleXMLElement implements Countable
{
    /**
     * @param $string
     * @return ExtendedXMLElement|null|false the child added from XML, false on error (invalid XML) or null if the
     *                                       child can not be represented as SimpleXMLElement (e.g. comment)
     */
    public function addXML($string) {
        $doc      = new DOMDocument();

        $saved = libxml_use_internal_errors(true);

        $result = $doc->loadXML($string);
        if ($result) {
            $import = $doc->documentElement;
        } else {
            $import = $doc->createDocumentFragment();
            $result   = $import->appendXML($string);
        }

        libxml_use_internal_errors($saved);

        if (!$result) {
            return false;
        }

        return SXML::appendDOMNode($this, $import);
    }

    /**
     * add another SimpleXMLElement as child
     *
     * @param SimpleXMLElement $element
     * @return ExtendedXMLElement
     */
    public function addElement(SimpleXMLElement $element) {
        $string = $this->cast($element)->asXML(null, LIBXML_NOXMLDECL);

        $child = $this->addXml($string);
        if (!$child instanceof $this) {
            throw new InvalidArgumentException('Invalid SimpleXMLElement given');
        }

        return $child;
    }

    /**
     * @param null $file
     * @param null $options
     * @return int|string
     */
    public function asXML($file = null, $options = null) {
        if (null === $file) {
            return $this->getXML($options);
        } else {
            return file_put_contents($file, $this->getXML($options));
        }
    }

    /**
     * @param $options
     * @return string
     */
    private function getXML($options) {
        $self = dom_import_simplexml($this);
        $doc  = $self->ownerDocument;

        $sXmlDocEle      = $self === $doc->documentElement;
        $dropDeclaration = $options & LIBXML_NOXMLDECL;

        $doc->formatOutput       = true;
        $doc->preserveWhiteSpace = false;

        $suffix = "";

        if (!$dropDeclaration && $sXmlDocEle) {
            $self = null;
        } elseif ($dropDeclaration && $sXmlDocEle) {
            $suffix = "\n";
        }

        return $doc->saveXML($self, $options) . $suffix;
    }

    /**
     * @param SimpleXMLElement $element
     * @return ExtendedXMLElement
     */
    private function cast(SimpleXMLElement $element) {
        return SXML::cast($this, $element);
    }
}
