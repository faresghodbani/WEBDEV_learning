window.onload = function () {

    let element1 = document.querySelector("#hlbutton");
    let element2 = document.querySelector("#ctbutton");
    let element3 = document.querySelector("#cbbutton");
    let image = document.getElementById("jsimage");
    let title = document.querySelector("#title");
    let form = document.getElementById("form");

    function affiche_helloworld() {
        alert("Hello world !");
    }

    function change_titre() {
        let newTitle = prompt("Entrez le nouveau titre :");
        if (newTitle !== null) {
            title.textContent = newTitle;
        }
    }

    function change_background() {
        document.getElementById("tout").style.backgroundColor = "lightblue";
    }

    function agrandir_image() {
        image.style.height = "10rem";
        image.style.width = "10rem";
    }

    function reduire_image() {
        image.style.height = "3rem";
        image.style.width = "3rem";
    }

    function validation_formulaire(event) {
        event.preventDefault();
        let name = document.getElementById("name").value;
        let prenom = document.getElementById("prénom").value;
        let messageErreur = document.getElementById("erreur");

        if (name === "" || prenom === "") {
            messageErreur.textContent = "Veuillez remplir tous les champs du formulaire.";
        } else {
            messageErreur.textContent = "";
            alert("Le formulaire a été envoyé !");
        }
    }

    element1.addEventListener("click", affiche_helloworld);
    element2.addEventListener("click", change_titre);
    element3.addEventListener("click", change_background);

    image.addEventListener("click", agrandir_image);
    image.addEventListener("mouseout", reduire_image);

    form.addEventListener("submit", validation_formulaire);
};
