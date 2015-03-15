<?php
return array(
    // XML, non canonicalized
    'raw'   => '<Predstavitev xmlns="http://www.sigen.si/PodpisaniDokument" Id="MyVisualisation2"><Podatki ca="SIGEN-CA" dsPodjetja="" dsUporabnika="12345678" emso="1212912500444" maticna="" serial="2462933412018"/></Predstavitev>',
    // XML, in canonical form
    'canon' => '<Predstavitev xmlns="http://www.sigen.si/PodpisaniDokument" Id="MyVisualisation2"><Podatki ca="SIGEN-CA" dsPodjetja="" dsUporabnika="12345678" emso="1212912500444" maticna="" serial="2462933412018"></Podatki></Predstavitev>',
    'digest' => 'tmLGK3IVc1mC/r5ScUKXQ46wcCA=',
);
