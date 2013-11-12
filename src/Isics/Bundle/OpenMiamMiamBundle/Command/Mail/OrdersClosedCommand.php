<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Command\Mail;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class OrdersClosedCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand
     */
    protected function configure()
    {
        $this->setName('openmiammiam:mail:orders-closed')
            ->setDescription('Send reminder order mail to customer who has and active order')
            ->addArgument(
            'period',
            InputArgument::REQUIRED,
            'desc todo'
        );
    }

    /**
     * @see ContainerAwareCommand
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $period = $input->getArgument('period');
        if(0 >= (int)$period) throw new \InvalidArgumentException('Period argument must be a integer great than 0. Input was: '.$period);

        $now = new \DateTime();
        $closingDateTime = clone $now;
        $closingDateTime->sub(new \DateInterval(
            sprintf('PT%sM', $period)
        ));

        $branches = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:Branch')
            ->findAll();

        $branchOccurrenceRepository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');

        $salesOrderRepository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:SalesOrder');

        $branchOccurrenceManager = $this->getContainer()->get('open_miam_miam.branch_occurrence_manager');

        $mailer = $this->getContainer()->get('open_miam_miam.mailer');
        $mailer->getTranslator()->setLocale($this->getContainer()->getParameter('locale'));

        foreach($branches as $branch) {

            $nextBranchOccurrence = $branchOccurrenceRepository->findOneNextNotClosedForBranch($branch);
            if (null === $nextBranchOccurrence) {
                continue;
            }

            $nextBranchOccurrenceClosingDateTime = $branchOccurrenceManager->getOrdersClosingDateTimeForBranchOccurrence($nextBranchOccurrence);
            if ($nextBranchOccurrenceClosingDateTime > $closingDateTime && $nextBranchOccurrenceClosingDateTime < $now){
                $salesOrders = $salesOrderRepository->findBy(array('branchOccurrence' => $nextBranchOccurrence));

                foreach($salesOrders as $salesOrder) {
                    $recipient = $salesOrder->getUser()->getEmail();
                    $message = $mailer->getNewMessage();
                    $message
                        ->setTo($recipient)
                        ->setSubject(
                            $mailer->translate(
                                'mail.branch.order_reminder.subject',
                                array(
                                    '%ref%' => $salesOrder->getRef(),
                                    '%branch_name%' => $branch->getName()
                                )
                            )
                        )
                        ->setBody(
                            $mailer->render(
                                'IsicsOpenMiamMiamBundle:Mail:closedOrder.html.twig',
                                array(
                                    'salesOrder' => $salesOrder,
                                    'branchOccurrence' => $nextBranchOccurrence
                                )
                            ),
                            'text/html'
                        );
                    $mailer->send($message);
                    $output->writeln(sprintf(
                        '<info>Sales order %s on %s Reminder mail send to %s</info>',
                        $salesOrder->getRef(),
                        $branch->getName(),
                        $recipient
                    ));
                }
            }
        }
    }

}
