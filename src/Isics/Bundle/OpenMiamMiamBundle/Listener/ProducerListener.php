<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\PreRemove;
use Doctrine\ORM\Query;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;

class ProducerListener
{
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entityManager = $event->getEntityManager();
        $connection = $entityManager->getConnection();

        if ($event->getEntity() instanceof Branch){

            $branch = $event->getEntity();

            $removedProducers = $branch->getProducers()->getDeleteDiff();

            foreach ($removedProducers as $removedProducer){
                // Prepare query to retrieve product's ids of producer
                $productIdsQuery = $connection->prepare('SELECT id FROM product WHERE producer_id = :producer_id');
                $productIdsQuery->execute(array(
                    'producer_id' => $removedProducer->getId()
                ));

                // Get product's ids of producer
                $productIds = array_map(function($result){
                    return reset($result);
                }, $productIdsQuery->fetchAll(Query::HYDRATE_SCALAR));

                if (0 < count($productIds)){

                    // Remove relations branch_has_product for these products and branch
                    $productIdsQuery = $connection->prepare(sprintf(
                        'DELETE FROM branch_has_product WHERE branch_id = ? AND product_id IN (%s)',
                        implode(', ', array_fill(0, count($productIds), '?'))
                    ));
                    $productIdsQuery->execute(array_merge(array(
                       $branch->getId()
                    ), $productIds));
                }
            }
        }
    }
}