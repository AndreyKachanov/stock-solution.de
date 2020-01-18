<?php

namespace Fnp\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="Fnp\AdminBundle\Repository\PostRepository")
 */
class Post
{
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
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;



    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;


    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="PostCategory", inversedBy="posts")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isPublished = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->category = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }


    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }


    /**
     * Set category
     *
     * @param \Fnp\AdminBundle\Entity\PostCategory $category
     *
     * @return Post
     */
    public function setCategory(\Fnp\AdminBundle\Entity\PostCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Fnp\AdminBundle\Entity\PostCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
        /**
         * @return mixed
         */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * @param mixed $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }
}
