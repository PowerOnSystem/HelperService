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
    
    /**
     * Modifica una URL agregando o quitando variables
     * @param array $add [Opcional] la URL a agregar
     * @param array $remove [Opcional] la URL a remover
     * @return string
     */
    public function modify(array $add = [], array $remove = [], $controller = NULL, $action = NULL) {
        $url_splitted = explode('?', $this->_config['request_path']);
        $path = array_filter(explode('/', reset($url_splitted)));

        $gets_remove = key_exists('query', $remove) ? $remove['query'] : [];
        if (key_exists('query', $remove)) {
            unset($remove['query']);
        }
        
        $url = array_filter(array_diff_key($path, array_fill_keys($remove, FALSE), $add) + $add, function ($var) {
            return ($var !== NULL && $var !== FALSE && $var !== '');
        });
        
        $gets_request = key_exists('query', $url) ? $url['query'] : [];
        
        if (key_exists('query', $url)) {
            unset($url['query']);
        }

        ksort($url);
        array_walk($url, function(&$v, $k) {
            $v = (is_string($k) ? $k . '-' : '') . $v;
        });
        
        $url_controller = $controller ? $controller : $this->_config['request_controller'];
        $url_action = $action ? $action : $this->_config['request_action'];

        $last_url = ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') . 
            '/' . ( ($url_action == $this->_config['default_action'] && 
                $url_controller == $this->_config['default_controller'] && !$url)
                || ($this->_config['request_controller'] === NULL && $this->_config['request_action'] === NULL) ? '' : $url_controller . '/' ) .
            ( ($url_action == $this->_config['default_action'] && $url) || $url_action
                != $this->_config['default_action'] ? $url_action . '/' : '' ) . 
            implode('/', $url);
        
        $queries = array_filter(explode('&', $this->_config['request_queries']));
        $queries_keys = array_map(function($v){ $e = explode('=', $v); return $e[0]; }, $queries);
        $queries_vals = array_map(function($v){ $e = explode('=', $v); return $e[1]; }, $queries);
        
        $gets = $gets_request + array_combine($queries_keys, $queries_vals);
        foreach ($gets_remove as $gr) {
            if (key_exists($gr, $gets)) {
                unset($gets[$gr]);
            }
        } 
        array_walk($gets, function(&$v, $k, $path) {
            $v = $k . '=' . ($k == 'return' ? base64_encode($path) : $v);
        }, $this->_config['request_path']);
        
        return $last_url . ($gets ? (substr($last_url, -1) == '/' ? '' : '/') . '?' . implode('&', $gets) : '');
    }
    
    /**
     * Agrega un valor al final de la url
     * @param array $push la URL a agregar al final
     * @return string
     */
    public function push(array $push = array()) {
        $query = array();
        if ( key_exists('query', $push) ) {
            $query = \CNCService\Core\CNCServiceArrayTrim($push, 'query');
            array_walk($query, function(&$v, $k, $path) {
                $v = $k . '=' . ($k == 'return' ? base64_encode($path) : $v);
            }, $this->_request->full_path);
        }
        $path = substr($this->_request->path, -1) == '/' ? 
                substr($this->_request->path, 0, strlen($this->_request->path) - 1) :
                $this->_request->path;
        return ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') . '/' . $path . '/' . implode('/', $push) .
                ( $query ? '/?' . implode('&', $query) : '' );
    }
}
