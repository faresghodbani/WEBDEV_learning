<?php
session_start();

// sécurité
if (empty($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$file = "data.json";

// lire fichier JSON
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
} else {
    $data = [];
}

// sécurité si null
if (!$data) {
    $data = [];
}

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} else {
    $action = "";
}
// =======================
// CREATE ROOM
// =======================
if ($action === "create") {

    // générer un code unique
    do {
        $room_id = rand(1000, 9999);
    } while (isset($data[$room_id]));

    // créer la room
    $room_type = $_POST["room_type"] ?? "public";

    $data[$room_id] = [
        "type" => $room_type,   // 🔥 TRÈS IMPORTANT
        "state" => "waiting",
        "players" => [
            [
                "username" => $_SESSION["username"],
                "avatar" => $_SESSION["avatar"]
            ]
        ]
    ];

    // sauvegarder proprement
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION["room_id"] = $room_id;

    header("Location: game.php");
    exit();
}

// =======================
// JOIN ROOM
// =======================
if ($action === "join") {
    $room_id = $_POST["room_id"];

    if (!isset($data[$room_id])) {
        $_SESSION["error"] = "Room inexistante";
        header("Location: home.php");
        exit();
    }

    // 🔥 AJOUT ICI
    if ($data[$room_id]["state"] !== "waiting") {
        $_SESSION["error"] = "La partie a déjà commencé";
        header("Location: home.php");
        exit();
    }

    $players = $data[$room_id]["players"];

    // LIMITE MAX
    if (count($players) >= 5) {
        $_SESSION["error"] = "Room pleine (max 5 joueurs)";
        header("Location: home.php");
        exit();
    }

    $base_username = $_SESSION["username"];
    $avatar = $_SESSION["avatar"];

    $players = $data[$room_id]["players"];

    $new_username = $base_username;
    $count = 1;

    $exists = true;

    while ($exists) {
        $exists = false;

        foreach ($players as $p) {
            if ($p["username"] === $new_username) {
                $exists = true;
                $count++;
                $new_username = $base_username . "($count)";
                break;
            }
        }
    }

    $data[$room_id]["players"][] = [
        "username" => $new_username,
        "avatar" => $avatar
    ];

    $_SESSION["username"] = $new_username;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION["room_id"] = $room_id;

    header("Location: game.php");
    exit();
}
?>