<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File
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
    private $token;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $filename;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $metadata = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $checksum;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="file", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="files")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Like", mappedBy="file")
     */
    private $likes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", mappedBy="files", cascade={"persist"})
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Hub", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $hub;

    public function __toString() {
        return (string) $this->getFilename();
    }

    public function __construct() {
        $this->setToken(uniqid());
        $this->setCreateDate(new \DateTime());
        $this->setUpdateDate(new \DateTime());
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
     
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

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

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getPath(): ?string
    {
        $filepath = $this->getHub()->getPath() . '/';
        $filepath .= $this->getToken() . '.';
        $filepath .= $this->getMetadata()['extension'] ?? null;
        return $filepath;
    }

    public function getSize(): ?string
    {
        if (file_exists($this->getPath())) {
            $bytes = filesize($this->getPath());
            $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor > 3):$decimals = 2;else:$decimals = 0;endif;
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
        } else {
            return '0B';
        }
    }

    public function getContent(): ?string
    {
        $content = file_get_contents($this->getPath());
        return $content;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum()
    {
        $checksum = \md5_file($this->getPath());
        if (!$this->getHub()->checksum($checksum)):
            $this->checksum = $checksum;
            return $this;
        else:
            $this->remove();
            return false;
        endif;
    }

    public function remove() {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getPath());
        return true;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setFile($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getFile() === $this) {
                $comment->setFile(null);
            }
        }

        return $this;
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

    /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setFile($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getFile() === $this) {
                $like->setFile(null);
            }
        }

        return $this;
    }

    public function removeLikes() {
        foreach ($this->getLikes() as $like) {
            $this->removeLike($like);
        }
        return true;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addFile($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeFile($this);
        }

        return $this;
    }

    public function removeTags(): self
    {
        foreach ($this->getTags() as $tag) {
            $this->removeTag($tag);
        }

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
}
