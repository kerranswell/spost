<?php
define('BUILDER_ITEM_WORD', 'item');
define('BUILDER_KEY_WORD', '_key');
define('BUILDER_NE', '!_');

class _Builder {
    public $doc;
    private $root;
    
    /**
     * Builder::__construct()
     * Create Root node ("root"), add ID "root" to it
     */
    public function __construct() {
        $this->doc = new DOMDocument();
        $this->doc->loadXML('<root/>');
        $t = $this->doc->getElementsByTagName('root');
        foreach ($t as $node) {
            $this->root = $node;
        }
        $this->root->setAttribute('id', 'root');
        $this->root->setIDAttribute('id', true);
    } // _construct()
    

    /**
     * Builder::addXML()
     * Add XML (text) into the specified node. Node can be DOMnode or ID.
     * If TAG not specified XML will be added into the Root node.
     * XML can be outered be TAG or can be added directly.
     * 
     * @param string $xml XML text.
     * @param string $tag Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs list of attributes for TAG. Not applied if no TAG specified.
     * @param mixed $node node to add xml to. Can be DOMnode or ID.
     * @return DOMnode node that XML added to.
     */
    public function addXML($xml, $tag = '', $extra_attrs = array(), $node = false) {
        $node = $this->_getElement($node);
        if (!empty($tag)) {
            $n = $this->doc->createElement($tag);
            foreach ($extra_attrs as $id => $value) {
                $n->setAttribute($id, $value);
            }
            if (!empty($extra_attrs['id'])) {
                $n->setIdAttribute('id', true);
            }
            $node = $node->appendChild($n);
        }
        $n = new DOMDocument;
        $n->loadXML($xml);
        $n = $this->doc->importNode($n->documentElement, true);
        return $node->appendChild($n);
    } // addXML()
    
    /**
     * Builder::addNode()
     * Add one DOMnode into another.
     * 
     * @param DOMnode $new_node - Node that will be added.
     * @param mixed $node - Node to add another node to. Can be DOMnode or ID. If not specified, Root node will be used.
     * @return DOMnode - Node that new_node added to.
     */
    public function addNode($new_node, $node = false) {
        $node = $this->_getElement($node);
        return $node->appendChild($new_node);
    } // adNode()

    /**
     * Builder::addArray()
     * Add to content of the array to the specified node. If no node specified th Root node will be used.
     * If an index of the element is integer the element will be added as "item" node with attribute "_key" set to element index.
     * If asItem set to TRUE - all elements will be added as "item" in any case.
     * If TAG specified it will be used as outer tag for added elements.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     * Walts array recursuvly. 
     * 
     * @param array $array - Array to add as XML.
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @param mixed $node - node to add array to. Can be DOMnode or ID.
     * @param bool $asItems - if TRUE all elements will be added as "item" tags with "_key" attribute set to array index.
     * @param array $attrs - list of keys that must be translated to attributes not to nodes.
     * @return DOMnode - node that array added to.
     */
    public function addArray($array, $tag = '', $extra_attrs = array(), $node = false, $asItems = false, $attrs = array()) {
        $node = $this->_getElement($node);
        if (!empty($tag)) {
            $n = $this->doc->createElement($tag);
            foreach ($extra_attrs as $id => $value) {
                $n->setAttribute($id, $value);
            }
            if (!empty($extra_attrs['id'])) {
                $n->setIdAttribute('id', true);
            }
            $node = $node->appendChild($n);
        }
        $node = $this->_addArray2Node($array, $node, $asItems, $attrs);
        return $node;    
    } // addArray()
    
    /**
     * Builder::addJSON()
     * Add JSON string as XML to the specified node. If node not specified the Root node will be used.
     * If an index of the element is integer the element will be added as "item" node with attribute "_key" set to element index.
     * If asItem set to TRUE - all elements will be added as "item" in any case.
     * If TAG specified it will be used as outer tag for added elements.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     * Walts JSON recursuvly. 
     * 
     * @param string $json - JSON string to add as XML.
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @param mixed $node - node to add array to. Can be DOMnode or ID.
     * @param bool $asItems - if TRUE all elements will be added as "item" tags with "_key" attribute set to array index.
     * @param array $attrs - list of keys that must be translated to attributes not to nodes.
     * @return DOMnode - node that array added to.
     */
    public function addJSON($json, $tag = '', $extra_attrs = array(), $node = false, $asItems = false, $attrs = array()) {
        $array = json_decode($json, true);
        return $this->addArray($array, $tag, $extra_attrs, $node, $asItems, $attrs);
    } // addJSON())
    
    /**
     * Builder::createArrayNode()
     * Create a new DOMnode from the array and return it.
     * If an index of the element is integer the element will be added as "item" node with attribute "_key" set to element index.
     * If asItem set to TRUE - all elements will be added as "item" in any case.
     * If TAG specified it will be used as outer tag for added elements.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     * Array indexes listed in attrs will be transformed to attributes not to nodes.
     * 
     * Array must content only one element on the first level or TAG must be specified. Otherwise false will be returned.
     * 
     * Walts array recursuvly. 
     *
     * @param array $array - Array to add as XML.
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @param bool $asItems - if TRUE all elements will be added as "item" tags with "_key" attribute set to array index.
     * @param array $attrs - list of keys that must be translated to attributes not to nodes.
     * @return DOMnode - new node (not added to the document). Or FALSE on error.
     */
    public function createArrayNode($array, $tag = '', $extra_attrs = array(), $asItems = false, $attrs = array()) {
        if ((count($array) == 1) && empty($tag)) { // if tag empty, try to make the first element as a root
            $tag = reset(array_keys($array));   // first index as tag name
            $array = reset($array);             // first element as value 
        }
        if (!empty($tag)) {
            $node = $this->doc->createElement($tag);
            foreach ($extra_attrs as $id => $value) {
                $node->setAttribute($id, $value);
            }
            if (!empty($extra_attrs['id'])) {
                $node->setIdAttribute('id', true);
            }
        } else {
            return false; // cannt make node with no single root
        }

        $node = $this->_addArray2Node($array, $node, $asItems, $attrs);
        return $node;
    } // createArrayNode()
    
    /**
     * Builder::createJSONNode()
     * Create DOMnode from JSON string and return it.
     * If an index of the element is integer the element will be added as "item" node with attribute "_key" set to element index.
     * If asItem set to TRUE - all elements will be added as "item" in any case.
     * If TAG specified it will be used as outer tag for added elements.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     * 
     * The JSON must have only 1 element on the first level or TAG must be specified. Otherwise false will be returned.
     * 
     * Walts JSON recursuvly. 
     * 
     * @param string $json - JSON string to add as XML.
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @param bool $asItems - if TRUE all elements will be added as "item" tags with "_key" attribute set to array index.
     * @param array $attrs - list of keys that must be translated to attributes not to nodes.
     * @return DOMnode - new node (not added to the document). Or FALSE on error.
     */
    public function createJSONNode($json, $tag = '', $extra_attrs = array(), $asItems = false, $attrs = array()) {
        $array = json_decode($json, true);
        return $this->createArrayNode($array, $tag, $extra_attrs, $asItems, $attrs);
    } // createJSONNode()

    /**
     * Builder::createNode()
     * Create DOMnode and return it.
     * If TAG specified the XML will be outered be TAG.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     *
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @return DOMnode - new node (not added to the document).
     */
    public function createNode($tag = '', $extra_attrs = array(), $value = null) {
        if (!empty($tag)) {
            $rnode = $this->doc->createElement($tag);
            foreach ($extra_attrs as $id => $val) {
                $rnode->setAttribute($id, $val);
            }
            if (!empty($extra_attrs['id'])) {
                $rnode->setIdAttribute('id', true);
            }
            if (null != $value) {
                $rnode->nodeValue = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            return $rnode;
        }
        return null;
    } // createNode()
    
    /**
     * Builder::createXMLNode()
     * Create DOMnode from XML (text) and return it. 
     * If TAG specified the XML will be outered be TAG.
     * Extra_attrs will be added as attributes for the TAG if TAG specified.
     * 
     * @param string $xml - XML text.
     * @param string $tag - Outer TAG that will be added. If no specified XML will be added directly into the node.
     * @param array $extra_attrs - list of attributes for TAG. Not applied if no TAG specified.
     * @return DOMnode - new node (not added to the document).
     */
    public function createXMLNode($xml, $tag = '', $extra_attrs = array()) {
        //$this->dsp->transforms->replaceEntity( $xml );
        $node = new DOMDocument;
        $node->loadXML($xml);
        $node = $this->doc->importNode($node->documentElement, true);
        if (!empty($tag)) {
            $rnode = $this->doc->createElement($tag);
            foreach ($extra_attrs as $id => $value) {
                $rnode->setAttribute($id, $value);
            }
            if (!empty($extra_attrs['id'])) {
                $rnode->setIdAttribute('id', true);
            }
            
            $node = $rnode->appendChild($node);
        }
        return $node;
    } // createXMLNode()

    /**
     * Builder::CreateAsCopySXMLNode()
     *
     * @param $node
     * @param string $tag
     * @param array $attrs
     * @return DOMNode|mixed
     */
    public function CreateAsCopySXMLNode($node, $tag = '', $attrs = array()) {
        $xml = new SimpleXMLElement("<{$tag}/>");
        foreach ($node->children() as $subnode) {
            xml_join($xml, $subnode);
        }
        $node = new DOMDocument;
        $node->loadXML($xml->asXML());
        $node = $this->doc->importNode($node->documentElement, true);
        return $node;
    } // CreateAsCopySXMLNode()
    
    /**
     * Builder::makeNodeID()
     * Search for "id" attribute in node and mark it as node_ID if found.
     * Node must be DOMnode (with no node_id the node cannt be found in the document by ID).
     * If "recursive" specified the function will work recursivly, otherwise only first level will be cheched.
     * id_name can be specified to the name of ID attribute instead of "id" (lower case)
     * 
     * @param DOMnode $node
     * @param bool $recursive
     * @param string $id_name
     */
    public function makeNodeID($node, $recursive = true, $id_name = 'id') {
        $node = $this->_getElement($node);
        if ($node->hasAttribute($id_name)) {
            $node->setIdAttribute($id_name, true);
        }
        if ($recursive) {
            foreach ($node->childNodes as $value) {
                if (in_array(strtolower(get_class($value)), array('domelement', 'domnode'))) 
                    $this->makeNodeID($value, $recursive, $id_name);
            } 
        } 
    } // makeNodeID()
    
    /**
     * Builder::_addArray2Node()
     * Internal use only.
     * Add array to the specified node. Node will be DOMnode only.
     * "#text" index will be translated as content of the node, not as child node.
     * If an index of the element is integer the element will be added as "item" node with attribute "_key" set to element index.
     * If asItem set to TRUE - all elements will be added as "item" in any case.
     * Array indexes listed in attrs will be transformed to attributes not to nodes.
     * 
     * @param array $array - array to be added.
     * @param DOMnode $node - node that array will be added to.
     * @param bool $asItems - if TRUE all elements will be added as "item" tags with "_key" attribute set to array index.
     * @param array $attrs - list of index that will be traslated to attributes not to nodes.
     * @return DOMnode node - node that array added into (tha same as $node on input).
     */
    private function _addArray2Node($array, $node, $asItems = false, $attrs = array()) {
        if (!empty($array['#text'])) {
            $t = $this->doc->createTextNode($array['#text']);
            unset($array['#text']);
            $node->appendChild($t);
        }
        foreach ($array as $idx => $value) {
            $attr_list = array();
            if (is_integer($idx) || $asItems) {
                $attr_list[BUILDER_KEY_WORD] = $idx;
                $idx = BUILDER_ITEM_WORD;
            } // if
            if (is_array($value)) {
                $new = $this->doc->createElement($idx);
                foreach ($value as $attr => $avalue) {
                    if (in_array($attr, $attrs, true)) {
                        unset($value[$attr]);
                        $attr_list[$attr] = $avalue;
                    }
                } // foreach
                $this->_addArray2Node($value, $new, $asItems, $attrs);
            } else {

                //--------------------------------------------------------------------------------
                // FIX: unterminated entity reference
                //
                // $new = $this->doc->createElement($idx, $value);
                //--------------------------------------------------------------------------------
                $new = $this->doc->createElement($idx);
                if ($idx == 'xml'){
                    $new = $this->createXMLNode('<xml>'.$value.'</xml>');
                }
                else{
                    $new->nodeValue = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                }

                //--------------------------------------------------------------------------------

            } // if
            foreach ($attr_list as $attr => $avalue) {
                $new->setAttribute($attr, $avalue);
            } // foreach
            if (!empty($attr_list['id'])) {
                $new->setIdAttribute('id', true);
            } // if
            $node->appendChild($new);
        } // foreach

        return $node;
    } //_addArray2Node()

    /**
     * Builder::asXML()
     * Return content on the specified node as XML (text). Node can be DOMnode or ID. If not specified the Root node will be used.
     * The tag of specified node wiil be skipped.
     * 
     * @param mixed $node - node to return content.
     * @return string - XML
     */
    public function asXML($node = false) {
        $node = $this->_getElement($node);
        return $this->doc->saveXML($node);
    } // asXML()

    /**
     * Builder::asArray()
     * Return content of the specified node as Array. Node can be DOMnode or ID. If not specified the Root node will be used.
     * The tag of specified node wiil be skipped.
     * "item" nodes with "_key" attribute will be translated to element with "_key" index.
     * If node have content and child nodes or attributes, content will be translated to "#text" element. Otherwise - to the value ogf the element.
     * 
     * @param mixed $node - node to return. DOMnode, ID or false (can be skipped).
     * @return array - content of the specified node as array.
     */
    public function asArray($node = false) {
        $node = $this->_getElement($node);
        $result = array();
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                if ($attr->name != BUILDER_KEY_WORD) $result[$attr->name] = $attr->value;
            }
        }
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $cnode) {
                $key = $cnode->nodeName; 
                if ($cnode->nodeName == BUILDER_ITEM_WORD) {
                    $key = $cnode->getAttribute(BUILDER_KEY_WORD);
                } 
                if (in_array(strtolower(get_class($cnode)), array('domelement', 'domnode'))) {
                    $result[$key] = $this->asArray($cnode);
                } elseif (strtolower(get_class($cnode)) == 'domtext') {
                    if (!empty($cnode->wholeText)) {
                        if (!empty($result)) {
                            $result['#text'] = $cnode->wholeText;
                        } else {
                            return $cnode->wholeText;
                        }
                    }
                }
            }
        } else {
            //$result = $node->nodeValue;
        }
        return $result;
    } // asArray()
    
    /**
     * Builder::asJSON()
     * Return content of the specified node as JSON string. Node can be DOMnode or ID. If not specified the Root node will be used.
     * The tag of specified node wiil be skipped.
     * "item" nodes with "_key" attribute will be translated to element with "_key" index.
     * If node have content and child nodes or attributes, content will be translated to "#text" element. Otherwise - to the value ogf the element.
     * 
     * @param mixed $node - node to return. DOMnode, ID or false (can be skipped).
     * @return string - content of the specified node as JSON string.
     */
    public function asJSON($node = false) {
        return json_encode($this->asArray($node));
    } // asJSON()
    
    /**
     * Builder::_getElement()
     * Internal use only.
     * Return DOMnode for node.
     * If node is DOMnode its return as is.
     * If node is ID of the existing element in document, the DOMnode of the element will be returned.
     * If node is FALSE or element with such ID not found the Root node will be returned.
     * 
     * @param mixed $node
     * @return DOMnode.
     */
    public function _getElement($node) {
        if (is_integer($node) || is_string($node)) {
            $node = $this->doc->getElementById($node);
            if (empty($node)) {
                # print "miss by id";
                return $this->root;
            } else {
                # print "found by id";
                return $node;
            }
        } elseif (empty($node)) {
            # print "empty";
            return $this->root;
        } elseif (in_array(strtolower(get_class($node)), array('domelement', 'domnode'))) {
            if ($node->ownerDocument == $this->doc) {
                # print 'in doc';
                return $node;
            } else {
                # print 'another doc';
                return $this->root;
            }
        }
        return $this->root;
    } // _getElement()

    /**
     * Builder::removeNode()
     * Remove specified node form document. Node can be DOMnode or ID.
     * If no such node or its the Root node, FALSE will be returned.
     * Return TRUE on success.
     * 
     * @param bool $node - Node to remove. Can be DOMnode or ID. 
     * @return bool - status.
     */
    public function removeNode($node) {
        $node = $this->_getElement($node);
        if ($node !== $this->root) {
            if (!empty($node->parentNode)) {
                $node->parentNode->removeChild($node);
                unset($node);
                return true;
            }
        }     
        return false;
    } // removeNode()

    /**
     * Builder::replaceNode()
     * Replace old_node by new_node. Both nodes can be DOMnode or ID. 
     * No Root node can be replaced or be replacment.
     * 
     * @param mixed $old_node
     * @param mixed $new_node
     * @return bool - status
     */
    public function replaceNode($old_node, $new_node) {
        $old_node = $this->_getElement($old_node);
        $new_node = $this->_getElement($new_node);
        if (($old_node === $this->root) || ($new_node === $this->root)) {
            return false;
        }
        if (!empty($old_node->parentNode)) {
            $old_node->parentNode->replaceChild($new_node, $old_node);
            unset($old_node);
            return $new_node;
        }     
        return false;
    } // replaceNode()

    /**
     * Builder::getByID()
     * 
     * @param mixed $node
     * @return
     */
    public function getByID($node) {
        $node = $this->_getElement($node);
        if ($node !== $this->root) {
            return $node;
        }
        return false;
    } // getByID()

    /**
     * Builder::getByTag()
     * 
     * @param mixed $tag
     * @param mixed $filter
     * @return
     */
    public function getByTag($tag, $filter = array()) {
        $nodes = $this->doc->getElementsByTagName($tag);
        $result = array();
        $eq_cond = array();
        $not_eq_cond = array();
        foreach ($filter as $id => $cond) {
            if (strpos($cond, BUILDER_NE) === 0) {
                $not_eq_cond[$id] = str_replace(BUILDER_NE, '', $cond);
            } else {
                $eq_cond[$id] = $cond;
            }
        }
        foreach ($nodes as $node) {
            if (!empty($filter)) {
                foreach ($eq_cond as $id => $cond) {
                    if ($node->getAttribute($id) != $cond) continue 2;
                }
                foreach ($not_eq_cond as $id => $cond) {
                    if (!$node->hasAttribute($id) || ($node->getAttribute($id) == $cond)) continue 2;
                }
            }
            $result[] = $node;
        }
        return $result;
    } // getByTag()

    /**
     * Builder::Transform()
     * 
     * @param mixed $xslfile
     * @param bool $return
     * @return
     */
    public function Transform($xslfile, $return = false) {
        if (Param('_Debug')) { 
            header("Content-type: text/xml");
            echo($this->asXML());
            die();
            // exit();
        }
        if (class_exists('xsltCache')) {
            $xslt = new xsltCache;
            $xslt->importStyleSheet(TPL_DIR . $xslfile);
        } else {
            $xslt = new xsltProcessor;
            $xsltDoc = new DomDocument;
            $xsltDoc->load(TPL_DIR . $xslfile);
            $xslt->importStyleSheet($xsltDoc);
        }

        $result = $xslt->transformToXML($this->doc);
        if (!$return) {
            header("Content-type: text/html");
            print $result;
            return null;
        }

        return $result;
    } // Transform()
    
    /**
     * Builder::GetPagerNode()
     * 
     * @param mixed $first
     * @param mixed $last
     * @param mixed $current
     * @param mixed $pre_url
     * @param mixed $post_url
     * @param integer $step
     * @return
     */
    public function GetPagerNode($first, $last, $current, $pre_url, $post_url, $step = 2) {
        if ($first >= $last) {
            //debug_print_backtrace();
            return false;
        }
        $pager = $this->doc->createElement('pager');
        //var_dump($pre_url);
        $pager->appendChild($this->doc->createElement('current', $current));
        //$pager->addChild('pre_url', htmlentities("12344&#38;eee=222"));
        $pager->setAttribute('pre_url', $pre_url);
        $pager->setAttribute('post_url', $post_url);
        
        $left_bound  = $current - $step;
        $right_bound = $current + $step;
    
        if ($left_bound < $first + 1) {
            $left_bound = $first + 1;
        }
        if ($right_bound > $last - 1) {
            $right_bound = $last - 1;
        }
        $pager->appendChild($this->doc->createElement('page', 1));
        if ($left_bound != $first + 1) {
            $pager->appendChild($this->doc->createElement('page', 0));
        }
        for ($i = $left_bound; $i <= $right_bound; $i++) {
            $pager->appendChild($this->doc->createElement('page', $i));
        }   
        if ($right_bound != $last - 1) {
            $pager->appendChild($this->doc->createElement('page', 0));
        }
        $pager->appendChild($this->doc->createElement('page', $last));
        return $pager;
    } // GetPagerNode()

    /**
     * @param DOMNode $dom
     * @param string $params
     * @param string $back
     * @param string $text
     */
    function AddRecordAdd($dom = null, $params, $back, $text = "Добавить") {
    	if (null == $dom) {
    		$dom = $this->getByID('root');
    	}
    
    	if (isset($params['table'])) {
    		$params['t'] = $params['table'];
    		unset($params['table']);
    	}
    	$params['op'] = 'add';
    	$params['back'] = $back;
    	if (!empty($params['defaults'])) {
    		$params['defaults'] = base64_encode(http_build_query($params['defaults']));
    	}
    	$this->AddButton($dom, 'record_add_link_' . $params['t'], $text, $params);
    
    }

    /**
     * @param DOMNode $dom
     * @param string $params
     * @param string $back
     * @param string $text
     */
    function AddTitleList($dom, $title) {
        if (null == $dom) {
            $dom = $this->getByID('root');
        }

        // Block
        $extra = array('align' => 'center', 'name' => 'block_for_title', 'type' => $title);
        $dom_block = $this->createNode('block', $extra);
        $this->makeNodeID($dom_block, true, 'id');
        $this->addNode($dom_block, $dom);

    }
    
    /**
     * @param DOMNode $dom
     * @param string $params
     * @param string $back
     * @param string $text
     */
    function AddRecordLog($dom = null, $params, $back, $text = "История") {
        if (null == $dom) {
            $dom = $this->getByID('root');
        }

        if (!isset($params['table'])) {
            return;
        }
        $params['t'] = $params['table'];
        $params['op'] = 'log';
        $params['back'] = $back;
        unset($params['table']);

        $key = "";
        if (isset($params["key"])) {
            $key = $params["key"];
        }
        $link = http_build_query($params);

        // Block
        if (!isset($dst_id)) $dst_id = '';
        $extra = array(
            'align' => 'center',
            'id' => $dst_id.'_log',
            'name' => 'button',
            'onclick' => "LogPopup.show('" . $params['t'] . "', '" . $key . "'); return false;");
        $dom_block = $this->createNode('block', $extra);
        $this->makeNodeID($dom_block, true, 'id');
        $this->addNode($dom_block, $dom);

        // Text&Link
        $this->addArray(array('text' => $text, 'link' => $link), '', array(), $dom_block);
    }

    /**
     * @param DOMNode $dom
     * @param string $name
     * @param string $text
     * @param array $params
     */
    function AddButton($dom = null, $name, $text, $params) {
        if (null == $dom) {
            $dom = $this->getByID('root');
        }

        $link = http_build_query($params);

        // Block
        if (!isset($dst_id)) $dst_id = '';
        $extra = array('align' => 'center', 'id' => $dst_id.'_'.$name, 'name' => 'button');
        $dom_block = $this->createNode('block', $extra);
        $this->makeNodeID($dom_block, true, 'id');
        $this->addNode($dom_block, $dom);

        // Text&Link
        $this->addArray(array('text' => $text, 'link' => $link), '', array(), $dom_block);
    }

    /**
     * @param DOMNode $dom
     * @param string $params
     * @param string $back
     * @param string $text
     */
    function SelectRecordAdd($dom = null, $params, $back, $text = "Выбрать") {
        if (null == $dom) {
            $dom = $this->getByID('root');
        }

        if (isset($params['table'])) {
            $params['t'] = $params['table'];
            unset($params['table']);
        }
        $params['op'] = 'select';
        $params['back'] = $back;
        if (!empty($params['defaults'])) {
            $params['defaults'] = base64_encode(http_build_query($params['defaults']));
        }
        $this->AddButton($dom, 'record_select_link_' . $params['t'], $text, $params);

    }

} // class Builder

/*
    $d = new Builder();

    $t = $d->_getElement('root');
    $x = $d->createXMLNode("<code><inside id='20'>content</inside></code>");
    $d->makeNodeID($x, true, 'id');
    //$d->addXML("<code id='1'>nnn</code>");
    $d->addArray(array(1, 2, 3, 4 => array('ref', 'ad', 'ads' => 'hf'), array('#text' => '!!!text')), 'items', array(), false, false, array('ads'));
    $j = $d->createJSONNode('{"a":1,"b":{"#text":10,"id":12},"c":3,"d":4,"e":5}', 'JSON', array('id' => 13), false, array('id'));
    $n = $d->createArrayNode(array(1, 2, 3, 4 => array('ref', 'ad', 'ads' => 'hf')), 'items');
    $d->addNode($j);
    $d->addNode($x, $j);
    //$d->addNode($n, 20);
    //$d->replaceNode(12, 20);
    //$pager = $d->GetPagerNode(1, 10, 4, 'pre=', '');
    //$d->addNode($pager, 12);

    print_r($d->getByTag('inside', array('id' => 20)));

    //$d->delNode(13);
    print '<pre>---';
    $t = $d->asXML();
    print htmlspecialchars($t);
    print '---';
    print "\r\n";
    $t = $d->asJSON($j);
    print_r($t);
*/
?>