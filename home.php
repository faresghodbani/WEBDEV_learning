<?php
session_start();

// sécurité
if (empty($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$file = "data.json";

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
} else {
    $data = [];
}

if (!$data) {
    $data = [];
}

$online_users = $data["online_users"] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>WhoRelate - Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<h1>WhoRelate</h1>

<form action="logout.php" method="POST" class="logout-form">
    <button class="btn">Déconnexion</button>
</form>

<div class="player">
    <?= $_SESSION["avatar"] ?> <?= htmlspecialchars($_SESSION["username"]) ?>
</div>

<div class="main-layout">

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <h2>Utilisateurs connectés</h2>

        <div id="online-users">
            <?php if (!empty($online_users)): ?>
                <?php foreach ($online_users as $user): ?>
                    <div class="online-user">
                        <div class="online-avatar"><?= $user["avatar"] ?></div>
                        <div class="online-name">
                            <?= htmlspecialchars($user["username"]) ?>
                            <?= ($user["username"] === $_SESSION["username"]) ? "(Moi)" : "" ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-text">Aucun utilisateur connecté</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        <div class="panel-box">
            <div class="panel-title">🎮 Parties</div>

            <div class="create-room-card">

                <!-- CREATE -->
                <div class="create-room-subtitle">Créer une partie</div>

                <form action="server.php" method="POST">
                    <input type="hidden" name="action" value="create">

                    <div class="room-type-row">
                        <div class="room-type-toggle">

                            <input type="radio" name="room_type" id="create_public" value="public" checked>
                            <input type="radio" name="room_type" id="create_private" value="private">

                            <label for="create_public" class="room-type-btn public">🌍 Publique</label>
                            <label for="create_private" class="room-type-btn private">🔒 Privée</label>

                        </div>
                    </div>

                    <button class="create-btn" type="submit">✨ Créer</button>
                </form>

                <!-- JOIN -->
                <div class="join-divider"></div>

                <div class="create-room-subtitle">Rejoindre une partie</div>

                <form action="server.php" method="POST" class="join-form">
                    <input type="hidden" name="action" value="join">

                    <input type="text" name="room_id" placeholder="Code de la room" required class="join-input">

                    <button class="join-btn">Rejoindre</button>
                </form>

            </div>
        </div>

        <!-- ROOMS -->
        <div class="panel-box bottom-placeholder">
            <div class="panel-title">🎯 Parties Disponibles</div>
            <div id="rooms-container"></div>
        </div>

    </div>
</div>

<?php if (!empty($_SESSION["error"])): ?>
    <p class="error"><?= $_SESSION["error"] ?></p>
    <?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<!-- 🔥 SCRIPT UNIQUE TEMPS RÉEL -->
<script>
setInterval(() => {
    fetch("data.json")
        .then(res => res.json())
        .then(data => {

            /* -------- USERS -------- */
            const users = data.online_users || [];
            let usersHTML = "";

            users.forEach(user => {
                usersHTML += `
                    <div class="online-user">
                        <div class="online-avatar">${user.avatar}</div>
                        <div class="online-name">
                            ${user.username}
                            ${user.username === "<?= $_SESSION["username"] ?>" ? "(Moi)" : ""}
                        </div>
                    </div>
                `;
            });

            if (users.length === 0) {
                usersHTML = `<p class="empty-text">Aucun utilisateur connecté</p>`;
            }

            document.getElementById("online-users").innerHTML = usersHTML;


            /* -------- ROOMS -------- */
            let roomsHTML = "";

            for (const id in data) {

                const room = data[id];

                if (!room.players) continue;
                if ((room.type ?? "public") !== "public") continue;

                const isFull = room.players.length >= 5;
                const isStarted = (room.state ?? "waiting") !== "waiting";

                roomsHTML += `
                    <div class="room-item">
                        <div class="room-info">
                            🎮 Room ${id}<br>
                            👥 ${room.players.length} / 5 joueurs
                        </div>

                        ${
                            isStarted
                            ? `<span style="opacity:0.6;">En cours</span>`
                            : isFull
                                ? `<span style="opacity:0.6;">Complet</span>`
                                : `
                                    <form action="server.php" method="POST">
                                        <input type="hidden" name="action" value="join">
                                        <input type="hidden" name="room_id" value="${id}">
                                        <button class="join-btn">+ Rejoindre</button>
                                    </form>
                                `
                        }
                    </div>
                `;
            }

            if (roomsHTML === "") {
                roomsHTML = `<div class="empty-public">Aucune partie publique en cours</div>`;
            }

            document.getElementById("rooms-container").innerHTML = roomsHTML;

        });
}, 1500);
</script>

</body>
</html>