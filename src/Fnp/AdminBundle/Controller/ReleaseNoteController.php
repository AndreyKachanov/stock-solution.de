<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.03.18
 * Time: 11:29
 */

namespace Fnp\AdminBundle\Controller;


use Fnp\AdminBundle\Entity\ReleaseNote;
use Fnp\AdminBundle\Form\ReleaseNoteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class ReleaseNoteController extends Controller
{

    /**
     * Lists all Release notes.
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('FnpAdminBundle:ReleaseNote')->getNotesQuery();
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("release note", $this->get("router")->generate("admin_release_note_index"));
        $breadcrumbs->addItem("notes");
        $paginator  = $this->get('knp_paginator');
        $notes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 10
        );

        return $this->render('FnpAdminBundle:ReleaseNote:index.html.twig', [
            'notes' => $notes
        ]);
    }

    /**
     * Creates a new release note entity.
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $releaseNote = new ReleaseNote();
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("release note", $this->get("router")->generate("admin_release_note_index"));
        $breadcrumbs->addItem("new");
        $form = $this->createForm(ReleaseNoteType::class, $releaseNote)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($releaseNote);
            $em->flush();

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_release_note_new');
            }

            return $this->redirectToRoute('admin_release_note_index');
        }

        return $this->render('FnpAdminBundle:ReleaseNote:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing release note entity.
     */
    public function editAction(Request $request, ReleaseNote $releaseNote)
    {
        $form = $this->createForm(ReleaseNoteType::class, $releaseNote);
        $form->handleRequest($request);
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("release note", $this->get("router")->generate("admin_release_note_index"));
        $breadcrumbs->addItem("edit");
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_release_note_index', ['id' => $releaseNote->getId()]);
        }

        return $this->render('FnpAdminBundle:ReleaseNote:edit.html.twig', [
            'note' => $releaseNote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a release note entity.
     */
    public function deleteAction(ReleaseNote $releaseNote)
    {
        $em = $this->getDoctrine()->getManager();

        if ($releaseNote !== null) {

            $em->remove($releaseNote);
            $em->flush();
        }

        return $this->redirectToRoute('admin_release_note_index');
    }
}