<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockageRepository")
 */
class Stockage
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="stockages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Like", mappedBy="stockage")
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="stockage", orphanRemoval=true)
     */
    private $files;

    public function __toString() {
        return (string) $this->getName();
    }

    public function __construct() {
        $this->setToken(uniqid());
        $this->setCreateDate(new \DateTime());
        $this->setUpdateDate(new \DateTime());
        $this->likes = new ArrayCollection();
        $this->files = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $name = trim($name);
        $this->name = $name;
        $this->setSlug($name);

        return $this;
    }

    public function getPath(): ?string
    {
        return rtrim($this->path, '/');
    }

    public function setPath(string $path): self
    {
        $this->path = rtrim($path, '/') . '/' . $this->getSlug().'_'.$this->getToken();

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

    public function getSize() {
        $bytes = 0;
        $files = $this->getFiles();
        foreach ($files as $file) {
            if (file_exists($file->getPath())):
                $bytes += filesize($file->getPath());
            endif;
        }
        
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 3):$decimals = 2;else:$decimals = 0;endif;
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);
        if (empty($slug)) {$slug = 'n-a';}

        $this->slug = $slug;

        return $this;
    }

    /**
     * Test if checksum was already record in this Stockage
     *
     * @param string $checksum
     * @return bool
     */
    public function checksum(string $checksum) {
        foreach ($this->getFiles() as $file) {
            if ($checksum === $file->getChecksum()):
                return true;
            endif;
        }
        return false;
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
     * @return Collection|like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setStockage($this);
        }

        return $this;
    }

    public function removeLike(like $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getStockage() === $this) {
                $like->setStockage(null);
            }
        }

        return $this;
    }

    public function getTags() {
        $tags = [];
        foreach ($this->getFiles() as $file) {
            foreach ($file->getTags() as $tag) {
                if (isset($tags[$tag->getSlug()])):
                    $tags[$tag->getSlug()]['counter']++;
                else:
                    $tags[$tag->getSlug()] = [
                        'data' => $tag,
                        'counter' => 1,
                    ];
                endif;
            }
        }

        // Order by count.
        if ($tags) {
            $counters = array_column($tags, 'counter');
            array_multisort($counters, SORT_DESC, $tags);   
        }

        return $tags;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setStockage($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->contains($file)) {
            $file->remove();
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getStockage() === $this) {
                $file->setStockage(null);
            }
        }

        return $this;
    }

    public function remove() {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getPath());
        return true;
    }
}
