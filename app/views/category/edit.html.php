<?php

?>

<div class="col-md-4 mt-2 mb-2">
  <form method="post">

    <?= $form->render($category) ?>
    <?= $form->renderButtons($category) ?>
    <?= $form->addCSRFToken() ?>

  </form>

</div>
