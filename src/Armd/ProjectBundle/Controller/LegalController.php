<?php

namespace Armd\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectBundle\Entity\Legal;
#use Armd\ProjectBundle\Form\Legal;

/**
 * Legal controller.
 *
 */
class LegalController extends Controller
{
    /**
     * Lists all Legal entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectBundle:Legal')->findAll();

        return $this->render('ArmdProjectBundle:Legal:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Legal entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Legal:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Legal entity.
     *
     */
    public function newAction()
    {
        $entity = new Legal();
        $form   = $this->createForm(new LegalType(), $entity);

        return $this->render('ArmdProjectBundle:Legal:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Legal entity.
     *
     */
    public function createAction()
    {
        $entity  = new Legal();
        $request = $this->getRequest();
        $form    = $this->createForm(new LegalType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->geneLegalUrl('Legal_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectBundle:Legal:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Legal entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $editForm = $this->createForm(new LegalType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdProjectBundle:Legal:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Legal entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $editForm   = $this->createForm(new LegalType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->geneLegalUrl('Legal_edit', array('id' => $id)));
        }

        return $this->render('ArmdProjectBundle:Legal:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Legal entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectBundle:Legal')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Legal entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->geneLegalUrl('Legal'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
