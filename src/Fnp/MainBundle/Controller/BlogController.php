<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 15.03.18
 * Time: 11:21
 */

namespace Fnp\MainBundle\Controller;


use Fnp\AdminBundle\Entity\Page;
use Fnp\AdminBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{

    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository(Post::class)->getPostsIsPublishedQuery();

        $paginator  = $this->get('knp_paginator');
        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 8
        );
//        Get main blog title
        $blogTitle = $em->getRepository(Page::class)->findOneByMachineName('Blog');

        return $this->render('FnpMainBundle:Blog:index.html.twig', [
            'posts' => $posts,
            'blog_title' => $blogTitle
        ]);
    }

    public function showAction(Request $request, Post $post)
    {
        if (!$post->getIsPublished())
            throw new NotFoundHttpException('The post does not exist!');

        $em = $this->getDoctrine()->getManager();
        $prevPost = $em->getRepository(Post::class)->previous($post);
        $nextPost = $em->getRepository(Post::class)->next($post);

        return $this->render('FnpMainBundle:Blog:show.html.twig', [
            'post' => $post,
            'prev_post' => $prevPost,
            'next_post' => $nextPost,
            'uri' => $request->getUri()
        ]);
    }


}