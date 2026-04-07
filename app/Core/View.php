<?php
// app/Core/View.php

class View {
    public static function render(string $view, array $args = []) {
        // Transformăm array-ul în variabile (ex: $args['pageTitle'] devine $pageTitle)
        extract($args, EXTR_SKIP);

        $viewFile = BASE_PATH . "Views/{$view}.php";
        $layoutFile = BASE_PATH . "Views/layout.php";

        if (is_readable($viewFile)) {
            // 1. Pornim "înregistrarea" (Output Buffering)
            ob_start();
            
            // 2. Includem pagina specifică (ex: home/index.php). HTML-ul nu se afișează, ci e captat.
            require $viewFile;
            
            // 3. Salvăm tot ce a fost captat în variabila $content și oprim înregistrarea
            $content = ob_get_clean();

            // 4. Includem Layout-ul principal, care va da 'echo $content' în interiorul `<main>`
            if (is_readable($layoutFile)) {
                require $layoutFile;
            } else {
                // Fallback dacă lipsește layout.php
                echo $content;
            }

        } else {
            http_response_code(404);
            echo "<h1>Eroare de sistem: Vizualizarea '{$view}' nu a fost găsită.</h1>";
        }
    }
}