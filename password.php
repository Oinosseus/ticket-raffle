<!DOCTYPE HTML>
<html>
    <head>
        <title>Password Encrypter</title>
    <head>        
    <body>
        <form action="#" method="post">
            <input type="password" name="generated_password" value=""/>
            <button type="submit">Generate</button>
            <br>
            <?php
                if (isset($_POST['generated_password'])) {
                    $pw_hash = password_hash($_POST['generated_password'], PASSWORD_DEFAULT);
                    echo "Generated password: \"$pw_hash\"<br>";
                    echo "password algorithm: \"" . password_get_info($pw_hash)['algoName'] . "\"<br>";
                }
            ?>
        </form>
    </body>
<html>
