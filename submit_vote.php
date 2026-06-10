<?php
session_start();

if (empty($_SESSION["username"]) || empty($_SESSION["room_id"])) {
    header("Location: index.php");
    exit();
}

$file = "data.json";

if (!file_exists($file)) {
    header("Location: home.php");
    exit();
}

$data = json_decode(file_get_contents($file), true);

if (!$data) {
    header("Location: home.php");
    exit();
}

$room_id = $_SESSION["room_id"];
$username = $_SESSION["username"];

if (!isset($data[$room_id])) {
    header("Location: home.php");
    exit();
}

$story_modes = ["sport", "internet", "histoiregeo", "celebrite", "musique", "amis"];

$room = &$data[$room_id];
$mode = $room["mode"] ?? "";

if (!in_array($mode, $story_modes, true)) {
    header("Location: game.php");
    exit();
}

$displayed_responses = $room["displayed_responses"] ?? [];
$current_display_index = $room["current_display_index"] ?? 0;

if (!isset($displayed_responses[$current_display_index])) {
    header("Location: game.php");
    exit();
}

$current_response = $displayed_responses[$current_display_index];
$author = $current_response["author"] ?? "";

if ($author === $username) {
    header("Location: game.php");
    exit();
}

$phase = $_POST["phase"] ?? "";

/* =========================
   VOTE AUTEUR (mode amis)
========================= */
if (($room["state"] ?? "") === "guess_author" && $mode === "amis" && $phase === "author_guess") {
    $guessed_author = trim($_POST["guessed_author"] ?? "");

    $allowed_authors = [];
    foreach (($room["players"] ?? []) as $p) {
        if (($p["username"] ?? "") !== $username) {
            $allowed_authors[] = $p["username"];
        }
    }

    if ($guessed_author === "" || !in_array($guessed_author, $allowed_authors, true)) {
        header("Location: game.php");
        exit();
    }

    if (!isset($room["current_author_votes"]) || !is_array($room["current_author_votes"])) {
        $room["current_author_votes"] = [];
    }

    $room["current_author_votes"][$username] = $guessed_author;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: game.php");
    exit();
}

/* =========================
   VOTE VRAI / BLUFF
========================= */
if (($room["state"] ?? "") === "voting" && $phase === "truth_vote") {
    $vote = $_POST["vote"] ?? "";

    if (!in_array($vote, ["VRAI", "BLUFF"], true)) {
        header("Location: game.php");
        exit();
    }

    if (!isset($room["current_votes"]) || !is_array($room["current_votes"])) {
        $room["current_votes"] = [];
    }

    $room["current_votes"][$username] = $vote;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: game.php");
    exit();
}

header("Location: game.php");
exit();
?>