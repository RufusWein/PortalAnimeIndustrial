<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
//use App\Utils\Util;

class IndiceController extends AbstractController
{
    /**
     * @Route("/", methods={"GET","HEAD"}, name="indice")
     */
    public function index(){       
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        
        return $this->render('indice/index.html.twig',
            ['cabecera'  =>true, 'animes'    => $animes->listar()]);    
    }
    
    /**
     * @Route("/verificacion/{alias}", methods={"GET","HEAD"}, name="verificacion")
     */
    public function verificacion(\Swift_Mailer $mailer, $alias)
    {
        $respuesta = "<h1>Registro del usuario ".$alias." exitoso!<br>".
                     "Ya sólo te falta un paso, verifica tú correo electrónico</h1>";
        $usuariosNoRegistrados = $this->getDoctrine()->getRepository(\App\Entity\UsuarioNoRegistrado::class);
        if ($usuariosNoRegistrados->findOneBy(['alias' => $alias])){
            $token=$usuariosNoRegistrados->findOneBy(['alias' => $alias])->getToken();
            $url=$this->generateUrl("validacion",["token"=>$token],UrlGeneratorInterface::ABSOLUTE_URL);
            $mensaje = (new \Swift_Message("Bienvenido y gracias por unirte a AnimePlus"))
                ->setFrom("anime@gmail.com")
                ->setTo("rgpedroa@gmail.com") // ->setTo($correo)
                ->setBody( "<p><strong>".$alias."</strong> pincha en el enlace para activar la cuenta :".
                           "<a href='".$url."'>".$url."</a></p>", "text/html");
            $mailer->send($mensaje);  
            // Error de sesión
        } else {
            $respuesta = "<h1 class='error'>Error de sesión!</h1>";
        } 

        return $this->render('login_registro/verificacion.html.twig', ["mensaje"=>$respuesta] );
    }
    
    /**
     * @Route("/jason", methods={"POST"} ,name="jason")
     */    
    public function jason(Request $request)
    {

        // Los convertimos en un array
        $datos = json_decode( $request->getContent(), true); // a traves de ajax

        // si es a traves de un formulario
        //$anime_id = $request->request->get("id"); 
        //exit(\Doctrine\Common\Util\Debug::dump($data));
        return new Response("Se recibió id = ".$datos["id"]);
        /*return new JsonResponse(
            [
                'status' => 'ok',
            ],
            JsonResponse::HTTP_CREATED
        );*/
    }
}
