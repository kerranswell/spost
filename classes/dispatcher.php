<?php

class Dispatcher {
    var $installed = array();
    var $aliaces = array('db' => 'database');

    /**
     * Инстанс объекта
     *
     * @var Dispatcher
     */
    private static $instance;

    /**
     * Constructor
     */
    function Dispatcher() {
        // No implementation required
    } // Dispatcher()

    /**
     * Destructor
     */
    function _Dispatcher() {
        foreach ($this->installed as $className => $obj) {
            call_user_func(array($obj, "_".$className));
        }
    } // _Dispatcher()

    /**
     * Class initializer
     * @param $className
     *          The name of the Class to initialize
     * @param array $params
     *          Advanced params, passed to Init method of the Class
     * @return bool
     *          Status
     */
    function Init($className, $params = array()) {

        // Returns from function if class with className already initialized
        if (isset($this->installed[$className]) ||
                (!empty($this->aliaces[$className]) && isset($this->installed[$this->aliaces[$className]]))) {
            return true;
        }

        // Translate className to realClassName
        if (!isset($class_file_name)) {
            $class_file_name = CLASS_DIR . strtolower($className) . "_class.php";
            if (is_file($class_file_name)) {
                $realClassName = $className;
            } elseif (!empty($this->aliaces[$className])) {
                $class_file_name = CLASS_DIR . strtolower($this->aliaces[$className]) . "_class.php";
                if (is_file($class_file_name)) {
                    $className = $this->aliaces[$className];
                    $realClassName = $className;
                }
            }
        }
        if (!isset($class_file_name)) {
            return false;
        }
        if (!is_file($class_file_name)) {
            $this->installed[$className] = null;
            return false;
        }

        // Initialize class
        require_once($class_file_name);

        /** @var $realClassName */
        $this->$className = new $realClassName();
        $this->installed[$className] = &$this->$className;
        if (is_callable(array($this->installed[$className], "SetDSP"))) {
            call_user_func_array(array($this->installed[$className], "SetDSP"), array(&$this));
        } else {
            $this->installed[$className]->dsp = &$this;
        }
        if (is_callable(array($this->installed[$className], "Init"))) {
            call_user_func(array($this->installed[$className], "Init"), $params);
        }
        return $this->installed[$className];
    } // Init()

    /**
     * Destroyer
     * @param $className
     *          Name of the Class whose objects being destroyed
     */
    function Destroy($className) {
        if (!isset($this->installed[$className])) {
            return;
        }
        call_user_func(array($this->installed[$className], "__destruct"));
        unset($this->installed[$className]);
    } // Destroy()

    /**
     * System method: getter
     * @param $name
     *          Name of the Class whose object being returned
     * @return object|null
     */
    function __get($name) {
        $this->Init($name);

        if (isset($this->installed[$name])) {
            return $this->installed[$name];
        } elseif (!empty($this->aliaces[$name]) && isset($this->installed[$this->aliaces[$name]])) {
            return $this->installed[$this->aliaces[$name]];
        }
        return null;
    } // __get()

    /**
     * Возвращает объект себя
     *
     * @return Dispatcher
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

} // class Dispatcher
?>