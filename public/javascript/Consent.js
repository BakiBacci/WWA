// cookieConsent.js

document.addEventListener('DOMContentLoaded', function() {
    // Vérifie si le consentement a déjà été donné
    if (!localStorage.getItem('cookieConsent')) {
        showConsentBanner();
    }

    function showConsentBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookie-consent-banner';
        banner.innerHTML = `
            <p>Nous utilisons des cookies pour améliorer votre expérience. 
            <a href="/politique-de-cookies" target="_blank">En savoir plus</a></p>
            <button id="accept-all">Accepter tout</button>
            <button id="reject-all">Tout refuser</button>
            <button id="customize">Personnaliser</button>
        `;
        document.body.appendChild(banner);

        // Événements pour les boutons
        document.getElementById('accept-all').onclick = function() {
            setConsent('all');
        };
        
        document.getElementById('reject-all').onclick = function() {
            setConsent('none');
        };

        document.getElementById('customize').onclick = function() {
            // Logique pour personnaliser les choix (à développer)
            alert("Personnalisation des choix de cookies à implémenter.");
        };
    }

    function setConsent(choice) {
        localStorage.setItem('cookieConsent', choice);
        document.getElementById('cookie-consent-banner').remove();
        
        // Implémentation de la logique pour activer ou désactiver les cookies selon le choix
        if (choice === 'all') {
            // Activer tous les cookies
            console.log("Tous les cookies sont activés.");
        } else {
            // Désactiver tous les cookies
            console.log("Tous les cookies sont désactivés.");
        }
        
        // Enregistrer la date du consentement
        const consentDate = new Date();
        localStorage.setItem('consentDate', consentDate.toISOString());
    }

    // Vérifie si le consentement est toujours valide (13 mois)
    const consentDate = localStorage.getItem('consentDate');
    if (consentDate) {
        const date = new Date(consentDate);
        const now = new Date();
        const diffMonths = (now - date) / (1000 * 60 * 60 * 24 * 30);
        
        if (diffMonths > 13) {
            localStorage.removeItem('cookieConsent');
            localStorage.removeItem('consentDate');
            showConsentBanner(); // Afficher à nouveau le bandeau après 13 mois
        }
    }
});