<?php

namespace Armd\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ProjectBundle\Entity\Employee;
#use Armd\ProjectBundle\Form\Employee;

/**
 * Employee controller.
 *
 */
class EmployeeController extends Controller
{
    /**
     * Lists all Employee entities.
     *
     */
    public function indexAction()
    {
		
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ArmdProjectBundle:Employee')->findAll();

        return $this->render('ArmdProjectBundle:Employee:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Employee entity.
     *
     */
    public function showAction($id)
    {
		
       
		$em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Employee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Employee entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
		
        return $this->render('ArmdProjectBundle:Employee:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Employee entity.
     *
     */
    public function newAction()
    {
        $entity = new Employee();
        $form   = $this->createForm(new EmployeeType(), $entity);

        return $this->render('ArmdProjectBundle:Employee:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Employee entity.
     *
     */
    public function createAction()
    {
        $entity  = new Employee();
        $request = $this->getRequest();
        $form    = $this->createForm(new EmployeeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('Employee_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ArmdProjectBundle:Employee:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Employee entity.
     *
     */
    public function editAction($id)
    {
        
		$em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Employee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Employee entity.');
        }

        $editForm = $this->createForm(new EmployeeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
		
        return $this->render('ArmdProjectBundle:Employee:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Employee entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdProjectBundle:Employee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Employee entity.');
        }

        $editForm   = $this->createForm(new EmployeeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('Employee_edit', array('id' => $id)));
        }
		
        return $this->render('ArmdProjectBundle:Employee:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Employee entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdProjectBundle:Employee')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Employee entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('Employee'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
