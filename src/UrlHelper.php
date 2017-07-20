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
 * UrlHelper
 * Maneja las url
 * @author Lucas Sosa
 * @version 0.1
 */
class UrlHelper extends Helper {
    
    /**
     * Configura el helper actual
     * @param array $config
     */
    public function configure(array $config = []) {
        $this->_config = [
            'request_path' => filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING),
            'request_queries' => filter_input(INPUT_SERVER, 'QUERY_STRINGS', FILTER_SANITIZE_STRING),
            'request_controller' => FALSE,
            'request_action' => FALSE,
            'root_dir' => NULL,
            'default_controller' => 'index',
            'default_action' => 'index'
        ] + $config;
        
        if ( $this->_config['request_controller'] === FALSE || $this->_config['request_action'] === FALSE) {
            $url = explode('/', $this->_config['request_path']);
            $this->_config['request_controller'] = array_shift($url);
            $this->_config['request_action'] = array_shift($url);
        }
    }
    
    /**
     * Crea una URL nueva a partir de los datos entregados
     * formato ['controller' => 'users', 'action' => 'messages', 'param1', 'param2']
     * puede especificar las queries de la url ej: ['?' => ['foo' => 'bar', 'foo2' => 'bar2']]
     * @param array $url
     * @return string
     */
    public function build( array $url = []) {        
        $vars = [];
        foreach ($url as $k => $u) {
            if ( !in_array((string)$k, ['controller', 'action', '?', '#']) ) {
                $vars[] = $u;
            }
        }

        $gets = key_exists('?', $url) && is_array($url['?']) ? $url['?'] : [];
        array_walk($gets, function(&$v, $k) {
            $v = $k . '=' . $v;
        });
        
        $anchor = key_exists('#', $url) && is_string($url['#']) ? $url['#'] : NULL;
        
        $result = ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') 
            . ( key_exists('controller', $url) ? '/' . $url['controller'] : 
                    (key_exists('action', $url) || $vars ? '/' . $this->_config['default_controller'] : ''))
            . ( key_exists('action', $url) ? '/' . $url['action'] : ($vars ? '/' . $this->_config['default_action'] : '') )
            . ( $vars ? '/' . implode('/', $vars) : '' )
            . ( $gets ? '/?' . implode('&', $gets) : '' )
            . ( $anchor ? '#' . $anchor : '' );
        
        return $result ? $result : '/';
    }
}
