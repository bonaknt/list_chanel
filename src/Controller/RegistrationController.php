<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TokenRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\TokenSendler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, TokenSendler $tokenSendler): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_ADMIN']);
            $user->setEnable(false);
            $token = new Token($user);
            $token->setValue(uniqid());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->persist($token);
            $entityManager->flush();

            $tokenSendler->sendToken($token);

            $this->addFlash('notice', 'Un email de confirmation vous a été envoyé pour pouvoir activer votre compte.');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirmation/{token}", name="token_validation")
     */
    public function validateToken($token, Request $request, TokenRepository $tokenRepository, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        if ($token === null) {
            throw new NotFoundHttpException();
            
        }
        $entityManager = $this->getDoctrine()->getManager();
        $token = $tokenRepository->findOneBy(['value' => $token]);
        $user = $token->getUser();
        if ($token->isValid()) {
            $user->setEnable(true);
            $entityManager->flush($user);
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        $entityManager->remove($user);
        $entityManager->remove($token);
        $this->addFlash('notice', 'Le token est expiré.');

        return $this->redirectToRoute('app_register');
    }
}
