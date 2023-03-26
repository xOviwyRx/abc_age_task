<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main.css">
    <title>abcAge</title>
</head>
<body>
    <div class="content">
    <?php require '../private/submit.php' ?>
            <form action="" method="GET">
                        <input type="date" name="date" 
                        value="<?= $_GET['date'] ?? '' ?>"/>
                        <input type="submit" value="Применить">
            </form> 
        <?php require '../private/showResult.php' ?>
    </div>
</body>
</html>