<?php

use framework\Router;
use framework\Tools;

$navBar = json_decode(file_get_contents(CONFIG_PATH . DIRECTORY_SEPARATOR . "menu.json"), true);

$navOff = "";
if (isset($migrations)) {
  $navOff = count($migrations) > 0 ? " disabled" : "";
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= APP_NAME ?></title>
  <link rel="stylesheet" href="/assets/bootstrap/dist/css/bootstrap.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
        integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
        crossorigin="anonymous" />
  <link rel="stylesheet" href="/assets/select2/dist/css/select2.min.css">
</head>
<body class="container-fluid">
<header class="card-header row">
  <div class="col-4">
    <a class="btn btn-outline-light text-primary" href="<?= Router::route([ "home" ]) ?>">
      <h3 title="<?= DSN ?>"><?= APP_NAME ?></h3>
    </a>
    <?php if ($navOff != "") : ?>
    <a class="text-warning" href="/migration" title="Mises à jour en attente...">
      <i class="fa fa-2x fa-exclamation-circle"></i>
    </a>
    <?php endif; ?>
  </div>
  <div class="col-4 text-center">
    <?php if (isset($form)) : ?>
      <h3 id="title"><?= $form->getTitle() ?></h3>
    <?php else : ?>
      <h3 id="title"><?= $title ?? "" ?></h3>
    <?php endif; ?>
  </div>
  <div class="col-4 text-right">
    <ul class="nav nav-pills">
      <?php foreach ($navBar as $navMenu) : ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
          <?= $navMenu["label"] ?>
        </a>
        <div class="dropdown-menu">
          <?php foreach ($navMenu["options"] as $navOption) : ?>
          <a class="dropdown-item<?= $navOff ?>" href="<?= Router::route($navOption['route']) ?>"><?= $navOption["label"] ?></a>
          <?php endforeach; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</header>

<div class="row justify-content-center">

  <?php
  $messages = Tools::getFlash();
  if (is_array($messages) && count($messages) > 0) : ?>
    <div class="alert alert-success alert-dismissible fade show col-md-6 mt-2" role="alert">
      <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
      </button>
      <ul>
        <?php foreach ($messages as $message) : ?>
          <li><?= $message ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif ?>

  <?php
  $messages = Tools::getFlash("warning");
  if (is_array($messages) && count($messages) > 0) : ?>
    <div class="alert alert-warning alert-dismissible fade show col-md-6 mt-2" role="alert">
      <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
      </button>
      <ul>
        <?php foreach ($messages as $message) : ?>
          <li><?= $message ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif ?>

  <?php
  $messages = Tools::getFlash("danger");
  if (is_array($messages) && count($messages) > 0) : ?>
    <div class="alert alert-danger alert-dismissible fade show col-md-6 mt-2" role="alert">
      <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
      </button>
      <ul>
        <?php foreach ($messages as $message) : ?>
          <li><?= $message ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="close"></button>
    </div>
  <?php endif ?>
</div>

<div class="row justify-content-center">

  <?= $content ?>

</div>

<!-- JS dependencies -->
<script src="/assets/jquery/dist/jquery.slim.js"></script>

<!-- Bootstrap 4 dependencies -->
<script src="/assets/popper.js/dist/umd/popper.js"></script>
<script src="/assets/bootstrap/dist/js/bootstrap.js"></script>

<!-- select2 dependencies -->
<script src="/assets/select2/dist/js/select2.min.js"></script>

<!-- bootbox dependencies -->
<script src="/assets/bootbox/dist/bootbox.all.min.js"></script>

<!-- Fontawesome dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/all.min.js"
        integrity="sha512-YSdqvJoZr83hj76AIVdOcvLWYMWzy6sJyIMic2aQz5kh2bPTd9dzY3NtdeEAzPp/PhgZqr4aJObB3ym/vsItMg=="
        crossorigin="anonymous">
</script>

<script src="/scripts/utils.js"></script>

<?php if (!empty($script)) : ?>
<script>
  <?php include $script ?>
</script>
<?php endif; ?>

<script>
  $(() => {
    select2();
    if (typeof onDocumentReady === 'function') onDocumentReady();
  })
</script>

</body>
</html>