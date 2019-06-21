<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UsuarioController extends AbstractController
{
    
    /**
     * @Route("/perfil", name="perfil")
     */
    public function perfil()
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
          
        return $this->render('usuario/perfil.html.twig');
    }

    /**
     * @Route("/edit", name="edit", methods={"POST"})
     */
    public function edit(Request $request)
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
        
        $fichero = $request->files->get('retrato');
        if ($fichero!=NULL) {
            $nombreFicheroEncriptado = md5(uniqid()).'.'.$fichero->guessExtension();
            try {
                $fichero->move( $this->getParameter('ruta_retratos'), $nombreFicheroEncriptado);
                
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return $this->redirectToRoute("perfil");
            }
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $usuarios = $entityManager->getRepository(\App\Entity\Usuario::class);
        $usuario = $usuarios->find($this->getUser()->getId());
        if ($usuario->getRetratoPath() != NULL && $fichero!=NULL){
            $fs=new \Symfony\Component\Filesystem\Filesystem();
            try {
                $fs->remove([$usuario->getRetratoPath()]);
            } catch (IOExceptionInterface $exception) {
                //echo "An error occurred while creating your directory at ".$exception->getPath();
            }    
        } 
        
        if ($fichero!=NULL) {
            $usuario->setRetrato($this->getParameter('ruta_retratos')."/".$nombreFicheroEncriptado);
        }
        
        $alias=$request->request->get("alias");
        if ($alias!=NULL && $usuario->getAlias()!=$alias){
            if (!$usuarios->findBy(["alias"=>$alias])) {
                $usuario->setAlias($alias);
            } else {
                // error, repetido
                return $this->render('usuario/perfil.html.twig',["error_alias" => "Alias en uso!"]);
            }
        }

        $email=$request->request->get("email");
        if ($email!=NULL){
            if ($usuario->getEmail()!=$email && !$usuarios->findByOne(['email'=>$email])) {        
                $usuario->setAlias($email);        
            } else {
                // error, repetido
                return $this->render('usuario/perfil.html.twig',["error_email" => "Email en uso!"]);
            }
        }
                
        $password=$request->request->get("password");
        if ($password!=NULL){
            if ($password == $request->request->get("repassword")) {        
                $usuario->setPassword(password_hash($password, PASSWORD_ARGON2I));        
            } else {
                // error , no conciden las contraseñas
                return $this->render('usuario/perfil.html.twig',["error_password" => "Debe repetir el password!"]);
            }
        }
                
        $usuario->setNombre($request->request->get("nombre"))
                ->setApellidos($request->request->get("apellidos"))
                ->setAnimesFavoritos($request->request->get("animes_favoritos"))
                ->setIntereses($request->request->get("intereses"));
        $entityManager->flush();
        
        return $this->redirectToRoute("indice");
    }
    
    function Valida($valor, $condicion, $accion){
        if ($valor!=NULL){
            if ($condicion){
                $accion;
                return true;
            } else {
                return false;
            }
        }
    }
    
}
