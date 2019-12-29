<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\LikeRepository")
 */
class Like
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Hub", inversedBy="likes", cascade={"persist"})
     */
    private $hub;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", inversedBy="likes", cascade={"persist"})
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Comment", inversedBy="likes", cascade={"persist"})
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDate;

    public function __toString() {
        return (string) $this->getId();
    }

    public function __construct() {
        $this->setCreateDate(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHub(): ?Hub
    {
        return $this->hub;
    }

    public function setHub(?Hub $hub): self
    {
        $this->hub = $hub;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }
}
