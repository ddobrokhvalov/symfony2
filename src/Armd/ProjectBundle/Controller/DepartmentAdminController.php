<?php
namespace Armd\ProjectBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController as Controller;

class DepartmentAdminController extends Controller
{
    //public function batchActionDelete($query)
    //{
    //    if (false === $this->admin->isGranted('DELETE')) {
    //        throw new AccessDeniedException();
    //    }
    //
    //    $modelManager = $this->admin->getModelManager();
    //    
    //    $entity_ids = $_POST['idx'];
    //    
    //    $em = $modelManager->getEntityManager();
    //    
    //    foreach($entity_ids as $entity_id) {
    //        $children = count($em->getRepository('ArmdProjectBundle:Employee')->findByDepartment($entity_id));
    //        if ($children == 0) {
    //            $modelManager->batchDelete($this->admin->getClass(), $query);
    //            $this->get('session')->setFlash('sonata_flash_success', 'flash_batch_delete_success');
    //        } else {
    //            $this->get('session')->setFlash('sonata_flash_error', 'flash_department_batch_delete_error_has_employees');
    //        }
    //    }
    //    return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    //}
}
