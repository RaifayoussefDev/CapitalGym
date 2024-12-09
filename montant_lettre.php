<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertir Nombre en Lettres</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="number"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Convertir un Nombre en Lettres</h1>
    <form method="POST">
        <label for="nombre">Entrez un nombre :</label><br>
        <input type="number" name="nombre" id="nombre" required placeholder="Ex : 123456"><br><br>
        <button type="submit">Convertir</button>
    </form>

    <?php
    // Fonction pour convertir un nombre en lettres (avec gestion des milliers, millions)
    function convertirNombreEnLettres($nombre) {
        $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];
        $exceptions = [
            11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze', 16 => 'seize',
            71 => 'soixante-et-onze', 72 => 'soixante-douze', 73 => 'soixante-treize', 74 => 'soixante-quatorze',
            91 => 'quatre-vingt-onze', 92 => 'quatre-vingt-douze', 93 => 'quatre-vingt-treize'
        ];

        $grands_nombres = ['', 'mille', 'million', 'milliard', 'billion'];

        if ($nombre == 0) {
            return 'zéro';
        }

        $texte = '';
        $parties = [];
        $i = 0;

        while ($nombre > 0) {
            $reste = $nombre % 1000; // Prendre les 3 derniers chiffres
            if ($reste > 0) {
                $parties[] = ($reste == 1 && $i == 1 ? '' : convertirCentaines($reste)) . ' ' . $grands_nombres[$i];
            }
            $nombre = intdiv($nombre, 1000); // Supprimer les 3 derniers chiffres
            $i++;
        }

        return implode(' ', array_reverse($parties));
    }

    // Fonction pour convertir les centaines et dizaines
    function convertirCentaines($nombre) {
        $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];
        $exceptions = [
            11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze', 16 => 'seize',
            71 => 'soixante-et-onze', 72 => 'soixante-douze', 73 => 'soixante-treize', 74 => 'soixante-quatorze',
            91 => 'quatre-vingt-onze', 92 => 'quatre-vingt-douze', 93 => 'quatre-vingt-treize'
        ];

        $texte = '';

        if ($nombre >= 100) {
            $centaines = intdiv($nombre, 100);
            $reste = $nombre % 100;
            $texte .= $centaines > 1 ? $unites[$centaines] . ' cent' : 'cent';
            if ($reste > 0) {
                $texte .= ' ';
            }
            $nombre = $reste;
        }

        if ($nombre >= 10) {
            if (isset($exceptions[$nombre])) {
                $texte .= $exceptions[$nombre];
            } else {
                $dizaine = intdiv($nombre, 10);
                $unite = $nombre % 10;
                $texte .= $dizaines[$dizaine];
                if ($unite == 1 && $dizaine != 8) {
                    $texte .= '-et-';
                }
                $texte .= $unites[$unite];
            }
        } else {
            $texte .= $unites[$nombre];
        }

        return trim($texte);
    }

    // Si un nombre est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
        $nombre = intval($_POST['nombre']);
        $resultat = convertirNombreEnLettres($nombre);
        echo "<div class='result'>Résultat : <strong>$resultat</strong></div>";
    }
    ?>
</body>
</html>
