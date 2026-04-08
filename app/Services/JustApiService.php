<?php
// app/Services/JustApiService.php

class JustApiService {
    private string $wsdlUrl = 'http://portalquery.just.ro/Query.asmx?WSDL';
    private ?SoapClient $client = null;

    public function __construct() {
        // Inițializăm clientul SOAP. Dezactivăm cache-ul ca să avem mereu date fresh.
        try {
            $this->client = new SoapClient($this->wsdlUrl, [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true,
                'connection_timeout' => 15
            ]);
        } catch (SoapFault $e) {
            error_log("Eroare inițializare SOAP: " . $e->getMessage());
        }
    }

    /**
     * Caută dosare după nume, și opțional după instituție și obiect.
     * Returnează un array de dosare sau NULL dacă API-ul MJ este picat.
     */
    public function searchCases(string $numeParte, ?string $institutie = null, ?string $obiect = null): ?array {
        // Dacă nu s-a putut inițializa clientul SOAP (ex: site căzut), returnăm null pentru a semnala eroarea
        if (!$this->client) return null; 

        // Construim parametrii exacți ceruți de metoda 'CautareDosare' a MJ
        $params = [
            'numeParte' => $numeParte
        ];

        if (!empty($institutie)) {
            $params['institutie'] = $institutie;
        }

        if (!empty($obiect)) {
            $params['obiectDosar'] = $obiect;
        }

        try {
            $response = $this->client->CautareDosare($params);
            
            // API-ul MJ poate returna un singur obiect (dacă e un dosar) sau un array de obiecte.
            // Noi le normalizăm mereu într-un array pentru a le putea procesa uniform.
            if (!isset($response->CautareDosareResult->Dosar)) {
                return []; // Nu a găsit nimic (comportament normal valid)
            }

            $dosare = $response->CautareDosareResult->Dosar;
            
            if (is_object($dosare)) {
                return [$dosare]; // A returnat un singur dosar, îl punem în array
            }
            
            return $dosare; // A returnat un array de dosare

        } catch (SoapFault $e) {
            error_log("Eroare apel API MJ pentru {$numeParte}: " . $e->getMessage());
            // Returnăm NULL (și nu array gol) pentru ca sistemul să știe că verificarea a fost invalidată de server
            return null; 
        }
    }
}
