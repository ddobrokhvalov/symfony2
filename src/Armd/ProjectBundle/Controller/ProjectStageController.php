<?php

namespace Armd\ProjectStageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectStageBundle\Entity\ProjectStage;
use Armd\ProjectStageBundle\Form\ProjectStageType;

/**
 * ProjectStage controller.
 *
 */
class ProjectStageController extends Controller
{
    /**
     * Lists all ProjectStage entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectStageBundle:ProjectStage')->findAll();

        return $this->render('ArmdProjectStageBundle:ProjectStage:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a ProjectStage entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectStageBundle:ProjectStage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectStage entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectStageBundle:ProjectStage:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new ProjectStage entity.
     *
     */
    public function newAction()
    {
        $entity = new ProjectStage();
        $form   = $this->createForm(new ProjectStageType(), $entity);

        return $this->render('ArmdProjectStageBundle:ProjectStage:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new ProjectStage entity.
     *
     */
    public function createAction()
    {
        $entity  = new ProjectStage();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectStageType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectStage_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectStageBundle:ProjectStage:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing ProjectStage entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectStageBundle:ProjectStage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectStage entity.');
        }

        $editForm = $this->createForm(new ProjectStageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectStageBundle:ProjectStage:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing ProjectStage entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectStageBundle:ProjectStage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectStage entity.');
        }

        $editForm   = $this->createForm(new ProjectStageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectStage_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectStageBundle:ProjectStage:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ProjectStage entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectStageBundle:ProjectStage')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectStage entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ProjectStage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
