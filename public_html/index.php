<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
</head>

<body>
    <div align="center" class="landing">
        <img src="images/Kirby_sleep.gif" id="gif" onclick="changeImage()">
        <br>Oh no, Kirby is sleeping! 
        <br>Wake Kirby up
    </div>

    <script>
        function changeImage() {
            document.getElementById("gif").src = "images/Kirby_wakeup.gif";
            window.setTimeout(function() {
                window.location.replace("homepage.php")
            }, 2000);  
        }
    </script>
</body>
</html>
