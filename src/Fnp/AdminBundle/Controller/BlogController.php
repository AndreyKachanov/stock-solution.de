<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 15.03.18
 * Time: 20:31
 */

namespace Fnp\AdminBundle\Controller;


use Fnp\AdminBundle\Entity\Post;
use Fnp\AdminBundle\Form\PostType;
use Fnp\LogBundle\Entity\LogRecord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;




class BlogController extends Controller
{

    /**
     * Lists all Post entities.
     */
    public function indexAction(Request $request)
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("show_posts"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Post::class)->getPostsQuery();

        $paginator  = $this->get('knp_paginator');
        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 8
        );

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Posts");

        return $this->render('FnpAdminBundle:Blog:index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * Preview post
     */
    public function previewAction(Request $request, Post $post)
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("show_posts"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();

        return $this->render('FnpAdminBundle:Blog:preview.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Creates a new Post entity.
     */
    public function newAction(Request $request)
    {

        if (!$this->getUser()->getRole()->hasAccessToAlias("new_post"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Posts", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Add new");
        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $post->getImage();

            if (!empty($file)) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('post_images_directory'), $fileName);
                $post->setImage($fileName);
            }

            $em->persist($post);

            $logActivity = 'Created post: "' . $post->getTitle().'"';
            $log = new LogRecord();

            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('FnpAdminBundle:Blog:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing Post entity.
     */
    public function editAction(Request $request, Post $post)
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("edit_post"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();

        $oldImage = $post->getImage();
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Posts", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Edit");
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFromForm = $post->getImage();

            if (empty($imageFromForm))
                $post->setImage($oldImage);
            else {
                $fileName = md5(uniqid()) . '.' . $imageFromForm->guessExtension();
                $imageFromForm->move($this->getParameter('post_images_directory'), $fileName);
                $post->setImage($fileName);
            }

            $logActivity = 'Edited post: "' . $post->getTitle().'"';

            $log = new LogRecord();

            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();

            return $this->redirectToRoute('admin_post_index', ['id' => $post->getId()]);
        }

        return $this->render('FnpAdminBundle:Blog:edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a post entity.
     */
    public function deleteAction(Post $post)
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("delete_post"))
            throw new AccessDeniedException();

        if ($post !== null) {

            $image = $post->getImage();
            if ($post->getImage() !== null)
                unlink($this->getParameter('post_images_directory') . "/" .  $image );

            $em = $this->getDoctrine()->getManager();

            $em->remove($post);

            $logActivity = 'Drop post: "' . $post->getTitle().'"';

            $log = new LogRecord();
            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();
        }

        return $this->redirectToRoute('admin_post_index');
    }

    /**
     * Change post status
     */
    public function changeIsPublishedAction($id, $status){
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if ($status == 0){
            $post->setIsPublished(false);

            $logActivity = 'Unpublished an post: ' . $post->getTitle();

            $log = new LogRecord();
            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
//            $em->persist($log);
        } else {
            $post->setIsPublished(true);

            $logActivity = 'Published an post: ' . $post->getTitle();

            $log = new LogRecord();
            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
//            $em->persist($log);
        }

//        $em->persist($post);
        $em->flush();

        return new JsonResponse(["message" => "success"],200);
    }


    public function deletePostImageAction(Post $post)
    {
        unlink($this->getParameter('post_images_directory') . "/" .  $post->getImage() );
        $post->setImage(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();
        return new JsonResponse(["message" => "success"],200);
    }
}