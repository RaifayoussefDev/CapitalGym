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
    return !empty($field) ? $field : '.....................';
}
function GenerateContrat($id_user)
{
    require '../inc/conn_db.php';
    // $id_user = $_GET['id_user'];
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
        DATE_FORMAT(u.date_naissance, '%d/%m/%Y') AS date_naissance, 
        a.type_abonnement, 
        u.cin, 
        u.genre, 
        p.package_type_id, 
        a.offres_promotionnelles, 
        a.description, 
        p.id AS id_pack, 
        SUM(py.montant_paye) AS montant_paye_total,
        py.id AS payement_id, 
        py.total AS total, 
        p.pack_name AS pack_name, 
        py.reste AS reste, 
        DATE_FORMAT(a.date_debut, '%d/%m/%Y') AS date_debut, 
        DATE_FORMAT(a.date_fin, '%d/%m/%Y') AS date_fin ,
        DATE_FORMAT(a.date_abonnement, '%d/%m/%Y') AS date_abonnement 
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
    ;
    ";




    $matricule = 'P2001';


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
        'width' => 80,  // Smaller width
        'height' => 60, // Smaller height
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
        $matricule = $user['matricule'];

        // Extraire la partie numérique du matricule (tout sauf la première lettre)
        $numericPart = substr($matricule, 1); // Enlève la première lettre

        // Créer le numéro de contrat en utilisant uniquement la partie numérique du matricule
        $numeroContrat = $numericPart;

        // Ajouter le texte au cadre
        $frame->addText('CONTRAT N° :        ' . $numeroContrat, [
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
            "A la signature du présent contrat, l'adhérent s'acquitte par chèque / espèce/ carte/ virement des sommes ci-après définies.",
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
        // Ajouter une cellule pour afficher le nom du pack
        $table->addCell(2000)->addText(strtoupper($user['pack_name']) . ' :', ['name' => 'Arial', 'size' => 8, 'bold' => true]); // Étiquette avec le nom du pack

        // Ajouter des cellules pour "Du", "Au", et "Soit" en utilisant les valeurs de $user
        $table->addCell(3000)->addText('Du :   ' . ($user['date_debut'] ?? '……………'), ['name' => 'Arial', 'size' => 8]); // Date de début
        $table->addCell(3000)->addText('Au :   ' . ($user['date_fin'] ?? '……………'), ['name' => 'Arial', 'size' => 8]); // Date de fin
        $table->addCell(3000)->addText('Soit :   ' . ($user['total'] ?? '……………') . ' DH TTC', ['name' => 'Arial', 'size' => 8]); // Montant total

        // Récupérer les dates au format DD/MM/YYYY
        $dateDebut = DateTime::createFromFormat('d/m/Y', $user['date_debut'] ?? 'now');
        $dateFin = DateTime::createFromFormat('d/m/Y', $user['date_fin'] ?? 'now');


        // Calcul de la différence en mois
        $diff = $dateDebut->diff($dateFin);
        $nombreMois = ($diff->y * 12) + $diff->m; // Années en mois + mois

        // Ajouter la cellule pour afficher le nombre de mois
        // Ajouter une cellule pour afficher le nombre de mois sous le format "Durée : 12 mois"
        $table->addCell(3000)->addText('Durée : ' . $nombreMois . ' mois', ['name' => 'Arial', 'size' => 8]); // Durée en mois


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
                ($isCheque ? '☑' : '☐') . " Chèque       " .
                ($isEspece ? '☑' : '☐') . " Espèce       " .
                ($isCarte ? '☑' : '☐') . " Carte        " .
                ($isVirement ? '☑' : '☐') . " Virement",
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
            'size' => 7,
        ]);

        $date_inscription = $user['date_abonnement'];
        $section->addText("Fait en double exemplaire à Casablanca le " . $date_inscription . "");
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
            "Signature de PRIVILEGE LUXURY FITNESS CLUB",
            ['name' => 'Arial', 'size' => 8, 'bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT] // Aligné à droite
        );

        // Ajouter une nouvelle section (section2) avec une disposition de deux colonnes
        $section2 = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(0.5),
            'marginBottom' => Converter::cmToTwip(0.5),
            'marginLeft' => Converter::cmToTwip(0.5),
            'marginRight' => Converter::cmToTwip(0.5),
        ]);

        // Créer une table avec 2 colonnes pour simuler deux colonnes
        $table = $section2->addTable();

        // Ajouter une ligne à la table
        $table->addRow();


        // Ajouter un paragraphe dans la première colonne avec une marge à droite (centrer le texte à gauche)
        $cell1 = $table->addCell(6500); // Largeur de la cellule en points (ajustez selon vos besoins)
        $cell1->addText(
            "1. Conditions générales et leur application :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte dans la première colonne avec une taille de 7
        $cell1->addText(
            " - Les conditions générales ci-dessous régissent les relations contractuelles entre le centre de remise en forme Privilège Luxury Fitness club objet de ce contrat et l'adhérent contractant.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell1->addText(
            " - Aucune condition particulière ne peut, sauf acceptation formelle et écrite du club ou de son mandataire commercialisant, prévaloir contre les conditions générales.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell1->addText(
            " - Toute condition contraire posée par l'adhérent sera donc caduque, quel que soit le moment où elle aura pu être portée à sa connaissance.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell1->addText(
            " - Le club se réserve le droit de modifier une ou plusieurs clauses dans un sens favorable aux deux parties.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell1->addText(
            "2. Objet du contrat :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte dans la première colonne avec une taille de 7
        $cell1->addText(
            "Après avoir visité les installations du club et/ou pris connaissance des prestations proposées, l'adhérent déclare souscrire un contrat d'abonnement nominatif incessible lui autorisant l'accès au club dans le cadre du forfait choisi, selon un prix et des modalités financières indiqués dans le présent contrat.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            "3. Horaires d'ouverture :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la première colonne avec une taille de 7
        $cell1->addText(
            " - Les horaires et périodes d’ouverture au public sont disponibles à l’entrée de l’établissement.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’évacuation de l’espace PRIVILEGE LUXURY FITNESS s’effectue 30 minutes avant la fermeture.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - La direction se réserve le droit de modifier les horaires pour des raisons de maintenance, événements spéciaux ou jours fériés. Les membres seront informés à l'avance de tout changement important.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            "4. Droit d’accès, obligations, conditions et contrôle :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la première colonne avec une taille de 7
        $cell1->addText(
            " - L'accès à l'espace PRIVILEGE LUXURY FITNESS CLUB est réservé aux personnes ayant payé les droits d'entrée, soit par abonnement, soit pour une entrée unique ou pass. L’accès du membre est soumis obligatoirement à la présence d’un moyen d’identification remis, ainsi qu’au respect des consignes de sécurité, d’hygiène et du règlement intérieur.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L'accès est réservé uniquement aux personnes de 16 ans et plus.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’accès est interdit : Aux mineurs, à toute personne en état d’ébriété ou de malpropreté évidente, aux porteurs de signes caractéristiques d’une maladie contagieuse.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’accès à l’espace PRIVILEGE LUXURY FITNESS ne se fait que par la porte principale. L’entrée par les autres accès est formellement interdite.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’accès au club donne droit à divers sites et prestations suivant la formule et le pack abonnement contracté par l’adhérent, selon les horaires d’ouverture, le programme prédéfini par la direction, ainsi que les disponibilités des espaces relatives à chaque discipline.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Les membres doivent présenter leur carte d’adhésion, QR ou le bracelet remis lors de l'inscription, valable selon la durée souscrite.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - En cas de perte ou de vol, le remplacement de la carte ou la clé du casier sera facturé à 100 DH TTC à l'abonné. Le renouvellement fait l'objet d'un nouveau contrat.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Le club se réserve le droit de fournir au client un QR Code valide à travers l'accès à l'application mobile ; ce code est personnel et non transférable.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’adhérent s'engage à ne pas partager, divulguer ou transmettre ses accès ou codes à toute autre personne. En cas de violation de cette disposition, le club se réserve le droit de résilier immédiatement le contrat d'adhésion sans préavis et sans remboursement.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - L’adhérent reconnaît à la direction du club le droit d'exclure de l'établissement, sans préavis ni indemnité, toute personne dont l'attitude, le comportement ou la tenue seraient contraires aux bonnes mœurs, ou notoirement gênants pour les autres membres, ou non conformes au présent contrat ou au règlement intérieur du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            "5. Tarification :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la première colonne avec une taille de 7
        $cell1->addText(
            " - Le prix fixé sur le contrat est indiqué en Dirhams constants.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Le club se réserve la possibilité d'actualiser le prix de l'abonnement lors de la réactivation d'un nouveau contrat.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Toutes prestations complémentaires non prévues au contrat pourront être facturées en supplément.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - En cas de paiement par prélèvement mensuel, le client autorise le club à effectuer des prélèvements mensuels automatiques sur son compte bancaire ou sa carte de crédit pour le paiement des frais d'adhésion et abonnement.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - En cas de défaut de paiement, le client sera notifié et devra régulariser la situation dans un délai de 48h. Le club se réserve le droit de suspendre l'accès aux installations en cas de non-paiement récurrent.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Le client accepte d'être responsable de tous frais bancaires ou autres frais encourus en cas de défaut de paiement ou de rejet de prélèvement automatique.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );


        $cell1->addText(
            "6. Règlement interne / Règles d’usage, de sécurité et d’hygiène :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la première colonne avec une taille de 7
        $cell1->addText(
            " - L'adhérent déclare se conformer aux conditions générales et au règlement intérieur, y adhérer sans restriction ni réserve et respecter les consignes suivantes :",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Interdiction de fumer à l'intérieur de l'établissement.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Interdiction de comportements et agissement inappropriés, offensants et agressifs. Le respect mutuel entre les membres et le personnel est crucial.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Interdiction de vandalisme, destruction ou détérioration des équipements.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Interdiction de tout comportement mettant en danger la sécurité au sein du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - En cas de non-respect ceci entraînera des sanctions, la direction peut avertir, suspendre temporairement ou exclure définitivement tout membre dont le comportement est inapproprié.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Le Respect des horaires des cours est obligatoire.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Le port de vêtements et de chaussures de sport spécifiques et exclusifs à toutes autres utilisations est obligatoire.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Chaque adhérent doit disposer d’une serviette et de matériel d'hygiène propre à lui.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Les membres doivent suivre les instructions d’utilisation des équipements.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Chaque utilisateur est prié de maintenir la zone rangée en remettant les poids, accessoires et autres équipements à leur place après usage.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Aucun utilisateur n’est autorisé à déplacer le matériel.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Certains équipements ou espaces peuvent nécessiter une réservation préalable. L’adhérent doit respecter les créneaux réservés et annuler les réservations non utilisées dans les délais dans la séance sera comptabilisée.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Les membres doivent partager les équipements et espaces de manière équitable. Les séances de groupe doivent respecter les horaires établis.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Signalez immédiatement tout équipement défectueux et respectez toutes les consignes affichées.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - Pour les premiers secours et en cas de blessure, s’adresser au personnel responsable.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell1->addText(
            " - En cas de signal d'évacuation, suivez le plan d'évacuation affiché et les consignes du personnel.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell1->addText(
            " - Toute personne extérieure, bénéficiant d'une invitation du club ou d'une séance découverte, est soumise au même règlement que les adhérents inscrits et doit déposer obligatoirement une pièce d'identité pendant sa séance et uniquement sur RDV.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell3 = $table->addCell(100);

        // Ajouter un paragraphe dans la deuxième colonne avec une marge à gauche (centrer le texte à droite)
        $cell2 = $table->addCell(6500); // Largeur de la cellule en points (ajustez selon vos besoins)



        // Ajouter la section Vestiaires / Dépôt
        $cell2->addText(
            "Vestiaires / Dépôt :",
            ['bold' => true, 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après le titre
        );

        $cell2->addText(
            " - L'adhérent a la possibilité d'utiliser des casiers individuels à fermeture traditionnelle (cadenas fournis par l’établissement) dont l'utilisation est limitée à la durée de la séance.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Il est strictement interdit de laisser ses affaires personnelles à l'intérieur des casiers après avoir quitté le club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Il est rappelé expressément à l’adhérent que les vestiaires ne font l'objet d'aucune surveillance spécifique ; il est donc recommandé de ne pas y entreposer des objets de valeur.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "7. Assurance :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la cellule 2 avec une taille de 7
        $cell2->addText(
            " - À la date de l’adhésion, l’adhérent doit obligatoirement souscrire à une assurance. En cas de non-souscription, l’adhérent reconnaît expressément être personnellement assuré pour tout dommage et ne pourra en aucun cas engager la responsabilité du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Le club est assuré pour les dommages engageant sa responsabilité civile et celle de son personnel, conformément aux dispositions légales. Toute déclaration d’incident doit être accompagnée d’un justificatif et faite dans les 48 heures suivant l’incident.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );


        // Ajouter le titre "8. Attestation / Certificat médical / Décharge médicale" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "8. Attestation / Certificat médical / Décharge médicale :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la cellule 2 avec une taille de 7
        $cell2->addText(
            " - L'adhérent confirme au club avoir été examiné par un médecin et être capable de pratiquer une activité sportive, qu’il ne souffre d'aucune blessure, maladie ou handicap, qu’il n'a jamais eu de problèmes cardiaques ou respiratoires décelés à ce jour, et avoir pris régulièrement toute précaution nécessaire pour sa santé.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Il doit remettre au club, dans les 2 jours suivant l'adhésion, un certificat médical attestant son aptitude à la pratique sportive au sein du club. À défaut du certificat médical, l’adhérent décharge le club de toutes réclamations, actions juridiques, frais, dépenses et requêtes concernant des blessures ou dommages occasionnés, et causés de quelque manière que ce soit, découlant ou en raison de sa pratique de cette activité sportive, et ce nonobstant le fait que cela ait pu être causé par négligence ou manquement à ses responsabilités en tant qu'occupant des lieux.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - L’adhérent consent à assumer tous les risques connus et inconnus, et toutes les conséquences afférentes ou liées à sa participation aux activités sportives du club. C'est en toute connaissance de cause que ce dernier accepte la présente décharge médicale.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );
        $cell2->addText(
            "9. Responsabilité :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter les textes dans la cellule 2 avec une taille de 7
        $cell2->addText(
            " - La responsabilité du club ne pourra être engagée en cas d'accidents résultant de l'inobservation des consignes de sécurité ou de l'utilisation inappropriée des appareils ou autres installations.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - L’établissement n’est engagé que pendant les heures d’ouverture et seulement vis-à-vis des usagers respectant les règles énoncées dans le présent règlement.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Toute personne ne se conformant pas au présent règlement pourra se voir exclure de l’établissement à titre temporaire ou définitif, sans pour autant récupérer son droit d’entrée.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - La Direction et le personnel de l’établissement sont chargés de l’application du présent règlement et la réprimande de  tout manquement aux dispositions prises, sans préjudice des poursuites judiciaires qui pourraient être intentées contre les auteurs.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            " - Chaque membre est responsable de ses effets personnels. La salle de sport décline toute responsabilité en cas de perte ou de vol.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "10. Communication dans le cadre du groupe : Autorisation des données personnelles" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "10. Communication dans le cadre du groupe : Autorisation des données personnelles :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 10 dans la cellule 2
        $cell2->addText(
            "Le client consent expressément à ce que ses données personnelles fournies dans le cadre de ce contrat soient utilisées par le club de fitness pour des communications internes au groupe, y compris, mais sans s'y limiter, des annonces concernant les événements, les promotions, les programmes d'entraînement, les nouveaux services ou toute autre information pertinente liée aux activités du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "Le client comprend et accepte que ses données personnelles ne seront pas partagées avec des tiers à des fins commerciales sans son consentement préalable.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "11. Surveillance vidéo et respect de la vie privée" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "11. Surveillance vidéo et respect de la vie privée :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 11 dans la cellule 2
        $cell2->addText(
            "- Le centre respecte toutes les lois et réglementations en vigueur concernant la sécurité, la surveillance et la protection des données personnelles.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "- Des caméras de surveillance sont installées uniquement dans les espaces communs et à l’entrée pour assurer la sécurité des membres et du personnel.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "- Les enregistrements sont utilisés uniquement à des fins de sécurité et de gestion, conformément à la législation sur la protection des données.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "12. Événements et hébergement" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "12. Événements et hébergement :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 12 dans la cellule 2
        $cell2->addText(
            "Privilège Luxury Fitness Club se réserve le droit d’organiser des événements spéciaux, des ateliers, ou des activités promotionnelles au sein de ses installations, sans affecter le bon fonctionnement des activités habituelles du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "Les membres seront informés des événements à l'avance par le biais de communications appropriées.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "Le club ne pourra être tenu responsable des modifications ou annulations d'événements, pour des raisons de force majeure ou d'autres circonstances imprévues.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "Les membres sont encouragés à participer aux événements organisés, mais leur participation reste facultative.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "13. Résiliation à l'initiative du club" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "13. Résiliation à l'initiative du club :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 13 dans la cellule 2
        $cell2->addText(
            "L'abonnement est résilié de plein droit et sans remboursement par le club pour les motifs suivants :",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "- En cas de comportement inapproprié ou de manquement grave aux conditions et au règlement intérieur, ainsi que pour tout autre agissement ou délit sanctionné par les lois marocaines en vigueur.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "- En cas de fraude dans la constitution du dossier d'abonnement, fausse déclaration, falsification, ou utilisation frauduleuse de la carte d'accès du club.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        $cell2->addText(
            "- En cas de défaut de paiement, étant précisé qu'un incident de paiement donne lieu à la suspension de la carte d'abonnement du club en attendant la régularisation, sans que les mois durant le blocage ne soient récupérables. Le règlement s'effectuera auprès de notre comptoir de commercialisation. En cas d'autres incidents de paiement, l’adhérent sera redevable jusqu'au douzième mois et devra s'acquitter de sa dette auprès du club ou de la société de recouvrement avec la tarification en vigueur.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "14. Modalités de transfert / résiliation" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "14. Modalités de transfert / résiliation :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 14 dans la cellule 2
        $cell2->addText(
            "Le présent contrat ne peut être ni transféré ni résilié ni par l’adhérent.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "15. Litige et règlement de différends" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "15. Litige et règlement de différends :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 15 dans la cellule 2
        $cell2->addText(
            "Le présent contrat est régi par la législation marocaine en vigueur. En cas de litige relatif à l’exécution ou à l’interprétation du présent contrat, les parties s’efforceront de régler le différend à l’amiable. Si le problème persiste, les tribunaux de Casablanca seront seuls compétents.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Ajouter le titre "16. Signature et réception du contrat" dans la cellule 2 (en gras et souligné)
        $cell2->addText(
            "16. Signature et réception du contrat :",
            ['bold' => true, 'underline' => true, 'size' => 7], // Titre en gras et souligné, taille de police 7
            ['alignment' => 'left', 'spaceAfter' => 0] // Réduire l'espace après le titre
        );

        // Ajouter le texte de la section 16 dans la cellule 2
        $cell2->addText(
            "Le présent contrat est signé sur papier au sein du club. Le client recevra une copie du contrat pour sa propre documentation.",
            ['name' => 'Calisto MT', 'size' => 7],
            ['spaceAfter' => 0] // Réduire l'espace après chaque ligne
        );

        // Sanitize 'nom' and 'prenom' to remove unwanted characters (' / \)
        $nom = preg_replace("/[\/\\\\']/", '', $user['nom']);
        $prenom = preg_replace("/[\/\\\\']/", '', $user['prenom']);


        // Définir le chemin de sortie dans le dossier "adhérents"
        $outputDir = './adherents/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);  // Crée le dossier s'il n'existe pas
        }

        $outputPath = $outputDir . $nom . '_' . $prenom . '_contract.docx';
        $phpWord->save($outputPath, 'Word2007');

        // Générer le nom du fichier contract pour la base de données
        $contractName = "adherents/{$nom}_{$prenom}_contract.docx";

        // Mettre à jour le nom du contrat dans la table "users"
        $updateQuery = "UPDATE users SET contract_name = '$contractName' WHERE id = $id_user";
        mysqli_query($conn, $updateQuery);
    }

    // Return only the contract name to the client (no success message)
    echo $contractName . "<br>";
}
