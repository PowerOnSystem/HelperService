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
use PowerOn\Utility\Arr;

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
            'root_dir' => NULL
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
     * puede especificar las queries de la url ej: ['query' => ['foo' => 'bar', 'foo2' => 'bar2']]
     * @param array $url
     * @return string
     */
    public function build( array $url = []) {        
        $vars = [];
        foreach ($url as $k => $u) {
            if ($k !== 'controller' && $k !== 'action' && $k !== 'query') {
                $vars[] = $u;
            }
        }
        
        $gets = key_exists('query', $url) ? $url['query'] : [];
        array_walk($gets, function(&$v, $k) {
            $v = $k . '=' . $v;
        });
        
        $result = ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') 
            . ( key_exists('controller', $url) ? '/' . $url['controller'] : (key_exists('action', $url) ? '/index' : ''))
            . ( key_exists('action', $url) ? '/' . $url['action'] : ($vars ? 'index' : '') )
            . ( $vars ? '/' . implode('/', $vars) : '' )
            . ( $gets ? '/?' . implode('&', $gets) : '' );
        
        return $result ? $result : '/';
    }
    
    /**
     * Modifica una URL agregando o quitando variables,
     * puede modificar las queries de la url ej: ['query' => ['foo' => 'bar', 'foo2' => 'bar2']]
     * @param array $add [Opcional] la URL a agregar
     * @param array $remove [Opcional] la URL a remover
     * @return string
     */
    public function modify(array $add = [], array $remove = [], $controller = NULL, $action = NULL) {
        $path = explode('/', $this->_config['request_path']);
        
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
            $v = (is_string($k) ? $k . '=' : '') . $v;
        });
        
        $url_controller = $controller ? $controller : $this->_config['request_controller'];
        $url_action = $action ? $action : $this->_config['request_action'];
        
        $last_url = ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') . 
            '/' . ( $url_action == 'index' && $url_controller == 'index' && !$url ? '' : $url_controller . '/' ) .
            ( ($url_action == 'index' && $url) || $url_action != 'index' ? $url_action . '/' : '' ) . 
            implode('/', $url);
        
        $gets = $gets_request + $this->_config['request_queries'];
        foreach ($gets_remove as $gr) {
            if (key_exists($gr, $gets)) {
                unset($gets[$gr]);
            }
        } 
        array_walk($gets, function(&$v, $k) {
            $v = $k . '=' . $v;
        });
        
        return $last_url . ($gets ? (substr($last_url, -1) == '/' ? '' : '/') . '?' . implode('&', $gets) : '');
    }
    
    /**
     * Agrega un valor al final de la url
     * @param array $push la URL a agregar al final (Si se quiere agregar queries: ['query' => ['foo' => 'bar', 'foo2' => 'bar2']]
     * @return string
     */
    public function push(array $push = []) {
        $query = [];
        if ( key_exists('query', $push) ) {
            $query = Arr::trim($push, 'query');
            array_walk($query, function(&$v, $k) {
                $v = $k . '=' . $v;
            });
        }
        $path = substr($this->_config['request_path'], -1) == '/' ? 
                substr($this->_config['request_path'], 0, strlen($this->_config['request_path']) - 1) :
                $this->_config['request_path'];
        return ($this->_config['root_dir'] ? '/' . $this->_config['root_dir'] : '') . '/' . $path . '/' . implode('/', $push) .
                ( $query ? '/?' . implode('&', $query) : '' );
    }
}
