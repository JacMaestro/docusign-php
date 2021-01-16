<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docusign Implementation</title>
    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <link href="./assets/css.css" rel="stylesheet">
</head>

<body>

  <div class="container-full-bg">

  <?php if (isset($_SESSION['flash']) && count($_SESSION['flash']) > 0): ?>
    <div class="container">
      <h4>Troubleshooting</h4>
      <ul>
        <?php foreach ($_SESSION['flash'] as $key => $value): ?>
          <li><?php echo $value; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php $_SESSION['flash'] = []; ?>
  <?php endif; ?>

  <div class="container">
      <?= $contenu ?>
  </div>
  </div>


    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js" integrity="sha256-/ijcOLwFf26xEYAjW75FizKVo5tnTYiQddPZoLUHHZ8=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.core.min.js" integrity="sha256-yEkk5ZYVs/fZgvxWU+sCb8bHTk9jScxIaZQD+CZ4vcg=" crossorigin="anonymous"></script>

</body>

</html>
