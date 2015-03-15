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
 * Canonical XML applications in context of XML Signatures
 *
 * Canonical XML Version 1.0
 * Canonical XML Version 1.1
 *
 * @link http://www.w3.org/TR/xml-c14n
 */
class XMLCanonical extends XMLDSigAlgorithm
{
    const CANONICAL_XML_VERSION_1_0               = 'Canonical XML Version 1.0';
    const CANONICAL_XML_VERSION_1_0_WITH_COMMENTS = 'Canonical XML Version 1.0 with comments';
    const CANONICAL_XML_VERSION_1_1               = 'Canonical XML Version 1.1';
    const CANONICAL_XML_VERSION_1_1_WITH_COMMENTS = 'Canonical XML Version 1.1 with comments';

    const CANON_XML10_OMIT_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const CANON_XML10_WITH_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    const CANON_XML11_OMIT_COMMENTS = 'http://www.w3.org/2006/12/xml-c14n11';
    const CANON_XML11_WITH_COMMENTS = 'http://www.w3.org/2006/12/xml-c14n11#WithComments';

    const IDX_NAME     = 0;
    const IDX_COMMENTS = 1;

    const XML_ERROR_LENGTH = 64;

    /**
     * @var array
     */
    private $canonicalMethods = null;

    /**
     * @param string $uri
     */
    public function __construct($uri) {
        $this->initImplementations();
        parent::__construct($uri);
    }

    private function initImplementations() {
        $this->canonicalMethods = array(
            self::CANON_XML10_OMIT_COMMENTS => array(self::CANONICAL_XML_VERSION_1_0, false),
            self::CANON_XML10_WITH_COMMENTS => array(self::CANONICAL_XML_VERSION_1_0_WITH_COMMENTS, true),
            self::CANON_XML11_OMIT_COMMENTS => array(self::CANONICAL_XML_VERSION_1_1, false),
            self::CANON_XML11_WITH_COMMENTS => array(self::CANONICAL_XML_VERSION_1_1_WITH_COMMENTS, true),
        );
    }

    /**
     * @param $xml
     *
     * @return string
     */
    public function C14N($xml) {
        $info = $this->getImplementationInfo($this->uri);

        if (!isset($info)) {
            throw new BadMethodCallException(sprintf('Unknown canonicalize algorithm %s', $this->uri));
        }

        if (is_string($xml)) {
            $doc    = new DOMDocument();
            $result = $doc->loadXML($xml);
            if (!$result) {
                $excerpt = strlen($xml) < self::XML_ERROR_LENGTH - 3
                    ? $xml
                    : substr($xml, 0, self::XML_ERROR_LENGTH) . '...';
                throw new InvalidArgumentException(sprintf('Invalid XML given %s', var_export($excerpt, true)));
            }
        } else {
            $doc = $xml;
        }

        if (!$doc instanceof DOMNode) {
            throw new InvalidArgumentException('Invalid XML argument given');
        }

        return $doc->C14N(true, $info[self::IDX_COMMENTS]);
    }

    /**
     * @return string|null
     */
    public function getName() {
        return $this->getImplementationInfoByIndex(self::IDX_NAME);
    }

    /**
     * @return bool|null
     */
    public function hasComments() {
        return $this->getImplementationInfoByIndex(self::IDX_COMMENTS);
    }

    /**
     * @return bool
     */
    public function hasImplementation() {
        return (bool) $this->getImplementationInfo();
    }

    private function getImplementationInfo($uri = null) {
        null !== $uri || $uri = $this->uri;

        return isset($this->canonicalMethods[$uri])
            ? $this->canonicalMethods[$uri] : null;
    }

    private function getImplementationInfoByIndex($index, $uri = null) {
        if (
            !$info = $this->getImplementationInfo($uri)
            or !isset($info[$index])
        ) {
            return null;
        }

        return $info[$index];
    }
}
