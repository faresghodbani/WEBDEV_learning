// État du jeu
let score = 0;
let dino = {
    yPos: 0,
    ySpeed: 0,
    jump: false
};
let cactus = {
    xPos: 0,
    xSpeed: 20
};

// Références DOM
const gameDiv = document.getElementById('game');
const dinoDiv = document.getElementById('dino');
const cactusDiv = document.getElementById('cactus');
const scoreDiv = document.getElementById('score');

// Timers
let cactusInterval = null;
let dinoInterval = null;

// Double click tracking
let lastClickTime = 0;
const doubleClickThreshold = 300; // ms

/**
 * Initialise le jeu
 */
function startGame() {
    // Réinitialiser l'état
    score = 0;
    dino.yPos = 0;
    dino.ySpeed = 0;
    dino.jump = false;
    cactus.xPos = 0;
    cactus.xSpeed = 20;
    lastClickTime = 0;

    // Réinitialiser l'affichage
    updateDinoDisplay();
    updateCactusDisplay();
    updateScore();

    // Arrêter les anciens timers s'ils existent
    if (cactusInterval) clearInterval(cactusInterval);
    if (dinoInterval) clearInterval(dinoInterval);

    // Démarrer les timers
    cactusInterval = setInterval(cactusMove, 100);
    dinoInterval = setInterval(dinoMove, 100);

    // Associer les événements clavier et souris au saut
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('click', handleClick);
}

/**
 * Gère les touches clavier
 */
function handleKeyDown(event) {
    if (event.code === 'Space') {
        event.preventDefault();
        // Saut simple sur espace (50%)
        if (!dino.jump) {
            dino.jump = true;
            dino.ySpeed = 30;
        }
    }
}

/**
 * Gère les clics pour le double click
 */
function handleClick(event) {
    const currentTime = Date.now();
    
    if (currentTime - lastClickTime < doubleClickThreshold) {
        // Double click détecté
        if (!dino.jump) {
            dino.jump = true;
            dino.ySpeed = 50; // Saut plus haut (100%)
        }
    }
    
    lastClickTime = currentTime;
}

/**
 * Déplace le cactus vers la gauche
 */
function cactusMove() {
    cactus.xPos += cactus.xSpeed;

    // Si le cactus dépasse la largeur du jeu, le remettre à droite
    const gameWidth = gameDiv.offsetWidth;
    const cactusWidth = cactusDiv.offsetWidth;

    if (cactus.xPos > gameWidth + cactusWidth) {
        cactus.xPos = 0;
        score++;
        updateScore();
        
        // Augmenter la difficulté: augmenter la vitesse tous les 5 points
        if (score % 5 === 0) {
            cactus.xSpeed += 3;
        }
    }

    updateCactusDisplay();
    collisionTest();
}

/**
 * Met à jour l'affichage du cactus
 */
function updateCactusDisplay() {
    cactusDiv.style.right = cactus.xPos + 'px';
}

/**
 * Moteur physique du dino
 */
function dinoMove() {
    if (dino.jump) {
        // Déplacer verticalement
        dino.yPos += dino.ySpeed;

        // Décrémenter la vitesse (gravité)
        dino.ySpeed -= 5;

        // Vérifier si le dino est au sol
        if (dino.yPos <= 0) {
            dino.yPos = 0;
            dino.ySpeed = 0;
            dino.jump = false;
        }

        updateDinoDisplay();
    }
}

/**
 * Met à jour l'affichage du dino
 */
function updateDinoDisplay() {
    const gameHeight = gameDiv.offsetHeight;
    const bottom = (dino.yPos / gameHeight) * 100;

    dinoDiv.style.bottom = bottom + '%';
}

/**
 * Met à jour l'affichage du score
 */
function updateScore() {
    scoreDiv.textContent = 'Score : ' + score;
}

/**
 * Test de collision AABB
 */
function collisionTest() {
    const dinoRect = dinoDiv.getBoundingClientRect();
    const cactusRect = cactusDiv.getBoundingClientRect();

    if (dinoRect.right >= cactusRect.left &&
        dinoRect.left <= cactusRect.right &&
        dinoRect.bottom >= cactusRect.top &&
        dinoRect.top <= cactusRect.bottom) {
        gameOver();
    }
}

/**
 * Fin du jeu
 */
function gameOver() {
    // Arrêter les timers
    clearInterval(cactusInterval);
    clearInterval(dinoInterval);

    // Retirer les écouteurs
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('click', handleClick);

    // Demander à l'utilisateur s'il veut rejouer
    if (confirm('Game Over! Score: ' + score + '\nVeux-tu rejouer?')) {
        startGame();
    }
}

// Démarrer le jeu au chargement de la page
window.addEventListener('load', startGame);
