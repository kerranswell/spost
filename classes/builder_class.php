<?php

class Builder  {
    var $blocks = array(); 
    var $blocks_attrs = array();
    
    var $xml_blocks = array();
    
    
    function GetCommonHeaders($params = array()) {
        global $error_text;

        $result = inTag('', 'header');
        $result .= inTag($this->GetFooter(), 'footer');
        //$result = inTag(inTag('jquery-min', 'js') . inTag('cusel.min', 'js') . inTag('jquery-ui-custom.min', 'js'), 'header');
        $result .= inTag(SITE, 'site');

        if (!empty($params['navbar'])) {
            $navbar_params = $params['navbar']; 
        } else {
            $navbar_params = '';
        }        

        if ($this->dsp->authadmin->IsLogged()) {
            $result .= inTag($this->dsp->navbar->GetNavbarXML($navbar_params), 'block', array('name' => 'navbar', 'align' => 'left'));
            
            if ($this->dsp->rights->HasRight($this->dsp->authadmin->user['id'], USERS_SERVICE_ID, 0, 'moder')) {
                $result .= inTag($this->dsp->users->GetStatXML(), 'block', array('name' => 'stats', 'align' => 'left'));
            }
        } else {
            $result .= inTag($this->dsp->navbar->GetUnauthNavbarXML($navbar_params), 'block', array('name' => 'navbar', 'align' => 'left'));
        }
        
        $result .= inTag(MakeXML(array('img' => 'bun_240-360px1.jpg', 'link' => 'http://medpred.ru/')), 'block', array('name' => 'banner', 'align' => 'left'));
        
        $result .= inTag($this->dsp->authadmin->GetAuthXML(), 'user');
        
        if ($this->dsp->authadmin->IsParamSet('error')) {
            $result .= inTag('<![CDATA[' . $error_text[ $this->dsp->authadmin->RetriveParam('error')] . ']]>', 'error');
        }        
        
        if ($this->dsp->authadmin->IsParamSet('message')) {
            $result .= inTag('<![CDATA[' . $error_text[$this->dsp->authadmin->RetriveParam('message')] . ']]>', 'message');
        }        
        
        if (!$this->dsp->authadmin->IsLogged()) {
            $result .= inTag('1', 'index');
        }
        
        $result .= inTag(MakeXML($this->dsp->specialities->GetList()), 'specialities');
        $result .= inTag($this->dsp->services->GetServicesXML(), 'services');

        
        return $result;
    } // getCommonHeaders
    
    
    function Start($params = array()) {
        global $error_text;

        $this->AddLink('root', 'site');
        $this->AddXML('site', SITE);
        $this->AddBlock('root', 'head');
        $this->AddLink('root', 'footer');
        $this->AddLink('root', 'error');
        $this->AddLink('root', 'message');
        $this->AddBlock('root', 'header');

        if (!empty($params['navbar'])) {
            $navbar_params = $params['navbar']; 
        } else {
            $navbar_params = '';
        }
        
        if ($this->dsp->authadmin->IsLogged()) {
            $this->AddBlock('root', 'navbar');
            $this->AddXML('navbar', $this->dsp->navbar->GetNavbarXML($navbar_params));
            $this->SetAttrs('navbar', array('align' => 'left'));

            if ($this->dsp->rights->HasRight($this->dsp->authadmin->user['id'], USERS_SERVICE_ID, 0, 'moder')) {
                $this->AddBlock('root', 'stats');
                $this->SetAttrs('stats', array('align' => 'left'));
                $this->AddXML('stats', $this->dsp->users->GetStatXML());
            }
        } else {
            $this->AddBlock('root', 'navbar');
            $this->AddXML('navbar', $this->dsp->navbar->GetUnauthNavbarXML($navbar_params));
            $this->SetAttrs('navbar', array('align' => 'left'));
        }

        $this->AddBlock('root', 'banner', '', array('align' => 'left'));
        $this->AddArray('banner', array('img' => 'bun_240-360px1.jpg', 'link' => 'http://medpred.ru/'));
        
        $this->AddXML('footer', $this->GetFooter());
        
        $this->AddLink('root', 'specialities');
        $this->AddArray('specialities', $this->dsp->specialities->GetList());

        $this->AddLink('root', 'services');
        $this->AddXML('services', $this->dsp->services->GetServicesXML());
        
        if ($this->dsp->authadmin->IsParamSet('error')) {
            $this->AddXML('error', '<![CDATA[' . $error_text[$this->dsp->authadmin->RetriveParam('error')] . ']]>');
        }        
        
        if ($this->dsp->authadmin->IsParamSet('message')) {
            $this->AddXML('message', '<![CDATA[' . $error_text[$this->dsp->authadmin->RetriveParam('message')] . ']]>');
        }        
        
        if (!$this->dsp->authadmin->IsLogged()) {
            $this->AddXML('root', inTag('1', 'index'));
        } else {
            $this->AddLink('root', 'auth_user', 'user');
            $this->AddArray('auth_user', $this->dsp->authadmin->user);
        }

//        $this->AddArray('js', array('jquery-min', 'cusel.min', 'jquery-ui-custom.min'));
        $this->AddLink('head', 'js');

    }

    
    
    
    
    function AddXML($id, $content, $tag = '') {
        if (empty($this->blocks[$id])) {
            $this->blocks[$id] = array();
        }
        
        $this->blocks[$id][] = array('type' => 'xml', 'content' => $content, 'tag' => $tag);
    }
    
    
    function AddArray($id, $content, $tag = '') {
        if (empty($this->blocks[$id])) {
            $this->blocks[$id] = array();
        }
        
        $this->blocks[$id][] = array('type' => 'array', 'content' => $content, 'tag' => $tag);
    }
    
    
    function AddLink($id, $src_id, $tag = '', $attrs = array()) {
        if (empty($this->blocks[$id])) {
            $this->blocks[$id] = array();
        }
        
        $this->blocks[$id][] = array('type' => 'link', 'content' => $src_id, 'tag' => $tag, 'attrs' => $attrs);
    }
    
    
    function AddBlock($id, $src_id, $tag = '', $attrs = array()) {
        if (empty($this->blocks[$id])) {
            $this->blocks[$id] = array();
        }
        
        $this->blocks[$id][] = array('type' => 'block', 'content' => $src_id, 'tag' => $tag, 'attrs' => $attrs);
    }
    

    function AddSXML($id, $src_id) {
        if (empty($this->blocks[$id])) {
            $this->blocks[$id] = array();
        }
        
        $this->blocks[$id][] = array('type' => 'sxml', 'content' => $src_id);
    }
    
    
    function CreateXMLblock($id, $str) {
        $this->xml_blocks[$id] = array('content' => new SimpleXMLElement($str), 'children' => array());
    }
    
    
    function LinkXMLblocks($parent_id, $child_id) {
        $this->xml_blocks[$parent_id]['children'][] = $child_id;
    }
    
    
    function AddXML2XMLblock($id, $str) {
        $new_node = new SimpleXMLElement($str);
        xml_join($this->xml_blocks[$id]['content'], $new_node);
    } 
    
    
    function AddChild($id, $name) {
        $this->xml_blocks[$id]['content']->addChild($name);
    }
    
    
    function AgregateNode($id) {
        $node = $this->xml_blocks[$id]['content'];
         
        foreach ($this->xml_blocks[$id]['children'] as $child_id) {
            $child = $this->AgregateNode($child_id);
            xml_join($node, $child);
        }
        
        return $node;
    }
    
    
    function GetAsXML($id) {
        $node = $this->AgregateNode($id);
        return $node->asXML();
    }
    
    
    function DelBlock($id) {
        if (!empty($this->blocks[$id])) {
            unset($this->blocks[$id]);
        }
    }
    
    
    
    function SetAttrs($id, $attrs) {
        $this->blocks_attrs[$id] = $attrs;
    }
    
    
    function ClearAttrs($id) {
        if (isset($this->blocks_attrs[$id])) {
            unset($this->blocks_attrs[$id]);
        }
    }
    
    
    function GetAttrs($id) {
        if (isset($this->blocks_attrs[$id])) {
            return $this->blocks_attrs[$id];
        }
        
        return array();
    }
    
    
    function AddAttrs($id, $attrs) {
        $this->blocks_attrs[$id] = array_merge($this->blocks_attrs[$id], $attrs);        
    } 
    
    
    function AddAttr($id, $attr, $value) {
        $this->AddAttrs($id, array($attr => $value));        
    } 
    
    
    function GetAsArray($id, $unset = false) {
        $result = array();
        
        if (empty($this->blocks[$id]))
            return $result;
            
        foreach ($this->blocks[$id] as $idx => $subblock) {
            if ($subblock['type'] == 'array') {
                $result[] = $subblock['content'];
                if ($unset) {
                    unset($this->blocks[$id][$idx]);
                }
            }
        }
        
        return $result;
    }
    
    
    function GetCompiled($id) {
        if (empty($this->blocks[$id])) return '';
        
        $result = '';
        
        foreach ($this->blocks[$id] as $idx => $subblock) {
            switch ($subblock['type']) {
                case 'array':
                    $result .= MakeXML($subblock['content'], true);
                    break;
                    
                case 'xml' :
                    $result .= $subblock['content'];
                    break;
                    
                case 'link' :
                    $result .= $this->GetXMLasName($subblock['content'], $subblock['tag'], $subblock['attrs']);
                    break;

                case 'block' :
                    $result .= $this->GetXMLasBlock($subblock['content'], $subblock['tag'], $subblock['attrs']);
                    break;
                    
                case 'sxml' :
                    $result .= $this->GetSXMLasString($subblock['content']);
                    break;
                    
            } // switch
        }
        
        return $result;
    }
    
    
    function GetXMLasBlock($id, $tag = '', $attrs = array()) {
        $attrs = array_merge($attrs, $this->GetAttrs($id));
        
        if (empty($this->blocks[$id]['tag'])) {
            if (empty($tag)) {
                $attrs['name'] = $id;
            } else {
                $attrs['name'] = $tag;
            }
        } else {
            $attrs['name'] = $this->blocks[$id]['tag'];
        }
        
        if (empty($attrs['id'])) $attrs['id'] = $id;
        
        return inTag($this->GetCompiled($id), 'block', $attrs);
    }
    
    
    function GetXMLasName($id, $tag = '', $attrs = array()) {
        $attrs = array_merge($attrs, $this->GetAttrs($id));
        
        if ($tag == '') $tag = $id;
        
        return inTag($this->GetCompiled($id), $tag, $attrs);
    }


    function GetFooter() {
        return '';
    }    


} // class Builder


?>