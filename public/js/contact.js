// path: public/js/contact.js
// validation form coté client
// améliore exp mais ne remplace pas validation serveur

const contactForm = document.querySelector(".contact-form");

if (contactForm) {
    // règle ContactMessageMapping
    const rules = {
        name: { min: 2, max: 100, message: "Le nom doit faire entre 2 et 100 caractères." },
        email: { min: 1, max: 254, message: "Merci d'indiquer une adresse e-mail valide." },
        subject: { min: 3, max: 120, message: "Le sujet doit faire entre 3 et 120 caractères." },
        message: { min: 10, max: 2000, message: "Le message doit faire entre 10 et 2000 caractères." },
    };
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const fields = Object.keys(rules).map((name) => contactForm.elements[name]);
    const messageField = contactForm.elements.message;
    const counter = contactForm.querySelector(".contact-counter");
    const submitButton = contactForm.querySelector(".contact-submit");

    // Js remplace bulle nav par message perso + sans js validation html5 et serveur restent actif
    contactForm.noValidate = true;

    // Crée 1 seul fois p sous champs
    const getErrorElement = (field) => {
        let error = field.parentElement.querySelector(".contact-error");

        if (!error) {
            error = document.createElement("p");
            error.className = "contact-error";
            error.id = field.id + "Error";
            error.setAttribute("aria-live", "polite");
            field.parentElement.appendChild(error);
        }

        return error;
    };

    // verif champ + affiche ou efface message + true si valide
    const validateField = (field) => {
        const rule = rules[field.name];
        const value = field.value.trim();
        let isValid = value.length >= rule.min && value.length <= rule.max;

        if (field.name === "email") {
            isValid = isValid && emailPattern.test(value);
        }

        const error = getErrorElement(field);
        error.textContent = isValid ? "" : rule.message;
        field.setAttribute("aria-invalid", isValid ? "false" : "true");
        field.setAttribute("aria-describedby", error.id);

        return isValid;
    };

    const updateCounter = () => {
        if (counter) {
            counter.textContent = messageField.value.length + "/2000 caractères";
        }
    };

    fields.forEach((field) => {
        // verif quand on quitte le champ, puis en direct une fois qu'une erreur est affichée
        field.addEventListener("blur", () => validateField(field));
        field.addEventListener("input", () => {
            if (field.getAttribute("aria-invalid") === "true") {
                validateField(field);
            }
        });
    });

    messageField.addEventListener("input", updateCounter);
    updateCounter();

    contactForm.addEventListener("submit", (event) => {
        const results = fields.map(validateField);

        if (results.includes(false)) {
            event.preventDefault();
            fields[results.indexOf(false)].focus();
            return;
        }

        // empêche doublon + chargement
        submitButton.disabled = true;
        submitButton.textContent = "Envoi en cours...";
    });
}