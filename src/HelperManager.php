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
use PowerOn\Utility\Inflector;

/**
 * HelperManager
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class HelperManager {
    /**
     * Contenedor de helpers
     * @var \Pimple\Container
     */
    private $_container;

    /**
     * Crea un nuevo manager de helpers
     * @param array $config
     */
    public function __construct() {
        $this->_container = new \Pimple\Container();
        
        $this->loadDefaultHelpers();
    }

    /**
     * Carga un Helper
     * @param string $name El nombre a utilizar
     * @param string $namespace [Opcional] Namespace del helper a cargar, debe finalizar con \\
     * @throws DevException
     */
    public function loadHelper($name, $namespace = NULL) {
        $helper_class = ($namespace ? $namespace : 'PowerOn\Helper\\') . Inflector::classify($name) . 'Helper';
        if ( !class_exists($helper_class) ) {
            throw new \Exception(sprintf('No se encuentra la clase(%s), debe configurar correctamente su autoloader', $helper_class));
        }
        
        $this->_container[$name] = function($c) use ($helper_class) {
            return new $helper_class($c);
        };
    }
    
    /**
     * Verifica si el helper solicitado existe
     * @param string $name Nombre del helper a verificar
     * @return boolean
     */
    public function helperExist($name) {
        return $this->_container->offsetExists($name);
    }
    
    /**
     * Devuelve el Helper solicitado
     * @param string $name Nombre del helper
     * @return Helper
     * @throws \Exception
     */
    public function getHelper($name) {
        if ( !$this->helperExist($name) ) {
            throw new \Exception(sprintf('El Helper (%s) solicitado no fue cargado.', $name));
        }
        $helper = $this->_container[$name];
        $helper->configure([]);
        return $helper;
    }
    
    /**
     * Carga los helpers por defecto
     */
    private function loadDefaultHelpers() {
        $this->loadHelper('html');
        $this->loadHelper('block');
        $this->loadHelper('url');
    }
}
