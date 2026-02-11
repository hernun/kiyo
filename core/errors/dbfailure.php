<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php include_template('favicon')?>
  <title><?php echo APP_TITLE?> - Error en la DB</title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      background-color: #fff;
      color: #000; 
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center; /* Centrado horizontal */
      align-items: center;     /* Centrado vertical */
      text-align: center;
    }
    .container {
      max-width: 600px;
      padding: 20px;
    }
    h3 {
        font-size:1rem;
        text-transform:uppercase;
    }
    p {
        font-size:.9rem;
        text-transform:uppercase;
        margin-top: 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <p>Error en la DB | Faltan las constantes de entorno.</p>
  </div>
</body>
</html>
