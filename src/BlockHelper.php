<?php

/*
 * Copyright (C) PowerOn Sistemas
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PowerOn\Helper;

/**
 * BlockHelper
 * Maneja los bloques de codigo
 * @author Lucas Sosa
 * @version 0.1
 */
class BlockHelper extends Helper {
    
    /**
     * Obtiene el trace de legible de una excepcion
     * @param array $trace
     * @return string
     */
    public function humaniceTrace(array $trace) {
        /* @var $html_helper HtmlHelper */
        $html_helper = $this->html;
        $list = [];
        foreach ($trace as $id_trace => $e) {
            $class = explode('\\', key_exists('class', $e) ? $e['class'] : '');
            $type = key_exists('type', $e) ? $e['type'] : '';
            $function = key_exists('function', $e) ? $e['function'] : '';
            $args = key_exists('args', $e) ? $e['args'] : [];
            $file = key_exists('file', $e) ? $e['file'] : [];
            $line = key_exists('line', $e) ? $e['line'] : [];

            $item = $class ? '<span style="color:blue" title="' . implode('\\', $class) . '">' . end($class) . '</span>' : '';
            $item .= $type ? '<span style="color:teal">' . $type . '</span>' : '';
            $item .= $function ? '<span style="color:red">' . $function . '</span>' : '';
            $item .= $args ? 
                    '(' .
                        $html_helper->link(count($args) . ' arg', [], 
                            ['onclick' => 'var a=document.getElementById(\'args_' . $id_trace . '\');'
                                . ' if (a.style.display==\'block\') { a.style.display=\'none\' } else { a.style.display=\'block\' };'
                                . 'return false;']) . 
                    ')' : '()';
            $item .= $file ? ' - <span style="color:lightgray" onmouseover="this.style.color=\'gray\'" '
                    . 'onmouseout="this.style.color=\'lightgray\'" >' . $file . '</span>' : '';
            $item .= $line ? ' <span style="color:salmon">' . $line . '</span>' : '';
            $item .= $args ? '<div style="display:none" id="args_' . $id_trace . '">' . @d($args) . '</div>' : '';
            
            $list[] = $item;
        }
        
        return $html_helper->nestedList($list, [
                'style' => 'font-family: arial; line-height:30px;',
                'reversed' => 'reversed'
            ], [], 'ordered');
    }
}
