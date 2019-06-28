<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\Validator\Constraints\DateTime;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author SrWhi
 */
class Util {

    public const ETIQUETAS =array(  "Acción","Héroes","Fantasía","Sangriento","Trama","Infantil",
                                    "Homosexual","Harem","Sexo","Erótico","Romance","Robots","Futurista",
                                    "Histórico","Musical","Apocalítico","Espacial","Drama","Monstruos",
                                    "Chicos","Chicas","Comedia","Policial");

    //put your code here
    public function ProcesaFichero($ruta, $fichero){
        if ($fichero) {
            $nombreFicheroEncriptado = md5(uniqid()).'.'.$fichero->guessExtension();
            try {
                $fichero->move( $ruta, $nombreFicheroEncriptado);               
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return NULL;
            }
        } else {
            return NULL;
        }

        return $ruta."/".$nombreFicheroEncriptado;
    }

    //put your code here
    public function GuardaFichero($ruta, $nombre, $fichero){
        if ($fichero) {
            $nombre.= ".".$fichero->guessExtension();
            try {
                $fichero->move( $ruta, $nombre);               
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return NULL;
            }
        } else {
            return NULL;
        }

        return $ruta."/".$nombre;
    }

    public function RenombraFichero($nombre_viejo, $nombre_nuevo){
        $filesystem = new Filesystem();

        try {
            $filesystem->rename($nombre_viejo , $nombre_nuevo);              
        } catch (IOExceptionInterface $e) {
            // ... handle exception if something happens during file upload
            return false;
        }

        return true;
    }

    public function FechaInvalida($fecha){
        if ($fecha){
            $fecha = new \DateTime($fecha);
            if ($fecha > new \DateTime()){
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    public function esUsuario($usuario,$rol) {
        if ($usuario ==NULL) { 
            return false; 
        }

        if ($rol!=""){
            return in_array($rol,$usuario->getRoles(),TRUE);
        }

        return true;
    }
}
