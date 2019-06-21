<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginRegistroController extends AbstractController
{
    /**
     * @Route("/login", name="login_registro", methods={"GET","HEAD"})
     */
    public function index()
    {
        return $this->render('login_registro/login.html.twig', [
            'controller_name' => 'LoginRegistroController',
            'error' => "FUCK"
        ]);
    }
    
    /**
     * @Route("/registro", name="registro", methods={"POST"})
     */
    public function registro(Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_csrf_token'))) {
            return $this->redirectToRoute('login');
        }
        
        $last_info=[];
        $respuesta=['last_username'   => ''];
        $emailIsOk=false;
        $usernameIsOk=false;
        $usuarios = $this->getDoctrine()->getRepository(\App\Entity\Usuario::class);

        $email=$request->request->get("email");
        if ($email!=NULL){
            $last_info["email"]=$email;
            // Comprobamos correo en la BBDD
            if ($usuarios->findOneBy(['email' => $email])){
                $respuesta['error_email']="Existe una cuenta con ese correo";                    
            } else {
                $emailIsOk=true;
            }
        }

        $name=$request->request->get("name");
        if ($name!=NULL){
            $last_info["name"]=$name;
        }

        $surname=$request->request->get("surname");
        if ($surname!=NULL){
            $last_info["surname"]=$surname;            
        }

        $username=$request->request->get("username");
        if ($username!=NULL){
            $last_info["username"]=$username; 
            // Comprobamos alias en la BBDD
            if ($usuarios->findOneBy(['alias' => $username])){
                $respuesta['error_alias']="Existe una cuenta con ese alias";                    
            } else {
                $usernameIsOk=true;
            }            
        }
        
        if ($emailIsOk && $usernameIsOk){
            //comprobamos contraseña
            $password=$request->request->get("password");
            if (true){
                // En caso de que todo vaya bien creamos usuario...
                $usuarioNoValidado = new \App\Entity\UsuarioNoRegistrado();
                $usuarioNoValidado->setNombre($name);
                $usuarioNoValidado->setApellidos($surname);
                $usuarioNoValidado->setAlias($username);
                $usuarioNoValidado->setEmail($email);
                $usuarioNoValidado->setPassword(password_hash($password, PASSWORD_ARGON2I));
                $usuarioNoValidado->setFechaAlta(new \DateTime("now"));//->format("Y-m-d H:i:s"));
                $usuarioNoValidado->setToken(md5($password.$email));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($usuarioNoValidado);
                $entityManager->flush();                
                //  ...enviamos el emial de verificacion
                
                return $this->redirectToRoute('verificacion',
                        ["alias" => $usuarioNoValidado->getAlias()]);
            }
        }
        
        $respuesta['last_info'] = $last_info;
                
        return $this->render('login_registro/login.html.twig', $respuesta );
    }
    
    /**
     * @Route("/validar/{token}", name="validacion", methods={"GET","HEAD"})
     */    
    public function validacion($token){
        // comprobamos el token
        $usuariosNoRegistrados = $this->getDoctrine()->getRepository(\App\Entity\UsuarioNoRegistrado::class);
        if ($usuariosNoRegistrados->findOneBy(['token' => $token])){
            $usuarioNoRegistrado=$usuariosNoRegistrados->findOneBy(['token' => $token]);
            if ($usuarioNoRegistrado->getFechaAlta()->diff(new \DateTime("now"))->format("%h")<24){
                $respuesta = "<h1>Cuenta del usuario ".$usuarioNoRegistrado->getAlias()." activada!<br>".
                             "Ya puedes loguearte</h1>";
                // Copiamos los datos del usuarioNoRegistrado a usuario
                $usuario = new \App\Entity\Usuario();
                $usuario->setNombre($usuarioNoRegistrado->getNombre());
                $usuario->setApellidos($usuarioNoRegistrado->getApellidos());
                $usuario->setAlias($usuarioNoRegistrado->getAlias());
                $usuario->setEmail($usuarioNoRegistrado->getEmail());
                $usuario->setPassword($usuarioNoRegistrado->getPassword());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($usuario);
                $entityManager->flush();    
                // Luego Eliminamos el usuarioNoRegistrado
                $entityManager->remove($usuarioNoRegistrado);
                $entityManager->flush();
            // Caducada
            } else {
               $respuesta = "<h1 class='error'>Token caducado!<br>Vuelva a registrarse</h1>"; 
            }
            // Error de sesión
        } else {
            $respuesta = "<h1 class='error'>Error de sesión!</h1>";
        } 
        
        // si es valido, copiamos los datos del usuario de la tabla "usuario_no_registrado" a la tabla "usuario"
        
        // retornamos pantalla del login
        return $this->render('login_registro/verificacion.html.twig', ["mensaje"=>$respuesta] );
    }
    
//    public function login(Request $request)
//    {
//        $usuarios = $this->getDoctrine()->getRepository(\App\Entity\Usuario::class);
//        $erroresLogin=[];
//        
//        // Comprobamos que exista el usuario
//        if (!$usuarios->findOneBy(['email' => $request->request->get("email")])){
//            $erroresLogin["email"]="Error de sesión";
//                    
//        } else { // Existe y comprobamos la clave
//            if (!$usuarios->findOneBy(['password' => md5($request->request->get("password"))])){
//                $erroresLogin["password"]="Contraseña incorrecta";                
//            
//            // Redireccionamos al indice con el logeo hecho    
//            } else {
//                
//            }
//        }
//        
//        return $this->render('login_registro/login.html.twig', [
//            'controller_name' => 'LoginRegistroController',
//            'erroresLogin'    => $erroresLogin,
//        ]);
//    }
}
