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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeConsumerCreditsCommand extends ContainerAwareCommand
{
  /**
   * @see ContainerAwareCommand
   */
  protected function configure()
  {
    $this->setName('openmiammiam:compute-consumer-credits')
            ->setDescription('Compute consumer credits');
  }

  /**
   * @see ContainerAwareCommand
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $subscriptions = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('IsicsOpenMiamMiamBundle:Subscription')
            ->findAll();
    $paymentManager = $this->getContainer()->get('open_miam_miam.payment_manager');

    foreach ($subscriptions as $subscription) {
        $paymentManager->computeConsumerCredit($subscription->getAssociation(), $subscription->getUser());

        $output->writeln(sprintf(
            'Processed consumer <info>%s %s</info> : credit = <info>%s</info>',
            $subscription->getUser()->getFirstname(),
            $subscription->getUser()->getLastname(),
            $subscription->getCredit()
        ));
    }
  }

}
