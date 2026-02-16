function validateWithTest() {
            const phonePattern = /^(?:\+261|0)[3-9][0-9]{8}$/;
            const phoneInput = document.getElementById("phone").value;
            const resultText = document.getElementById("resultTest");

            if (phonePattern.test(phoneInput)) {
                resultText.textContent = "Numéro valide !";
                resultText.className = 'valid';
            } else {
                resultText.textContent = "Numéro invalide, veuillez réessayer.";
                resultText.className = 'invalid';
            }
        }

        // Validation personnalisée sans utiliser test()
        function customValidatePhone(phoneInput) {
            // Vérifier le format de base du numéro
            if (phoneInput.length !== 13 && phoneInput.length !== 10) {
                return "Numéro invalide, la longueur ne correspond pas.";
            }

            // Vérifier le cas où le numéro commence par +261
            if (phoneInput.startsWith("+261")) {
                // Vérifier que le cinquième caractère est un chiffre entre 3 et 9
                if (parseInt(phoneInput.charAt(4)) >= 3 && parseInt(phoneInput.charAt(4)) <= 9) {
                    // Vérifier que les 9 caractères qui suivent sont des chiffres
                    for (let i = 5; i < phoneInput.length; i++) {
                        if (isNaN(phoneInput.charAt(i))) {
                            return "Numéro invalide, tous les caractères après +261 doivent être des chiffres.";
                        }
                    }
                    return "Numéro valide !";
                } else {
                    return "Numéro invalide, le premier chiffre après +261 doit être entre 3 et 9.";
                }
            }

            // Vérifier le cas où le numéro commence par 0
            if (phoneInput.startsWith("0")) {
                // Vérifier que le deuxième caractère est un chiffre entre 3 et 9
                if (parseInt(phoneInput.charAt(1)) >= 3 && parseInt(phoneInput.charAt(1)) <= 9) {
                    // Vérifier que les 8 caractères qui suivent sont des chiffres
                    for (let i = 2; i < phoneInput.length; i++) {
                        if (isNaN(phoneInput.charAt(i))) {
                            return "Numéro invalide, tous les caractères après le 0 doivent être des chiffres.";
                        }
                    }
                    return "Numéro valide !";
                } else {
                    return "Numéro invalide, le premier chiffre après 0 doit être entre 3 et 9.";
                }
            }

            return "Numéro invalide, il doit commencer par +261 ou 0.";
        }

        function validateWithCustom() {
            const phoneInput = document.getElementById("phone").value;
            const resultText = document.getElementById("resultCustom");
            const validationResult = customValidatePhone(phoneInput);
            resultText.textContent = validationResult;
            resultText.className = (validationResult.includes("valide") ? 'valid' : 'invalid');
        }
