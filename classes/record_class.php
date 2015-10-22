<?php

define ('ERROR_EMPTY', 1) ;
define ('ERROR_EXISTS', 2) ;

class record  {

    public $dsp;
    public $__tablename__  = '';
    public $errors = array();

    protected $table_structure = array();

    public function record()
    {
        global $dsp;

        if ($this->__tablename__ == '') $this->__tablename__ = strtolower(get_class($this));

        $this->dsp = $dsp;

        $this->init();
    }

    protected function init() { }

    public function GetAll() {
       $items = $this->dsp->db->Select("SELECT * FROM `".$this->__tablename__."`");

       return $items;
    } // GetAll()

    public function GetItem($key, $fields = '*') {

        $item = $this->dsp->db->SelectRow("SELECT ".$fields." FROM `".$this->__tablename__."` WHERE `id` = ?", $key);

        return $item;
    } // GetItem()

    protected function tableStructure() { }

    protected function checkUpdate($item) { }

    public function deleteItem($id)
    {
        $this->dsp->db->Execute("delete from `".$this->__tablename__."` where `id` = ?", $id);
    }
}

?>