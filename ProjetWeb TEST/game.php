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
$room_id = $_SESSION["room_id"];

if (!$data || !isset($data[$room_id])) {
    unset($_SESSION["room_id"]);
    header("Location: home.php");
    exit();
}

function save_and_redirect($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: game.php");
    exit();
}

function build_displayed_responses($players, $round_answers) {
    $displayed = [];
    $used = [];

    foreach ($players as $index => $player) {
        $author = $player["username"];
        $selected = null;
        $selected_key = null;

        if (isset($round_answers[$index]["answers"][$author])) {
            $selected = [
                "question" => $round_answers[$index]["question"] ?? "",
                "author" => $author,
                "text" => $round_answers[$index]["answers"][$author]["text"] ?? "",
                "type" => $round_answers[$index]["answers"][$author]["type"] ?? ""
            ];
            $selected_key = $index . "|" . $author;
        }

        if ($selected === null) {
            foreach ($round_answers as $round_index => $round) {
                if (isset($round["answers"][$author])) {
                    $candidate_key = $round_index . "|" . $author;
                    if (!in_array($candidate_key, $used, true)) {
                        $selected = [
                            "question" => $round["question"] ?? "",
                            "author" => $author,
                            "text" => $round["answers"][$author]["text"] ?? "",
                            "type" => $round["answers"][$author]["type"] ?? ""
                        ];
                        $selected_key = $candidate_key;
                        break;
                    }
                }
            }
        }

        if ($selected !== null) {
            $used[] = $selected_key;
            $displayed[] = $selected;
        }
    }

    return $displayed;
}

function sorted_scores($scores) {
    arsort($scores);
    return $scores;
}

$story_modes = ["sport", "internet", "histoiregeo", "celebrite", "musique", "amis"];

$room = &$data[$room_id];
$players = $room["players"] ?? [];
$player_count = count($players);

$state = $room["state"] ?? "waiting";
$mode = $room["mode"] ?? "";
$is_story_mode = in_array($mode, $story_modes, true);
$is_host = isset($players[0]) && ($players[0]["username"] === $_SESSION["username"]);

if (!isset($room["scores"]) || !is_array($room["scores"])) {
    $room["scores"] = [];
    foreach ($players as $p) {
        $room["scores"][$p["username"]] = 0;
    }
}

/* =========================
   PHASE QUESTIONS
========================= */
if ($state === "question" && $is_story_mode) {
    $question_text = $room["question_text"] ?? "";
    $question_end_time = $room["question_end_time"] ?? 0;
    $answers = $room["answers"] ?? [];
    $current_question_index = $room["current_question_index"] ?? 0;
    $total_questions = $room["total_questions"] ?? 0;

    $all_answered = count($answers) >= $player_count;
    $time_up = ($question_end_time > 0 && time() >= $question_end_time);

    if ($all_answered || $time_up) {
        if (!isset($room["round_answers"]) || !is_array($room["round_answers"])) {
            $room["round_answers"] = [];
        }

        $room["round_answers"][] = [
            "question" => $question_text,
            "answers" => $answers
        ];

        $next_index = $current_question_index + 1;
        $questions_key = $mode . "_questions";

        if ($next_index < $total_questions && isset($room[$questions_key][$next_index])) {
            $room["current_question_index"] = $next_index;
            $room["question_text"] = $room[$questions_key][$next_index];
            $room["question_end_time"] = time() + 60;
            $room["answers"] = [];
            save_and_redirect($file, $data);
        } else {
            $room["displayed_responses"] = build_displayed_responses($players, $room["round_answers"]);
            $room["current_display_index"] = 0;
            $room["current_author_votes"] = [];
            $room["author_vote_end_time"] = time() + 30;
            $room["current_votes"] = [];
            $room["vote_end_time"] = 0;
            $room["result_end_time"] = 0;
            $room["last_result"] = null;
            $room["vote_history"] = [];

            if ($mode === "amis") {
                $room["state"] = "guess_author";
            } else {
                $room["state"] = "voting";
                $room["vote_end_time"] = time() + 30;
            }

            save_and_redirect($file, $data);
        }
    }
}

/* =========================
   PHASE DEVINER L'AUTEUR (AMIS)
========================= */
if (($room["state"] ?? "") === "guess_author" && ($room["mode"] ?? "") === "amis") {
    $displayed_responses = $room["displayed_responses"] ?? [];
    $current_display_index = $room["current_display_index"] ?? 0;
    $current_author_votes = $room["current_author_votes"] ?? [];
    $author_vote_end_time = $room["author_vote_end_time"] ?? 0;

    if (isset($displayed_responses[$current_display_index])) {
        $current_response = $displayed_responses[$current_display_index];
        $author = $current_response["author"] ?? "";

        $eligible_voters = [];
        foreach ($players as $p) {
            if ($p["username"] !== $author) {
                $eligible_voters[] = $p["username"];
            }
        }

        $all_voted = count($current_author_votes) >= count($eligible_voters);
        $time_up = ($author_vote_end_time > 0 && time() >= $author_vote_end_time);

        if ($all_voted || $time_up) {
            $correct_author_guessers = [];
            $wrong_author_guessers = [];
            $author_guess_bonus = 0;

            foreach ($eligible_voters as $voter) {
                $guessed_author = $current_author_votes[$voter] ?? null;

                if ($guessed_author === $author) {
                    $correct_author_guessers[] = $voter;
                    if (!isset($room["scores"][$voter])) {
                        $room["scores"][$voter] = 0;
                    }
                    $room["scores"][$voter] += 100;
                } else {
                    $wrong_author_guessers[] = $voter;
                    if (!isset($room["scores"][$author])) {
                        $room["scores"][$author] = 0;
                    }
                    $room["scores"][$author] += 100;
                    $author_guess_bonus += 100;
                }
            }

            $room["last_result"] = [
                "question" => $current_response["question"] ?? "",
                "author" => $author,
                "text" => $current_response["text"] ?? "",
                "correct_type" => $current_response["type"] ?? "",
                "correct_author_guessers" => $correct_author_guessers,
                "wrong_author_guessers" => $wrong_author_guessers,
                "author_guess_points" => 100,
                "author_guess_bonus" => $author_guess_bonus,
                "correct_voters" => [],
                "wrong_voters" => [],
                "author_bonus" => 0,
                "scores" => $room["scores"]
            ];

            $room["state"] = "author_reveal";
            $room["result_end_time"] = time() + 4;
            save_and_redirect($file, $data);
        }
    } else {
        $room["state"] = "final_scores";
        save_and_redirect($file, $data);
    }
}

/* =========================
   REVEAL AUTEUR (AMIS)
========================= */
if (($room["state"] ?? "") === "author_reveal" && ($room["mode"] ?? "") === "amis") {
    $result_end_time = $room["result_end_time"] ?? 0;

    if ($result_end_time > 0 && time() >= $result_end_time) {
        $room["current_votes"] = [];
        $room["vote_end_time"] = time() + 30;
        $room["result_end_time"] = 0;
        $room["state"] = "voting";
        save_and_redirect($file, $data);
    }
}

/* =========================
   PHASE VOTE VRAI / BLUFF
========================= */
if (($room["state"] ?? "") === "voting" && in_array(($room["mode"] ?? ""), $story_modes, true)) {
    $displayed_responses = $room["displayed_responses"] ?? [];
    $current_display_index = $room["current_display_index"] ?? 0;
    $current_votes = $room["current_votes"] ?? [];
    $vote_end_time = $room["vote_end_time"] ?? 0;

    if (isset($displayed_responses[$current_display_index])) {
        $current_response = $displayed_responses[$current_display_index];
        $author = $current_response["author"] ?? "";
        $correct_type = $current_response["type"] ?? "";

        $eligible_voters = [];
        foreach ($players as $p) {
            if ($p["username"] !== $author) {
                $eligible_voters[] = $p["username"];
            }
        }

        $all_voted = count($current_votes) >= count($eligible_voters);
        $time_up = ($vote_end_time > 0 && time() >= $vote_end_time);

        if ($all_voted || $time_up) {
            $correct_voters = [];
            $wrong_voters = [];
            $author_bonus = 0;

            foreach ($eligible_voters as $voter) {
                $vote_value = $current_votes[$voter] ?? null;

                if ($vote_value === $correct_type) {
                    $correct_voters[] = $voter;
                    if (!isset($room["scores"][$voter])) {
                        $room["scores"][$voter] = 0;
                    }
                    $room["scores"][$voter] += 50;
                } else {
                    $wrong_voters[] = $voter;

                    if (!isset($room["scores"][$author])) {
                        $room["scores"][$author] = 0;
                    }
                    $room["scores"][$author] += 50;
                    $author_bonus += 50;
                }
            }

            if (!isset($room["last_result"]) || !is_array($room["last_result"])) {
                $room["last_result"] = [];
            }

            $room["last_result"]["question"] = $current_response["question"] ?? "";
            $room["last_result"]["author"] = $author;
            $room["last_result"]["text"] = $current_response["text"] ?? "";
            $room["last_result"]["correct_type"] = $correct_type;
            $room["last_result"]["correct_voters"] = $correct_voters;
            $room["last_result"]["wrong_voters"] = $wrong_voters;
            $room["last_result"]["author_bonus"] = ($room["last_result"]["author_bonus"] ?? 0) + $author_bonus;
            $room["last_result"]["scores"] = $room["scores"];

            if (!isset($room["last_result"]["correct_author_guessers"])) {
                $room["last_result"]["correct_author_guessers"] = [];
            }

            if (!isset($room["last_result"]["author_guess_points"])) {
                $room["last_result"]["author_guess_points"] = 0;
            }

            if (!isset($room["last_result"]["wrong_author_guessers"])) {
                $room["last_result"]["wrong_author_guessers"] = [];
            }

            if (!isset($room["last_result"]["author_guess_bonus"])) {
                $room["last_result"]["author_guess_bonus"] = 0;
            }

            if (!isset($room["vote_history"]) || !is_array($room["vote_history"])) {
                $room["vote_history"] = [];
            }

            $room["vote_history"][] = $room["last_result"];
            $room["state"] = "vote_result";
            $room["result_end_time"] = time() + 4;
            save_and_redirect($file, $data);
        }
    } else {
        $room["state"] = "final_scores";
        save_and_redirect($file, $data);
    }
}

/* =========================
   RESULTAT FINAL D'UNE REPONSE
========================= */
if (($room["state"] ?? "") === "vote_result" && in_array(($room["mode"] ?? ""), $story_modes, true)) {
    $displayed_responses = $room["displayed_responses"] ?? [];
    $current_display_index = $room["current_display_index"] ?? 0;
    $result_end_time = $room["result_end_time"] ?? 0;

    if ($result_end_time > 0 && time() >= $result_end_time) {
        $next_index = $current_display_index + 1;

        if ($next_index < count($displayed_responses)) {
            $room["current_display_index"] = $next_index;
            $room["current_author_votes"] = [];
            $room["author_vote_end_time"] = 0;
            $room["current_votes"] = [];
            $room["vote_end_time"] = 0;
            $room["result_end_time"] = 0;
            $room["last_result"] = null;

            if (($room["mode"] ?? "") === "amis") {
                $room["state"] = "guess_author";
                $room["author_vote_end_time"] = time() + 30;
            } else {
                $room["state"] = "voting";
                $room["vote_end_time"] = time() + 30;
            }

            save_and_redirect($file, $data);
        } else {
            $room["state"] = "final_scores";
            save_and_redirect($file, $data);
        }
    }
}

/* =========================
   VARIABLES FINALES
========================= */
$players = $room["players"] ?? [];
$player_count = count($players);
$state = $room["state"] ?? "waiting";
$mode = $room["mode"] ?? "";
$is_story_mode = in_array($mode, $story_modes, true);

$question_text = $room["question_text"] ?? "";
$question_end_time = $room["question_end_time"] ?? 0;
$answers = $room["answers"] ?? [];
$has_answered = isset($answers[$_SESSION["username"]]);
$current_question_index = $room["current_question_index"] ?? 0;
$total_questions = $room["total_questions"] ?? 0;

$displayed_responses = $room["displayed_responses"] ?? [];
$current_display_index = $room["current_display_index"] ?? 0;
$current_response = $displayed_responses[$current_display_index] ?? null;

$current_author_votes = $room["current_author_votes"] ?? [];
$author_vote_end_time = $room["author_vote_end_time"] ?? 0;
$has_author_voted = isset($current_author_votes[$_SESSION["username"]]);

$current_votes = $room["current_votes"] ?? [];
$vote_end_time = $room["vote_end_time"] ?? 0;
$has_voted = isset($current_votes[$_SESSION["username"]]);

$last_result = $room["last_result"] ?? null;
$sorted_scores = sorted_scores($room["scores"] ?? []);

$current_author = $current_response["author"] ?? "";
$is_own_story = ($current_author === $_SESSION["username"]);

$eligible_vote_count = 0;
if ($current_response) {
    $eligible_vote_count = $player_count - 1;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WhoRelate - Game</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="game.css">
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<h1>WhoRelate</h1>

<form action="logout.php" method="POST" class="logout-form">
    <button class="btn">Déconnexion</button>
</form>

<div class="container">

    <h2>Code de la partie : <?= htmlspecialchars($room_id) ?></h2>

    <?php if ($state === "waiting"): ?>

        <?php if ($is_host): ?>
            <form action="start.php" method="POST">
                <button class="play-btn" <?= ($player_count < 3) ? 'disabled' : '' ?>>
                    Play
                </button>
            </form>
        <?php endif; ?>

        <h3>Joueurs connectés :</h3>

        <div class="players">
            <?php foreach ($players as $p): ?>
                <div class="player">
                    <div class="avatar"><?= htmlspecialchars($p["avatar"]) ?></div>
                    <div><?= htmlspecialchars($p["username"]) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($state === "mode_selection"): ?>

        <?php if ($is_host): ?>
            <form action="choose_mode.php" method="POST">
                <h2>Choisis un mode</h2>

                <div class="modes">
                    <label class="mode-option">
                        <input type="radio" name="mode" value="amis" required>
                        <div class="mode">Jouer avec des amis</div>
                    </label>

                    <label class="mode-option">
                        <input type="radio" name="mode" value="sport">
                        <div class="mode">Sport</div>
                    </label>

                    <label class="mode-option">
                        <input type="radio" name="mode" value="internet">
                        <div class="mode">Internet</div>
                    </label>

                    <label class="mode-option">
                        <input type="radio" name="mode" value="histoiregeo">
                        <div class="mode">Histoire&amp;Geo</div>
                    </label>

                    <label class="mode-option">
                        <input type="radio" name="mode" value="celebrite">
                        <div class="mode">Celebrité</div>
                    </label>

                    <label class="mode-option">
                        <input type="radio" name="mode" value="musique">
                        <div class="mode">Musique</div>
                    </label>
                </div>

                <button class="play-btn">Start</button>
            </form>
        <?php else: ?>
            <h2>Le host choisit le mode de l'histoire...</h2>
        <?php endif; ?>

    <?php elseif ($state === "question" && $is_story_mode): ?>

        <div class="question-screen">
            <div class="round-badge">
                Question <?= $current_question_index + 1 ?> / <?= $total_questions ?>
            </div>

            <h2 class="big-question"><?= htmlspecialchars($question_text) ?></h2>

            <?php if (!$has_answered): ?>
                <form action="submit_answer.php" method="POST" class="answer-layout">
                    <div class="answer-left">
                        <textarea
                            name="answer_text"
                            class="answer-box"
                            placeholder="Écris ton histoire ici..."
                            required
                        ></textarea>
                    </div>

                    <div class="answer-right">
                        <h3>Ta réponse est :</h3>

                        <label class="truth-option">
                            <input type="radio" name="answer_type" value="VRAI" required>
                            <span>✅ VRAI</span>
                        </label>

                        <label class="truth-option">
                            <input type="radio" name="answer_type" value="BLUFF" required>
                            <span>🎭 BLUFF</span>
                        </label>

                        <button type="submit" class="play-btn">Valider</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="waiting-answer-box">
                    <h3>✅ Réponse envoyée</h3>
                    <p>Attends les autres joueurs...</p>
                    <p id="answers-count"><?= count($answers) ?> / <?= $player_count ?> réponses reçues</p>
                </div>
            <?php endif; ?>

            <div class="timer-box">
                Temps restant : <span id="question-timer">60</span> sec
            </div>
        </div>

    <?php elseif ($state === "guess_author" && $mode === "amis" && $current_response): ?>

        <div class="vote-screen">
            <div class="round-badge">
                Histoire <?= $current_display_index + 1 ?> / <?= count($displayed_responses) ?>
            </div>

            <p class="vote-question-label">Question d’origine :</p>
            <h3 class="vote-question"><?= htmlspecialchars($current_response["question"] ?? "") ?></h3>

            <div class="story-card">
                <?= nl2br(htmlspecialchars($current_response["text"] ?? "")) ?>
            </div>

            <?php if ($is_own_story): ?>
                <div class="waiting-answer-box">
                    <h3>⏳ C’est ton histoire</h3>
                    <p>Tu ne votes pas pour l’auteur de ta propre histoire.</p>
                    <p id="author-votes-count"><?= count($current_author_votes) ?> / <?= $eligible_vote_count ?> votes reçus</p>
                </div>
            <?php elseif (!$has_author_voted): ?>
                <form action="submit_vote.php" method="POST" class="vote-form">
                    <input type="hidden" name="phase" value="author_guess">

                    <h3>Qui a écrit cette histoire ?</h3>

                    <div class="vote-options">
                        <?php foreach ($players as $p): ?>
                            <?php if ($p["username"] !== $_SESSION["username"]): ?>
                                <label class="vote-card">
                                    <input type="radio" name="guessed_author" value="<?= htmlspecialchars($p["username"]) ?>" required>
                                    <div class="vote-card-box"><?= htmlspecialchars($p["username"]) ?></div>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="play-btn">Valider mon choix</button>
                </form>
            <?php else: ?>
                <div class="waiting-answer-box">
                    <h3>✅ Vote auteur envoyé</h3>
                    <p>Attends les autres joueurs...</p>
                    <p id="author-votes-count"><?= count($current_author_votes) ?> / <?= $eligible_vote_count ?> votes reçus</p>
                </div>
            <?php endif; ?>

            <div class="timer-box">
                Temps restant : <span id="author-vote-timer">30</span> sec
            </div>
        </div>

    <?php elseif ($state === "author_reveal" && $mode === "amis" && $last_result): ?>

        <div class="result-screen">
            <div class="round-badge">
                Reveal auteur <?= $current_display_index + 1 ?> / <?= count($displayed_responses) ?>
            </div>

            <p class="vote-question-label">Question d’origine :</p>
            <h3 class="vote-question"><?= htmlspecialchars($last_result["question"] ?? "") ?></h3>

            <div class="story-card">
                <?= nl2br(htmlspecialchars($last_result["text"] ?? "")) ?>
            </div>

            <div class="reveal-box">
                <h2>Auteur révélé :</h2>
                <p><strong><?= htmlspecialchars($last_result["author"] ?? "") ?></strong></p>

                <div class="result-column">
                    <h3>🎯 Ont trouvé la bonne personne</h3>
                    <?php if (!empty($last_result["correct_author_guessers"])): ?>
                        <?php foreach ($last_result["correct_author_guessers"] as $name): ?>
                            <div class="result-chip good"><?= htmlspecialchars($name) ?> (+100)</div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="result-chip neutral">Personne</div>
                    <?php endif; ?>
                </div>

                <div class="result-column" style="margin-top:16px;">
                    <h3>🙈 Se sont trompés sur l’auteur</h3>
                    <?php if (!empty($last_result["wrong_author_guessers"])): ?>
                        <?php foreach ($last_result["wrong_author_guessers"] as $name): ?>
                            <div class="result-chip bonus">
                                <?= htmlspecialchars($last_result["author"] ?? "") ?> gagne +100 grâce à <?= htmlspecialchars($name) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="result-chip neutral">Personne</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="scoreboard">
                <h3>Scores après reveal auteur</h3>
                <?php foreach (sorted_scores($last_result["scores"] ?? []) as $name => $score): ?>
                    <div class="score-row">
                        <span><?= htmlspecialchars($name) ?></span>
                        <strong><?= (int)$score ?> pts</strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($state === "voting" && $is_story_mode && $current_response): ?>

        <div class="vote-screen">
            <div class="round-badge">
                Réponse <?= $current_display_index + 1 ?> / <?= count($displayed_responses) ?>
            </div>

            <p class="vote-question-label">Question d’origine :</p>
            <h3 class="vote-question"><?= htmlspecialchars($current_response["question"] ?? "") ?></h3>

            <div class="story-card">
                <?= nl2br(htmlspecialchars($current_response["text"] ?? "")) ?>
            </div>

            <?php if ($mode === "amis"): ?>
                <div class="waiting-answer-box" style="margin-bottom:16px;">
                    <h3>👤 Auteur révélé : <?= htmlspecialchars($current_response["author"] ?? "") ?></h3>
                    <p>Maintenant, vote : cette histoire est-elle vraie ou bluff ?</p>
                </div>
            <?php endif; ?>

            <?php if ($is_own_story): ?>
                <div class="waiting-answer-box">
                    <h3>⏳ C’est ton histoire</h3>
                    <p>Tu ne peux pas voter sur ta propre réponse.</p>
                    <p id="votes-count"><?= count($current_votes) ?> / <?= $eligible_vote_count ?> votes reçus</p>
                </div>
            <?php elseif (!$has_voted): ?>
                <form action="submit_vote.php" method="POST" class="vote-form">
                    <input type="hidden" name="phase" value="truth_vote">

                    <h3>Tu penses que cette histoire est :</h3>

                    <div class="vote-options">
                        <label class="vote-card">
                            <input type="radio" name="vote" value="VRAI" required>
                            <div class="vote-card-box">✅ VRAI</div>
                        </label>

                        <label class="vote-card">
                            <input type="radio" name="vote" value="BLUFF" required>
                            <div class="vote-card-box">🎭 BLUFF</div>
                        </label>
                    </div>

                    <button type="submit" class="play-btn">Valider mon vote</button>
                </form>
            <?php else: ?>
                <div class="waiting-answer-box">
                    <h3>✅ Vote envoyé</h3>
                    <p>Attends les autres joueurs...</p>
                    <p id="votes-count"><?= count($current_votes) ?> / <?= $eligible_vote_count ?> votes reçus</p>
                </div>
            <?php endif; ?>

            <div class="timer-box">
                Temps restant : <span id="vote-timer">30</span> sec
            </div>
        </div>

    <?php elseif ($state === "vote_result" && $is_story_mode && $last_result): ?>

        <div class="result-screen">
            <div class="round-badge">
                Résultat <?= $current_display_index + 1 ?> / <?= count($displayed_responses) ?>
            </div>

            <p class="vote-question-label">Question d’origine :</p>
            <h3 class="vote-question"><?= htmlspecialchars($last_result["question"] ?? "") ?></h3>

            <div class="story-card">
                <?= nl2br(htmlspecialchars($last_result["text"] ?? "")) ?>
            </div>

            <div class="reveal-box">
                <p><strong>Auteur :</strong> <?= htmlspecialchars($last_result["author"] ?? "") ?></p>

                <?php if ($mode === "amis"): ?>
                    <div class="result-column" style="margin-bottom:16px;">
                        <h3>🎯 Bonne personne trouvée</h3>
                        <?php if (!empty($last_result["correct_author_guessers"])): ?>
                            <?php foreach ($last_result["correct_author_guessers"] as $name): ?>
                                <div class="result-chip good"><?= htmlspecialchars($name) ?> (+100)</div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="result-chip neutral">Personne</div>
                        <?php endif; ?>
                    </div>

                    <div class="result-column" style="margin-bottom:16px;">
                        <h3>🙈 Auteur récompensé quand on se trompe</h3>
                        <?php if (!empty($last_result["wrong_author_guessers"])): ?>
                            <?php foreach ($last_result["wrong_author_guessers"] as $name): ?>
                                <div class="result-chip bonus">
                                    <?= htmlspecialchars($last_result["author"] ?? "") ?> gagne +100 grâce à <?= htmlspecialchars($name) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="result-chip neutral">Personne</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h2>
                    Bonne réponse :
                    <span class="<?= ($last_result["correct_type"] === "VRAI") ? 'truth-green' : 'truth-pink' ?>">
                        <?= htmlspecialchars($last_result["correct_type"] ?? "") ?>
                    </span>
                </h2>

                <div class="result-columns">
                    <div class="result-column">
                        <h3>✅ Ont eu juste</h3>
                        <?php if (!empty($last_result["correct_voters"])): ?>
                            <?php foreach ($last_result["correct_voters"] as $name): ?>
                                <div class="result-chip good"><?= htmlspecialchars($name) ?> (+50)</div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="result-chip neutral">Personne</div>
                        <?php endif; ?>
                    </div>

                    <div class="result-column">
                        <h3>🎭 Bonus auteur</h3>
                        <?php if (($last_result["author_bonus"] ?? 0) > 0): ?>
                            <div class="result-chip bonus">
                                <?= htmlspecialchars($last_result["author"] ?? "") ?> (+<?= (int)$last_result["author_bonus"] ?>)
                            </div>
                        <?php else: ?>
                            <div class="result-chip neutral">Aucun bonus</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="scoreboard">
                <h3>Scores mis à jour</h3>
                <?php foreach (sorted_scores($last_result["scores"] ?? []) as $name => $score): ?>
                    <div class="score-row">
                        <span><?= htmlspecialchars($name) ?></span>
                        <strong><?= (int)$score ?> pts</strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($state === "final_scores" && $is_story_mode): ?>

        <div class="final-screen">
            <h2>🏆 Tableau final des scores</h2>

            <div class="final-scoreboard">
                <?php
                $rank = 1;
                foreach ($sorted_scores as $name => $score):
                ?>
                    <div class="final-score-row <?= $rank === 1 ? 'winner' : '' ?>">
                        <span>#<?= $rank ?> — <?= htmlspecialchars($name) ?></span>
                        <strong><?= (int)$score ?> pts</strong>
                    </div>
                <?php
                    $rank++;
                endforeach;
                ?>
            </div>

            <div class="results-preview">
                <h3>Historique des votes</h3>

                <?php foreach (($room["vote_history"] ?? []) as $index => $result): ?>
                    <div class="round-preview-card">
                        <h4>Réponse <?= $index + 1 ?></h4>
                        <p class="round-preview-question"><?= htmlspecialchars($result["question"] ?? "") ?></p>
                        <div class="preview-answer-item">
                            <strong><?= htmlspecialchars($result["author"] ?? "") ?></strong> :
                            <?= htmlspecialchars($result["text"] ?? "") ?>
                            <span class="preview-type">(<?= htmlspecialchars($result["correct_type"] ?? "") ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

</div>

<script>
const ROOM_ID = "<?= $room_id ?>";
const CURRENT_STATE = "<?= $state ?>";
const CURRENT_QUESTION_INDEX = <?= (int)$current_question_index ?>;
const CURRENT_DISPLAY_INDEX = <?= (int)$current_display_index ?>;
const CURRENT_PLAYER_COUNT = <?= (int)$player_count ?>;
const ELIGIBLE_VOTE_COUNT = <?= (int)$eligible_vote_count ?>;

async function fetchRoomData() {
    const response = await fetch(`data.json?t=${Date.now()}`, {
        cache: "no-store"
    });
    return response.json();
}

setInterval(async () => {
    try {
        const data = await fetchRoomData();

        if (!data[ROOM_ID]) {
            window.location.href = "home.php";
            return;
        }

        const room = data[ROOM_ID];
        const newState = room.state || "waiting";
        const newQuestionIndex = room.current_question_index || 0;
        const newDisplayIndex = room.current_display_index || 0;
        const newPlayerCount = (room.players || []).length;

        if (
            newState !== CURRENT_STATE ||
            newQuestionIndex !== CURRENT_QUESTION_INDEX ||
            newDisplayIndex !== CURRENT_DISPLAY_INDEX ||
            newPlayerCount !== CURRENT_PLAYER_COUNT
        ) {
            location.reload();
            return;
        }

        const playersContainer = document.querySelector(".players");
        if (playersContainer && room.players) {
            let html = "";
            room.players.forEach(p => {
                html += `
                    <div class="player">
                        <div class="avatar">${p.avatar}</div>
                        <div>${p.username}</div>
                    </div>
                `;
            });
            playersContainer.innerHTML = html;

            const playBtn = document.querySelector(".play-btn");
            if (playBtn && document.querySelector(".players")) {
                playBtn.disabled = room.players.length < 3;
            }
        }

        if (CURRENT_STATE === "question") {
            const answers = room.answers || {};
            const answersCountEl = document.getElementById("answers-count");
            if (answersCountEl) {
                answersCountEl.textContent = `${Object.keys(answers).length} / ${newPlayerCount} réponses reçues`;
            }

            const endTime = room.question_end_time || 0;
            const now = Math.floor(Date.now() / 1000);
            if (endTime > 0 && now >= endTime) {
                location.reload();
                return;
            }

            if (Object.keys(answers).length >= newPlayerCount) {
                location.reload();
                return;
            }
        }

        if (CURRENT_STATE === "guess_author") {
            const votes = room.current_author_votes || {};
            const votesCountEl = document.getElementById("author-votes-count");
            if (votesCountEl) {
                votesCountEl.textContent = `${Object.keys(votes).length} / ${ELIGIBLE_VOTE_COUNT} votes reçus`;
            }

            const endTime = room.author_vote_end_time || 0;
            const now = Math.floor(Date.now() / 1000);
            if (endTime > 0 && now >= endTime) {
                location.reload();
                return;
            }

            if (Object.keys(votes).length >= ELIGIBLE_VOTE_COUNT) {
                location.reload();
                return;
            }
        }

        if (CURRENT_STATE === "voting") {
            const votes = room.current_votes || {};
            const votesCountEl = document.getElementById("votes-count");
            if (votesCountEl) {
                votesCountEl.textContent = `${Object.keys(votes).length} / ${ELIGIBLE_VOTE_COUNT} votes reçus`;
            }

            const endTime = room.vote_end_time || 0;
            const now = Math.floor(Date.now() / 1000);
            if (endTime > 0 && now >= endTime) {
                location.reload();
                return;
            }

            if (Object.keys(votes).length >= ELIGIBLE_VOTE_COUNT) {
                location.reload();
                return;
            }
        }

        if (CURRENT_STATE === "author_reveal" || CURRENT_STATE === "vote_result") {
            const endTime = room.result_end_time || 0;
            const now = Math.floor(Date.now() / 1000);
            if (endTime > 0 && now >= endTime) {
                location.reload();
                return;
            }
        }
    } catch (e) {
        console.error("Erreur sync:", e);
    }
}, 800);

const questionEndTime = <?= (int)$question_end_time ?>;
const questionTimer = document.getElementById("question-timer");

if (questionTimer && questionEndTime > 0) {
    const countdown = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const remaining = questionEndTime - now;

        if (remaining <= 0) {
            questionTimer.textContent = 0;
            clearInterval(countdown);
            location.reload();
        } else {
            questionTimer.textContent = remaining;
        }
    }, 250);
}

const authorVoteEndTime = <?= (int)$author_vote_end_time ?>;
const authorVoteTimer = document.getElementById("author-vote-timer");

if (authorVoteTimer && authorVoteEndTime > 0) {
    const countdownAuthorVote = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const remaining = authorVoteEndTime - now;

        if (remaining <= 0) {
            authorVoteTimer.textContent = 0;
            clearInterval(countdownAuthorVote);
            location.reload();
        } else {
            authorVoteTimer.textContent = remaining;
        }
    }, 250);
}

const voteEndTime = <?= (int)$vote_end_time ?>;
const voteTimer = document.getElementById("vote-timer");

if (voteTimer && voteEndTime > 0) {
    const countdownVote = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const remaining = voteEndTime - now;

        if (remaining <= 0) {
            voteTimer.textContent = 0;
            clearInterval(countdownVote);
            location.reload();
        } else {
            voteTimer.textContent = remaining;
        }
    }, 250);
}
</script>

</body>
</html>