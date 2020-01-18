<?php

namespace Fnp\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostCategory
 *
 * @ORM\Table(name="post_category")
 * @ORM\Entity(repositoryClass="Fnp\AdminBundle\Repository\PostCategoryRepository")
 */
class PostCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category")
     */
    private $posts;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return PostCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add post
     *
     * @param \Fnp\AdminBundle\Entity\Post $post
     *
     * @return PostCategory
     */
    public function addPost(\Fnp\AdminBundle\Entity\Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post
     *
     * @param \Fnp\AdminBundle\Entity\Post $post
     */
    public function removePost(\Fnp\AdminBundle\Entity\Post $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}
