<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;


use Doctrine\ORM\EntityManager;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;

class AssociationManager
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager   $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager   = $entityManager;
    }

    public function exportProducerTransferForMonth(Association $association, \DateTime $fromDate)
    {
        $toDate = clone $fromDate;
        $toDate->modify('first day of next month midnight - 1 second');

        $branchOccurrenceRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');
        $branchOccurrences = $branchOccurrenceRepository->findForAssociationByDate($association, $fromDate, $toDate);

        $producerRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Producer');

//SELECT b.name, bo.id, bo.begin, sor.producer_id, SUM(sor.total) FROM association as a
//INNER JOIN branch as b ON b.association_id = a.id
//INNER JOIN branch_occurrence as bo ON bo.branch_id=b.id
//INNER JOIN sales_order as so ON so.branch_occurrence_id=bo.id
//INNER JOIN sales_order_row as sor ON sor.sales_order_id=so.id
//WHERE a.id=1
//GROUP BY sor.producer_id, bo.id

        $producerForTransfertExportQueryBuilder = $producerRepository->getForTransferExportQueryBuilder();
        $producerForTransfertExportQueryBuilder->addSelect('SUM(sor.total)')
            ->andWhere('bo IN (:bos)')
            ->setParameter('bos', $branchOccurrences)
            ->addGroupBy('bo')
            ->addGroupBy('p');

        var_dump(
            array_map(function($o){return$o->getId();}, $branchOccurrences),
            $producerForTransfertExportQueryBuilder->getQuery()->getResult()
        );
        die;

        // ask the service for a Excel5
        $xls = $this->get('xls.service_xls5');
        // or $this->get('xls.service_pdf');
        // or create your own is easy just modify services.yml

        // create the object see http://phpexcel.codeplex.com documentation
        $xls->excelObj->getProperties()->setCreator("OpenMiamMiam")
            ->setTitle("Producer transfert for ".$fromDate->format('d M Y'))
            ->setSubject("Producer transfert for ".$fromDate->format('d M Y'));
        $xls->excelObj->setActiveSheetIndex(0);
        for($i=0; $i<count($branchOccurrences);$i++) {
            $xls->excelObj->setActiveSheetIndex(0)->setCellValue('A'.$i, $branchOccurrences[$i]->getBranch()->getName().$branchOccurrences[$i].getEnd());
        }
        $xls->excelObj->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!');
        $xls->excelObj->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $xls->excelObj->setActiveSheetIndex(0);

        //create the response
        $response = $xls->getResponse();
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=stdream2.xls');

        // If you are using a https connection, you have to set those two headers and use sendHeaders() for compatibility with IE <9
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;

    }
} 