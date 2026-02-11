<div class="main-info">
    <div>
        <div class="list-title d-flex justify-content-start align-items-center gap-3">
            <div class="button-new-item"><?php echo $this->getNewButton()?></div>
            <div class="button-new-item"><?php echo $this->getRemoveButton()?></div>
            <div class="title-container">
                <span class="title"><a href="<?php echo getAdminUrl()?>database/<?php echo $this->getTablename()?>"><?php echo $this->getTitle()?></a></span> <span class="separator">|</span> <span class="subtitle"><?php echo $this->getSubTitle()?>
                <?php if($fl = $this->getCurrentFilterLabel()) echo ' <span class="separator">|</span> ' .$fl?></span>
                <div>
                    <span class="disc-space">Hay <?php echo formatBytes(disk_free_space("/"));?> disponibles</span> |
                    <span class="disc-space"><a href="/admin/add-tags/<?php echo $this->getTablename()?>">Agregar tags</a></span>
                </div>
            </div>
        </div>
    </div>
    
    <?php $filters = $this->getFilters() ?>
    <?php if(!empty($filters)):?>
        <div class="dorpdowns p-4">
            <?php $currentFilters = $this->getCurrentFilters() ?>
            <?php if(!empty($filters) || !empty($_GET['filter'])): ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        Filtrar por
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="<?php echo nqv::rmQuery('filter')?>"><small>LIMPIAR FILTRO</small></a></li>
                        <?php foreach($filters as $f):?>
                            <?php 
                            $key = key($f['input']);
                            if(isset($currentFilters[$key])) {
                                $class = 'minus';
                                $f['rm'] = $key;
                            } else {
                                $class = 'plus';
                            }
                            ?>
                            <li><a class="dropdown-item <?php echo $class?>" href="<?php echo nqv::getQuery(['filter'=>$this->getFilterString($f['input'],$f['rm'])])?>"><?php echo $f['label']?></a></li>
                        <?php endforeach?>
                    </ul>
                </div>
            <?php endif?>
        </div>
    <?php endif?>
</div>