<?php
    class Authadmin {
        var $user = null;
        var $temp_vars = array();
        var $user_id = null;
        
        function Authadmin() {
            @session_start();
        } // Auth()
        
        
        function _Authadmin() {
        } // _Auth()
        
        
        function Init() {
            if ($this->IsParamSet('user')) {
                $this->user = $this->GetParam('user');

                $user = $this->dsp->usersadmin->GetItem($this->user['id']);
                $this->user_id = $this->user['id'];

                if (!empty($user)) {
                    $this->dsp->usersadmin->SetLastAccess($this->user['id']);
                } else {
                    $this->ClearParam('user');
                    $this->user = null;
                }
                
            }

            $this->SetParam('lastaccess', time());
        } // Init()


        function Login($login, $password) {
            $this->user = $this->dsp->usersadmin->GetByLoginPass($login, $password);
            if ($this->user) {
                $this->SetParam('lastvisitbefore', $this->user['lastvisit']);
                $this->SetParam('lastaccessbefore', $this->user['lastaccess']);
                $this->Authorize($this->user);
                return true;
            }
            
            return false;
        } // Login() 


        function Authorize($user) {
            if ($user) {
                $this->user = $user;
                $this->dsp->usersadmin->SetLastVisit($this->user['id']);
                $now = time();
                $this->SetParam('user', $this->user);
                $this->SetParam('lastvisit', $now);
                $this->SetParam('lastaccess', $now);
                // var_dump($user);
            }
        } //Authorize()
        
        
        function IsLogged() {
            return (!empty($this->user));
        } // isAuthorized()
        
        
        function Logout() {
            $this->user = null;
            session_destroy();
        } // Logout()


        function LoginForURL($url) {
            $this->SetParam('login_redirect', $url);
            Redirect('/login');
        } // LoginForURL()


        function GetParam($param_name) {
            if (isset($_SESSION[$param_name])) {
                return unserialize($_SESSION[$param_name]);
            } else {
                return false;
            }
        } // GetParam()
	
	
	    function SetParam($param_name, $value) {
            $_SESSION[$param_name] = serialize($value);
        } // SetParam()
        
        
        function ClearParam($param_name) {
            if (isset($_SESSION[$param_name])) {
                unset($_SESSION[$param_name]);
            }
        } // ClearParam()
        
        
        function RetriveParam($param_name) {
            $result = $this->GetParam($param_name);
            $this->ClearParam($param_name);
            
            return $result;
        } // RetriveParam()
        
        
        function IsParamSet($param_name) {
            return (isset($_SESSION[$param_name]));
        } // IsParamSet()
        
        
        function SetTempParam($param_name, $value) {
            $this->temp_vars[$param_name] = $value;
        } // SetTempParam(
        
        
        function GetTempParam($param_name) {
            if (isset($this->temp_vars[$param_name])) {
                return $this->temp_vars[$param_name];
            } else {
                return false;
            }
        } // GetTempParam(
        
        
        function ClearTempParam($param_name) {
            if (isset($this->temp_vars[$param_name])) {
                unset($this->temp_vars[$param_name]);
            }
        } // ClearTempParam()
        
        
        function RetriveTempParam($param_name) {
            $result = $this->GetTempParam($param_name);
            $this->ClearTempParam($param_name);
            
            return $result;
        } // RetriveTempParam()
        
        function IsTempParamSet($param_name) {
            return (isset($this->temp_vars[$param_name]));
        } // IsParamSet()
  
  
    } // class Authadmin

?>