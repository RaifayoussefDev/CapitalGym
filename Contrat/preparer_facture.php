<?php
require '../vendor/autoload.php'; // Include the PHPWord library

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\SimpleType\Jc;

// Helper function to check if a field exists and is not empty, otherwise return "---"
function safeField($field)
{
    return !empty($field) ? $field : '...........................................................';
}
function GenerateFacture($id_user)
{
    require '../inc/conn_db.php';

    // $id_user = $_GET['id_user'];
    $sql = "SELECT 
    u.id, 
    u.matricule,  
    u.nom, 
    u.prenom, 
    u.photo, 
    u.fonction, 
    u.employeur, 
    a.id AS id_abonnement, 
    DATE_FORMAT(u.date_naissance, '%d/%m/%Y') AS date_naissance, 
    a.type_abonnement, 
    u.cin, 
    u.genre, 
    p.package_type_id, 
    a.offres_promotionnelles, 
    a.description, 
    p.id AS id_pack, 

    -- Utilisation de la sous-requête pour obtenir le montant payé total, agrégé par abonnement
    COALESCE(py.montant_paye_total, 0) AS montant_paye_total, -- Default to 0 if no payments found

    p.pack_name AS pack_name, 
    COALESCE(py.reste, 0) AS reste,  -- Handle NULL values for 'reste'
    COALESCE(py.total, 0) AS total,  -- Handle NULL values for 'total'
    
    -- Formatage des dates
    DATE_FORMAT(a.date_debut, '%d/%m/%Y') AS date_debut, 
    DATE_FORMAT(a.date_fin, '%d/%m/%Y') AS date_fin,
    DATE_FORMAT(a.date_abonnement, '%d/%m/%Y') AS date_abonnement,

    -- Suppression des doublons dans les activités et périodes
    GROUP_CONCAT(DISTINCT ua.activite_id ORDER BY ua.activite_id ASC) AS activites_list,
    GROUP_CONCAT(DISTINCT ua.periode_activites ORDER BY ua.activite_id ASC) AS activites_periode

FROM 
    users u
JOIN 
    abonnements a ON u.id = a.user_id 
JOIN 
    packages p ON p.id = a.type_abonnement 
LEFT JOIN 
    (SELECT 
        abonnement_id, 
        SUM(montant_paye) AS montant_paye_total,
        MAX(reste) AS reste, -- Prendre le reste maximum
        MAX(total) AS total  -- Prendre le total maximum
     FROM 
        payments 
     GROUP BY 
        abonnement_id) py ON py.abonnement_id = a.id  -- Sous-requête pour agrégat des paiements
LEFT JOIN 
    user_activites ua ON ua.user_id = u.id  -- Joindre les activités de l'utilisateur

WHERE 
    u.role_id = 3 
    AND u.id = '$id_user'

GROUP BY 
    u.id, a.id, p.id;";


    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $users = [];
        echo $sql;
    }
    // Create a new PHPWord object
    $phpWord = new PhpWord();
    // Définir les marges en cm (0,5 cm) pour la section
    // Define A4 paper size (in twips: 595x842)
    $section = $phpWord->addSection([
        'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(21),  // A4 width (21 cm)
        'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(29.7), // A4 height (29.7 cm)
        'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),  // Top margin
        'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1), // Bottom margin
        'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1),  // Left margin
        'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1), // Right margin
    ]);


    // Add the logo at the top, centered with smaller dimensions
    $logoPath = '../assets/img/capitalsoft/logo_light.png'; // Path to your logo
    $section->addImage($logoPath, [
        'width' => 200,  // Smaller width
        'height' => 100, // Smaller height
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        'wrappingStyle' => 'inline'
    ]);


    foreach ($users as $user) {

        // Create a text box with a border
        // $matricule = $user['matricule'];

        // Extraire la partie numérique du matricule (tout sauf la première lettre)
        // $numericPart = substr($matricule, 1); // Enlève la première lettre

        // Créer le numéro de contrat en utilisant uniquement la partie numérique du matricule
        // $numeroContrat = $numericPart;



        $matricule = $user['matricule'];

        // Extraire la partie numérique du matricule (tout sauf la première lettre)
        $numericPart = substr($matricule, 1); // Enlève la première lettre

        // Créer le numéro de contrat en utilisant uniquement la partie numérique du matricule
        $numeroContrat = $numericPart;


        $code_pack = '';
        $pack_name = $user['pack_name'];
        $activites_list = $user['activites_list'];
        $activites_periode = $user['activites_periode'];

        if ($pack_name == 'Familial') {
            if ($activites_list == '53' && $activites_periode == '12') {
                $code_pack = 'FG';
            } elseif ($activites_list == '53,54,55,56' && $activites_periode == '12,10') {
                $code_pack = 'FP';
            } else {
                $code_pack = 'FS';
            }
        } elseif ($pack_name == 'Silver') {
            $code_pack = 'S';
        } elseif ($pack_name == 'Gold') {
            $code_pack = 'G';
        } elseif ($pack_name == 'Platinum') {
            $code_pack = 'P';
        } elseif (strpos($pack_name, 'Groupe') === 0) {  // Checks if pack_name starts with "Groupe"
            $code_pack = 'GP';
        }

        $pack_name = $user['pack_name'];

        // Assign the pack name based on code_pack
        if ($code_pack == 'FS') {
            $pack_name = 'Famille Silver';
        } elseif ($code_pack == 'FG') {
            $pack_name = 'Famille Gold';
        } elseif ($code_pack == 'FP') {
            $pack_name = 'Famille Platinum';
        } elseif ($code_pack == 'G') {
            $pack_name = 'Gold';
        } elseif ($code_pack == 'P') {
            $pack_name = 'Platinum';
        } elseif ($code_pack == 'S') {
            $pack_name = 'Silver';
        }


        function calculateFromTotalTTC($totalTTC)
        {
            // Calcul du PU HT (prix unitaire hors taxes)
            $puHT = $totalTTC / 1.20; // Diviser le TTC par 1.20 (car 20% de TVA)

            // Calcul de la TVA
            $tva = $totalTTC - $puHT;

            // Formater les montants avec des milliers séparateurs
            return [
                'puHT' => number_format($puHT, 2, '.', ' '),
                'tva' => number_format($tva, 2, '.', ' ')
            ];
        }


        // Get the total paid amount from user
        $totalAmountPaid = $user['total']; // Assuming this is the total amount paid

        $totals = calculateFromTotalTTC($totalAmountPaid);

        // Format the total amount with a space as thousands separator
        $totalAmountPaid2 = number_format($totalAmountPaid, 2, '.', ' ');

        $tva = $totals['tva'];



        // Définir les styles de cellule et de texte
        $cellStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'valign' => 'center',
        ];
        $cellCENTERStyle = [
            // 'borderSize' => 6,
            // 'borderColor' => '000000',
            'valign' => 'center',
        ];
        $textStyle = [
            'name' => 'Times New Roman',
            'size' => 10,
        ];

        // Vérifier et récupérer les données utilisateur
        $NOM = isset($user['nom']) ? safeField($user['nom']) : 'Nom inconnu';
        $PRENOM = isset($user['prenom']) ? safeField($user['prenom']) : 'Prénom inconnu';
        $societe = isset($user['employeur']) ? safeField($user['employeur']) : 'Société inconnue';
        $ICE = "78898"; // Valeur par défaut ou dynamique


        // Add text for "FACTURE N"
        $section->addText(
            "FACTURE N : 00012024",
            [
                'name' => 'Arial',
                'size' => 12,
                'bold' => true,
            ],
            [
                'alignment' => Jc::LEFT // Align the text to the left
            ]
        );

        // Add text for "DATE"
        $section->addText(
            "DATE : " . date('d/m/Y'), // Use current system date
            [
                'name' => 'Arial',
                'size' => 12,
                'bold' => true,
            ],
            [
                'alignment' => Jc::LEFT // Align the text to the left
            ]
        );
        // Ajouter une table avec deux colonnes
        $table = $section->addTable();
        $table->addRow();

        // Colonne gauche : Informations du Club
        $leftCell = $table->addCell(4000, $cellStyle);
        $leftCell->addText(
            "PRIVILEGE LUXURY FITNESS CLUB",
            [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );
        $leftCell->addText(
            "711 BOULEVARD MODIBO KEITA",
            [
                'name' => 'Arial',
                'size' => 10,

            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );
        $leftCell->addText(
            "Casablanca",
            [
                'name' => 'Arial',
                'size' => 10,

            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );
        $leftCell->addText(
            "MAROC",
            [
                'name' => 'Arial',
                'size' => 10,

            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );

        function convertirNombreEnLettres($nombre)
        {
            $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
            $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];
            $exceptions = [
                11 => 'onze',
                12 => 'douze',
                13 => 'treize',
                14 => 'quatorze',
                15 => 'quinze',
                16 => 'seize',
                71 => 'soixante-et-onze',
                72 => 'soixante-douze',
                73 => 'soixante-treize',
                74 => 'soixante-quatorze',
                91 => 'quatre-vingt-onze',
                92 => 'quatre-vingt-douze',
                93 => 'quatre-vingt-treize'
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
        function convertirCentaines($nombre)
        {
            $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
            $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];
            $exceptions = [
                11 => 'onze',
                12 => 'douze',
                13 => 'treize',
                14 => 'quatorze',
                15 => 'quinze',
                16 => 'seize',
                71 => 'soixante-et-onze',
                72 => 'soixante-douze',
                73 => 'soixante-treize',
                74 => 'soixante-quatorze',
                91 => 'quatre-vingt-onze',
                92 => 'quatre-vingt-douze',
                93 => 'quatre-vingt-treize'
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

        // Colonne droite : Informations de l'adhérent
        $CENTERtCell = $table->addCell(2000, $cellCENTERStyle);
        $rightCell = $table->addCell(5000, $cellCENTERStyle);
        // Colonne gauche : Informations du Club

        $rightCell->addText(
            "NOM ET PRENOM ADHERENT : $NOM $PRENOM",
            [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
                'allCaps' => true // Met le texte en majuscules

            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );
        $rightCell->addText(
            "Nom de la société : $societe",
            [
                'name' => 'Arial',
                'size' => 10,
                'allCaps' => true // Met le texte en majuscules

            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );
        $rightCell->addText(
            "ICE : $ICE",
            [
                'name' => 'Arial',
                'size' => 10,
                'allCaps' => true // Met le texte en majuscules


            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
            ]
        );

        // Add some space
        $section->addTextBreak(1);

        // Créer le tableau de facture
        $table = $section->addTable([
            'alignment' => Jc::CENTER, // Alignement au centre
            'cellMargin' => 50, // Marge interne des cellules
        ]);

        // Propriétés pour les cellules avec bordures horizontales (titre et boutons)
        $headerStyle = [
            'borderTopSize' => 6,
            'borderBottomSize' => 6,
            'borderLeftSize' => 6,
            'borderRightSize' => 6,
            'borderColor' => '000000',
        ];

        // Propriétés pour les cellules avec uniquement des bordures verticales (données)
        $dataCellStyle = [
            'borderLeftSize' => 6,
            'borderRightSize' => 6,
            'borderLeftColor' => '000000',
            'borderRightColor' => '000000',
        ];

        // Ajouter la ligne d'en-tête avec bordures horizontales
        $table->addRow();
        $table->addCell(2000, $headerStyle)->addText("REFERENCE", ['bold' => true]);
        $table->addCell(4500, $headerStyle)->addText("DESCRIPTION", ['bold' => true]);
        $table->addCell(1000, $headerStyle)->addText("QTE", ['bold' => true]);
        $table->addCell(2000, $headerStyle)->addText("PU HT", ['bold' => true]);
        $table->addCell(2000, $headerStyle)->addText("Montant", ['bold' => true]);

        $date_debut = $user['date_debut'];
        $date_fin = $user['date_fin'];
        // Ajouter des données avec bordures verticales uniquement
        $table->addRow();
        $table->addCell(2000, $dataCellStyle)->addText("$code_pack$numeroContrat");
        $table->addCell(4500, $dataCellStyle)->addText("Abonnement $pack_name du $date_debut Au $date_fin");
        $table->addCell(1000, $dataCellStyle)->addText("1");
        $table->addCell(2000, $dataCellStyle)->addText($totals['puHT']);
        $table->addCell(2000, $dataCellStyle)->addText($totals['puHT']);

        // Ajouter 10 lignes vides avec bordures verticales uniquement
        for ($i = 0; $i < 18 ; $i++) {
            $table->addRow();
            $table->addCell(2000, $dataCellStyle)->addText(""); // Cellule vide
            $table->addCell(4500, $dataCellStyle)->addText("");
            $table->addCell(1000, $dataCellStyle)->addText("");
            $table->addCell(2000, $dataCellStyle)->addText("");
            $table->addCell(2000, $dataCellStyle)->addText("");
        }

        $lastRowCellStyle = [
            'borderLeftSize' => 6,
            'borderRightSize' => 6,
            'borderBottomSize' => 6,
            'borderLeftColor' => '000000',
            'borderRightColor' => '000000',
            'borderBottomColor' => '000000',
        ];
        $table->addRow();
        $table->addCell(2000, $lastRowCellStyle)->addText(""); // Cellule vide
        $table->addCell(4500, $lastRowCellStyle)->addText("");
        $table->addCell(1000, $lastRowCellStyle)->addText("");
        $table->addCell(2000, $lastRowCellStyle)->addText("");
        $table->addCell(2000, $lastRowCellStyle)->addText("");

        // Ajouter une table pour le pied de page
        $tablefooter = $section->addTable([
            'alignment' => Jc::CENTER, // Alignement au centre
            'cellMargin' => 50, // Marge interne des cellules
        ]);

        // Ajouter un espace
        $section->addTextBreak(1);

        // Ajouter le résumé
        $section->addText(
            "La présente facture est arrêtée à la somme de :",
            ['name' => 'Arial', 'size' => 10]
        );
        $montant_lettre = convertirNombreEnLettres($totalAmountPaid);

        // Convertir en majuscules
        $montant_lettre_maj = strtoupper($montant_lettre);

        $section->addText(
            "$montant_lettre_maj Dirhams",
            ['name' => 'Arial', 'size' => 10, 'bold' => true]
        );

        // Ajouter la table des totaux
        $totalsTable = $section->addTable([
            'alignment' => Jc::RIGHT,
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 0
        ]);

        // Ajouter les lignes des totaux
        $totalsTable->addRow();
        $totalPUHT = $totals['puHT'];
        $totalsTable->addCell(4000)->addText("Total HT", ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $totalsTable->addCell(2000)->addText("$totalPUHT MAD", ['bold' => true, 'name' => 'Arial', 'size' => 10]);

        $totalsTable->addRow();
        $totalsTable->addCell(4000)->addText("TVA 20%", ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $totalsTable->addCell(2000)->addText("$tva MAD", ['bold' => true, 'name' => 'Arial', 'size' => 10]);

        $totalsTable->addRow();
        $totalsTable->addCell(4000)->addText("TOTAL TTC", ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $totalsTable->addCell(2000)->addText("$totalAmountPaid2 MAD", ['bold' => true, 'name' => 'Arial', 'size' => 10]);

        // Ajouter un pied de page
        $footer = $section->addFooter();

        // Ajouter les textes au pied de page
        $footer->addText(
            "S.A.R.L au Capital de 1.000.000,00 DHS",
            ['name' => 'Arial', 'size' => 10]
        );
        $footer->addText(
            "RC : 512897 - Patente : 33301331 - IF : 50496468 - ICE : 002895498000062",
            ['name' => 'Arial', 'size' => 10]
        );
        $footer->addText(
            "711, Angle Boulevard Modibo Keita et rue de la Saone - CASABLANCA",
            ['name' => 'Arial', 'size' => 10]
        );
        $footer->addText(
            "Tél : 0522 83 18 18 - E-mail : privilegeLuxuryfitnessc@gmail.com",
            ['name' => 'Arial', 'size' => 10]
        );


        // Définir le chemin de sortie
        $outputDir = './adherents/factures/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);  // Crée le dossier s'il n'existe pas
        }

        // Générer le chemin complet et sauvegarder le fichier
        $nom = preg_replace("/[\/\\\\']/", '', $NOM);
        $prenom = preg_replace("/[\/\\\\']/", '', $PRENOM);
        $outputPath = $outputDir . $nom . '_' . $prenom . '_facture.docx';

        try {
            $phpWord->save($outputPath, 'Word2007');
        } catch (Exception $e) {
            throw new RuntimeException("Erreur lors de la sauvegarde du fichier Word : " . $e->getMessage());
        }

        // Générer le nom du fichier pour la base de données
        $contractName = "adherents/factures/{$nom}_{$prenom}_facture.docx";
        // // Mettre à jour le nom du contrat dans la table "users"
        // $updateQuery = "UPDATE users SET contract_name = '$contractName' WHERE id = $id_user";
        // mysqli_query($conn, $updateQuery);
    }


    // Return only the contract name to the client (no success message)
    return $contractName;

    // header('location:../Adherents/');
}
