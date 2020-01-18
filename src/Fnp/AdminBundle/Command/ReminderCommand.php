<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 20.04.18
 * Time: 10:27
 */

namespace Fnp\AdminBundle\Command;


use Daily\NotifBundle\Model\Processor;
use Fnp\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('reminder:unaccepted-clients')

            // the short description shown while running "php bin/console list"
            ->setDescription('Notification for managers')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Notification for managers about unaccepted clients")
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $em = $container->get('doctrine')->getManager();

        $mailer = $container->get('mailer');
        $templating = $container->get('templating');
        $twig = $container->get('twig');

        //Получаем не принятых пользователей (в админке вкладка: Users -> New)
        $unAcceptedUsers = $em->getRepository(User::class)->getUnAcceptedUsers();

        $sortedArray = [];

//        Перегоняем в новый массив, где в случае если у пользователя есть родитель, то ключ массива
//        будет равен id родителя, иначе ключ массива будет равен not_parent.
//        Так же делаем доп. проверку на разницу дней и записываем в соответствующие подмассивы
        foreach ($unAcceptedUsers as $key => $user) {

            // Разница дней между текущим днем и датой регистрации
            $dateDiff = ( time() - strtotime($user->getRegistrationDate()->format('Y-m-d'))) / 86400;

            if ( !$user->getParent() ) {
                if ($dateDiff >= 2 && $dateDiff < 5)
                    $sortedArray['not_parent']['2'][] = $unAcceptedUsers[$key];

                elseif ($dateDiff >= 5 && $dateDiff < 7)
                    $sortedArray['not_parent']['5'][] = $unAcceptedUsers[$key];

                elseif ($dateDiff >= 7)
                    $sortedArray['not_parent']['7'][] = $unAcceptedUsers[$key];
            }

            else {
                if ($dateDiff >= 2 && $dateDiff < 5)
                    $sortedArray[$user->getParent()->getId()]['2'][] = $unAcceptedUsers[$key];

                elseif ($dateDiff >= 5 && $dateDiff < 7)
                    $sortedArray[$user->getParent()->getId()]['5'][] = $unAcceptedUsers[$key];

                elseif ($dateDiff >= 7)
                    $sortedArray[$user->getParent()->getId()]['7'][] = $unAcceptedUsers[$key];
            }

        }

        // Цикл для отправки сообщений.
        // Отправляет сгруппированные по кол-ву просроченных дней списки пользователей.
        // Если ключ массива равен not_parent тогда подключаем NotifyBundle, который
        // формирует текст письма + отправляет списки пользователей на адреса, которые указаны в админке.
        // Иначе списки пользователей отправляются "родителям" пользователей.
        foreach ($sortedArray as $idParent => $arrayByDay) {

            if ($idParent != 'not_parent') {
                $parentUser = $em->getRepository(User::class)->findById($idParent);

                $subject = 'Your customers, who have not been accepted';

                //вывожу email родителя в консоль для наглядности(можно закомментировать)
                $output->writeln("parent - "  . $parentUser[0]->getEmail());

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom('stock-solution@stock-solution.de')
                    ->setTo($parentUser[0]->getEmail())
                    ->setCharset('UTF-8')
                    ->setContentType('text/html')
                    ->setBody($templating->render(':Emails:unaccepted_users_with_parent.html.twig',[
                        'parent' => $parentUser[0],
                        'arrayByDay' => $arrayByDay
                    ]));

                $mailer->send($message);

                // для наглядности вывожу в консоль пользователей(можно закомментировать)
                foreach ($arrayByDay as $key => $user) {
                    $output->writeln("dateDiff - $key");
                    foreach ($user as $u) {
                        $output->write("id - " . $u->getId() . ", Name - " . $u->getFirstName() . " " . $u->getLastName());
                        $output->writeln(", email - " . $u->getEmail());
                    }
                }
                $output->writeln("__________");
            }
            else {

                $clientsList = $templating->render(':Emails:unaccepted_users_not_parent.html.twig', [
                    'arrayByDay' => $arrayByDay
                ]);

                $processorParams = ['unacceptedClientsList' => $clientsList];

                $processor = new Processor($em, $mailer, $twig, $processorParams, 'unaccepted_clients');
                $processor->complete();

                // для наглядности вывожу в консоль пользователей(можно закомментировать)
                $output->writeln("NOT PARENT");
                foreach ($arrayByDay as $key => $user) {
                    $output->writeln("dateDiff - $key");
                    foreach ($user as $u) {
                        $output->write("id - " . $u->getId() . ", Name - " . $u->getFirstName() . " " . $u->getLastName());
                        $output->writeln(", email - " . $u->getEmail());
                    }
                }
            }

        }
    }
}