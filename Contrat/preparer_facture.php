<?php
require '../vendor/autoload.php'; // Include the PHPWord library


use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Table;
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
        'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5),  // Top margin
        'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Bottom margin
        'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5),  // Left margin
        'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Right margin
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

        // Colonne droite : Informations de l'adhérent
        $CENTERtCell = $table->addCell(3000, $cellCENTERStyle);
        $rightCell = $table->addCell(4000, $cellStyle);
        // $rightCell->addText(
        //     "NOM & PRENOM ADHERENT : $NOM $PRENOM",
        //     [
        //         'name' => 'Arial',
        //         'size' => 10,

        //     ],
        //     [
        //         'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        //     ]
        // );
        // $rightCell->addText(
        //     "Nom de la société : $societe",
        //     [
        //         'name' => 'Arial',
        //         'size' => 10,

        //     ],
        //     [
        //         'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        //     ]
        // );
        // $rightCell->addText(
        //     "ICE : $ICE",
        //     [
        //         'name' => 'Arial',
        //         'size' => 10,

        //     ],
        //     [
        //         'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        //     ]
        // );

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

    header('location:../Adherents/');
}
