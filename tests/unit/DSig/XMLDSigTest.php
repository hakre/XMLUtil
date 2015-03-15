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
 * Class XMLDSigTest
 *
 * @covers XMLDSig
 */
class XMLDSigTest extends XMLUtilTestCase
{
    /**
     * @test
     */
    public function creation() {
        $xml = $this->getFixtureString('sig29050349soap.xml');
        $sig = new XMLDSig($xml);
        $this->assertInstanceOf('XMLDSig', $sig);

        $doc = new DOMDocument();
        $this->assertTrue($doc->loadXML($xml));

        $sig = new XMLDSig($doc);
        $this->assertInstanceOf('XMLDSig', $sig);

        $sig = new XMLDSig($doc->documentElement);
        $this->assertInstanceOf('XMLDSig', $sig);
    }


    /**
     * @test
     */
    public function getElements() {
        $sig = new XMLDSig($this->getFixtureString('sig29050349soap.xml'));
        $this->assertInstanceOf('DOMElement', $sig->getSignatureElement());
        $this->assertInstanceOf('DOMElement', $sig->getCanonicalizationMethodElement());
        $this->assertInstanceOf('DOMElement', $sig->getDigestMethodElement());
    }

    /**
     * @test
     */
    public function processing() {
        $prefix = 'sig29050349';

        $sig = new XMLDSig($this->getFixtureString($prefix . 'soap.xml'));
        $xml = $this->getFixtureString($prefix . '.php#raw');

        $canon = $sig->getCanonicalizationMethod()->C14N($xml);
        $this->assertEquals($this->getFixtureString($prefix . '.php#canon'), $canon);

        $correctDigest = $this->getFixtureString($prefix . '.php#digest');

        $digest = $sig->getDigestMethod()->digest($canon);
        $this->assertEquals($correctDigest, $digest);

        $this->assertEquals($correctDigest, $sig->getDigest($canon));
        $this->assertEquals($correctDigest, $sig->getDigest($xml));

        $none = new XMLDSig('<xml/>');
        $this->assertFalse(@$none->getDigest('<xml/>'));
        $this->assertEquals('Can not create digest: There is no xmldsig:Signature', $this->getErrorMessage());

        $this->assertFalse(@$none->getDigest(''));
        $this->assertEquals('Can not create digest: There is no xmldsig:Signature', $this->getErrorMessage());
    }

    /**
     * @test
     */
    public function example29050349() {
        $prefix       = 'sig29050349';
        $soapResponse = $this->getFixtureString($prefix . 'soap.xml');
        $this->expectOutputString('string(28) "tmLGK3IVc1mC/r5ScUKXQ46wcCA="'. "\n");

        $string = '<Predstavitev xmlns="http://www.sigen.si/PodpisaniDokument" Id="MyVisualisation2"><Podatki ca="SIGEN-CA" dsPodjetja="" dsUporabnika="12345678" emso="1212912500444" maticna="" serial="2462933412018"/></Predstavitev>';

        $sig = new XMLDSig($soapResponse);
        $digest = $sig->getDigest($string);

        var_dump($digest); // string(28) "tmLGK3IVc1mC/r5ScUKXQ46wcCA="
    }

    /**
     * @test
     */
    public function referencing() {

        $prefix = 'sig29050349';

        $doc       = $this->getFixtureDoc($prefix . 'soap.xml');
        $sig       = new XMLDSig($doc);
        $reference = $sig->getReferenceElement();
        $uri       = $reference->getAttribute('URI');

        $id = parse_url($uri, PHP_URL_FRAGMENT);
        $this->assertEquals('MyVisualisation2', $id);

        $xp   = new DOMXPath($doc);
        $node = $xp->query(sprintf('//*[@Id="%s"]', $id))->item(0);
        $this->assertInstanceOf('DOMElement', $node);

        $digest = $sig->getDigest($node);
        $this->assertEquals('tmLGK3IVc1mC/r5ScUKXQ46wcCA=', $digest);

        $this->assertEquals($digest, $sig->getDigestValue());
    }

    private function getErrorMessage(array $last = null) {
        if (null === $last) {
            $last = error_get_last();
        }

        return $last['message'];
    }
}
