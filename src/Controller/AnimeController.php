<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Constraints\DateTime;
use App\Utils\Util;

class AnimeController extends AbstractController
{
    public function searchAnime(){
        
    }
    
    /**
     * @Route("/series", name="series")
     */    
    public function anime(){
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }

        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        
        return $this->render('anime/index.html.twig', 
            ['cabecera'  =>true, 
             'ETIQUETAS' => Util::ETIQUETAS,
             'animes'    => $animes->listar()
            ]);        
        /*
        return new JsonResponse(
            ['animes' => $animes->listar() ],
            JsonResponse::HTTP_CREATED
        );*/
    }
    
    /**
     * @Route("/anime/add", name="add_anime")
     */
    public function addAnime() { // solo Admin
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
        if (!in_array("ADMINISTRADOR",$this->getUser()->getRoles(),TRUE)) { return $this->redirectToRoute("series"); }
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        
        $porDefecto = [
            'nuevo_anime'     => "checked",
            //'portada'         => "",
            'titulo'          => "",
            'descripcion'     => "",
            'etiquetas_sel'   => "",
            'fecha_publicacion'=> (new \DateTime())->format("Y-m-d"),
            
            'anime_id'      => "",
            'video_titulo'  => "",
            'video_url'     => "",
            
            'animes' => $animes->listar(),

            'ETIQUETAS' => Util::ETIQUETAS
        ];
        
        return $this->render('anime/add.html.twig', $porDefecto);
    }

    /**
     * @Route("/anime/{id}", name="get_anime", methods={"POST"})
     */
    public function getAnime($id, Request $request) {
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        //$datos = json_decode( $request->getContent(), true); // a traves de ajax
        $anime = $animes->find($id);

        $respuesta =[
            'titulo'      => $anime->getTitulo(),
            'descripcion' => $anime->getDescripcion(),
            'etiquetas'   => $anime->getEtiquetas(),
                
            'portada'     => $anime->getPortada()
        ];
        
        return new Response(json_encode($respuesta));
        /*return new JsonResponse(
            $respuesta,
            JsonResponse::HTTP_CREATED
        );*/ 
    }
    
    /**
     * @Route("/anime/save", name="save_anime", methods={"POST"})
     */
    public function saveAnime(Request $request)  // solo Admin
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); } 
        if (!in_array("ADMINISTRADOR",$this->getUser()->getRoles(),TRUE)) { return $this->redirectToRoute("series"); } 
        
        $error=false;
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        $entityManager = $this->getDoctrine()->getManager();
        $respuesta = ["ETIQUETAS"    => Util::ETIQUETAS];
        $video = new \App\Entity\Capitulo();
        $nuevoAnime= $request->request->get("nuevo_anime"); // devuelve "on" o null        
        $fichero = $request->files->get('portada');
        $etiquetas = $request->request->get("generos");
        $animeId=1; // Aqui se tomaria del request al name "anime_id"
        
        $descripcion = $request->request->get("descripcion");
        if ($descripcion==NULL){
            $error=true;    
        }      
        
        $videoUrl = $request->request->get("video_url");
        if ($videoUrl==NULL){
            $error=true;
        }
        
        $videoTitulo = $request->request->get("video_titulo");
        if ($descripcion==NULL){
            $error=true;
        }
        
        $titulo = $request->request->get("titulo");
        if ($titulo){
            if ($animes->findOneBy(['titulo'=>$titulo])) {
                $error = true;
                $respuesta["error_titulo"] = "Título en uso!";
            }
        } else {
            $error=true;
        }

        $fechaPublicacion = $request->request->get("fecha_publicacion");
        if ($fechaPublicacion){
            $fechaPublicacion = new \DateTime($fechaPublicacion);
            if ($fechaPublicacion > new \DateTime()){
                $error=true;
                $respuesta["error_fecha"] = "Fecha incorrecta!";
            }
        } else {
            $error=true;
        }
        
        //$error=true;
        if ($error){ 
            $respuesta['nuevo_anime']    = ($nuevoAnime=="on"?"checked":"");
            //$respuesta['portada']        = $fichero;
            $respuesta['titulo']         = $titulo;
            $respuesta['descripcion']    = $descripcion;
            $respuesta['etiquetas_sel' ] = $etiquetas;
            $respuesta['fecha_publicacion'] = $fechaPublicacion->format("Y-m-d");
                    
            $respuesta['anime_id']     = $animeId;
            $respuesta['video_titulo'] = $videoTitulo;
            $respuesta['video_url']    = $videoUrl;

            return $this->render('anime/add.html.twig', $respuesta);
        }
        // Guardamos ******************************************************
        if ($fichero) {
            $nombreFicheroEncriptado = md5(uniqid()).'.'.$fichero->guessExtension();
            try {
                $fichero->move( $this->getParameter('ruta_portadas'), $nombreFicheroEncriptado);               
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return $this->redirectToRoute("perfil");
            }
        }
        
        // Creamos el anime si es el caso, OJO porque esto no es el v�deo
        if ($nuevoAnime){
            $anime = new \App\Entity\Anime();
            $anime->setTitulo($titulo);
            $anime->setDescripcion($descripcion);
            $anime->setEtiquetas($etiquetas);
            $anime->setValoracion(3); // por defecto
            if ($fichero) {
                $anime->setPortada($this->getParameter('ruta_portadas')."/".$nombreFicheroEncriptado);
            }
            $anime->setFechaPublicacion($fechaPublicacion);//->format("Y-m-d H:i:s"));
            $entityManager->persist($anime);
            $entityManager->flush();
        } else {
            $anime = $animes->find($animeId);
        }
        
        $video->setIdAnime($anime);
        $video->setUrlVideo($videoUrl);
        $video->setFechaPublicacion($fechaPublicacion);
        $video->setTitulo($videoTitulo);
        // En teor�a lo de los cap�tulos podr�amos hacerlo con un trigger en MySQL
        $entityManager->persist($video);
        $entityManager->flush();
        
        return $this->redirectToRoute("series");
    }
    /**
     * @Route("/anime/edit/{id}", name="editAnime")
     */
    public function editAnime($id, Request $request) // solo Admin
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
        if (!in_array("ADMINISTRADOR",$this->getUser()->getRoles(),TRUE)) { return $this->redirectToRoute("series"); } 
          
        return $this->render('anime/ver.html.twig');
    }
    
    /**
     * @Route("/anime/remove/{id}", name="removeAnime")
     */
    public function removeAnime($id) // solo Admin
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
        if (!in_array("ADMINISTRADOR",$this->getUser()->getRoles(),TRUE)) { return $this->redirectToRoute("series"); } 
  
        return $this->render('anime/index.html.twig');
    }
    
    /**
     * @Route("/anime/info/{id}", name="infoAnime")
     */
    public function infoAnime($id)
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
          
        return $this->render('usuario/perfil.html.twig');
    }

    /**
     * @Route("/anime/list", name="listAnime")
     */
    public function listAnimes(Request $request) // cualquier usuario incluso invitado
    {          
        return $this->render('usuario/perfil.html.twig');
    }

    /**
     * @Route("/anime/{idanime}/capitulo/{capitulo}", name="verAnime")
     */    
    public function verAnime($idanime, $capitulo)
    {
        if ($this->getUser() ==NULL) { return $this->redirectToRoute("login_registro"); }
          
        return $this->render('usuario/perfil.html.twig');
    }
   
}
