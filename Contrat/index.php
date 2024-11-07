<?php
require '../vendor/autoload.php'; // Include the PHPWord library

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Style\Paragraph;

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



// Create a table that spans the full width with invisible borders
$table = $section->addTable([
    'borderColor' => 'ffffff', // Invisible borders
    'borderSize' => 0,
    'cellMargin' => 150, // Margin between cells
]);

// Set the width of the table to 100% (relative to page width)
$table->setWidth(100 * 50); // Percentage width (100%) for full width on the page

// Add a row for the titles
$table->addRow();

// Add the title "ENTREE" to the left cell, setting a wider width to span half the table width
$table->addCell(5000)->addText('ENTREE              ', [ // Adjust cell width as needed
    'name' => 'Arial',
    'size' => 12,
    'bold' => true,
    'underline' => 'single' // Underline the text
], [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
]);

// Add the title "ET Mme/Mr" to the right cell, setting a wider width to span half the table width
$table->addCell(5000)->addText('ET Mme/Mr           ', [ // Adjust cell width as needed
    'name' => 'Arial',
    'size' => 12,
    'bold' => true,
    'underline' => 'single' // Underline the text
], [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
]);
// Add a new row for additional information
$table->addRow();

// Add the left cell with a bordered frame for the address
$leftCell = $table->addCell(5000, [
    'borderSize' => 1,
    'borderColor' => '000000',
    'valign' => 'top', // Align content to the top of the cell
]);

// Add the address text inside the left cell
$leftCell->addText("PRIVILEGE LUXURY FITNESS CLUB\n111 BOULEVARD MODIBO KEITA\nCASABLANCA\nMAROC", [
    'name' => 'Times New Roman',
    'size' => 8,
], [
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
]);
// Add the right cell with a nested mini table for user details
$rightCell = $table->addCell(5000, [
    'valign' => 'top', // Align content to the top of the cell
]);

// Create a nested table within the right cell for the user information
$miniTable = $rightCell->addTable(['borderSize' => 0, 'cellMargin' => 20, 'borderColor' => 'ffffff']);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Nom et Prénom", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("Raifa Youssef", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Né le", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("10/07/1998 a: Casablanca", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Adresse, Ville", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("Mimouna 2 Rue 41 N° 39, Casablanca", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("CIN", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("BL151132", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Tél portable, Tél fixe", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("+212688808238, +212522342244", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Profession, Société", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("Développeur Web, CAPITALSOFT", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("E-mail", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("Yucefr@gmail.com", [
    'name' => 'Arial',
    'size' => 8,
]);

$miniTable->addRow();
$miniTable->addCell(4000)->addText("Personne à contacter en cas d'urgence", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$miniTable->addCell(4000)->addText("+212666835011", [
    'name' => 'Arial',
    'size' => 8,
]);

$section->addTextBreak(1);
// Ajouter une ligne horizontale après le tableau
$section->addLine([
    'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(16), // Largeur de la ligne (ajustez selon vos besoins)
    'height' => 1, // Épaisseur de la ligne
    'color' => '000000', // Couleur de la ligne en hexadécimal (ici, noir)
    'align' => 'center', // Alignement de la ligne (centre)
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

// Ajouter une ligne pour les boutons radio
$table->addRow();

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
    'cellMargin' => 150, // Espacement interne des cellules
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

// Ajouter un espace après la table si nécessaire
$section->addTextBreak(1);


// Créer la table principale avec deux colonnes
$mainTable = $section->addTable([
    'borderColor' => 'ffffff',
    'borderSize' => 0,
    'cellMargin' => 150,
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
    'cellMargin' => 100,
]);

// Ajouter l'entête des colonnes
$paymentTable->addRow();
$paymentTable->addCell(3000)->addText("Mode", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$paymentTable->addCell(1500)->addText("Montant", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
]);
$paymentTable->addCell(3000)->addText("N° Pièce", [
    'name' => 'Arial',
    'size' => 8,
    'bold' => true,
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


// Colonne de droite : Observations avec un cadre de texte simple
$rightCell = $mainTable->addCell(5000);

// Ajouter un cadre pour "Observations"
$rightCell->addText("Pour tout règlement, prière d'exiger un reçu. Ce dernier pourra être demandé par la direction à tout moment en cas de vérification de l'adhésion:", [
    'name' => 'Arial',
    'size' => 8,
]);

// Ajouter le cadre pour les observations
$rightCell->addTextBreak(1);
$rightCell->addTextBox([
    'borderColor' => '000000',
    'borderSize' => 1,
    'width' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3), // Ajuster la largeur pour un cadre plus petit
    'height' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1), // Ajuster la hauteur pour un cadre plus petit
])->addText("Observations :", ['name' => 'Arial', 'size' => 8]);





// Save the document
$outputPath = './contrat_adhesion.docx';
$phpWord->save($outputPath, 'Word2007');

echo "Le contrat a été créé avec succès à l'emplacement : $outputPath";
