<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @Route("/anime/edit/{idanime}", name="editAnime")
     */
    public function editAnime($idanime){ // solo Admin
        if (!Util::esUsuario($this->getUser(),"ADMINISTRADOR")){ return $this->redirectToRoute("series"); } 

        setlocale(LC_ALL,"es_ES.UTF8");
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        $repositorio = $this->getDoctrine()->getRepository(\App\Entity\Capitulo::class);
        $anime = $animes->find($idanime);
        $capitulos =[];

        foreach ($repositorio->listarCapitulosPorFecha($anime) as $capitulo){
            $capitulos[]=[
                "id"                => $capitulo->getId(),
                "titulo"            => $capitulo->getTitulo(),
                "fecha_publicacion" => ucfirst(strftime("%A, %d de %B de %Y",
                                               $capitulo->getFechaPublicacion()->getTimestamp()))
            ];
        }

        $this->container->get('session')->set("portada_".$idanime,[ "fichero"=>NULL,"imagen"=>$anime->getPortada()]);       

        $respuesta =[
            'anime_id'    => $idanime,
            'imagen'      => $this->container->get('session')->get("portada_".$idanime)["imagen"],
            'titulo'      => $anime->getTitulo(),
            'descripcion' => $anime->getDescripcion(),
            'fecha_publicacion'=> $anime->getFechaPublicacion()->format("Y-m-d"),

            'capitulos'  => $capitulos,

            'ETIQUETAS'  => Util::ETIQUETAS,
            'etiquetas_sel'   => $anime->getEtiquetas(),
        ];

        return $this->render('anime/edit_anime.html.twig', $respuesta);
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
        $edicion = $request->request->get("edicion"); // on o null      
        $etiquetas = $request->request->get("generos");
        $animeId=$request->request->get("anime_id");
        $fichero = $request->files->get('portada');
        $error=false;

        $descripcion = $request->request->get("descripcion");
        if ($descripcion===NULL){
            $error = $respuesta["error_descripcion"] = "Error en la descripción!";
        }

        $videoUrl = $request->request->get("video_url");
        if ($videoUrl===NULL && $edicion===NULL){
            $error = $respuesta["error_url"] = "Error en la Url!";
        }
        
        $videoTitulo = $request->request->get("video_titulo");
        if ($videoTitulo===NULL && $edicion===NULL){
            $error = $respuesta["error_video_titulo"] = "Error en el título!";
        }

        $titulo = $request->request->get("titulo");
        if ($animes->tituloInvalido( $nuevoAnime, $animeId, $titulo)){
            $error = $respuesta["error_titulo"] = "Título en uso!";
        }

        $fechaPublicacion = $request->request->get("fecha_publicacion");
        if (Util::FechaInvalida($fechaPublicacion)){
            $error = $respuesta["error_fecha"] = "Fecha incorrecta!";
        }

        //$error=true;
        if ($error){ 
            $respuesta['nuevo_anime']       = ($nuevoAnime=="on"?"checked":"");
            // Cada vez que hacemos un submit el frontend hace una copia temporal en local (en el browser)
            // si enviamos "error" al frontend le indicamos que recupere la copia temporal y la use.
            $respuesta['imagen']            = $this->container->get('session')->get('portada_'.$animeId)["imagen"];
            $respuesta['titulo']            = $titulo;
            $respuesta['descripcion']       = $descripcion;
            $respuesta['etiquetas_sel' ]    = $etiquetas;
            $respuesta['fecha_publicacion'] = $fechaPublicacion;
                    
            $respuesta['anime_id']     = $animeId;
            $respuesta['video_titulo'] = $videoTitulo;
            $respuesta['video_url']    = $videoUrl;

            $respuesta['animes'] = $animes->listar();

            if ($edicion){
                $respuesta['error'] = "error";
                $respuesta['capitulos'] = $animes->find($animeId)->getCapitulos();
                return $this->render('anime/edit_anime.html.twig', $respuesta);                
            }

            return $this->render('anime/add.html.twig', $respuesta);
        }
        // Guardamos ******************************************************       
        $nombreFicheroEncriptado = Util::ProcesaFichero($this->getParameter('ruta_portadas'),$fichero);
        if ($nombreFicheroEncriptado===NULL){
            $nombreFichero = $this->container->get('session')->get("portada_".$animeId)["fichero"];
            $array = explode(".",$nombreFichero);
            $nombreFicheroEncriptado = $this->getParameter('ruta_portadas')."/".md5(uniqid()).".".end($array);
            //return new Response("Fichero Encriptado : ".$nombreFicheroEncriptado." Fichero movido : ".$nombreFichero);
            if (Util::RenombraFichero($nombreFichero, $nombreFicheroEncriptado )===false){
                $nombreFicheroEncriptado = NULL;
            }
            //return new Response("Fichero Encriptado : ".$nombreFicheroEncriptado);

        }

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
            if ($edicion){
                $anime->setFechaPublicacion(new \DateTime($fechaPublicacion));
            }
        }

        // Esta parte hay que desabilitarla en el caso de que sea una peticion de actualizacion
        // desde un "edit"
        if ($edicion===NULL){
            $video = new \App\Entity\Capitulo();
            $video->setAnime($anime);
            $video->setUrlVideo($videoUrl);
            $video->setFechaPublicacion(new \DateTime($fechaPublicacion));
            $video->setTitulo($videoTitulo);
            // En teor�a lo de los cap�tulos podr�amos hacerlo con un trigger en MySQL
            $entityManager->persist($video);
        }
        $entityManager->flush();
        
        $this->container->get('session')->set("portada_".$animeId, NULL);

        return $this->redirectToRoute("series");
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
     * @Route("/anime/{idanime}", name="ver_capitulos")
     */ 
    public function verCapitulos($idanime){
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro"); }
          
        setlocale(LC_ALL,"es_ES.UTF8");
        $animes = $this->getDoctrine()->getRepository(\App\Entity\Anime::class);
        $repositorio = $this->getDoctrine()->getRepository(\App\Entity\Capitulo::class);
        $anime = $animes->find($idanime);
        $capitulos =[];

        foreach ($repositorio->listarCapitulosPorFecha($anime) as $capitulo){
            $capitulos[]=[
                "id"                => $capitulo->getId(),
                "titulo"            => $capitulo->getTitulo(),
                "fecha_publicacion" => ucfirst(strftime("%A, %d de %B de %Y",
                                               $capitulo->getFechaPublicacion()->getTimestamp()))
            ];
        }

        $respuesta =[
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

    /**
     * @Route("/anime/ver/{idcapitulo}", name="ver_capitulo")
     */ 
    public function verCapitulo($idcapitulo){
        if (!Util::esUsuario($this->getUser(),"")){ return $this->redirectToRoute("login_registro"); }
          
        $capitulos = $this->getDoctrine()->getRepository(\App\Entity\Capitulo::class); 
        $capitulo = $capitulos->find($idcapitulo);

        $respuesta =[
            'titulo'      => $capitulo->getAnime()->getTitulo(),
            'capitulo'    => $capitulo->getTitulo(),
            'url'         => $capitulo->getUrlVideo(),

            //'anterior'    => $capitulos->anterior($idcapitulo)->getId(), // devuelve null en caso de ser el primero
            //'siguiente'   => $capitulos->siguiente($idcapitulo) // devuelve null en caso de no acabar
        ];

        return $this->render('anime/ver.html.twig', $respuesta);
        //return new Response(json_encode($respuesta));
    }

    /**
     * @Route("/inicia/sesion", name="prueba_session")
     */ 
    public function pruebaSession(){
        // Iniciamos la session y guardamos un elemento
        $this->container->get('session')->set('prueba','hola mundo');
        return new Response(json_encode($this->container->get('session')->get('prueba')));
    }

    /**
     * @Route("/recupera/sesion", name="request_session")
     */ 
    public function requestSession(){
        // mostramos el contenido
        return new Response(json_encode($this->container->get('session')->get('prueba')));
    }
   
    /**
     * @Route("/anime/portada/{idanime}", name="guarda_fichero_imagen", methods={"POST"})
     */
    public function guardaFicheroImagen($idanime, Request $request){
        $this->container->get('session')->set("portada_".$idanime,[
            "fichero" => Util::GuardaFichero($this->getParameter('ruta_portadas'),
                                             "portada_".$idanime,
                                             $request->files->get('fichero')),
            "imagen"  => $request->request->get("imagen")
        ]);
        return new Response();
    }

}
