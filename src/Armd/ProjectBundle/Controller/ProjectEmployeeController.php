<?php

namespace Armd\ProjectEmployeeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectEmployeeBundle\Entity\ProjectEmployee;
use Armd\ProjectEmployeeBundle\Form\ProjectEmployeeType;

/**
 * ProjectEmployee controller.
 *
 */
class ProjectEmployeeController extends Controller
{
    /**
     * Lists all ProjectEmployee entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectEmployeeBundle:ProjectEmployee')->findAll();

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a ProjectEmployee entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectEmployeeBundle:ProjectEmployee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectEmployee entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new ProjectEmployee entity.
     *
     */
    public function newAction()
    {
        $entity = new ProjectEmployee();
        $form   = $this->createForm(new ProjectEmployeeType(), $entity);

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new ProjectEmployee entity.
     *
     */
    public function createAction()
    {
        $entity  = new ProjectEmployee();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectEmployeeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectEmployee_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing ProjectEmployee entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectEmployeeBundle:ProjectEmployee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectEmployee entity.');
        }

        $editForm = $this->createForm(new ProjectEmployeeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing ProjectEmployee entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectEmployeeBundle:ProjectEmployee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectEmployee entity.');
        }

        $editForm   = $this->createForm(new ProjectEmployeeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ProjectEmployee_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectEmployeeBundle:ProjectEmployee:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ProjectEmployee entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectEmployeeBundle:ProjectEmployee')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectEmployee entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ProjectEmployee'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
