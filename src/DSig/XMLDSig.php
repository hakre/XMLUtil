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
 *
 * XML digital signature processing
 *
 * @link http://www.w3.org/TR/2002/REC-xmldsig-core-20020212/  XML-Signature Syntax and Processing
 */
class XMLDSig
{
    const URI                     = 'http://www.w3.org/2000/09/xmldsig#';
    const PREFIX                  = 'xmldsig';

    const SIGNATURE               = 'xmldsig:Signature';
    const SIGNED_INFO             = 'xmldsig:SignedInfo';
    const CANONICALIZATION_METHOD = 'xmldsig:CanonicalizationMethod';
    const REFERENCE               = 'xmldsig:Reference';
    const DIGEST_METHOD           = 'xmldsig:DigestMethod';
    const DIGEST_VALUE            = 'xmldsig:DigestValue';

    const ALGORITHM               = 'Algorithm';

    const FIRST                   = '(//%s)[1]';
    const FIRST_CHILD             = '(./%s)[1]';

    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMXPath
     */
    private $xpath;

    public function __construct($xml) {
        $this->initXml($xml);
    }

    private function initXml($xml) {
        if (is_string($xml)) {
            $doc = new DOMDocument();
            $doc->loadXML($xml);
        } else {
            $doc = $xml;
        }

        $xpath = new DOMXPath($doc->ownerDocument ?: $doc);
        $xpath->registerNamespace(self::PREFIX, self::URI);

        $this->doc   = $doc;
        $this->xpath = $xpath;
    }

    /**
     * get digest of XML
     *
     * @param string $xml
     * @return string  digest, FALSE on error
     */
    public function getDigest($xml) {
        try {
            $canon  = $this->getCanonicalizationMethod()->C14N($xml);
            $return = $this->getDigestMethod()->digest($canon);
        } catch (LogicException $e) {
            trigger_error(sprintf('Can not create digest: %s', $e->getMessage()));
            $return = false;
        }

        return $return;
    }

    /**
     * @return DOMElement
     */
    public function getSignatureElement() {
        return $this->first(self::FIRST, self::SIGNATURE);
    }

    /**
     * @return DOMElement
     */
    public function getSignedInfoElement() {
        return $this->first(self::FIRST_CHILD, self::SIGNED_INFO, $this->getSignatureElement());
    }

    /**
     * @return DOMElement
     */
    public function getReferenceElement() {
        return $this->first(self::FIRST_CHILD, self::REFERENCE, $this->getSignedInfoElement());
    }

    /**
     * @return DOMElement
     */
    public function getCanonicalizationMethodElement() {
        return $this->first(self::FIRST_CHILD, self::CANONICALIZATION_METHOD, $this->getSignedInfoElement());
    }

    /**
     * @return XMLCanonical
     */
    public function getCanonicalizationMethod() {
        $algorithm = $this->getCanonicalizationMethodElement()->getAttribute(self::ALGORITHM);

        return new XMLCanonical($algorithm);
    }

    public function getDigestMethodElement() {
        return $this->first(self::FIRST_CHILD, self::DIGEST_METHOD, $this->getReferenceElement());
    }

    public function getDigestValueElement() {
        return $this->first(self::FIRST_CHILD, self::DIGEST_VALUE, $this->getReferenceElement());
    }

    public function getDigestValue() {
        return trim($this->getDigestValueElement()->nodeValue);
    }

    /**
     * @return XMLDigest
     */
    public function getDigestMethod() {
        $algorithm = $this->getDigestMethodElement()->getAttribute(self::ALGORITHM);

        return new XMLDigest($algorithm);
    }

    /**
     * @param string  $format
     * @param string  $name
     * @param DOMNode $context (optional) context node
     *
     * @return DOMElement
     */
    private function first($format, $name, $context = null) {
        $signature = $this->evaluate($format, array($name), $context);
        if (!$signature instanceof DOMNodeList or $signature->length !== 1) {
            throw new BadMethodCallException('There is no ' . $name);
        }

        return $signature->item(0);
    }

    private function evaluate($format, array $parameters = array(), $context = null) {
        $expression = vsprintf($format, $parameters);

        $result = $this->xpath->evaluate($expression, $context);
        if (false === $result) {
            throw new InvalidArgumentException(sprintf('Xpath evaluation failed: %s', var_export($expression, true)));
        }

        return $result;
    }
}
