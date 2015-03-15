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
 * XML Signature Syntax and Processing (Second Edition)
 *
 * Class XMLDigest
 */
class XMLDigest extends XMLDSigAlgorithm
{
    const DIGEST_SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    const HASH_SHA1   = 'sha1';

    /**
     * @var array
     */
    private $digestAlgorithms;

    /**
     * @var null|string name of the hash algorithm used
     */
    private $algorithm;

    public function __construct($uri) {
        $this->initImplementations();

        parent::__construct($uri);
        $this->initUri($uri);
    }

    private function initUri($uri) {
        $uri = (string) $uri;

        $algorithm = isset($this->digestAlgorithms[$uri]) ? $this->digestAlgorithms[$uri] : null;

        if ($uri === '___test_algos') {
            $algorithm = $uri;
        }

        if ($algorithm and !in_array($algorithm, hash_algos(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Hash extension does not support the algorithm named %s for uri %s', var_export($algorithm, true),
                    var_export($uri, true)
                )
            );
        }

        $this->algorithm = $algorithm;
    }

    private function initImplementations() {
        $this->digestAlgorithms = array(
            self::DIGEST_SHA1 => self::HASH_SHA1,
        );
    }

    /**
     * @return bool
     */
    public function hasImplementation() {
        return (bool) $this->algorithm;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function digest($data) {
        if (null === $this->algorithm) {
            throw new BadMethodCallException(sprintf('Unknown hash algorithm %s', var_export($this->uri, true)));
        }

        return base64_encode(hash($this->algorithm, $data, true));
    }
}
