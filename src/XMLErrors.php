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
 * Class XMLErrors
 */
class XMLErrors implements Countable, Iterator
{
    private $errors;
    private $count;
    private $index;

    public static function out($xml) {
        $errors = new self();
        $lines  = preg_split('~\\R~u', $xml, -1);
        foreach ($lines as $index => $line) {
            printf("#%03d    %s\xE2\x90\x8A\n", ++$index, $line);
            $it = $errors->atLine($index)->columnDesc();
            foreach ($it as $error) {
                $buffer = sprintf('[%s]', $error->getSeverity());
                $buffer .= str_repeat(' ', 7 + $error->getColumn() - strlen($buffer));
                $buffer .= sprintf("^- (%d) %s (%d:%d)\n", $error->getCode(), $error->getMessage(), $error->getLine(), $error->getColumn());
                foreach ($it->followUpColumns() as $column) {
                    $buffer[$column + 7] = '^';
                }
                echo $buffer;
            }
        }
    }

    public function __construct(array $errors = null) {
        if (null === $errors) {
            $errors = libxml_get_errors();
        }
        $this->errors = $errors;
        $this->count  = count($errors);
        $this->index  = $this->count ? 0 : null;
    }

    /**
     * @param int $index
     *
     * @return XMLError|null
     */
    public function item($index) {
        if (!isset($this->errors[$index])) {
            return null;
        }

        $error = $this->errors[$index];
        if ($error instanceof XMLError) {
            return $error;
        }

        return new XMLError($error);
    }

    /**
     * @param int $line
     *
     * @return XMLErrors
     */
    public function atLine($line) {
        if ($line < 1) {
            return null;
        }

        $saved  = $this->index;
        $errors = array();
        foreach ($this as $error) {
            if ($error->getLine() === $line) {
                $errors[] = $error;
            }
        }
        $this->index = $saved;

        return new XMLErrors($errors);
    }

    public function columnDesc() {
        if (!$this->count) {
            return array();
        }

        $saved    = $this->index;
        $sortKeys = array();  // 0:line, 1:column, 2:original-index

        foreach ($this as $index => $error) {
            $sortKeys[0][] = $error->getLine();
            $sortKeys[1][] = $error->getColumn();
            $sortKeys[2][] = $index;
        }
        $this->index = $saved;

        array_multisort($sortKeys[0], $sortKeys[1], SORT_DESC, $sortKeys[2]);

        $result = array();
        foreach ($sortKeys[2] as $key) {
            $result[] = $this->errors[$key];
        }

        return new XMLErrors($result);
    }

    /**
     * @return array
     */
    public function followUpColumns() {
        $columns = array();

        if (!$this->valid()) {
            return $columns;
        }

        $index = $this->index;

        $columns[$first = $this->item($index)->getColumn()] = 1;

        while (++$index < $this->count) {
            $columns[$this->item($index)->getColumn()] = 1;
        }

        unset($columns[$first]);

        return array_keys($columns);
    }

    public function count() {
        return $this->count;
    }

    public function rewind() {
        $this->index = $this->count ? 0 : null;
    }

    public function valid() {
        return $this->index < $this->count;
    }

    /**
     * @return XMLError|null
     */
    public function current() {
        return $this->item($this->index);
    }

    /**
     * @return int|null
     */
    public function key() {
        return $this->index;
    }

    public function next() {
        $this->count && $this->index++;
    }
}
