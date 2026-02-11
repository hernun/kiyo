<?php if(nqv::userCan(['crud','images-resize'])): ?>
    <main>
        <div class="container my-5">
            <?php
                $stmt = nqvDb::prepare('SELECT * FROM `mainimages` WHERE id > 0');
                $images = nqvDb::parseSelect($stmt);
                $th = 0;
                foreach($images as $image) {
                    $file = ROOT_PATH . $image['filepath'];
                    if(is_file($file)) {
                        $im = nqvMainImages::getImageByPath($file);
                        if(!$im->exists()) continue;
                        if(is_file($im->getThumbnailPath())) continue;

                        echo '<br>ID: '. $image['id'] .'<br>';
                        echo 'Main path: '. $file .'<br>';

                        if($im->hasJpegExtension()) {
                            echo 'Main path fixed: '. $file .'<br>';
                             $im->fixJpegExtension();
                        }
                        else $im->createThumbnail();

                        echo '<br>Element: '. $image['tablename'] .' '. $image['element_id'] .'<br>';
                        echo '<br/><img src="/images/'.$image['tablename'].'/'.$image['element_id'].'/thumbnail" /><br/>';
                        $th++;
                    }
                }
                if($th == 1) echo '<h5 class="my-5">Se creó una miniatura.</h5>';
                elseif($th) echo '<h5 class="my-5">Se crearon ' . $th . ' miniaturas.</h5>';
                else echo '<h5>No se crearon miniaturas</h5>';
            ?>
        </div>
    </main>
<?php endif?>