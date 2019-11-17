<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shweta</title>

    <style>
    /* h1 {
        text-decoration: underline;
    } */
    p {
        background: red;
    }

    .xoxo {
        text-decoration: underline;
        background: blue;
    }

    .yoyo {
        text-decoration: none;
        background: yellow;
    }

    </style>

</head>
<body>
    <h1>Main heading</h1>
    <p class="xoxo">
        Hi shweta. <br>
        How do you do? <br>
        Where are you?
    </p>

    <p class="xoxo">Hello there</p>

    <hr>
    <p id="yyyy">What's up ______?</p>

    <p>whattttttttttttt</p>

    <script>
    document.querySelector('#yyyy').addEventListener('click', addClasses, false);

    function addClasses() {

        document.getElementById('yyyy').classList.toggle('yoyo');
    }
    </script>
</body>
</html>