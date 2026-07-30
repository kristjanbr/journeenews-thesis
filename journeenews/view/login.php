<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="<?= CSS_URL . "login.css" ?>">
    <link rel="stylesheet" type="text/css" href="<?= CSS_URL . "style.css" ?>">
    <link rel="icon" type="image/x-icon" href="<?= IMAGES_URL . "favicon.ico" ?>">
    <title>Journee | Login</title>
  </head>
  <body>
    <div id="root">
    <?php include("view/menu.php"); ?>
    <form action="<?= BASE_URL . "user/login" ?>" method="post">
      <div id="main">
        <header>Login:</header>
          <div id="field-email">
            <input
              id="email"
              name="email"
              type="text"
              placeholder="E-mail"
              required
            />
        </div>
        <div id="field-pass">
            <input
              id="password"
              name="password"
              type="password"
              placeholder="Password"
              required
            />
        </div>
        <div>
            <input type="submit" id="login" value="LOGIN" />
          </div>
          <p class="important"><?= $errorMessage?></p>
      </div>
      </form>
    </div>
  </body>
</html>
