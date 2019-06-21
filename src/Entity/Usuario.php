<?php

namespace App\Entity;

use ErrorException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class Usuario implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     */
    private $alias;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $apellidos;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;
    
    /**
     * @ORM\Column(type="string")
     */
    private $retrato;
    
    /**
     * @ORM\Column(type="string")
     */
    private $animes_favoritos;

    /**
     * @ORM\Column(type="string")
     */
    private $intereses;

    public function setAnimesFavoritos($animes_favoritos = null): self{
        $this->animes_favoritos = $animes_favoritos;
        
        return $this;
    }
    
    public function getAnimesFavoritos(){
        return $this->animes_favoritos;
    }

    public function setIntereses($intereses = null):self{
        $this->intereses = $intereses;
        
        return $this;
    }    

    public function getIntereses(){
        return $this->intereses;
    }    
    
    public function setRetrato($retrato = null): self
    {
        $this->retrato = $retrato;
        
        return $this;
    }

    public function getRetratoPath(){
        return $this->retrato;
    }
    
    public function getRetrato()
    {
        try {
            $fs=new \Symfony\Component\Filesystem\Filesystem();
            if ($fs->exists($this->retrato)){         
                $con=file_get_contents($this->retrato);
                $en=base64_encode($con);
                $mime="image/gif";
                $binary_data="data:" .$mime. ";base64," . $en ;        
                return $binary_data;
            }
        } catch (ErrorException $e){

        }
        return NULL;
    }
    
    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }
    
    public function getApellidos(): string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }
    
    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }
    
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'USUARIO';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
