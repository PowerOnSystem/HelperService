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
 * Helper Contenedor de helpers del framework
 * @author Lucas Sosa
 */
class Helper {
    /**
     * Configuración del Helper
     * @var array
     */
    protected $_config;
    /**
     * Contenedor principal
     * @var \Pimple\Container
     */
    private $_container;
    
    /**
     * Configura el helper
     */
    public function configure() {}

    /**
     * Inicializa el helper
     * @param \Pimple\Container $container El contenedor principal
     */
    public function __construct(\Pimple\Container $container) {
        $this->_container = $container;
    }
    
    /**
     * Cargador directo de helpers
     * @param string $name Nombre del helper a cargar
     * @return Helper|null Si se encuentra un helper con ese nombre lo retorna.
     */
    public function __get($name) {
        if ( !isset( $this->{$name} )  && $this->_container->offsetExists($name) ) {
            $this->{$name} = $this->_container[$name];
            
            return $this->{$name};
        }
    }
}
