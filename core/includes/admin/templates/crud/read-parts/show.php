<?php
$tablename = nqv::getVars(1);
$id = nqv::getVars(3);
nqv::setAccess(['read',$tablename]);
$table = new nqvDbTable($tablename);

$sql = 'SELECT * FROM ' . $tablename . ' WHERE id = ? LIMIT 1';
$stmt = nqvDB::prepare($sql);
$stmt->bind_param('i',$id);
$result = nqvDB::parseSelect($stmt);
$item = @$result[0];

if(empty($item)) {
    header('location:/admin/' . $tablename);
    exit;
}

$obj = new nqvElement($table,$item);
$image = nqvMainImages::getByElementId($tablename,$id);
?>
<?php if($tablename === 'users' && (!currentSessionTypeIs('root') && !currentSessionTypeIs('admin'))): ?>
    <?php if(nqv::getCurrentUserId() !== $item['id']):?>
        <main>
            <div class="my-4 center-center container">
                <h2>No tenés permiso para ver este elemento</h2>
            </div>
        </main>
        <?php return?>
    <?php endif?>
<?php endif ?>
<main id="show-template" class="px-4">
    <div class="my-4 container">
        <h1><?php echo $obj?></h1>
        <div class="p-4 ps-0">
            <img style="width:150px" src="<?php echo $image->getSrc()?>">
        </div>
        <?php if($tablename === 'pages'):?>
            <div class="p-4 ps-0">
                <?php $url = 'https://' . DOMAIN . '/' . $item['slug']?>
                <div class="single-page-url"><a href="<?php echo $url;?>" target="_blank"><?php echo $url;?></a></div>
            </div>
        <?php endif?>
        <?php foreach($item as $k => $v):?>
            <?php if($k === 'password') continue?>
            <?php 
                $field = $table->getField($k);
                $field->setValue($v);
                $v = $field->getHumanValue();
            ?>
            <div class="">
                <span class="label fw-bold"><?php echo ucfirst(nqv::translate($k,'es','label'))?>:</span>
                <?php if(userIs('root')):?>
                    <span class="label fw-normal"><small>(<?php echo $k?>)</small></span>
                <?php endif?>
                <span><?php echo nqv::translate((string) $v,'es')?></span>
            </div>
        <?php endforeach?>
        <?php if($tablename === 'activities' || $tablename === 'holders') getImageGallery($tablename,$id);?>
        <div class="my-4">
            <a class="btn btn-sm btn-primary" href="<?php echo getAdminUrl() . $tablename ?>/edit/<?php echo $id?>">Editar</a>
            <?php if($tablename === 'inscriptions'):?>
                <?php $inscription = new nqvInscription(['id'=>$id])?>
                <?php if($inscription->isReady()):?>
                    <a class="btn btn-sm btn-success" href="<?php echo getAdminUrl() . $tablename ?>/publish/<?php echo $id?>">Publicar</a>
                <?php endif?>
            <?php endif?>
        </div>
    </div>
</main>