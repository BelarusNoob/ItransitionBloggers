<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User implements UserInterface, \Serializable
{
    public const NUM_ITEMS = 10;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\Length(min=2, max=50)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $info;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Please, upload the profile image as a PNG/JPG/JPEG file.")
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" , "image/jpg"})
     */
    private $image;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     */
    private $regDate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="following")
     */
    private $followers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followers")
     * @ORM\JoinTable(name="following",
     *     joinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="following_user_id", referencedColumnName="id")
     *     }
     * )
     */
    private $following;

    /**
     * @ORM\Column(type="boolean")
     */
    private $IsActive;

    /**
     * @ORM\Column(type="boolean")
     */
    private $IsBlogger;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Twitter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Pinterest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Instagram;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author", orphanRemoval=true)
     */
    private $posts;


    public function __construct()
    {
        $this->roles = array('ROLE_USER');
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->regDate = new \DateTime();
        $this->IsActive = true;
        $this->IsBlogger = false;
        $this->posts = new ArrayCollection();
        $this->image = "user.png";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setfirstName(string $firstName): string
    {
        $this->firstName = $firstName;
        return $this->firstName;
    }

    public function getfirstName(): ?string
    {
        return $this->firstName;
    }

    public function setlastName(string $lastName): string
    {
        $this->lastName = $lastName;
        return $this->lastName;
    }

    public function getlastName(): ?string
    {
        return $this->lastName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): string
    {
        $this->username = $username;
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): string
    {
        $this->email = $email;
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): string
    {
        $this->password = $password;
        return $this->password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): array
    {
        $this->roles = $roles;
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info)
    {
        $this->info = $info;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getregDate(): ?\DateTimeInterface
    {
        return $this->regDate;
    }

    public function setregDate(\DateTimeInterface $regDate): self
    {
        $this->regDate = $regDate;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @return Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    public function follow(User $userToFollow)
    {
        if ($this->getFollowing()->contains($userToFollow)) {
            return;
        }

        $this->getFollowing()->add($userToFollow);
    }

    public function getIsActive(): ?bool
    {
        return $this->IsActive;
    }

    public function setIsActive(bool $IsActive): self
    {
        $this->IsActive = $IsActive;

        return $this;
    }

    public function getIsBlogger(): ?bool
    {
        return $this->IsBlogger;
    }

    public function setIsBlogger(bool $IsBlogger): self
    {
        $this->IsBlogger = $IsBlogger;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->Twitter;
    }

    public function setTwitter(?string $Twitter): self
    {
        $this->Twitter = $Twitter;

        return $this;
    }

    public function getPinterest(): ?string
    {
        return $this->Pinterest;
    }

    public function setPinterest(?string $Pinterest): self
    {
        $this->Pinterest = $Pinterest;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->Instagram;
    }

    public function setInstagram(?string $Instagram): self
    {
        $this->Instagram = $Instagram;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }
}
