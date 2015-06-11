<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/4/14
 * Time: 11:48 AM
 */

namespace RuchJow\UserBundle\Services;

use Ruchjow\MailPoolBundle\Service\MailPool;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;

class Mailer
{
    protected $ruchJowMailPool;
    protected $router;
    protected $templating;
    protected $container;
    protected $parameters;

    public function __construct(MailPool $ruchJowMailPool, RouterInterface $router, EngineInterface $templating, Container $container, array $parameters)
    {
        $this->ruchJowMailPool = $ruchJowMailPool;
        $this->router     = $router;
        $this->templating = $templating;
        $this->container  = $container;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(User $user)
    {
        $subject  = $this->container->getParameter('ruch_jow_user.registration.confirmation_email.subject');
        $from     = $this->container->getParameter('ruch_jow_user.registration.confirmation_email.from');
        $fromName = $this->container->getParameter('ruch_jow_user.registration.confirmation_email.from_name');


        // Body
        $template = $this->parameters['confirmation.email.template'];

        // TODO: Get user confirmation link from user manager.
        $url  = $this->router->generate('frontend_homepage', array(), true) . '?url=' . urlencode('/action/confirm_support:' . $user->getConfirmationToken());
        $body = $this->templating->render($template, array(
            'user'            => $user,
            'confirmationUrl' => $url
        ));

        $this->sendEmailMessage(
            $user->getEmail(),
            $subject,
            $body,
            $from,
            $fromName
        );
    }

    /**
     * @param User $user
     */
    public function sendPasswordResetEmailMessage(User $user)
    {
        $subject  = $this->container->getParameter('ruch_jow_user.password_reset.email.subject');
        $from     = $this->container->getParameter('ruch_jow_user.password_reset.email.from');
        $fromName = $this->container->getParameter('ruch_jow_user.password_reset.email.from_name');

        // Body
        $template = $this->parameters['password.reset.email.template'];
        $url      = $this->router->generate('frontend_homepage', array(), true) . '?url=' . urlencode('/action/password_reset:' . $user->getPasswordResetToken());
        $body     = $this->templating->render($template, array(
            'user'           => $user,
            'newPasswordUrl' => $url,
        ));

        $this->sendEmailMessage(
            $user->getEmail(),
            $subject,
            $body,
            $from,
            $fromName
        );
    }

    /**
     * @param User           $user
     * @param string[]|array $addresses
     */
    public function sendInvitationEmails($user, $addresses)
    {
        $subject  = $this->container->getParameter('ruch_jow_user.invitation.email.subject');
        $from     = $this->container->getParameter('ruch_jow_user.invitation.email.from');
        $fromName = $this->container->getParameter('ruch_jow_user.invitation.email.from_name');

        // Body
        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $template = $this->parameters['invitation.email.template'];
        $url      = $userManager->getReferralLink($user);
        $body     = $this->templating->render($template, array(
            'user'        => $user,
            'referralUrl' => $url,
        ));

        $this->sendEmailMessage(
            $addresses,
            $subject,
            $body,
            $from,
            $fromName
        );
    }

    /**
     * @param User $user
     */
    public function sendThanksEmails($user)
    {
        $subject  = $this->container->getParameter('ruch_jow_user.thanks.email.subject');
        $from     = $this->container->getParameter('ruch_jow_user.thanks.email.from');
        $fromName = $this->container->getParameter('ruch_jow_user.thanks.email.from_name');

        // Body
        $template = $this->parameters['thanks.email.template'];
        $body     = $this->templating->render($template);

        $this->sendEmailMessage(
            $user->getEmail(), $subject, $body, $from, $fromName
        );
    }

    /**
     * @param User $user
     */
    public function sendReminderEmails($user)
    {
        $subject  = $this->container->getParameter('ruch_jow_user.reminder.email.subject');
        $from     = $this->container->getParameter('ruch_jow_user.reminder.email.from');
        $fromName = $this->container->getParameter('ruch_jow_user.reminder.email.from_name');

        // Body
        $template = $this->parameters['reminder.email.template'];
        $url      = $this->router->generate('frontend_homepage', array(), true) . '?url=' . urlencode('/action/confirm_support:' . $user->getConfirmationToken());
        $body     = $this->templating->render($template,
            array(
                'user'            => $user,
                'confirmationUrl' => $url
            ));

        $this->sendEmailMessage(
            $user->getEmail(), $subject, $body, $from, $fromName
        );
    }

    /**
     * @param string|string[] $recipient
     * @param string          $subject
     * @param string          $body
     * @param string          $from
     * @param                 $fromName
     */
    protected function sendEmailMessage($recipient, $subject, $body, $from, $fromName)
    {
        $this->ruchJowMailPool->sendMail(
            $recipient,
            $subject,
            $body,
            null,
            true,
            $from,
            $fromName
        );
    }
}