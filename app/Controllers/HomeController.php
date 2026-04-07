<?php
// app/Controllers/HomeController.php

class HomeController {
    public function index() {
        $db = Database::getInstance();
        
        $stmtCountries = $db->query("SELECT * FROM countries ORDER BY is_active DESC, name_key ASC");
        $countries = $stmtCountries->fetchAll();

        // Acum lista de instanțe e perfect curată (doar curți, judecătorii, etc.)
        $stmtInst = $db->query("SELECT * FROM institutions ORDER BY name ASC");
        $institutions = $stmtInst->fetchAll();

        // Extragem obiectele dosarelor pentru autocomplete
        $stmtObj = $db->query("SELECT * FROM case_objects ORDER BY name ASC");
        $caseObjects = $stmtObj->fetchAll();

        $data = [
            'pageTitle' => translate('app.title'),
            'countries' => $countries,
            'institutions' => $institutions,
            'caseObjects' => $caseObjects // Trimitem lista nouă către View
        ];

        View::render('pages/home/index', $data);
    }
}