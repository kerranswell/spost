<?php

/**
 * @property mixed dsp
 *          Dispatcher
 */
class _BuilderPatterns {

        /**
         */
        public function root() {
            return $this->dsp->_Builder->_getElement(null);
        }

        /**
         * @param $id
         * @param $name
         * @return mixed
         */
        public function create_block($id, $name, $align = 'center') {
            $builder = $this->dsp->_Builder;
            $extra = array('align' => $align, 'id'=>$id, 'name'=>$name);
            $dom_element = call_user_func(array($builder, 'createNode'), 'block', $extra);
            call_user_func(array($builder, 'makeNodeID'), $dom_element);
            call_user_func(array($builder, 'addNode'), $dom_element);
            return $dom_element;
        }

        /**
         * @param $target_node
         * @param $tagname
         * @param string $value
         * @param array $extra
         * @return mixed
         */
        public function append_simple_node(&$target_node, $tagname, $value = '', $extra = array()) {
            $builder = $this->dsp->_Builder;
            $node = call_user_func(array($builder, 'createNode'), $tagname, $extra, $value);
            call_user_func(array($builder, 'addNode'), $node, $target_node);
            return $node;
        }

        /**
         * @param $target_node
         * @param $tagname
         * @param $sxml
         * @param array $extra
         * @return mixed
         */
        public function append_simplexml_node(&$target_node, $tagname, $sxml, $extra = array()) {
            $builder = $this->dsp->_Builder;
            $node = call_user_func(array($builder, 'CreateAsCopySXMLNode'), $sxml, $tagname, $extra);
            call_user_func(array($builder, 'addNode'), $node, $target_node);
            return $node;
        }

        /**
         * @param $target_node
         * @param $pager
         */
        public function append_pager(&$target_node, $pager) {
            $builder = $this->dsp->_Builder;
            if (is_array($pager)) {
                $pager = call_user_func(array($builder, 'GetPagerNode'),
                  1, $pager['last'], $pager['current'], $pager['pre_url'], $pager['post_url']);
                if ($pager) {
                    call_user_func(array($builder, 'addNode'), $pager, $target_node);
                }
            }
        }

        /**
         * @param $target_node
         * @param $row_panel
         * @return mixed
         */
        public function append_row_panel(&$target_node, $row_panel) {
            $builder = $this->dsp->_Builder;
            $dom_row_panel = call_user_func(array($builder, 'createNode'), 'row_panel');
            if (!empty($row_panel['common'])) {
                call_user_func(array($builder, 'addArray'), $row_panel['common'], 'common', array(), $dom_row_panel);
                unset($row_panel['common']);
            }
            call_user_func(array($builder, 'addNode'),
                call_user_func(array($builder, 'addArray'), $row_panel, '', array(), $dom_row_panel, true),
                $target_node
            );
            return $dom_row_panel;
        }

        /**
         * @param $target_node
         */
        public function append_mass_panel(&$target_node, $mass_panel) {
            $builder = $this->dsp->_Builder;
            call_user_func(array($builder, 'addArray'),
                array(array('title' => 'Удалить', 'opcode' => 'massdel')),
                'mass_panel', array(), $target_node
            );
        }

        /**
         * @param $target_node
         * @param $rec
         * @param $field
         */
        public function append_url(&$target_node, $rec, $field) {
            if (!empty($rec->link)) {
                $t = (string)$rec->link->t;
                $link_key = array_combine($this->dsp->$t->__primary_key__, array($field));
                $link_key = call_user_func(array($this, 'dsp', $t, 'MakeHttpKey'), $link_key);
                $url = array('t' => $t, 'op' => (string) $rec->link->op, 'key' => $link_key);
                $url = '?' . htmlentities(http_build_query($url));
                $this->append_simple_node($target_node, 'url', $url);
            }
        }
    }