// Ce fichier peut être utilisé pour ajouter des interactions JavaScript futures 
// Par exemple, des validations de formulaire côté client, des effets visuels, etc.
// Pour l'instant, il est vide, mais sa présence est importante pour la structure.

document.addEventListener('DOMContentLoaded', function() {
    // Exemple simple : masquer les messages d'alerte après quelques secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => alert.remove(), 500);
        }, 5000); // 5 secondes
    });
});
