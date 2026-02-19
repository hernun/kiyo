<?php if(nqv::userCan(['crud','images-resize'])): ?>
    <div class="container my-5">
        <?php
            $output = [];
            $stmt = nqvDb::prepare('SELECT * FROM `mainimages` WHERE id > 0');
            $images = nqvDb::parseSelect($stmt);
            $th = 0;
            foreach($images as $data) {
                $im = new nqvMainImages($data);
                $file = $im->getBaseFilepath();;
                if($im->exists() && is_file($file)) {
                    $thumbnailSizes = $im->getThumbnailsSizes();
                    foreach($thumbnailSizes as $size) {
                        $thumb = $im->getThumbnailPath($size['size'], $size['crop']);
                        if(is_file($thumb)) continue;
                        echo '<br>ID: '. $image['id'] .'<br>';
                        echo 'Main path: '. $file .'<br>';

                        if($im->hasJpegExtension()) {
                            echo 'Main path fixed: '. $file .'<br>';
                            $im->fixJpegExtension();
                        }
                        else $im->createThumbnail($size['size'], $size['crop']);

                        $output[] = '<br>Element: '. $image['tablename'] .' '. $image['element_id'] .'<br>';
                        $output[] = '<br/><img src="/images/'.$image['tablename'].'/'.$image['element_id'].'/thumbnail" /><br/>';
                        $th++;
                    }
                }
            }
            if($th == 1) echo '<p class="fs-5 my-5">Se creó una miniatura.</p>';
            elseif($th) echo '<p class="fs-5 my-5">Se crearon ' . $th . ' miniaturas.</p>';
            else echo '<p class="fs-5">No se crearon miniaturas</p>';

            echo '<div class="container">' . implode('<br/>',$output) . '</div>';
        ?>
    </div>
<?php endif?>