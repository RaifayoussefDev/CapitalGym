<?php
require '../vendor/autoload.php'; // Include the PHPWord library

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;


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

$section->addText("Mme / Mr : Nom / Prénom, né le ../../…., demeurant à ……(Adresse) … (ville), titulaire de la CIN n ………….. GSM : ………   Profession : ……………..   Employeur : ……………….. E-mail : ……………………… Personne à contacter en cas d’urgence : ……………", [
    'name' => 'Times New Roman',
    'size' => 8,
]);



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

// Ajouter le bouton radio "Individuel"
$table->addCell(500)->addText('O', ['name' => 'Arial', 'size' => 8]); // Simule le bouton radio
$table->addCell(2000)->addText(' Individuel', ['name' => 'Arial', 'size' => 8]); // Texte

// Ajouter le bouton radio "Famille"
$table->addCell(500)->addText('O', ['name' => 'Arial', 'size' => 8]); // Simule le bouton radio
$table->addCell(2000)->addText(' Famille', ['name' => 'Arial', 'size' => 8]); // Texte

// Ajouter le bouton radio "Groupe"
$table->addCell(500)->addText('O', ['name' => 'Arial', 'size' => 8]); // Simule le bouton radio
$table->addCell(2000)->addText(' Groupe', ['name' => 'Arial', 'size' => 8]); // Texte

// Ajouter le bouton radio "Convention"
$table->addCell(500)->addText('O', ['name' => 'Arial', 'size' => 8]); // Simule le bouton radio
$table->addCell(2000)->addText(' Convention', ['name' => 'Arial', 'size' => 8]); // Texte


// Ajouter une nouvelle table pour les informations de l'abonnement
$table = $section->addTable([
    'borderColor' => 'ffffff', // Invisible borders
    'borderSize' => 0, // Invisible borders
]);

// Ajouter une ligne pour le type d'abonnement "SILVER"
$table->addRow();
$table->addCell(2000)->addText('SILVER :', ['name' => 'Arial', 'size' => 8, 'bold' => true]); // Étiquette SILVER

// Ajouter des cellules pour "Du", "Au", "Soit"
$table->addCell(3000)->addText('Du :   ……………', ['name' => 'Arial', 'size' => 8]); // Du
$table->addCell(3000)->addText('Au :   ……………', ['name' => 'Arial', 'size' => 8]); // Au
$table->addCell(3000)->addText('Soit :   …………… DH TTC', ['name' => 'Arial', 'size' => 8]); // Soit

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
$table->addCell(3000)->addText("DE : …………… DH TTC", [
    'name' => 'Arial',
    'size' => 8,
], [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT, // Aligné à droite
]);


// Ajouter une nouvelle ligne avec première cellule vide et texte dans la deuxième cellule
$table->addRow();
$table->addCell(7000); // Cellule vide à gauche
$table->addCell(4000)->addText("TOTAL A REGLER : .........DH TTC", [
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
$leftCell->addText("         ⃝ Chèque       ⃝ Espèce       ⃝ Carte        ⃝ Rib", [
    'name' => 'Arial',
    'size' => 8,
]);

// Créer une sous-table pour les détails du mode de paiement
$paymentTable = $leftCell->addTable([
    'borderColor' => '000000',
    'borderSize' => 1,
]);

// Ajouter l'entête des colonnes
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

// Ligne pour le paiement en espèces
$paymentTable->addRow();
$paymentTable->addCell(3000)->addText("Esp", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(1500)->addText("…………DH", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(3000)->addText("………………………", ['name' => 'Arial', 'size' => 8]);

// Ligne pour le paiement par chèque
$paymentTable->addRow();
$paymentTable->addCell(3000)->addText("Chèque", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(1500)->addText("…………DH", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(3000)->addText("………………………", ['name' => 'Arial', 'size' => 8]);

// Ligne pour le paiement par carte
$paymentTable->addRow();
$paymentTable->addCell(3000)->addText("Carte", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(1500)->addText("…………DH", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(3000)->addText("………………………", ['name' => 'Arial', 'size' => 8]);
// Ligne pour le paiement par virement
// Ligne pour le paiement par virement avec une bordure en bas
$paymentTable->addRow();
$paymentTable->addCell(3000, ['borderBottomSize' => 6, 'borderBottomColor' => '000000'])->addText("Virement", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(1500, ['borderBottomSize' => 6, 'borderBottomColor' => '000000'])->addText("…………DH", ['name' => 'Arial', 'size' => 8]);
$paymentTable->addCell(3000, ['borderBottomSize' => 6, 'borderBottomColor' => '000000'])->addText("………………………", ['name' => 'Arial', 'size' => 8]);


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

// Ajouter le titre "Observation" en haut à gauche du cadre
$observationTable->addRow();
$observationTable->addCell(5000)->addText("Observation", [
    'name' => 'Arial',
    'size' => 10,
    'bold' => true,
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



// Save the document
$outputPath = './contrat_adhesion.docx';
$phpWord->save($outputPath, 'Word2007');

echo "Le contrat a été créé avec succès à l'emplacement : $outputPath";
