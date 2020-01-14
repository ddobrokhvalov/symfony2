<?php

namespace Armd\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectBundle\Entity\Subcontract;
use Armd\ProjectBundle\Form\SubcontractType;

/**
 * Subcontract controller.
 *
 */
class SubcontractController extends Controller
{
    /**
     * Lists all Subcontract entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectBundle:Subcontract')->findAll();

        return $this->render('ArmdProjectBundle:Subcontract:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Subcontract entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Subcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Subcontract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Subcontract:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Subcontract entity.
     *
     */
    public function newAction()
    {
        $entity = new Subcontract();
        $form   = $this->createForm(new SubcontractType(), $entity);

        return $this->render('ArmdProjectBundle:Subcontract:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Subcontract entity.
     *
     */
    public function createAction()
    {
        $entity  = new Subcontract();
        $request = $this->getRequest();
        $form    = $this->createForm(new SubcontractType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('subcontract_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectBundle:Subcontract:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Subcontract entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Subcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Subcontract entity.');
        }

        $editForm = $this->createForm(new SubcontractType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Subcontract:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Subcontract entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Subcontract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Subcontract entity.');
        }

        $editForm   = $this->createForm(new SubcontractType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('subcontract_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectBundle:Subcontract:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Subcontract entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectBundle:Subcontract')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Subcontract entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('subcontract'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
