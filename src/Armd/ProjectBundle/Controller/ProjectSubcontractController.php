<?php

namespace Armd\ProjectSubcontractBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectSubcontractBundle\Entity\ProjectSubcontract;
use Armd\ProjectSubcontractBundle\Form\ProjectSubcontractType;

/**
 * ProjectSubcontract controller.
 *
 */
class ProjectSubcontractController extends Controller
{
    /**
     * Lists all ProjectSubcontract entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectSubcontractBundle:ProjectSubcontract')->findAll();

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a ProjectSubcontract entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectSubcontractBundle:ProjectSubcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectSubcontract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new ProjectSubcontract entity.
     *
     */
    public function newAction()
    {
        $entity = new ProjectSubcontract();
        $form   = $this->createForm(new ProjectSubcontractType(), $entity);

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new ProjectSubcontract entity.
     *
     */
    public function createAction()
    {
        $entity  = new ProjectSubcontract();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectSubcontractType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectSubcontract_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing ProjectSubcontract entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectSubcontractBundle:ProjectSubcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectSubcontract entity.');
        }

        $editForm = $this->createForm(new ProjectSubcontractType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing ProjectSubcontract entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectSubcontractBundle:ProjectSubcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectSubcontract entity.');
        }

        $editForm   = $this->createForm(new ProjectSubcontractType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectSubcontract_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectSubcontractBundle:ProjectSubcontract:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ProjectSubcontract entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectSubcontractBundle:ProjectSubcontract')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectSubcontract entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ProjectSubcontract'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
