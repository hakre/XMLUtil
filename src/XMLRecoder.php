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
 * XMLRecoder
 *
 * Utility class to extract and change encoding information stored
 * inside an XML declaration and to recode an XML string
 *
 * Requires: iconv for string recoding (iconv is available by default)
 *           <http://php.net/iconv>
 *
 * For character set names valid in the XML Declaration see:
 *    <http://www.iana.org/assignments/character-sets/character-sets.xml>
 *
 * For character set names valid in iconv see:
 *    <http://www.gnu.org/software/libiconv/>
 *
 * You must take names that are valid in both if the XML declaration exists
 * when re-encoding an XML string.
 */
class XMLRecoder
{
    const BOM_UTF_8    = "\xEF\xBB\xBF";
    const BOM_UTF_32LE = "\xFF\xFE\x00\x00";
    const BOM_UTF_16LE = "\xFF\xFE";
    const BOM_UTF_32BE = "\x00\x00\xFE\xFF";
    const BOM_UTF_16BE = "\xFE\xFF";
    /**
     * pcre pattern to access EncodingDecl, see <http://www.w3.org/TR/REC-xml/#sec-prolog-dtd>
     */
    const DECL_PATTERN      = '(^<\?xml\s+version\s*=\s*(["\'])(1\.\d+)\1\s+encoding\s*=\s*(["\'])(((?!\3).)*)\3)';
    const DECL_ENC_GROUP    = 4;
    const DECL_VERSION_ONLY = '(^<\?xml\s+version\s*=\s*(["\'])(1\.\d+)\1(\s*)\?>)';
    const DECL_VER_GROUP    = 3;
    const ENC_PATTERN       = '(^[A-Za-z][A-Za-z0-9._-]*$)';

    /**
     * @param string $string string (recommended length 4 characters/octets)
     * @param string $default (optional) if none detected what to return
     *
     * @return string Encoding, if it can not be detected defaults $default (NULL)
     * @throws InvalidArgumentException
     */
    public function detectEncodingViaBom($string, $default = null) {
        $len = strlen($string);

        if ($len > 4) {
            $string = substr($string . "    ", 0, 4);
        } elseif ($len < 4) {
            $string = substr($string . "    ", 0, 4);
        }

        switch (true) {
            case $string === self::BOM_UTF_16BE . $string[2] . $string[3]:
                return "UTF-16BE";

            case $string === self::BOM_UTF_8 . $string[3]:
                return "UTF-8";

            case $string === self::BOM_UTF_32LE:
                return "UTF-32LE";

            case $string === self::BOM_UTF_16LE . $string[2] . $string[3]:
                return "UTF-16LE";

            case $string === self::BOM_UTF_32BE:
                return "UTF-32BE";
        }

        return $default;
    }

    /**
     * @param string $buffer
     *
     * @return string
     */
    public function removeUTF8Bom($buffer) {
        if (self::BOM_UTF_8 === substr($buffer, 0, 3)) {
            $buffer = substr($buffer, 3);
        }

        return $buffer;
    }

    /**
     * @param string $fromEncoding encoding to recode from. if not specified (NULL) taken from XML declaration, then
     *                             BOM and if missing then defaults to UTF-8
     * @param string $toEncoding encoding to recode into
     * @param string $string
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @return string
     */
    public function recodeXMLString($fromEncoding, $toEncoding, $string) {
        $buffer = $string;

        ($result = preg_match(self::DECL_PATTERN, $buffer, $matches, PREG_OFFSET_CAPTURE))
        && $result = $matches[self::DECL_ENC_GROUP];

        if (null === $fromEncoding) {
            if (!$result) {
                $fromEncoding = $this->detectEncodingViaBom($buffer, 'UTF-8');
            } else {
                $fromEncoding = $result[0];
            }
        }

        $buffer = iconv($fromEncoding, $toEncoding, $buffer);

        if (false === $buffer) {
            throw new UnexpectedValueException(
                sprintf('Can not recode string from "%s" to "%s".', $fromEncoding, $toEncoding)
            );
        }

        ($result = preg_match(self::DECL_PATTERN, $buffer, $matches, PREG_OFFSET_CAPTURE))
        && $result = $matches[self::DECL_ENC_GROUP];

        if ($result) {
            if (!preg_match(self::ENC_PATTERN, $toEncoding)) {
                throw new InvalidArgumentException(sprintf('Invalid target encoding for XML declaration: "%s"', $toEncoding));
            }

            $buffer = substr_replace($buffer, strtoupper($toEncoding), $result[1], strlen($result[0]));
        }

        return $buffer;
    }

    /**
     * get encoding attribute value from XML Declaration
     *
     * @param      $string
     * @param null $default (optional) default value to return if no encoding is set
     *
     * @return string|null null ($default) if encoding does not exist in processing instruction
     */
    public function getEncodingDeclaration($string, $default = null) {
        return preg_match(self::DECL_PATTERN, $string, $matches) ? $matches[self::DECL_ENC_GROUP] : $default;
    }

    /**
     * sets the XML Declaration encoding attribute value (EncName of EncodingDecl)
     *
     * @param $string
     * @param $toEncoding
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setEncodingDeclaration($string, $toEncoding) {

        if (!preg_match(self::ENC_PATTERN, $toEncoding)) {
            throw new InvalidArgumentException(sprintf('Invalid target encoding for XML declaration: "%s"', $toEncoding));
        }

        ($result = preg_match(self::DECL_PATTERN, $string, $matches, PREG_OFFSET_CAPTURE))
        && $result = $matches[self::DECL_ENC_GROUP];

        if ($result) {
            return substr_replace($string, strtoupper($toEncoding), $result[1], strlen($result[0]));
        }

        ($result = preg_match(self::DECL_VERSION_ONLY, $string, $matches, PREG_OFFSET_CAPTURE))
        && $result = $matches[self::DECL_VER_GROUP];

        if ($result) {
            return substr_replace($string, sprintf(' encoding="%s"%s', strtoupper($toEncoding), $result[0]), $result[1], strlen($result[0]));
        }

        return substr_replace($string, sprintf('<?xml version="1.0" encoding="%s"?>', strtoupper($toEncoding)), 0, 0);
    }
}
