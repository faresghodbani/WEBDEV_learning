<?php
session_start();

$file = "data.json";

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
} else {
    $data = [];
}

if (!$data) {
    $data = [];
    }

if (!isset($data["online_users"])) {
    $data["online_users"] = [];
}

if (!empty($_SESSION["room_id"])) {

    $room_id = $_SESSION["room_id"];
    $username = $_SESSION["username"];

    if (isset($data[$room_id])) {

        $players = $data[$room_id]["players"];
        $host = $players[0]["username"];

        if ($username === $host) {
            unset($data[$room_id]);
        } else {
            $new_players = [];

            foreach ($players as $p) {
                if ($p["username"] !== $username) {
                    $new_players[] = $p;
                }
            }

            $data[$room_id]["players"] = $new_players;
        }
    }
}

// Retirer de la liste des utilisateurs connectés
if (!empty($_SESSION["username"]) && !empty($_SESSION["avatar"])) {
    $new_online_users = [];

    foreach ($data["online_users"] as $user) {
        if (
            !(
                $user["username"] === $_SESSION["username"] &&
                $user["avatar"] === $_SESSION["avatar"]
            )
        ) {
            $new_online_users[] = $user;
        }
    }

    $data["online_users"] = $new_online_users;
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// supprimer session
session_unset();
session_destroy();

// retour login
header("Location: index.php");
exit();
?>