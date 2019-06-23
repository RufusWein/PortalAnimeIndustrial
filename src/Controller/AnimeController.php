<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro");}

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
        if (!Util::esUsuario($this->getUser(),"ADMINISTRADOR")){ return $this->redirectToRoute("series"); }

        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        
        $porDefecto = [
            'nuevo_anime'      => "checked",
            'portada'          => "",
            'titulo'           => "",
            'descripcion'      => "",
            'etiquetas_sel'    => "",
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
     * @Route("/anime/get/{id}", name="get_anime", methods={"POST"})
     */
    public function getAnime($id, Request $request) {
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro"); }

        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        //$datos = json_decode( $request->getContent(), true); // a traves de ajax
        $anime = $animes->find($id);

        $respuesta =[
            'titulo'      => $anime->getTitulo(),
            'descripcion' => $anime->getDescripcion(),
            'etiquetas'   => $anime->getEtiquetas(),
                
            'imagen'     => $anime->getPortada()
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
    public function saveAnime(Request $request){  // solo Admin  
        if (!Util::esUsuario($this->getUser(),"ADMINISTRADOR")) { return $this->redirectToRoute("series"); } 
        
        $entityManager = $this->getDoctrine()->getManager();
        $animes = $entityManager->getRepository(\App\Entity\Anime::class);

        $respuesta = ["ETIQUETAS"    => Util::ETIQUETAS];

        $nuevoAnime= $request->request->get("nuevo_anime"); // devuelve "on" o null        
        $fichero = $request->files->get('portada');
        $etiquetas = $request->request->get("generos");
        $animeId=$request->request->get("anime_id");
        
        $descripcion = $request->request->get("descripcion");
        $error=($descripcion==NULL);
        
        $videoUrl = $request->request->get("video_url");
        $error=($videoUrl==NULL);
        
        $videoTitulo = $request->request->get("video_titulo");
        $error=($descripcion==NULL);

        $titulo = $request->request->get("titulo");
        if ($error = $animes->tituloInvalido( $nuevoAnime, $animeId, $titulo)){
            $respuesta["error_titulo"] = "Título en uso!";
        }

        $fechaPublicacion = $request->request->get("fecha_publicacion");
        if ($error= Util::FechaInvalida($fechaPublicacion)){
            $respuesta["error_fecha"] = "Fecha incorrecta!";
        }

        //$error=true;
        if ($error){ 
            $respuesta['nuevo_anime']       = ($nuevoAnime=="on"?"checked":"");
            // Cada vez que hacemos un submit el frontend hace una copia temporal en local (en el browser)
            // si enviamos "error" al frontend le indicamos que recupere la copia temporal y la use.
            $respuesta['portada']           = "error";
            $respuesta['titulo']            = $titulo;
            $respuesta['descripcion']       = $descripcion;
            $respuesta['etiquetas_sel' ]    = $etiquetas;
            $respuesta['fecha_publicacion'] = $fechaPublicacion->format("Y-m-d");
                    
            $respuesta['anime_id']     = $animeId;
            $respuesta['video_titulo'] = $videoTitulo;
            $respuesta['video_url']    = $videoUrl;

            $respuesta['animes'] = $animes->listar();

            return $this->render('anime/add.html.twig', $respuesta);
        }
        // Guardamos ******************************************************
        $nombreFicheroEncriptado = Util::ProcesaFichero($this->getParameter('ruta_portadas'),$fichero);

        // Creamos el anime si es el caso, OJO porque esto no es el v�deo
        if ($nuevoAnime){
            $anime = $animes->crear([
                'titulo'            => $titulo,
                'descripcion'       => $descripcion,
                'etiquetas'         => $etiquetas,
                'fecha_publicacion' => new \DateTime($fechaPublicacion),
                'fichero'           => $nombreFicheroEncriptado
            ]);
            $entityManager->persist($anime);
        } else {
            $anime = $animes->actualiza($animeId,[
                'titulo'      => $titulo,
                'descripcion' => $descripcion,
                'etiquetas'   => $etiquetas,
                'fichero'     => $nombreFicheroEncriptado
            ]);
        }

        $video = new \App\Entity\Capitulo();
        $video->setIdAnime($anime);
        $video->setUrlVideo($videoUrl);
        $video->setFechaPublicacion(new \DateTime($fechaPublicacion));
        $video->setTitulo($videoTitulo);
        // En teor�a lo de los cap�tulos podr�amos hacerlo con un trigger en MySQL
        $entityManager->persist($video);
        $entityManager->flush();
        
        return $this->redirectToRoute("series");
    }
    /**
     * @Route("/anime/edit/{id}", name="editAnime")
     */
    public function editAnime($id, Request $request){ // solo Admin
        if (!Util::esUsuario($this->getUser(),"ADMINISTRADOR")){ return $this->redirectToRoute("series"); } 
          
        return $this->render('anime/ver.html.twig');
    }
    
    /**
     * @Route("/anime/remove/{id}", name="removeAnime")
     */
    public function removeAnime($id) { // solo Admin
        if (!Util::esUsuario($this->getUser(),"ADMINISTRADOR")){ return $this->redirectToRoute("series"); } 
  
        return $this->render('anime/index.html.twig');
    }
    
    /**
     * @Route("/anime/info/{id}", name="infoAnime")
     */
    public function infoAnime($id){
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro"); }
          
        //return $this->render('usuario/perfil.html.twig');
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        //$datos = json_decode( $request->getContent(), true); // a traves de ajax
        $anime = $animes->find($id);
        $capitulos =[];

        foreach ($anime->getCapitulos() as $capitulo){
            $capitulos[]=[
                "capitulo_id"     => $capitulo->getId(),
                "capitulo_titulo" => $capitulo->getTitulo(),
                "capitulo_url"    => $capitulo->getUrlVideo() 
            ];
        }

        $respuesta =[
            'titulo'      => $anime->getTitulo(),
            'descripcion' => $anime->getDescripcion(),
            'etiquetas'   => $anime->getEtiquetas(),
                
            //'imagen'     => $anime->getPortada(),
            'capitulos'  => $capitulos
        ];
        
        return new Response(json_encode($respuesta));
        /*return new JsonResponse(
            $anime->getCapitulos(),
            JsonResponse::HTTP_CREATED
        );*/ 
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

    /**
     * @Route("/anime/{idanime}", name="ver_capitulos")
     */ 
    public function verCapitulos($idanime){
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro"); }
          
        setlocale(LC_ALL,"es_ES.UTF8");
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        $anime = $animes->find($idanime);
        $capitulos =[];

        foreach ($anime->getCapitulos() as $capitulo){
            $capitulos[]=[
                "id"                => $capitulo->getId(),
                "titulo"            => $capitulo->getTitulo(),
                "fecha_publicacion" => ucfirst(strftime("%A, %d de %B de %Y",
                                               $capitulo->getFechaPublicacion()->getTimestamp()))
            ];
        }

        $respuesta =[
            'id'          => $anime->getId(),
            'imagen'      => $anime->getPortada(),
            'titulo'      => $anime->getTitulo(),
            'descripcion' => $anime->getDescripcion(),
            'fecha_publicacion'=> ucfirst(strftime("%A, %d de %B de %Y",
                                          $anime->getFechaPublicacion()->getTimestamp())),

            'capitulos'  => $capitulos,

            'ETIQUETAS'  => Util::ETIQUETAS,
            'etiquetas_sel'   => $anime->getEtiquetas(),
        ];

        return $this->render('anime/capitulos.html.twig', $respuesta);
    }
   
}
