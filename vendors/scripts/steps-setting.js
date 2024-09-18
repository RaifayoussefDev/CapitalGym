
let valid = true;

$(".tab-wizard").steps({
    headerTag: "h5",
    bodyTag: "section",
    transitionEffect: "fade",
    titleTemplate: '<span class="step">#index#</span> #title#',
    labels: {
        finish: "Valider",
        next: "Suivant",
        previous: "Précédent"
    },
    enablePagination: true,
    onStepChanging: function (event, currentIndex, newIndex) {

        
        // Always allow navigation to previous steps
        if (newIndex < currentIndex) {
            return true;
        }


        // Validate Step 1: Information Personnel
        if (currentIndex === 0) { // Step 1
            if (!$('#cin').val().trim()) {
                $('#cin').addClass('is-invalid');
                valid = false;
            } else {
                $('#cin').removeClass('is-invalid');
            }
            if (!$('#nom').val().trim()) {
                $('#nom').addClass('is-invalid');
                valid = false;
            } else {
                $('#nom').removeClass('is-invalid');
            }
            if (!$('#prenom').val().trim()) {
                $('#prenom').addClass('is-invalid');
                valid = false;
            } else {
                $('#prenom').removeClass('is-invalid');
            }
            if (!$('#email').val().trim()) {
                $('#email').addClass('is-invalid');
                valid = false;
            } else {
                $('#email').removeClass('is-invalid');
            }
            if (!$('#phone').val().trim()) {
                $('#phone').addClass('is-invalid');
                valid = false;
            } else {
                $('#phone').removeClass('is-invalid');
            }
            if (valid) {
                // Si la validation est réussie, envoyer les données via AJAX
                $.ajax({
                    url: 'save_step1.php', // Script PHP pour enregistrer les données
                    type: 'POST',
                    data: {
                        cin: $('#cin').val(),
                        nom: $('#nom').val(),
                        prenom: $('#prenom').val(),
                        email: $('#email').val(),
                        phone: $('#phone').val()
                    },
                    success: function(response) {
                        // Gérer la réponse (optionnel)
                        // console.log("Données enregistrées : ", response);
                    },
                    error: function(error) {
                        console.log("Erreur lors de l'enregistrement des données : ", error);
                        valid = false;
                    }
                });
            }
        }

        // Validate Step 2: Abonnement
        if (currentIndex === 1) { // Step 2
            if (!$('#type_abonnement').val().trim()) {
                $('#type_abonnement').addClass('is-invalid');
                valid = false;
            } else {
                $('#type_abonnement').removeClass('is-invalid');
                valid = true;

            }

            let activitySelected = false;
            $('input[name="activites[]"]').each(function() {
                if (this.checked) {
                    activitySelected = true;
                    return false;
                }
            });
            if (!activitySelected) {
                $('.selectgroup-pills').addClass('is-invalid');
                valid = false;
            } else {
                $('.selectgroup-pills').removeClass('is-invalid');
            }
        }

        // Validate Step 3: Autres informations
        if (currentIndex === 2) { 
            valid=true
        }

        // Validate Step 4: Payement
        if (currentIndex === 3) { // Step 4
            valid=true
        }

        return valid;
    },
    onStepChanged: function (event, currentIndex, priorIndex) {
        // Always add the 'disabled' class to previous steps
        $('.steps .current').prevAll().addClass('disabled');
    },
    onFinished: function (event, currentIndex) {
        // Submit the form when finished
        $('#example-form').submit(); // Submit the form with id 'example-form'
    }
});

function validateField(input) {
    const field = input.name; // Get the name of the field
    const value = input.value; // Get the value of the field
    const errorMessage = document.getElementById(`${field}-error`);

    return fetch(`validate_field.php?field=${field}&value=${value}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                // Show the error message and change the text to "already exists"
                errorMessage.style.display = 'block';
                errorMessage.textContent = `${field.toUpperCase()} existe déjà !`;
                valid=false
                return false;
            }
            // Hide the error message if the value is valid
            errorMessage.style.display = 'none';
            valid=true
            return true;
        })
        .catch(error => console.error('Error:', error));
}
