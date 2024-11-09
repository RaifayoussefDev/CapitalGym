<?php
require '../vendor/autoload.php'; // Include the PHPWord library
require '../inc/conn_db.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Shared\Converter;


$id_user = 314;
// Récupérer les utilisateurs avec les détails de l'abonnement et les activités
$sql = "
SELECT 
    u.id, 
    u.matricule, 
    u.id_card, 
    u.nom, 
    u.prenom, 
    u.Note, 
    u.email, 
    u.phone, 
    u.adresse, 
    u.num_urgence, 
    u.photo, 
    u.fonction, 
    u.employeur, 
    a.id AS id_abonnement, 
    u.date_naissance, 
    a.type_abonnement, 
    u.cin, 
    u.genre, 
    p.package_type_id, 
    a.offres_promotionnelles, 
    a.description, 
    p.id AS id_pack, 
    SUM(py.montant_paye) AS montant_paye_total, -- Total montant_paye for the user
    py.id AS payement_id, 
    py.total AS total, 
    p.pack_name AS pack_name, 
    py.reste AS reste, 
    a.date_debut AS date_debut, 
    a.date_fin AS date_fin
FROM 
    users u 
JOIN 
    abonnements a ON u.id = a.user_id 
JOIN 
    packages p ON p.id = a.type_abonnement 
JOIN 
    payments py ON py.abonnement_id = a.id 
WHERE 
    u.role_id = 3 
    AND u.id = '$id_user'
GROUP BY 
    u.id, a.id, p.id;
";

// Helper function to check if a field exists and is not empty, otherwise return "---"
function safeField($field)
{
    return !empty($field) ? $field : '............';
}


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
$section = $phpWord->addSection([
    'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Haut
    'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Bas
    'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Gauche
    'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.5), // Droite
]);


// Add the logo at the top, centered with smaller dimensions
$logoPath = '../assets/img/capitalsoft/logo_light.png'; // Path to your logo
$section->addImage($logoPath, [
    'width' => 60,  // Adjusted width to reduce size
    'height' => 40, // Adjusted height to reduce size
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
    'wrappingStyle' => 'inline'
]);

foreach ($users as $user) {

    // Add centered club name text at the bottom
    $section->addText('PRIVILEGE LUXURY FITNESS CLUB', [
        'name' => 'Arial',
        'size' => 9,
        'bold' => true
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);

    // Add a space after the club name

    // Define the frame style with a border
    $styleFrame = [
        'borderSize' => 2,
        'borderColor' => '000000',
        'width' => 450, // Width of the border
        'height' => 20,
        'align' => 'center',
    ];

    // Create a text box with a border
    $frame = $section->addTextBox($styleFrame);

    // Set text in the frame with centered alignment
    $frame->addText('CONTRAT D’ADHESION', [
        'name' => 'Arial',
        'size' => 8,
        'bold' => true
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);

    // Add centered club name text at the bottom
    $section->addText('Conditions particulières de souscription au contrat d\'abonnement ci-après définies', [
        'name' => 'Arial',
        'size' => 7,
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);

    // Define the frame style with a border
    $styleFrame = [
        'borderSize' => 2,
        'borderColor' => '000000',
        'width' => 300, // Width of the border
        'height' => 20,
        'align' => 'center',
    ];

    // Create a text box with a border
    $frame = $section->addTextBox($styleFrame);

    // Set text in the frame with centered alignment
    $frame->addText('CONTRAT N° :        2401', [
        'name' => 'Arial',
        'size' => 8,
        'bold' => true
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);

    // Add the title "ENTREE" to the left cell, setting a wider width to span half the table width
    $section->addText('Entree', [ // Adjust cell width as needed
        'name' => 'Arial',
        'size' => 8,
        'bold' => true,
        'underline' => 'single' // Underline the text
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
    ]);

    // Add a new row for additional information
    // Ajouter le texte pour le Club et l'adhérent dans le même bloc, sans table
    $section->addText("PRIVILEGE LUXURY FITNESS CLUB ayant son siège social à Casablanca, 111 boulevard MODIBO KEITA.\nD’une part ;", [
        'name' => 'Times New Roman',
        'size' => 8,
    ]);

    $section->addText("Ci-après dénommé « Club »", [
        'name' => 'Times New Roman',
        'size' => 8,
        'bold' => true,
    ]);

    $section->addText("ET :", [
        'name' => 'Times New Roman',
        'size' => 8,
        'bold' => true,
    ]);

    $section->addText(
        "Mme / Mr : " . safeField($user['nom']) . " " . safeField($user['prenom']) .
            ", né le " . safeField($user['date_naissance']) .
            ", demeurant à " . safeField($user['adresse']) .
            " , titulaire de la CIN :" . safeField($user['cin']) .
            ", GSM : " . safeField($user['phone']) .
            ", Profession : " . safeField($user['fonction']) .
            ", Employeur : " . safeField($user['employeur']) .
            ", E-mail : " . safeField($user['email']) .
            ", Personne à contacter en cas d’urgence : " . safeField($user['num_urgence']),
        [
            'name' => 'Times New Roman',
            'size' => 8,
        ]
    );



    $section->addText("Ci-après dénommé « Adhérent »", [
        'name' => 'Times New Roman',
        'size' => 8,
        'bold' => true,
    ]);

    $section->addText("D’autre part ;", [
        'name' => 'Times New Roman',
        'size' => 8,
    ]);


    // Ajouter le texte après la ligne
    $section->addText(
        "Préalablement à la signature de ce contrat, l’adhérent a visité les installations du club et a pris connaissance des prestations proposées et du règlement intérieur. Il a été convenu ce qui suit :",
        [
            'name' => 'Arial',
            'size' => 8,
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, // Justifier le texte pour un aspect professionnel
        ]
    );


    // Ajouter le titre avec un style en gras, en majuscules et souligné
    $section->addText(
        "I - OBJET DU CONTRAT :",
        [
            'name' => 'Arial',
            'size' => 8,
            'bold' => true,
            'underline' => 'single', // Ajoute un soulignement
            'allCaps' => true // Met le texte en majuscules
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        ]
    );


    // Ajouter le texte explicatif avec une taille de police de 9
    $section->addText(
        "Le présent contrat d'abonnement a pour objet la souscription d'un abonnement donnant droit à la pratique d’activités sportives, et l’utilisation des installations avec un accès illimité suivant les jours et les heures d’ouverture du club dans le cadre de l’abonnement souscrit ci-après dans la rubrique \"Choix de l'abonnement\".",
        [
            'name' => 'Arial',
            'size' => 8
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH // Justifié pour un aspect plus formel
        ]
    );

    // Ajouter le titre avec un style en gras, en majuscules et souligné
    $section->addText(
        "II- DROITS D'INSCRIPTION :",
        [
            'name' => 'Arial',
            'size' => 8,
            'bold' => true,
            'underline' => 'single', // Ajoute un soulignement
            'allCaps' => true // Met le texte en majuscules
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        ]
    );

    $section->addText(
        "A la signature du présent contrat, l'adhérent s'acquitte par chèque / espèce/ carte/ des sommes ci-après définies.",
        [
            'name' => 'Arial',
            'size' => 8
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH // Justifié pour un aspect plus formel
        ]
    );
    $section->addText(
        "Le présent contrat d'abonnement est conclu pour une offre et une durée déterminée, comme mentionnée ci-dessous.",
        [
            'name' => 'Arial',
            'size' => 8
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH // Justifié pour un aspect plus formel
        ]
    );
    $section->addText(
        "Le présent contrat d'abonnement est accepté selon les conditions particulières ci-dessous définies et les conditions générales figurant au verso.",
        [
            'name' => 'Arial',
            'size' => 8
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH // Justifié pour un aspect plus formel
        ]
    );

    // Ajouter le titre avec un style en gras, en majuscules et souligné
    $section->addText(
        "III- CHOIX DU TYPE D’ABONNEMENT :",
        [
            'name' => 'Arial',
            'size' => 8,
            'bold' => true,
            'underline' => 'single', // Ajoute un soulignement
            'allCaps' => true // Met le texte en majuscules
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        ]
    );

    // Créer un tableau pour les boutons radio
    // Créer un tableau pour les boutons radio
    $table = $section->addTable([
        'borderColor' => 'ffffff', // Invisible borders
        'borderSize' => 0, // Invisible borders
    ]);

    // Ajouter une ligne pour les boutons radio
    $table->addRow();

    // Define the symbol for a selected and unselected radio button
    $selectedRadio = '●'; // Filled circle to indicate selected
    $unselectedRadio = 'O'; // Unfilled circle for unselected

    // Determine the selected option based on the value of $user['pack_name']
    $individuelRadio = $unselectedRadio;
    $familleRadio = $unselectedRadio;
    $groupeRadio = $unselectedRadio;
    $conventionRadio = $unselectedRadio;

    if (in_array($user['pack_name'], ['Silver', 'Gold', 'Platinum'])) {
        $individuelRadio = $selectedRadio;
    } elseif ($user['pack_name'] === 'Famille') {
        $familleRadio = $selectedRadio;
    } elseif (str_starts_with($user['pack_name'], 'Groupe')) {
        $groupeRadio = $selectedRadio;
    } else {
        $conventionRadio = $selectedRadio;
    }

    // Add the "Individuel" radio button
    $table->addCell(500)->addText($individuelRadio, ['name' => 'Arial', 'size' => 8]);
    $table->addCell(2000)->addText(' Individuel', ['name' => 'Arial', 'size' => 8]);

    // Add the "Famille" radio button
    $table->addCell(500)->addText($familleRadio, ['name' => 'Arial', 'size' => 8]);
    $table->addCell(2000)->addText(' Famille', ['name' => 'Arial', 'size' => 8]);

    // Add the "Groupe" radio button
    $table->addCell(500)->addText($groupeRadio, ['name' => 'Arial', 'size' => 8]);
    $table->addCell(2000)->addText(' Groupe', ['name' => 'Arial', 'size' => 8]);

    // Add the "Convention" radio button
    $table->addCell(500)->addText($conventionRadio, ['name' => 'Arial', 'size' => 8]);
    $table->addCell(2000)->addText(' Convention', ['name' => 'Arial', 'size' => 8]);


    // Ajouter une nouvelle table pour les informations de l'abonnement
    $table = $section->addTable([
        'borderColor' => 'ffffff', // Invisible borders
        'borderSize' => 0, // Invisible borders
    ]);

    // Ajouter une ligne pour le type d'abonnement en utilisant le nom du pack
    $table->addRow();
    $table->addCell(2000)->addText(strtoupper($user['pack_name']) . ' :', ['name' => 'Arial', 'size' => 8, 'bold' => true]); // Étiquette avec le nom du pack

    // Ajouter des cellules pour "Du", "Au", et "Soit" en utilisant les valeurs de $user
    $table->addCell(3000)->addText('Du :   ' . ($user['date_debut'] ?? '……………'), ['name' => 'Arial', 'size' => 8]); // Date de début
    $table->addCell(3000)->addText('Au :   ' . ($user['date_fin'] ?? '……………'), ['name' => 'Arial', 'size' => 8]); // Date de fin
    $table->addCell(3000)->addText('Soit :   ' . ($user['total'] ?? '……………') . ' DH TTC', ['name' => 'Arial', 'size' => 8]); // Montant total

    // Ajouter le texte sous la table
    $section->addText(
        "Le présent contrat est conclu pour une durée déterminée conformément à l’abonnement contracté.",
        [
            'name' => 'Arial',
            'size' => 8, // Taille de la police ajustée à 9
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        ]
    );

    // Ajouter le titre "IV - MODALITES DE REGLEMENT" avec style
    $section->addText(
        "IV - MODALITES DE REGLEMENT",
        [
            'name' => 'Arial',
            'size' => 8,
            'bold' => true,
            'underline' => 'single', // Ajoute un soulignement
            'allCaps' => true // Met le texte en majuscules
        ],
        [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, // Aligné à gauche
        ]
    );


    // Créer une table pour le texte avec deux colonnes
    $table = $section->addTable([
        'borderColor' => 'ffffff',
        'borderSize' => 0, // Bordures invisibles
        'cellMargin' => 10, // Espacement interne des cellules
    ]);

    // Ajouter une ligne pour "SOIT UN MONTANT TOTAL..."
    $table->addRow();
    $table->addCell(8000)->addText("SOIT UN MONTANT TOTAL (frais d’inscription au club Assurance + Abonnement)", [
        'name' => 'Arial',
        'size' => 8,
    ]);

    // Ajouter la deuxième cellule avec montant aligné à droite
    $table->addCell(3000)->addText("DE : " . ($user['total'] ?? '……………') . " DH TTC", [
        'name' => 'Arial',
        'size' => 8,
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT, // Aligné à droite
    ]);


    // Ajouter une nouvelle ligne avec première cellule vide et texte dans la deuxième cellule
    $table->addRow();
    $table->addCell(7000); // Cellule vide à gauche
    $table->addCell(4000)->addText("TOTAL A REGLER : " . ($user['total'] ?? '……………') . "DH TTC", [
        'name' => 'Arial',
        'size' => 8,
    ], [
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT, // Aligné à droite
    ]);

    // Créer la table principale avec deux colonnes
    $mainTable = $section->addTable([
        'borderColor' => 'ffffff',
        'borderSize' => 0,
    ]);

    // Colonne de gauche : Texte, boutons radio, et tableau pour les modes de paiement
    $leftCell = $mainTable->addRow()->addCell(8000);

    // Texte d'instruction
    $leftCell->addText("L'abonnement est payable selon le mode suivant :", [
        'name' => 'Arial',
        'size' => 8,
    ]);

    // Ajouter les boutons radio pour les modes de paiement
    // Get payment types for the user
    $sqlPayments = "
    SELECT `type_paiement_id` 
    FROM `payments` 
    WHERE `user_id` = '$id_user'
";
    $resultPayments = $conn->query($sqlPayments);

    // Initialize payment type flags
    $isEspece = false;
    $isCarte = false;
    $isCheque = false;
    $isVirement = false;

    // Check each payment type from the result
    if ($resultPayments->num_rows > 0) {
        while ($payment = $resultPayments->fetch_assoc()) {
            switch ($payment['type_paiement_id']) {
                case 1:
                    $isEspece = true;
                    break;
                case 2:
                    $isCarte = true;
                    break;
                case 3:
                    $isCheque = true;
                    break;
                case 4:
                    $isVirement = true;
                    break;
            }
        }
    }

    // Display the payment options with checked indicators based on payment types
    $leftCell->addText(
        "         " .
            ($isCheque ? '☑' : '⃝') . " Chèque       " .
            ($isEspece ? '☑' : '⃝') . " Espèce       " .
            ($isCarte ? '☑' : '⃝') . " Carte        " .
            ($isVirement ? '☑' : '⃝') . " Virement",
        ['name' => 'Arial', 'size' => 8]
    );


    // Query payments for the specific user
    $sqlPayments = "SELECT `type_paiement_id`, SUM(`montant_paye`) AS montant_paye FROM `payments` WHERE `user_id` = '$id_user' GROUP BY `type_paiement_id`;";
    $resultPayments = $conn->query($sqlPayments);

    // Initialize payment data placeholders for each payment type
    $paymentData = [
        1 => ['mode' => 'Espèces', 'amount' => '…………DH', 'reference' => '................'],
        2 => ['mode' => 'Carte', 'amount' => 'N/A', 'reference' => '................'],
        3 => ['mode' => 'Chèque', 'amount' => 'N/A', 'reference' => '................'],
        4 => ['mode' => 'Virement', 'amount' => 'N/A', 'reference' => '................']
    ];

    // Track which payment types are available
    $availablePayments = [];

    // Fill payment data based on database records
    if ($resultPayments->num_rows > 0) {
        while ($payment = $resultPayments->fetch_assoc()) {
            $type = $payment['type_paiement_id'];
            if (isset($paymentData[$type])) {
                // Update amount and reference if payment data exists for the type
                $paymentData[$type]['amount'] = $payment['montant_paye'] . ' DH';
                $paymentData[$type]['reference'] = !empty($payment['user_activites_id']) ? $payment['user_activites_id'] : '................';
                $availablePayments[] = $type; // Mark this payment type as available
            }
        }
    }

    // Create payment table in the Word document
    $paymentTable = $leftCell->addTable([
        'borderColor' => '000000',
        'borderSize' => 6,
    ]);

    // Add column headers
    $paymentTable->addRow();
    $paymentTable->addCell(3000)->addText("Mode", [
        'name' => 'Arial',
        'size' => 8,
        'bold' => true,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);
    $paymentTable->addCell(1500)->addText("Montant", [
        'name' => 'Arial',
        'size' => 8,
        'bold' => true,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);
    $paymentTable->addCell(3000)->addText("N° Pièce", [
        'name' => 'Arial',
        'size' => 8,
        'bold' => true,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);

    // Populate rows only for available payment types
    foreach ($availablePayments as $type) {
        $paymentType = $paymentData[$type];
        $borderBottom = ($paymentType['mode'] === 'Virement') ? ['borderBottomSize' => 8, 'borderBottomColor' => '000000'] : [];
        $paymentTable->addRow();
        $paymentTable->addCell(3000, $borderBottom)->addText($paymentType['mode'], ['name' => 'Arial', 'size' => 8]);
        $paymentTable->addCell(1500, $borderBottom)->addText($paymentType['amount'], ['name' => 'Arial', 'size' => 8]);
        $paymentTable->addCell(3000, $borderBottom)->addText($paymentType['reference'], ['name' => 'Arial', 'size' => 8]);
    }

    // Colonne de droite : Observations avec un cadre de texte simple
    $rightCell = $mainTable->addCell(5000);

    // Ajouter un cadre pour "Observations"
    $rightCell->addText("Pour tout règlement, prière d'exiger un reçu. Ce dernier pourra être demandé par la direction à tout moment en cas de vérification de l'adhésion:", [
        'name' => 'Arial',
        'size' => 8,
    ]);

    // Créer une cellule avec un cadre pour "Observation"
    $observationTable = $rightCell->addTable([
        'borderColor' => '000000',
        'borderSize' => 6, // Taille de la bordure pour simuler le cadre
        'cellMargin' => 300, // Marges internes pour le contenu du cadre
    ]);

    // Ajouter "Observation :" suivi de la note de l'utilisateur dans la même cellule
    $observationText = "Observation : " . (!empty($user['note']) ? $user['note'] : "..........."); // Affiche "---" si la note est vide

    $observationTable->addRow();
    $observationTable->addCell(5000)->addText($observationText, [
        'name' => 'Arial',
        'size' => 10,
    ]);


    $section->addText("Fait en double exemplaire à Casablanca le ");
    $section->addText("L'adhérent reconnait avoir pris connaissance et accepte les conditions générales au verso sans réserve. ");

    // Ajouter une table pour les signatures
    $signatureTable = $section->addTable([
        'borderColor' => 'FFFFFF',
        'borderSize' => 1,
    ]);

    // Ajouter la première ligne avec deux cellules (signature de l'adhérent à gauche, signature du club à droite)
    $signatureTable->addRow();

    // Ajouter la cellule gauche pour la signature de l'adhérent avec texte aligné à gauche
    $signatureTable->addCell(6000, ['align' => 'left'])->addText(
        "Signature de l’adhérent précédée de la mention « lu et approuvé »",
        ['name' => 'Arial', 'size' => 8]
    );

    // Ajouter la cellule droite pour la signature du club avec texte aligné à droite
    $signatureTable->addCell(6000, ['align' => 'right'])->addText(
        "PRIVILEGE LUXURY FITNESS CLUB",
        ['name' => 'Arial', 'size' => 8, 'bold' => true],
        ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT] // Aligné à droite
    );
    // Ajouter une nouvelle section pour la deuxième page
    $section2 = $phpWord->addSection([
        'marginTop' => Converter::cmToTwip(0.5),
        'marginBottom' => Converter::cmToTwip(0.5),
        'marginLeft' => Converter::cmToTwip(0.5),
        'marginRight' => Converter::cmToTwip(0.5),
    ]);


    // Enregistrez le fichier final
    $outputPath = './contrat_adhesion.docx';
    $phpWord->save($outputPath, 'Word2007');
}
// Afficher un message de succès
echo "Le contrat a été créé avec succès à l'emplacement : $outputPath";
