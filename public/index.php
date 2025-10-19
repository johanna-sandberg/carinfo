<?php
?>
<!doctype html>
<html lang="sv">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Car.info PHP Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-3">Sök bilar</h1>
    <form id="f" class="row g-2 mb-3">
      <div class="col-sm-4">
        <input class="form-control" name="brand" placeholder="Märke ex. Volvo">
      </div>
      <div class="col-sm-3">
        <input class="form-control" name="model_year" placeholder="Modellår ex. 2018" inputmode="numeric">
      </div>
      <div class="col-sm-3">
        <input class="form-control" name="reg" placeholder="Regnummer prefix">
      </div>
      <div class="col-sm-2 d-grid">
        <button class="btn btn-primary">Sök</button>
      </div>
    </form>
    <div id="results" class="row row-cols-1 g-2"></div>
    <div id="pager" class="d-flex gap-2 mt-3" style="display:none">
      <button id="prev" class="btn btn-outline-secondary">Föregående</button>
      <button id="next" class="btn btn-outline-secondary">Nästa</button>
    </div>
  </div>
  <script src="./app.js"></script>
</body>

</html>
