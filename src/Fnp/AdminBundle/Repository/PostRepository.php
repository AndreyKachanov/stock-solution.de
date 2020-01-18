<?php

namespace Fnp\AdminBundle\Repository;


use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    /**
     * Get all posts
     */
    public function getPostsQuery()
    {
        $em = $this->getEntityManager();
        $dql = "SELECT a FROM FnpAdminBundle:Post a ORDER BY a.id DESC";
        return $query = $em->createQuery($dql);
    }

    /**
     * Get only published posts
     */
    public function getPostsIsPublishedQuery()
    {
        $em = $this->getEntityManager();
        $dql = "SELECT a FROM FnpAdminBundle:Post a WHERE a.isPublished = 1 ORDER BY a.id DESC";
        return $query = $em->createQuery($dql);
    }
    /*
    * Get previous post
    */
    public function previous($post)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u
            FROM FnpAdminBundle:Post u
            WHERE u.id = (SELECT MAX(us.id) FROM FnpAdminBundle:Post us WHERE us.id < :id and us.isPublished = 1 )'
            )
            ->setParameter(':id', $post->getId())
            ->getOneOrNullResult();
    }


    /*
     * Get next post
     */
    public function next($post)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u
            FROM FnpAdminBundle:Post u
            WHERE u.id = (SELECT MIN(us.id) FROM FnpAdminBundle:Post us WHERE us.id > :id and us.isPublished = 1 )'
            )
            ->setParameter(':id', $post->getId())
           ->getOneOrNullResult();
    }

//    Get count posts in Post Category
    public function countPostWithCategory($id)
    {
        return $this->createQueryBuilder('p')
            ->select('count(p)')
            ->where('p.category = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

    }
}