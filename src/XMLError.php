<?php
/*
 * XMLUtil - Utility Classes for XML Standard Applications in PHP
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
 * Class XMLError
 */
class XMLError
{
    const SEVERITY_NONE    = 'NONE';
    const SEVERITY_WARNING = 'WARN';
    const SEVERITY_ERROR   = 'ERROR';
    const SEVERITY_FATAL   = 'FATAL';

    /**
     * @param $level
     *
     * @return null|string
     */
    private static function mapSeverityByLevel($level) {
        static $severities = array(
            LIBXML_ERR_NONE    => self::SEVERITY_NONE,
            LIBXML_ERR_WARNING => self::SEVERITY_WARNING,
            LIBXML_ERR_ERROR   => self::SEVERITY_ERROR,
            LIBXML_ERR_FATAL   => self::SEVERITY_FATAL,
        );

        if (!isset($severities[$level])) {
            return null;
        }

        return $severities[$level];
    }

    /**
     * @return LibXMLError
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->getNamed('level', 0);
    }


    private function getNamed($name, $default = null) {
        return isset($this->error->$name) ? $this->error->$name : $default;
    }

    /**
     * @return int
     */
    public function getCode() {
        return $this->getNamed('code', 0);
    }

    /**
     * @return string any of the SEVERITY_* strings
     */
    public function getSeverity() {
        return self::mapSeverityByLevel($this->getLevel());
    }

    /**
     * @return string
     */
    public function getFile() {
        return $this->getNamed('file', '');
    }


    /**
     * @return int
     */
    public function getLine() {
        return $this->getNamed('line');
    }

    /**
     * @return int
     */
    public function getColumn() {
        return $this->getNamed('column');
    }

    /**
     * @return string
     */
    public function getMessage() {
        return rtrim($this->getNamed('message', ''));
    }

    /**
     * @var LibXMLError
     */
    private $error;

    /**
     * @param LibXMLError $error
     */
    public function __construct(LibXMLError $error) {
        $this->error = $error;
    }

    public function __toString() {
        $buffer = sprintf(
            "[%s] (%' 2d) %s (%d:%d)\n", $this->getSeverity(), $this->getCode(), $this->getMessage()
            , $this->getLine(), $this->getColumn()
        );

        return $buffer;
    }

    /**
     * @param LibXMLError $error
     */
    public static function out(LibXMLError $error) {
        echo new self($error);
    }
}
