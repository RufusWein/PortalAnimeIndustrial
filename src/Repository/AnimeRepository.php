<?php

namespace App\Repository;

use App\Entity\Anime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Anime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anime[]    findAll()
 * @method Anime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Anime::class);
    }

    /**
     * @return Object[] Returns an array of json objects
     */   
    public function listar(){
        $resultado=array(["id_anime" => -1]);
    
        foreach ($this->findBy([],['titulo'=>'ASC']) as $anime){
            array_push($resultado,[
                "id_anime"   => $anime->getId(),
                "titulo"     => $anime->getTitulo(),
                "portada"    => $anime->getPortada(),
                "num_videos" => $anime->getCapitulos()->count()               
            ]);
        }
        
        return $resultado;
    }
    
    /**
      * @return Boolean Returns true/false
      */
    public function tituloInvalido( $nuevoAnime, $animeId, $titulo){
        if ($titulo){
            if ($nuevoAnime == NULL) {
                $resultado = $this->findOneBy(['titulo'=>$titulo]);
                if ($resultado != NULL) {
                    if ($resultado->getId() != $animeId) {
                        return true;
                    }
                }
            } else 
            if ($this->findOneBy(['titulo'=>$titulo])) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
      * @return Anime Returns an Anime object
      */
    public function crear($animeInfo){
        $anime = new \App\Entity\Anime();
            
        $anime->setTitulo($animeInfo['titulo']);
        $anime->setDescripcion($animeInfo['descripcion']);
        $anime->setEtiquetas($animeInfo['etiquetas']);
        $anime->setValoracion(3); // por defecto
        $anime->setFechaPublicacion($animeInfo['fecha_publicacion']);//->format("Y-m-d H:i:s"));)

        if ($animeInfo['fichero']) {
            $anime->setPortada($animeInfo['fichero']);
        }

        return $anime;    
    }

    /**
      * @return Anime Returns an Anime object
      */
    public function actualiza($id, $animeInfo)
    {
        $anime = $this->find($id);
        $anime->setTitulo($animeInfo['titulo']);
        $anime->setDescripcion($animeInfo['descripcion']);
        $anime->setEtiquetas($animeInfo['etiquetas']);
        if ($anime->getPortadaPath() != NULL && $animeInfo['fichero']!=NULL){
            $fs=new \Symfony\Component\Filesystem\Filesystem();
            try {
                $fs->remove([$anime->getPortadaPath()]);
            } catch (IOExceptionInterface $exception) {
                //echo "An error occurred while creating your directory at ".$exception->getPath();
            }    
        }
        if ($animeInfo['fichero']) {
            $anime->setPortada($animeInfo['fichero']);
        }

        return $anime;
    }

    // /**
    //  * @return Anime[] Returns an array of Anime objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Anime
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
