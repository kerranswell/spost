<?php

class structure {

    public $tree = array('id' => array(), 'pid' => array());
    public $table = '';

    protected $sort = '';
    protected $fields = '';

    public function structure()
    {
        $this->setSort("`order` asc");
        $this->setFields("`id`, `pid`, `title`, `translit`");
    }

    public function getStructure()
    {
        $sql = $this->getSql();
//        $this->
    }

    # sorting
    protected function getSort()
    {
        return $this->sort;
    }

    protected function setSort($s)
    {
        if ($s != '') $this->sort = 'order by '.$s;
        else $this->sort = '';
    }

    # fields
    protected function getFields()
    {
        return $this->fields;
    }

    protected function setFields($f)
    {
        if ($f != '') $this->fields = $f;
        else $this->fields = '*';
    }

    # wheres
    protected function getWheres()
    {
        return $this->wheres;
    }

    protected function setWheres($w)
    {
        if (is_array($w)) $this->wheres = "where (".implode(") and (", $w).")";
        else $this->wheres = '';
    }

    protected function getSql()
    {
        return "select ".$this->getFields()." from ".$this->table." ".$this->getWheres()." ".$this->getSort();
    }
}

?>