<?php

class usersadmin extends Record {

    private static $ptrn_login = "/^[a-zA-Z0-9_-]+$/i";
    private static $ptrn_pass  = "/^[a-zA-Z0-9_-]+$/i";

    function beforeAdd($item) {
        if (!empty($item['pass'])) {
            $this->dsp->authadmin->SetTempParam('pass_value', $item['pass'] . $item['salt']);
        }
        unset($item['pass']);
        
        if (!empty($item['login'])) {
            $item['login'] = strtolower($item['login']);
        }
        
        return $item;
    } // beforeAdd()

    function afterAdd($item) {
        if ($this->dsp->authadmin->IsTempParamSet('pass_value')) {
            $this->dsp->db->Execute(
              "UPDATE `{$this->__tablename__}` SET `pass` = SHA1(?) WHERE `id` = ?",
              $this->dsp->authadmin->RetriveTempParam('pass_value'), $item['id']
            );
        }
        
        return $item;
    } // afterAdd()

    function beforeEdit($key, $item) { 
        return $this->beforeAdd($item);
    } // beforeEdit()

    function afterEdit($item) {
        $item = $this->afterAdd($item);
        if ($this->dsp->authadmin->IsLogged() && $item['id'] == $this->dsp->authadmin->user['id']) {
            $this->dsp->authadmin->user = $item;
        }
        return $item;
    } // afterEdit()

    function GetByLoginPass($login, $pass) {
        $salt = $this->dsp->db->SelectRow(
          "SELECT `salt` FROM {$this->__tablename__} WHERE `login` = ?", strtolower($login)
        );
        if (0 == count($salt)) {
            return array();
        }
        $salt = $salt["salt"];
        if (!preg_match(self::$ptrn_login, $login) || !preg_match(self::$ptrn_pass, $pass)) {
            return Array();
        }
        $result = $this->dsp->db->SelectRow(
          "SELECT * FROM {$this->__tablename__} WHERE `login` = ? AND `pass` = SHA1(?)",
          strtolower($login), ($pass.$salt)
        );
        return $result;
    } // GetByLoginPass()

    function SetLastVisit($user_id) {
        $now = date('Y-m-d H:i:s');
        $this->dsp->db->Execute("update `".$this->__tablename__."` set `lastvisit` = ?, `lastaccess` = ? where `id` = ?", $now, $now, $user_id);
    } // SetLastVisit

    function SetLastAccess($user_id) {
        $now = date('Y-m-d H:i:s');
        $this->dsp->db->Execute("update `".$this->__tablename__."` set `lastaccess` = ? where `id` = ?", $now, $user_id);
    } // SetLastAccess

    function IsExist($login) {
        if (!preg_match(self::$ptrn_login, $login)) {
            return false;
        }
        $result = $this->GetByCause(array('login' => strtolower($login)));
        if (empty($result)) {
            return false;
        }
        
        $result = reset($result);
        return $result['id'];
    } // IsExist()

    function afterGet($item) {

//if (!$this->dsp->Init('users_roles_admin_admin')) echo "false";

        if (is_callable(array('parent', 'afterGet'))) {
            $item = parent::afterGet($item);
        }

//        $item['roles_direct'] = $this->dsp->users_roles_admin_admin->GetItem($item['id']);
//        $item['roles_group'] = $this->dsp->groups_roles_admin_admin->GetItem($item['id']);
        
        return $item;
    } // afterGet()
         
} // class Users_admin

?>
