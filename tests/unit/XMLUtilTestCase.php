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
 * Class XMLUtilTestCase
 */
abstract class XMLUtilTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @param $name
     * @return string
     */
    protected function getFixtureString($name) {

        if (strpos($name, '#', 1)) {
            return $this->getFixtureStringArray($name);
        }

        return $this->getFixtureStringFile($name);
    }

    /**
     * @param $name
     * @return DOMDocument
     */
    protected function getFixtureDoc($name) {
        $xml = $this->getFixtureString($name);
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        return $doc;
    }

    private function getFixtureStringArray($name) {
        list($phpFile, $key) = explode('#', $name, 2);

        $filename = __DIR__ . '/../fixtures/' . basename($phpFile);
        if (!is_readable($filename)) {
            throw new UnexpectedValueException(
                sprintf("Unable to load fixture file %s from file %s", var_export($phpFile, true), var_export($filename, true))
            );
        }

        $array = include $filename;

        if (!isset($array[$key])) {
            throw new UnexpectedValueException(
                sprintf("Unable to load fixture %s from file %s", var_export($name, true), var_export($filename, true))
            );
        }

        return $array[$key];
    }

    private function getFixtureStringFile($name) {

        $filename = __DIR__ . '/../fixtures/' . basename($name);
        if (!is_readable($filename) or false === $buffer = file_get_contents($filename)) {
            throw new UnexpectedValueException(
                sprintf("Unable to load fixture %s from file %s", var_export($name, true), var_export($filename, true))
            );
        }

        return $buffer;
    }
}
