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
use Symfony\Component\Console\Output\OutputInterface;

class ComputeConsumerCreditsCommand extends ContainerAwareCommand
{
  /**
   * @see ContainerAwareCommand
   */
  protected function configure()
  {
    $this
        ->setName('openmiammiam:compute-consumer-credits')
        ->setDescription('Compute consumer credits')
        ->addArgument('file', InputArgument::REQUIRED, 'CSV file')
    ;
  }

  /**
   * @see ContainerAwareCommand
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $file = $input->getArgument('file');

    if (file_exists($file) && false !== ($handle = fopen($file, 'r'))) {
      $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
      $producerRepository = $entityManager->getRepository('IsicsOpenMiamMiamBundle:Producer');
      $producerManager = $this->getContainer('open_miam_miam.producer_manager');
      $userManager = $this->getContainer()->get('fos_user.user_manager');
      $aclProvider = $this->getContainer()->get('security.acl.provider');

      while (false !== ($data = fgetcsv($handle, 1000, ';'))) {
        if (10 === count($data)) {
          try {
            list(
                $name,
                $address1,
                $address2,
                $zipcode,
                $city,
                $phone1,
                $phone2,
                $website,
                $facebook,
                $users
                ) = $data;

            // imports producer

            // checks if a producer already exists with this email
            if (null === $producer = $producerRepository->findOneBy(array('name' => $name))) {
              $producer = new Producer();

              // required values
              $producer->setName($name);

              // optional values
              if ('' !== $address1) {
                $producer->setAddress1($address1);
              }

              if ('' !== $address2) {
                $producer->setAddress2($address2);
              }

              if ('' !== $zipcode) {
                $producer->setZipcode($zipcode);
              }

              if ('' !== $city) {
                $producer->setCity($city);
              }

              if ('' !== $phone1) {
                $producer->setPhone1($phone1);
              }

              if ('' !== $phone2) {
                $producer->setPhone2($phone2);
              }

              if ('' !== $website) {
                $producer->setWebsite($website);
              }

              if ('' !== $facebook) {
                $producer->setWebsite($facebook);
              }

              $entityManager->persist($producer);
              $entityManager->flush();

              $output->writeln(sprintf('Imported producer <info>%s</info>', $name));
            } else {
              $output->writeln(sprintf('<comment>Passed existing producer %s</comment>', $name));
            }

            // ACL / ACEs
            $objectIdentity = ObjectIdentity::fromDomainObject($producer);
            $acl = $aclProvider->findAcl($objectIdentity);

            foreach (explode(',', $users) as $email) {
              if (null === $user = $userManager->findUserBy(array('email' => $email))) {
                $output->writeln(sprintf('<error>Unable to find user %s</error>', $email));

                return 1;
              }

              $securityIdentity = UserSecurityIdentity::fromAccount($user);

              $existing = false;
              foreach($acl->getObjectAces() as $index => $ace) {
                if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                  $existing = true;
                  $output->writeln(sprintf('<comment>Passed existing user %s for producer %s</comment>', $email, $name));
                }
              }

              if (!$existing) {
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                $aclProvider->updateAcl($acl);
                $output->writeln(sprintf('Added user <info>%s</info> to producer <info>%s</info>', $email, $name));
              }
            }
          } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Unable to import producer %s (%s)</error>', $email, $e->getMessage()));

            return 1;
          }
        } else {
          $output->writeln(sprintf('<error>The producer CSV file must contains 10 columns: name|address1|address2|zipcode|city|phone1|phone2|website|facebook|producers|users</error>', $file));

          return 1;
        }
      }
      fclose($handle);
    } else {
      $output->writeln(sprintf('<error>Unable to read file %s</error>', $file));

      return 1;
    }
  }

}
