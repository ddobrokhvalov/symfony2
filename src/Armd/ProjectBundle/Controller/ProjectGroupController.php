<?php

namespace Armd\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectBundle\Entity\ProjectGroup;
use Armd\ProjectBundle\Form\ProjectGroupType;

/**
 * ProjectGroup controller.
 *
 */
class ProjectGroupController extends Controller
{
    /**
     * Lists all ProjectGroup entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectBundle:ProjectGroup')->findAll();

        return $this->render('ArmdProjectBundle:ProjectGroup:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a ProjectGroup entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:ProjectGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectGroup entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:ProjectGroup:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new ProjectGroup entity.
     *
     */
    public function newAction()
    {
        $entity = new ProjectGroup();
        $form   = $this->createForm(new ProjectGroupType(), $entity);

        return $this->render('ArmdProjectBundle:ProjectGroup:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new ProjectGroup entity.
     *
     */
    public function createAction()
    {
        $entity  = new ProjectGroup();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectGroupType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('projectgroup_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectBundle:ProjectGroup:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing ProjectGroup entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:ProjectGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectGroup entity.');
        }

        $editForm = $this->createForm(new ProjectGroupType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:ProjectGroup:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing ProjectGroup entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:ProjectGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectGroup entity.');
        }

        $editForm   = $this->createForm(new ProjectGroupType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('projectgroup_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectBundle:ProjectGroup:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ProjectGroup entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectBundle:ProjectGroup')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectGroup entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('projectgroup'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
