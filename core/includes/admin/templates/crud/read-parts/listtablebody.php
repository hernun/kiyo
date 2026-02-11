<?php
$mainfield =  $this->getDbTable()->getMainFieldName();
$include = ['id','mainfield'];
$exclude = [];
if(!empty($options)) {
    $include = array_merge($include, $options['include']);
    $exclude = (array) @$options['exclude'];
}
$data = $this->getElements();
$fields = array_map(function($item) use ($include, $mainfield){
    $output = $item;
    foreach($include as $key) {
        if($key === 'mainfield') {
            if(!is_array($mainfield)) $output[$mainfield] = $item[$mainfield];
        }
        if(!key_exists($key,$item)) continue;
        $output[$key] = $item[$key];
    }
    return $output;
},$data);
?>
<tbody>
    <?php foreach($fields as $row): ?>
        <tr>
            <td><?php echo $row['id']?></td>
            <?php if(!in_array('mainfield',$exclude)):?>
                <?php if(is_array($mainfield)):?>
                    <?php 
                        $vs = [];
                        foreach($mainfield as $l) {
                            $field = $this->getDbTable()->getField($l);
                            if(!empty($field)) $vs[] = $field->setValue($row[$l])->getHumanValue();
                        }
                    ?>
                    <td><?php echo implode(' + ', $vs)?></td>
                <?php else:?>
                    <td>
                        <?php 
                            $field = $this->getDbTable()->getField($mainfield);
                            if(!empty($field)) echo $field->setValue($row[$mainfield])->getHumanValue();
                        ?>
                    </td>
                <?php endif?>
            <?php endif?> 
            <?php foreach($row as $k => $v):?>
                <?php if($k === 'id') continue?>
                <?php if(!empty($exclude) && in_array($k,$exclude)) continue;?>
                <?php 
                    $field = $this->getDbTable()->getField($k);
                    if(!empty($field)) $value = $field->setValue($v)->getHumanValue();
                    else $value = '';
                ?>
                <?php if($k === 'password') continue?>
                <?php $val = str_split((string) @$value,75)?>
                <td><?php echo count($val) > 1 ? $val[0] . ' ...':$value?></td>
            <?php endforeach?>
            <td class="action action-show"><a href="<?php echo getAdminUrl()?><?php echo $this->getTablename()?>/show/<?php echo $row['id']?>">Ver más</a></td>
            <td class="action action-edit"><a href="<?php echo getAdminUrl()?><?php echo $this->getTablename()?>/edit/<?php echo $row['id']?>">Editar</a></td>
            <td class="action action-delete"><a href="<?php echo getAdminUrl()?><?php echo $this->getTablename()?>/delete/<?php echo $row['id']?>">Borrar</a></td>
        </tr>
    <?php endforeach?>
</tbody>