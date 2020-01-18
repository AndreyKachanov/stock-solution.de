<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 18.03.18
 * Time: 13:33
 */

namespace Fnp\AdminBundle\Controller;

use Fnp\AdminBundle\Entity\Post;
use Fnp\AdminBundle\Entity\PostCategory;
use Fnp\AdminBundle\Form\PostCategoryType;
use Fnp\LogBundle\Entity\LogRecord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostCategoryController extends Controller
{
    /**
     * Lists all categories of posts
     */
    public function indexAction()
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("show_categories"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(PostCategory::class)->findAll(['createdAt' => 'DESC']);

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Categories");

        return $this->render('FnpAdminBundle:Blog:index.category.html.twig',
            [ 'categories' => $categories ]
        );
    }

    /**
     * Creates a new category of posts.
     */
    public function newAction(Request $request)
    {

        if (!$this->getUser()->getRole()->hasAccessToAlias("new_category"))
            throw new AccessDeniedException();

        $category = new PostCategory();

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Categories", $this->get("router")->generate("admin_post_category_index"));
        $breadcrumbs->addItem("add new");

        $form = $this->createForm(PostCategoryType::class, $category)
        ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);

            $logActivity = 'Created post category: "' . $category->getCategory().'"';
            $log = new LogRecord();

            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();

            if ($form->get('saveAndCreateNew')->isClicked())
                return $this->redirectToRoute('admin_post_category_new');


            return $this->redirectToRoute('admin_post_category_index');
        }

        return $this->render('FnpAdminBundle:Blog:new.category.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing PostCategory entity.
     */
    public function editAction(Request $request, PostCategory $category)
    {
        if (!$this->getUser()->getRole()->hasAccessToAlias("edit_category"))
            throw new AccessDeniedException();

        $form = $this->createForm(PostCategoryType::class, $category);
        $form->handleRequest($request);
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Blog", $this->get("router")->generate("admin_post_index"));
        $breadcrumbs->addItem("Categories", $this->get("router")->generate("admin_post_category_index"));
        $breadcrumbs->addItem("edit");
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $logActivity = 'Edited post category: "' . $category->getCategory().'"';
            $log = new LogRecord();

            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();

            return $this->redirectToRoute('admin_post_category_index', ['id' => $category->getId()]);
        }

        return $this->render('FnpAdminBundle:Blog:edit.category.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a PostCategory entity.
     */
    public function deleteAction(Request $request) {

        if (!$this->getUser()->getRole()->hasAccessToAlias("delete_category"))
            throw new AccessDeniedException();

        $em = $this->getDoctrine()->getManager();
        $newCategory = $this->getDoctrine()->getRepository(PostCategory::class)->findOneBy(['category' => $request->get('new_category')]);
        $oldCategory = $this->getDoctrine()->getRepository(PostCategory::class)->find($request->get('delete'));

        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(['category' => $request->get('delete')]);

        foreach ($posts as $post) {
            $post->setCategory($newCategory);
            $em->persist($post);
        }

        $em->remove($oldCategory);

        $logActivity = 'Drop post category: "' . $oldCategory->getCategory().'"';
        $log = new LogRecord();

        $log->setUser($this->getUser());
        $log->setLogActivity($logActivity);
        $em->persist($log);

        $em->flush();

        return $this->redirectToRoute('admin_post_category_index');
    }

    public function countPostWithCategoryAction(PostCategory $postCategory) {

        $posts = $this->getDoctrine()->getRepository(Post::class)->countPostWithCategory($postCategory->getId());

        if ($posts < 1 ) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($postCategory);

            $logActivity = 'Drop post category: "' . $postCategory->getCategory().'"';
            $log = new LogRecord();

            $log->setUser($this->getUser());
            $log->setLogActivity($logActivity);
            $em->persist($log);

            $em->flush();

            return new JsonResponse(false, 200);
        }
        return new JsonResponse($posts, 200);
    }

}