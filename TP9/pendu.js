document.addEventListener('DOMContentLoaded', function() {
    const buttonsDiv = document.getElementById('buttons');
    const messageDiv = document.getElementById('message');
    const wordDiv = document.getElementById('word');
    const coupsSpan = document.getElementById('coups');
    const restartDiv = document.getElementById('restart');

    // Ajouter boutons A-Z
    for (let i = 65; i <= 90; i++) {
        const letter = String.fromCharCode(i).toLowerCase();
        const button = document.createElement('button');
        button.textContent = letter.toUpperCase();
        button.addEventListener('click', function() {
            verifierLettre(letter);
            this.disabled = true;
        });
        buttonsDiv.appendChild(button);
    }

    // Charger initialement le mot
    verifierLettre(''); // Appel initial pour afficher le mot

    function verifierLettre(lettre) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'verifier_lettre.php?lettre=' + lettre, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                messageDiv.textContent = response.message;
                wordDiv.textContent = response.motAffiche.split('').join(' ');
                coupsSpan.textContent = response.coups;
                if (response.fini) {
                    // Désactiver tous les boutons
                    const buttons = buttonsDiv.querySelectorAll('button');
                    buttons.forEach(btn => btn.disabled = true);
                    // Ajouter bouton rejouer
                    const restartBtn = document.createElement('button');
                    restartBtn.textContent = 'Rejouer';
                    restartBtn.addEventListener('click', function() {
                        // Reset session et reload
                        const xhr2 = new XMLHttpRequest();
                        xhr2.open('GET', 'reset.php', true);
                        xhr2.onreadystatechange = function() {
                            if (xhr2.readyState === 4 && xhr2.status === 200) {
                                window.location.reload();
                            }
                        };
                        xhr2.send();
                    });
                    restartDiv.appendChild(restartBtn);
                }
            }
        };
        xhr.send();
    }
});