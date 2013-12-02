<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailOrdersOpenCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand
     */
    protected function configure()
    {
        $this->setName('openmiammiam:send-mail-orders-open')
            ->setDescription('Send a notification to branch\'s customer when orders open')
            ->addArgument(
                'period',
                InputArgument::REQUIRED,
                'Check orders open between (now) and (now - %period% minutes) for branch occurrences. Notify previous customers if true.'
            );
    }

    /**
     * @see ContainerAwareCommand
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startMicroTime = microtime(true);
        $mailNumber = 0;

        $period = $input->getArgument('period');
        if (0 >= (int)$period) {
            throw new \InvalidArgumentException('Period argument must be a integer great than 0. Input was: '.$period);
        }

        $now = new \DateTime();
        $openingDateTime = clone $now;
        $openingDateTime->sub(new \DateInterval(
            sprintf('PT%sM', $period)
        ));

        $userManager = $this->getContainer()->get('open_miam_miam_user.manager.user');

        $branches = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:Branch')
            ->findAll();

        $branchOccurrenceManager = $this->getContainer()->get('open_miam_miam.branch_occurrence_manager');

        $branchOccurrenceRepository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence');

        $mailer = $this->getContainer()->get('open_miam_miam.mailer');
        $translator = $mailer->getTranslator();
        $translator->setLocale($this->getContainer()->getParameter('locale'));

        foreach ($branches as $branch) {
            $nextBranchOccurrence = $branchOccurrenceRepository->findOneNextNotClosedForBranch($branch);
            if (null === $nextBranchOccurrence) {
                continue;
            }

            $ordersOpeningDateTime = $branchOccurrenceManager->getOrdersOpeningDateTimeForBranchOccurrence($nextBranchOccurrence);
            $ordersClosingDateTime = $branchOccurrenceManager->getOrdersClosingDateTimeForBranchOccurrence($nextBranchOccurrence);

            if ($ordersOpeningDateTime > $openingDateTime && $ordersOpeningDateTime <= $now){
                $customers = $userManager->findOrdersOpenNotificationSubscribersForBranches(array($branch));

                if (0 === count($customers)) {
                    continue;
                }

                $output->writeln($translator->trans('mail.branch.orders_open.log.branch_name', array(
                    '%branch_name%' => $branch->getName()
                )));

                foreach ($customers as $customer) {
                    $message = $mailer->getNewMessage();
                    $message
                        ->setTo($customer->getEmail())
                        ->setSubject(
                            $mailer->translate(
                                'mail.branch.orders_open.subject',
                                array(
                                    '%branch_name%' => $branch->getName()
                                )
                            )
                        )
                        ->setBody(
                            $mailer->render(
                                'IsicsOpenMiamMiamBundle:Mail:ordersOpen.html.twig',
                                array(
                                    'customer'              => $customer,
                                    'branchOccurrence'      => $nextBranchOccurrence,
                                    'ordersOpeningDateTime' => $ordersOpeningDateTime,
                                    'ordersClosingDateTime' => $ordersClosingDateTime
                                )
                            ),
                            'text/html'
                        );

                    $mailer->send($message);

                    ++$mailNumber;

                    $output->writeln(sprintf('<info>- %s</info>', $customer->getEmail()));
                }

                $output->writeln('');
            }
        }

        /**
         * Flush queue if spool memory because swiftmailer flush on kernel.terminate
         */
        $transport = $mailer->getMailer()->getTransport();
        if ($transport instanceof \Swift_Transport_SpoolTransport) {
            $spool = $transport->getSpool();
            if ($spool instanceof \Swift_MemorySpool) {
                $mailNumber = $spool->flushQueue($this->getContainer()->get('swiftmailer.transport.real'));
            }
        }

        $output->writeln($translator->trans('mail.branch.orders_open.log.task_end', array(
            '%email_sent%' => $mailNumber,
            '%time%' => round(microtime(true) - $startMicroTime, 2),
            '%memory%' => round(memory_get_usage() / 1024 / 1024, 2)
        )));
    }
}
