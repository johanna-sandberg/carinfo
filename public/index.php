<?php ?>
<!doctype html>
<html lang="sv" data-bs-theme="dark">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Car Info PHP Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="./style.css" rel="stylesheet">
</head>

<body>
  <div class="container py-4">
    <h1 class="mb-3 text-center">Sök bilar</h1>

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
      <div id="meta" class="text-muted small"></div>
      <div class="ms-auto d-flex gap-2" id="pager" style="display:none">
        <button id="prev" class="btn btn-outline-light btn-sm">Föregående</button>
        <button id="next" class="btn btn-outline-light btn-sm">Nästa</button>
      </div>
    </div>

    <div id="results" class="row row-cols-1 g-2"></div>
  </div>
  <script src="./app.js"></script>
</body>

</html>
