<?php if ($message = session()->getFlashdata("error")): ?>
<div class="alert alert-danger" role="alert"><?= esc($message) ?></div>
<?php endif; ?>
<?php if ($message = session()->getFlashdata("success")): ?>
<div class="alert alert-success" role="status"><?= esc($message) ?></div>
<?php endif; ?>
