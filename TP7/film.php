<?php

$films = array(
  "Forrest Gump" => 1994,
  "The Shawshank Redemption" => 1994,
  "The Godfather" => 1972,
  "The Godfather: Part II" => 1974,
  "The Dark Knight" => 2008,
  "12 Angry Men" => 1957,
  "Schindler's List" => 1993,
  "The Lord of the Rings: The Return of the King" => 2003,
  "Pulp Fiction" => 1994,
  "The Good, the Bad and the Ugly" => 1966,
  "The Lord of the Rings: The Fellowship of the Ring" => 2001,
  "Fight Club" => 1999,
  "The Lord of the Rings: The Two Towers" => 2002,
  "Inception" => 2010,
  "Star Wars: Episode V - The Empire Strikes Back" => 1980,
  "The Matrix" => 1999,
  "Goodfellas" => 1990,
  "One Flew Over the Cuckoo's Nest" => 1975,
  "Seven Samurai" => 1954,
  "Se7en" => 1995
);

$filmsApres2000 = array();

foreach ($films as $film => $annee) {
    if ($annee > 2000) {
        $filmsApres2000[] = $film;
    }
}

sort($filmsApres2000);

echo "Films sortis après 2000 :\n";

foreach ($filmsApres2000 as $film) {
    echo $film . "\n";
}

?>