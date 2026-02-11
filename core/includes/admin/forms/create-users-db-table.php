<?php 
if(nqvDB::isTable('users')) return null;
if(submitted('create-users-db-table')) {
    createUsersDbTable();
}
?>
<div class="my-2">
    <form method="post" id="create-users-db-table" accept-charset="utf8" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo get_token('create-users-db-table',false)?>" name="form-token" />
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary mb-3">Crear tabla de usuarios</button>
        </div>
    </form>
</div>