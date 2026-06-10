<!DOCTYPE html>
<html>
<head>
    <title>WhoRelate</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background-image: url("WHORELATE.png");
            background-size: 80%;   /* plus petit */
            background-repeat: no-repeat;
            background-position: center;

            color: white;
            font-family: Arial;
            cursor: pointer;
        }

        .press {
            color: black;
            position: absolute;
            bottom: 40px;
            font-size: 20px;
            opacity: 0.7;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0% { opacity: 0.2; }
            50% { opacity: 1; }
            100% { opacity: 0.2; }
        }
    </style>
</head>
<body>


<div class="press">Press any key</div>

<script>
document.addEventListener("keydown", () => {
    window.location.href = "index.php";
});

document.addEventListener("click", () => {
    window.location.href = "index.php";
});
</script>

</body>
</html>