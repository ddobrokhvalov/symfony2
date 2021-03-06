<?php

namespace Armd\ProjectBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskRepository extends EntityRepository
{
    public function findAll()
    {
         return $this->getEntityManager()
            ->createQuery('SELECT t FROM ArmdProjectBundle:Task t ORDER BY t.title ASC')
            ->getResult();
        //return parent::findAll();
    }
}