// path: public/js/ajax.js 
// envoi form en json sans rechargement 

// Envoi form au back avec en-tete accept : application/json
// bouton desactivé pdt l'envoi
const sendJsonForm = async (form) =>{
    const submitButton = form.querySelector('[type="submit"]');
    const label = submitButton?.querySelector("span") ?? submitButton;
    const originalText = label?.textContent;

    if (submitButton){
        submitButton.disabled = true;
        label.textContent = "Envoi en cours..."
    }

    try{
        const response = await fetch(form.action,{
            method: "POST",
            headers: { Accept: "application/json"},
            body: new FormData(form),
        });

        return await response.json();
    } catch (error){
        // reseau coupé ou reponse non json : msg neutre 
        return {
            success: false, 
            message: "Le serveur ne réponds pas. Vérifier votre connexion puis réessayer.",
            data: null,
        };
    } finally {
        if (submitButton){
            submitButton.disabled = false;
            label.textContent = originalText;
        }
    }
};