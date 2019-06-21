<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ErrorException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnimeRepository")
 */
class Anime
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etiquetas;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $valoracion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $portada;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha_publicacion;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Capitulo", mappedBy="anime", orphanRemoval=true)
     */
    private $capitulos;

    public function __construct()
    {
        $this->capitulos = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getEtiquetas(): string
    {
        return $this->etiquetas;
    }

    public function setEtiquetas(string $etiquetas): self
    {
        $this->etiquetas = $etiquetas;

        return $this;
    }

    public function getValoracion(): int
    {
        return $this->valoracion;
    }

    public function setValoracion(int $valoracion): self
    {
        $this->valoracion = $valoracion;

        return $this;
    }
    
    public function getPortadaPath(){
        return $this->portada;
    }
    
    public function getPortada()
    {
        try {
            $fs=new \Symfony\Component\Filesystem\Filesystem();
            if ($fs->exists($this->portada)){
                $con=file_get_contents($this->portada);
                $en=base64_encode($con);
                $mime="image/gif";
                $binary_data="data:" .$mime. ";base64," . $en ;
                return $binary_data;
            }
        } catch (ErrorException $e){
            
        }
        return NULL;
    }

    public function setPortada(string $portada): self
    {
        $this->portada = $portada;

        return $this;
    }

    public function getFechaPublicacion(): \DateTime
    {
        return $this->fecha_publicacion;
    }

    public function setFechaPublicacion(\DateTime $fecha_publicacion): self
    {
        $this->fecha_publicacion = $fecha_publicacion;

        return $this;
    }

    /**
     * @return Collection|Capitulo[]
     */
    public function getCapitulos(): Collection
    {
        return $this->capitulos;
    }

    public function addCapitulo(Capitulo $capitulo): self
    {
        if (!$this->capitulos->contains($capitulo)) {
            $this->capitulos[] = $capitulo;
            $capitulo->setIdAnime($this);
        }

        return $this;
    }

    public function removeCapitulo(Capitulo $capitulo): self
    {
        if ($this->capitulos->contains($capitulo)) {
            $this->capitulos->removeElement($capitulo);
            // set the owning side to null (unless already changed)
            if ($capitulo->getIdAnime() === $this) {
                $capitulo->setIdAnime(null);
            }
        }

        return $this;
    }
}
