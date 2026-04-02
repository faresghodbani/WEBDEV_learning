&lt;?php
function exist($login) {
    if (($handle = fopen("users.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] == $login) {
                fclose($handle);
                return true;
            }
        }
        fclose($handle);
    }
    return false;
}

function loginOk($login, $mdp) {
    if (($handle = fopen("users.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] == $login && $data[1] == $mdp) {
                fclose($handle);
                return true;
            }
        }
        fclose($handle);
    }
    return false;
}

function addUser($login, $mdp) {
    if (($handle = fopen("users.csv", "a")) !== FALSE) {
        fputcsv($handle, array($login, $mdp));
        fclose($handle);
        return true;
    }
    return false;
}

function score($login) {
    if (($handle = fopen("scores.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] == $login) {
                fclose($handle);
                return (int)$data[1];
            }
        }
        fclose($handle);
    }
    return null;
}

function update($login, $score) {
    $scores = [];
    $found = false;
    if (($handle = fopen("scores.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] == $login) {
                if ($score < $data[1]) {
                    $data[1] = $score;
                }
                $found = true;
            }
            $scores[] = $data;
        }
        fclose($handle);
    }
    if (!$found) {
        $scores[] = [$login, $score];
    }
    if (($handle = fopen("scores.csv", "w")) !== FALSE) {
        foreach ($scores as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        return true;
    }
    return false;
}

function top() {
    $scores = [];
    if (($handle = fopen("scores.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $scores[$data[0]] = (int)$data[1];
        }
        fclose($handle);
    }
    asort($scores); // trier par score ascendant
    return array_slice($scores, 0, 10, true);
}
?&gt;