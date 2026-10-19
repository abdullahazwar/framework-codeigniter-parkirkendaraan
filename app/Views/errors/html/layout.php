<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($errorTitle) ?> | Parkir</title>
    <link rel="stylesheet" href="<?= esc(rtrim(config('App')->baseURL, '/')) ?>/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= esc(rtrim(config('App')->baseURL, '/')) ?>/assets/css/style.css">
</head>
<body class="error-page">
    <main class="panel error-card">
        <span class="brand-mark" aria-hidden="true">P</span>
        <p class="eyebrow">STATUS <?= (int) $errorCode ?></p>
        <h1><?= esc($errorTitle) ?></h1>
        <p class="text-muted"><?= esc($errorMessage) ?></p>
        <a class="btn btn-primary" href="<?= esc(rtrim(config('App')->baseURL, '/')) ?>/dashboard">Kembali ke dashboard</a>
    </main>
</body>
</html>
