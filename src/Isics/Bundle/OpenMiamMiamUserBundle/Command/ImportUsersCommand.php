<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamUserBundle\Command;

use Isics\Bundle\OpenMiamMiamUserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportUsersCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand
     */
    protected function configure()
    {
        $this
            ->setName('openmiammiam:import-users')
            ->setDescription('Imports user from CSV file')
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
            $userManager = $this->getContainer()->get('fos_user.user_manager');

            while (false !== ($data = fgetcsv($handle, 1000, ';'))) {
                if (8 === count($data)) {
                    try {
                        list(
                            $email,
                            $password,
                            $lastname,
                            $firstname,
                            $address1,
                            $address2,
                            $zipcode,
                            $city
                        ) = $data;

                        // checks if a user already exists with this email
                        if (null !== $userManager->findUserBy(array('email' => $email))) {
                            $output->writeln(sprintf('<comment>Passed existing user %s</comment>', $email));
                            continue;
                        }

                        // generates password if empty
                        // @todo use a tool for that
                        if ('' === $password) {
                            $password = substr(md5(uniqid('', true)), 0, 8);
                        }

                        // required values
                        $user = $userManager->createUser();
                        $user->setEmail($email);
                        $user->setPlainPassword($password);
                        $user->setEnabled(true);
                        $user->setSuperAdmin(false);
                        $user->setFirstname($firstname);
                        $user->setLastname($lastname);
                        $user->setAddress1($address1);
                        $user->setZipcode($zipcode);
                        $user->setCity($city);

                        // optional value
                        if ('' !== $address2) {
                            $user->setAddress2($address2);
                        }

                        $userManager->updateUser($user);
                    } catch (\Exception $e) {
                        $output->writeln(sprintf('<error>Unable to import user %s (%s)</error>', $email, $e->getMessage()));

                        return 1;
                    }

                    $output->writeln(sprintf('Imported user <info>%s</info> with password <info>%s</info>', $email, $password));
                } else {
                    $output->writeln(sprintf('<error>The user CSV file must contains 8 columns: email|password|lastname|firstname|address1|address2|zipcode|city</error>', $file));

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
