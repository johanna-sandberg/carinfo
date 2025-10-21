<?php ?>
<!doctype html>
<html lang="sv">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Car.info PHP Assignment</title>

  <script>
    (function() {
      const key = 'theme';
      const stored = localStorage.getItem(key);
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const initial = stored === 'light' || stored === 'dark' ? stored : (prefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', initial);
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-body text-body">
  <div class="container py-4">
    <div class="d-flex align-items-center mb-3">
      <h1 class="m-0">Sök bilar</h1>
      <div class="ms-auto d-flex align-items-center gap-2">
        <label class="form-check form-switch m-0">
          <input id="themeToggle" class="form-check-input" type="checkbox">
          <span class="ms-2 small" id="themeLabel">Mörkt</span>
        </label>
      </div>
    </div>

    <form id="f" class="row g-2 mb-3">
      <div class="col-sm-4">
        <input class="form-control" name="brand" placeholder="Märke ex. Volvo" autocomplete="off">
      </div>
      <div class="col-sm-3">
        <input class="form-control" name="model_year" placeholder="Modellår ex. 2018" inputmode="numeric" autocomplete="off">
      </div>
      <div class="col-sm-3">
        <input class="form-control" name="reg" placeholder="Regnummer prefix" autocomplete="off">
      </div>
      <div class="col-sm-2 d-grid">
        <button class="btn btn-primary">Sök</button>
      </div>
    </form>

    <div class="d-flex align-items-center mb-2">
      <div id="meta" class="small text-secondary"></div>
      <div class="ms-auto d-flex gap-2" id="pager" style="display:none">
        <button id="prev" class="btn btn-outline-secondary btn-sm">Föregående</button>
        <button id="next" class="btn btn-outline-secondary btn-sm">Nästa</button>
      </div>
    </div>

    <div id="results" class="row row-cols-1 g-2"></div>
  </div>

  <script src="./theme.js"></script>
  <script src="./app.js"></script>
</body>

</html>
