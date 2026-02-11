<div class="my-5">
    <h2>Crear tablas a partir de ficheros cvs</h2>
    <form method="post" id="db-file-upload" accept-charset="utf8" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo get_token('db-file-upload',false)?>" name="form-token" />
        <div class="mb-3">
            <label for="datafile-input" class="form-label">Selecciomá un archivo sql o csv:</label>
            <input class="form-control" type="file" id="datafile-input" name="datafile">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary mb-3">Enviar</button>
        </div>
    </form>
</div>