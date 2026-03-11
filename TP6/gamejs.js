/**
 * Gestionnaire SPA pour le jeu Marvel
 */
class GameSPA {
    constructor() {
        this.currentPage = 'home';
        this.currentPierres = this.loadData('pierres', 0);
        this.health = 100; // Points de vie
        this.gameGrid = null; // La grille mélangée
        this.revealedCases = new Set(); // Cases cliquées
        this.bunkerPierres = new Set(); // Pierres collectées dans le bunker
        this.secretCode = 0; // Code secret à deviner
        this.secretAttempts = 0; // Tentatives utilisées
        this.secretMaxAttempts = 5;
        this.initializeGame();
    }

    initializeGame() {
        this.setupEventListeners();
        this.showPage('home');
    }

    setupEventListeners() {
        // PAGE D'ACCUEIL - Bouton démarrer
        const startBtn = document.getElementById('start-game-btn');
        if (startBtn) {
            startBtn.addEventListener('click', () => {
                this.resetGame();
                this.showPage('game');
                this.generateRandomGrid();
                this.renderGameState();
            });
        }

        // PAGE JEU - Logo Marvel pour accéder au bunker
        const marvelLogoBtn = document.getElementById('marvel-logo-btn');
        if (marvelLogoBtn) {
            marvelLogoBtn.addEventListener('click', () => {
                const gamePage = document.getElementById('game-page');
                gamePage.classList.add('bunker-mode');
                this.showPage('bunker');
                this.renderBunker();
            });
        }

        // PAGE JEU - Clic sur les cases
        document.addEventListener('click', (e) => {
            if (e.target.closest('.game-case')) {
                const btn = e.target.closest('.game-case');
                const caseIndex = parseInt(btn.dataset.index);
                this.handleCaseClick(caseIndex);
            }
        });

        // PAGE JEU - Clic sur les pierres du bunker
        document.addEventListener('click', (e) => {
            if (e.target.closest('.stone')) {
                const stone = e.target.closest('.stone');
                const stoneIndex = parseInt(stone.dataset.index);
                this.collectStone(stoneIndex);
            }
        });

        // PAGE VICTOIRE - Bouton rejouer
        const replayBtn = document.getElementById('replay-btn');
        if (replayBtn) {
            replayBtn.addEventListener('click', () => {
                this.resetGame();
                this.showPage('game');
                this.generateRandomGrid();
                this.renderGameState();
            });
        }

        // PAGE DÉFAITE - Bouton recommencer
        const restartBtn = document.getElementById('restart-btn');
        if (restartBtn) {
            restartBtn.addEventListener('click', () => {
                this.resetGame();
                this.showPage('game');
                this.generateRandomGrid();
                this.renderGameState();
            });
        }

        // PAGE BUNKER - Bouton retour
        const backBtn = document.getElementById('back-to-game-btn');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                const gamePage = document.getElementById('game-page');
                gamePage.classList.remove('bunker-mode');
                this.showPage('game');
            });
        }

        // PAGE BUNKER - Bouton Anéantir le monde
        const annihilateBtn = document.getElementById('annihilate-btn');
        if (annihilateBtn) {
            annihilateBtn.addEventListener('click', () => {
                if (this.bunkerPierres.size === 6) {
                    this.showPage('thanos');
                }
            });
        }

        // PAGE THANOS - Bouton retour au menu
        const backToHomeBtn = document.getElementById('back-to-home-btn');
        if (backToHomeBtn) {
            backToHomeBtn.addEventListener('click', () => {
                this.resetGame();
                this.bunkerPierres = new Set();
                const gamePage = document.getElementById('game-page');
                gamePage.classList.remove('bunker-mode');
                this.showPage('home');
            });
        }

        // GANT FINALE - Clic sur l'image
        const gantImg = document.getElementById('gantImg');
        if (gantImg) {
            gantImg.addEventListener('click', () => {
                console.log('Gant cliqué!');
            });
        }

        // EASTER EGG - Clic sur "le Gant de Thanos" dans le texte d'accueil
        const easterEgg = document.getElementById('easter-egg-gant');
        if (easterEgg) {
            easterEgg.addEventListener('click', () => {
                this.showPage('victory');
            });
        }

        // CADENAS - Clic pour ouvrir le mini-jeu code secret
        const gantLock = document.getElementById('gant-lock');
        if (gantLock) {
            gantLock.style.cursor = 'pointer';
            gantLock.style.pointerEvents = 'all';
            gantLock.addEventListener('click', () => {
                this.openSecretCode();
            });
        }

        // CODE SECRET - Bouton Valider
        const codeSubmitBtn = document.getElementById('secret-code-submit');
        if (codeSubmitBtn) {
            codeSubmitBtn.addEventListener('click', () => {
                this.handleSecretCodeGuess();
            });
        }

        // CODE SECRET - Entrée pour valider
        const codeInput = document.getElementById('secret-code-input');
        if (codeInput) {
            codeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') this.handleSecretCodeGuess();
            });
        }

        // CODE SECRET - Bouton Fermer
        const codeCloseBtn = document.getElementById('secret-code-close');
        if (codeCloseBtn) {
            codeCloseBtn.addEventListener('click', () => {
                this.closeSecretCode();
            });
        }

        // PAGE AVENGERS - Bouton retour au menu
        const avengersBackBtn = document.getElementById('avengers-back-btn');
        if (avengersBackBtn) {
            avengersBackBtn.addEventListener('click', () => {
                this.resetGame();
                this.showPage('home');
            });
        }
    }

    resetGame() {
        this.currentPierres = 0;
        this.health = 100;
        this.revealedCases = new Set();
        this.gameGrid = null;
        this.bunkerPierres = new Set();
        this.secretCode = 0;
        this.secretAttempts = 0;
        this.saveData('pierres', 0);
    }

    // ============== MINI-JEU CODE SECRET ==============

    openSecretCode() {
        // Générer un code aléatoire entre 10 et 99
        this.secretCode = Math.floor(Math.random() * 90) + 10;
        this.secretAttempts = 0;
        console.log('Code secret (debug):', this.secretCode);

        // Réinitialiser l'interface
        const overlay = document.getElementById('secret-code-overlay');
        const hint = document.getElementById('secret-code-hint');
        const attempts = document.getElementById('secret-code-attempts');
        const input = document.getElementById('secret-code-input');

        if (hint) hint.textContent = '';
        if (attempts) attempts.textContent = `Tentatives : 0/${this.secretMaxAttempts}`;
        if (input) { input.value = ''; input.disabled = false; }

        const submitBtn = document.getElementById('secret-code-submit');
        if (submitBtn) submitBtn.disabled = false;

        if (overlay) overlay.classList.add('active');
        if (input) input.focus();
    }

    closeSecretCode() {
        const overlay = document.getElementById('secret-code-overlay');
        if (overlay) overlay.classList.remove('active');
    }

    handleSecretCodeGuess() {
        const input = document.getElementById('secret-code-input');
        const hint = document.getElementById('secret-code-hint');
        const attemptsEl = document.getElementById('secret-code-attempts');
        const guess = parseInt(input.value);

        if (isNaN(guess) || guess < 10 || guess > 99) {
            hint.textContent = '⚠️ Entrez un nombre entre 10 et 99';
            hint.className = 'secret-code-hint hint-warning';
            return;
        }

        this.secretAttempts++;
        attemptsEl.textContent = `Tentatives : ${this.secretAttempts}/${this.secretMaxAttempts}`;

        if (guess === this.secretCode) {
            // GAGNÉ !
            hint.textContent = '✅ CODE CORRECT ! Bienvenue chez les Avengers !';
            hint.className = 'secret-code-hint hint-success';
            input.disabled = true;
            document.getElementById('secret-code-submit').disabled = true;

            setTimeout(() => {
                this.closeSecretCode();
                this.showPage('avengers');
            }, 1500);
        } else if (this.secretAttempts >= this.secretMaxAttempts) {
            // 5 tentatives épuisées → PERDU
            hint.textContent = `❌ ÉCHEC ! Le code était ${this.secretCode}`;
            hint.className = 'secret-code-hint hint-fail';
            input.disabled = true;
            document.getElementById('secret-code-submit').disabled = true;
        } else if (guess < this.secretCode) {
            hint.textContent = '⬆️ Le code est SUPÉRIEUR';
            hint.className = 'secret-code-hint hint-higher';
            input.value = '';
            input.focus();
        } else {
            hint.textContent = '⬇️ Le code est INFÉRIEUR';
            hint.className = 'secret-code-hint hint-lower';
            input.value = '';
            input.focus();
        }
    }

    generateRandomGrid() {
        // Créer un tableau avec 6 pierres et 2 ennemis
        const grid = [];

        // Ajouter les 6 pierres
        for (let i = 1; i <= 6; i++) {
            grid.push({ type: 'pierre', numero: i });
        }

        // Ajouter les 2 ennemis
        grid.push({ type: 'lose-avengers' });
        grid.push({ type: 'lose-gardiens' });

        // Mélanger le tableau (Fisher-Yates shuffle)
        for (let i = grid.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [grid[i], grid[j]] = [grid[j], grid[i]];
        }

        this.gameGrid = grid;
    }

    handleCaseClick(caseIndex) {
        if (this.revealedCases.has(caseIndex)) return; // Case déjà cliquée

        const caseData = this.gameGrid[caseIndex];
        this.revealedCases.add(caseIndex);

        if (caseData.type === 'pierre') {
            this.currentPierres++;
            this.saveData('pierres', this.currentPierres);
            this.renderGameState();

            if (this.currentPierres === 6) {
                this.showPage('victory');
                console.log('🎉 VICTOIRE !');
            }
        } else if (caseData.type === 'lose-avengers' || caseData.type === 'lose-gardiens') {
            // Retirer 50 HP
            this.health -= 50;
            this.renderGameState();

            // Animation de dégât sur la barre de vie
            const hud = document.getElementById('game-hud');
            if (hud) {
                hud.classList.add('hud-damage');
                setTimeout(() => hud.classList.remove('hud-damage'), 600);
            }

            if (this.health <= 0) {
                // Game over après un court délai pour voir l'animation
                setTimeout(() => {
                    const enemy = caseData.type === 'lose-avengers' ? 'AVENGERS' : 'GARDIENS';
                    this.showLossPage(enemy);
                }, 700);
            }
        }
    }

    renderGameState() {
        const grid = document.getElementById('game-grid');
        const title = document.getElementById('game-title');
        grid.innerHTML = '';

        // Mettre à jour le titre
        title.textContent = `Pierre ${this.currentPierres}/6 trouvées`;

        // Mettre à jour le HUD
        this.renderHUD();

        // Afficher les 8 cases
        for (let i = 0; i < 8; i++) {
            const caseData = this.gameGrid[i];
            const isRevealed = this.revealedCases.has(i);

            const caseDiv = document.createElement('div');
            caseDiv.className = 'case';
            caseDiv.dataset.index = i;

            if (isRevealed) {
                // Case cliquée - afficher le contenu
                if (caseData.type === 'pierre') {
                    caseDiv.className = 'case revealed game-case';
                    caseDiv.innerHTML = `<img src="pierre${caseData.numero}.jpeg" alt="Pierre ${caseData.numero}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">`;
                } else if (caseData.type === 'lose-avengers') {
                    caseDiv.className = 'case revealed-enemy game-case';
                    caseDiv.innerHTML = '<img src="spiderman.jpg" alt="Spider-Man" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">';
                } else if (caseData.type === 'lose-gardiens') {
                    caseDiv.className = 'case revealed-enemy game-case';
                    caseDiv.innerHTML = '<img src="groot.webp" alt="Groot" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">';
                }
            } else {
                // Case non cliquée - afficher un ?
                caseDiv.className = 'case game-case';
                caseDiv.innerHTML = '<span>?</span>';
            }

            grid.appendChild(caseDiv);
        }

        // Mettre à jour l'état du gant
        const gantLock = document.getElementById('gant-lock');
        if (this.currentPierres === 6) {
            gantLock.style.display = 'none';
        } else {
            gantLock.style.display = 'block';
        }
    }

    renderHUD() {
        // Barre de vie
        const healthBar = document.getElementById('health-bar-fill');
        const healthText = document.getElementById('health-text');
        if (healthBar) {
            const pct = Math.max(0, this.health);
            healthBar.style.width = `${pct}%`;
            // Couleur dynamique : vert → orange → rouge
            if (pct > 50) {
                healthBar.style.background = 'linear-gradient(90deg, #00ff00, #44ff44)';
            } else if (pct > 25) {
                healthBar.style.background = 'linear-gradient(90deg, #ff8800, #ffaa00)';
            } else {
                healthBar.style.background = 'linear-gradient(90deg, #ff0000, #ff4444)';
            }
        }
        if (healthText) {
            healthText.textContent = Math.max(0, this.health);
        }

        // Compteur de pierres
        const stonesCount = document.getElementById('hud-stones-count');
        if (stonesCount) {
            stonesCount.textContent = `${this.currentPierres}/6`;
        }
    }

    renderBunker() {
        const container = document.getElementById('stones-container');
        container.innerHTML = '';

        for (let i = 1; i <= 6; i++) {
            const stone = document.createElement('div');
            stone.className = `stone stone-${i}`;
            stone.dataset.index = i;

            // Afficher l'image de la pierre
            const img = document.createElement('img');
            img.src = `pierre${i}.jpeg`;
            img.alt = `Pierre ${i}`;
            img.className = 'stone-image';

            stone.appendChild(img);

            if (this.bunkerPierres.has(i)) {
                stone.classList.add('collected');
            }

            container.appendChild(stone);
        }

        this.updateAnnihilateButton();
    }

    collectStone(stoneIndex) {
        if (!this.bunkerPierres.has(stoneIndex)) {
            this.bunkerPierres.add(stoneIndex);
            this.renderBunker();

            const countSpan = document.getElementById('bunker-stones-count');
            if (countSpan) {
                countSpan.textContent = this.bunkerPierres.size;
            }
        }
    }

    updateAnnihilateButton() {
        const btn = document.getElementById('annihilate-btn');
        if (btn) {
            if (this.bunkerPierres.size === 6) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }
    }

    showLossPage(enemy) {
        this.showPage('loss');
        const lossTitle = document.getElementById('loss-title');
        const restartBtn = document.getElementById('restart-btn');
        const lossPage = document.getElementById('loss-page');

        if (enemy === 'AVENGERS') {
            lossTitle.innerHTML = '💀 LES AVENGERS T\'ONT TROUVÉ 💀<br>MISSION ÉCHOUÉE';
            lossPage.style.background = 'url("https://images.wallpapersden.com/image/download/marvel-s-avengers-assemble-comic_bGdoamyUmZqaraWkpJRnamtlrWZpaWU.jpg") no-repeat center center';
            lossPage.style.backgroundSize = 'cover';
            lossPage.style.backgroundAttachment = 'fixed';
            lossPage.style.backgroundColor = 'black';
            restartBtn.className = 'btn-restart avengers';
        } else {
            lossTitle.innerHTML = '🚀 LES GARDIENS DE LA GALAXIE T\'ONT ARRÊTÉ 🚀<br>MISSION ÉCHOUÉE';
            lossPage.style.background = 'url("https://leclaireur.fnac.com/wp-content/uploads/2022/10/gotg-header.jpg") no-repeat center center';
            lossPage.style.backgroundSize = 'cover';
            lossPage.style.backgroundAttachment = 'fixed';
            lossPage.style.backgroundColor = 'black';
            restartBtn.className = 'btn-restart gardiens';
        }
    }

    showPage(pageName) {
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });

        const pageElement = document.getElementById(`${pageName}-page`);
        if (pageElement) {
            pageElement.classList.add('active');
            this.currentPage = pageName;
        }
    }

    loadData(key, defaultValue) {
        try {
            const stored = localStorage.getItem(`marvelGame_${key}`);
            return stored ? JSON.parse(stored) : defaultValue;
        } catch (error) {
            console.error(`Erreur chargement ${key}`, error);
            return defaultValue;
        }
    }

    saveData(key, value) {
        try {
            localStorage.setItem(`marvelGame_${key}`, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error(`Erreur sauvegarde ${key}`, error);
            return false;
        }
    }
}

// Initialiser le jeu au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    const game = new GameSPA();
});
