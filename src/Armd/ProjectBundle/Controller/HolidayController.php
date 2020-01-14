<?php

namespace Armd\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectBundle\Entity\Holiday;
#use Armd\ProjectBundle\Form\Holiday;

/**
 * Holiday controller.
 *
 */
class HolidayController extends Controller
{
    /**
     * Lists all Holiday entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectBundle:Holiday')->findAll();

        return $this->render('ArmdProjectBundle:Holiday:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Holiday entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Holiday')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Holiday entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Holiday:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Holiday entity.
     *
     */
    public function newAction()
    {
        $entity = new Holiday();
        $form   = $this->createForm(new HolidayType(), $entity);

        return $this->render('ArmdProjectBundle:Holiday:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Holiday entity.
     *
     */
    public function createAction()
    {
        $entity  = new Holiday();
        $request = $this->getRequest();
        $form    = $this->createForm(new HolidayType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->geneHolidayUrl('Holiday_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectBundle:Holiday:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Holiday entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Holiday')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Holiday entity.');
        }

        $editForm = $this->createForm(new HolidayType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Holiday:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Holiday entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Holiday')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Holiday entity.');
        }

        $editForm   = $this->createForm(new HolidayType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->geneHolidayUrl('Holiday_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectBundle:Holiday:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Holiday entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectBundle:Holiday')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Holiday entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->geneHolidayUrl('Holiday'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
