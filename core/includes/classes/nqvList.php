<?php

class nqvList {
    protected $type = 'ArrayInput';
    protected $elements = [];
    protected $fields = [];
    protected string $tablename = '';
    protected $dbTable = null;
    protected $tablenameLabel = '';
    protected $from_db = [];
    protected $classname;
    protected $conditions = [];
    protected $highlights;
    protected $criteria;
    protected $dbname;
    protected $filterArray = [];
    protected $count;
    protected $orderers = [];
    protected $name = '';
    protected $singularName = '';
    protected $templatesPath = '';
    protected $total;
    protected $pagenumber = null;
    protected $showsearch = true;

    public function __construct($tablename) {
        $this->tablename = $tablename;
        $this->count = intval(nqv::getConfig('listcount'));
        $dirname = dirname(__FILE__);
        $this->templatesPath = ADMIN_TEMPLATES_PATH;
    }

    public function getNavigator(?int $num = 0) {
        if (empty($this->elements)) $this->query();
        $totalpages = ceil($this->count() / $this->getCount());
        //$pivot = ceil($totalpages /2);
        $pivot = 16;
        $ellipsis = false;
        $prellipsis = false;
        $html = '<div class="navigator">';
        if($totalpages > $pivot) {
            $pages = $pivot;
            $ellipsis = true;
        } else {
            $pages = $totalpages;
            $pivot = 0;
        }
        $current = $this->getPagenumber();
        if($ellipsis && $current > ceil($pivot / 2) + 1) $prellipsis = true;
        if($current > 1) {
            $html .= '<div class="navigator-button">';
            $html .= '<a href="' . nqv::getQuery(['page' => 1]) . '"><<</a>';
            $html .= '</div>';
            if($prellipsis) $html .= '<span class="navigator-ellipsis mx-2"> ... </span>';
        }
        if ($totalpages >= $current) {
            $init = $current > ceil($pivot / 2) ? $current - ceil($pivot / 2):1;
            if($pivot) {
                if($current >= ceil($totalpages - ($pivot / 2)) ) {
                    $init = $totalpages - $pivot;
                    $end = $totalpages;
                    $ellipsis = false;
                } else {
                    $end = $init + $pages;
                }
            } else {
                $init = 1;
                $end = $totalpages;
            }
            for ($i = $init; $i <= $end; $i++) {
                $p = $i;
                if (intval($current) === intval($p)) {
                    $html .= '<div class="navigator-number">' . $p . '</div>';
                } else {
                    $html .= '<div class="navigator-button">';
                    $html .= '<a href="' . nqv::getQuery(['page'=>$p]) . '">' . $p . '</a></div>';
                }
            }
        }
        if(!empty($ellipsis)) $html .= '<span class="navigator-ellipsis mx-2"> ... </span>';
        if ($totalpages > $current +1) {
            $html .= '<div class="navigator-button">';
            $html .= '<a href="' . nqv::getQuery(['page'=>$totalpages]) . '">>></a>';
            $html .= '</div>';
        }
        $html .= '</div>';
        echo $html;
    }

    protected function getCount() {
        return $this->count;
    }

    public function setCount(int $int): void {
        $this->count = $int;
    }

    public function getElements() {
        try {
            if (empty($this->elements)) $this->query();
            return $this->elements;
        } catch(Exception $e) {
            throw new Exception(basename($e->getFile()) . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    protected function getDbTable(): nqvDbTable {
        if(empty($this->dbTable))  $this->dbTable = new nqvDbTable($this->tablename);
        return $this->dbTable;
    }

    public function getPage(?int $num = 0): array {
        try {
            return (array) $this->getElements();
        } catch(Exception $e) {
            my_print(basename($e->getFile()) . ' ' . $e->getLine() . ' ' . $e->getMessage());
            exit;
        }
    }

    public function count() {
        if(empty($this->total)) {
            $stmt = nqvDB::prepare('SELECT COUNT(id) as count FROM ' . $this->getTablename());
            $this->total = @nqvDB::parseSelect($stmt)[0]['count'];
        }
        return $this->total;
    }

    protected function parseFilters(): void {
        if(isset($this->conditions['filter'])) {
            $json = json_decode(base64_decode($this->conditions['filter']),true);
            unset($this->conditions['filter']);
            foreach($json as $k => $v) {
                $key = explode('|',$k)[0];
                $this->conditions[$key] = $v;
            }
        }
    }

    protected function query(): void {
        try {
            if (!empty($this->tablename)) {
                $conds = $this->getConditions();
                if(isset($conds['limit'])) {
                    $limit = $conds['limit'];
                    unset($conds['limit']);
                }
                $this->elements = nqv::get($this->tablename,$conds,$limit);
            }
        } catch(Exception $e) {
            throw new Exception(basename($e->getFile()) . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }

    public function setConditions(array $conds): void {
        $this->conditions = $conds;
    }

    protected function getPagenumber() {
        if($this->pagenumber === null) {
            if(@$_GET['page'] === 'last') $this->pagenumber = 'last';
            else $this->pagenumber = !empty($_GET['page']) ? intval($_GET['page']) : 1;
        }
        return $this->pagenumber;
    }

    public function getConditions() {
        $this->parseFilters();
        $this->conditions['limit'] = [($this->getPagenumber() - 1) *  $this->getCount() , $this->getCount()];
        return $this->conditions;
    }

    public function addCondition($key,$value) {
        $this->conditions[$key] = $value;
    }

    public function setElements(array $data): void {
        $this->elements = $data;
    }

    public function getClassname() {
        return $this->classname;
    }

    public function getTablename() {
        return $this->tablename;
    }

    public function getTablenameLabel () {
        return !empty($this->tablenameLabel) ? $this->tablenameLabel:$this->getTablename();
    }

    protected function getTableFieldsObjects(): array {
        try {
            $o = [];
            $fieldnames = nqvDB::getFieldNames($this->getTablename());
            foreach($fieldnames as $fieldname) {
                $o[$fieldname] = new nqvDbField($fieldname, $this->getTablename());
            }
           return $o;
        } catch (Exception $e) {
            throw new Exception($e->getMessage() . ' ' . basename(__FILE__) . ' ' . __LINE__);
            return [];
        }
    }

    public function setFields(?array $fields = []) {
        try {
            $c = $this->getClassname();
            if(class_exists($c)) $this->fields = $this->getTableFieldsObjects();
            else nqvNotifications::add('La clase ' . $c . ' no existe', 'error');
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getFields(): array {
        if (empty($this->fields)) $this->setFields();
        return $this->fields;
    }

    public function filterColumns(array $keys, ?string $action = 'deny'): void {
        try {
            if (empty($this->fields)) $this->setFields();
            $this->fields = array_filter(
                $this->fields,
                function ($key) use ($keys, $action) {
                    if ($action === 'deny') return !in_array($key, $keys);
                    else return in_array($key, $keys);
                },
                ARRAY_FILTER_USE_KEY
            );
            if ($action === 'allow') $this->fields = array_merge(array_flip($keys), $this->fields);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function filter(array $criteria): void {
    }

    public function highlightCell($keys): void {
        $fields = $this->getFields();
        foreach ((array)$keys as $key) {
            if (!empty($fields[$key])) $fields[$key]->setCssClasses('highlight');
            $this->highlights[] = $key;
        }
    }

    public function getMainFieldName() {
        return $this->classname::getStaticMainField();
    }

    protected function getCurrentFilters() {
        return json_decode(base64_decode((string) @$_GET['filter']),true);
    }
    
    public function getFilters(): array {
        return $this->filterArray;
    }

    public function setFilters(array $filters): nqvList {
        $this->filterArray = $filters;
        return $this;
    }

    public function addFilters(array $filters): nqvList {
        $this->filterArray = array_merge($this->filterArray,$filters);
        return $this;
    }

    public function getOrderers(): array {
        return $this->orderers ;
    }

    public function setOrderers(array $orderers): void {
        $this->orderers = $orderers;
    }

    public function addOrderers(array $orderers): void {
        $this->orderers = array_merge($this->orderers, $orderers);
    }

    public function removeOrderers(): void {
        $this->orderers = [];
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setSingularName($name) {
        $this->singularName = $name;
    }

    public function getName() {
        if(empty($this->name)) $this->setName(nqv::translate($this->getTablenameLabel(),'es','plural'));
        return $this->name;
    }

    public function getSingularName() {
        if(empty($this->singularName)) $this->singularName = nqv::translate($this->getTablenameLabel(),'es','singular');
        return $this->singularName;
    }

    public function getTitle() {
        return $this->getName();
    }

    public function getSubTitle() {
        if($this->count() === 1) $title = $this->getSingularName();
        else $title = $this->getName();
        return 'Hay ' . $this->count() . ' ' . $title;
    }

    public function getNewButton() {
        return '<a href="' . getAdminUrl() . $this->getTablename() . '/new"><span class="btn btn-sm btn-success">+</span></a>';
    }

    public function getRemoveButton() {
        return '<a href="' . getAdminUrl() . $this->getTablename() . '/remove"><span class="btn btn-sm btn-danger"><i class="fa-solid fa-trash-can"></i></span></a>';
    }

    protected function getCurrentFilterLabel() {
        return '';
    }

    public function getHeader($showsearch = true) {
        $this->showsearch = $showsearch;
        ob_start();
        include($this->templatesPath . 'crud/read-parts/listheader.php');
        return ob_get_clean();
    }

    public function getTableHead(?array $options = []) {
        ob_start();
        include($this->templatesPath . 'crud/read-parts/listtableheaders.php');
        return ob_get_clean();
    }

    public function getTableBody(?array $options = []) {
        ob_start();
        include($this->templatesPath . 'crud/read-parts/listtablebody.php');
        return ob_get_clean();
    }
}
