<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CapituloRepository")
 */
class Capitulo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\anime", inversedBy="capitulos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $anime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url_video;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha_publicacion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titulo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capitulo;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdAnime(): Anime
    {
        return $this->anime;
    }

    public function setIdAnime(Anime $anime): self
    {
        $this->anime = $anime;

        return $this;
    }

    public function getUrlVideo(): string
    {
        return $this->url_video;
    }

    public function setUrlVideo(string $url_video): self
    {
        $this->url_video = $url_video;

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

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getCapitulo(): int
    {
        return $this->capitulo;
    }

    public function setCapitulo(int $capitulo): self
    {
        $this->capitulo = $capitulo;

        return $this;
    }
}
