<?php

namespace App\Controller;

use App\Entity\Chanel;
use App\Repository\ChanelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(ChanelRepository $chanelRepository)
    {
        $listChanel = $chanelRepository->findAll();

        return $this->render('front/index.html.twig', [
            'listChanel' => $listChanel
        ]);
    }

    /**
     * @Route("/create", name="create_chanel")
     */
    public function createProduct(EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // $entityManager = $this->getDoctrine()->getManager();

        $chanel = new Chanel();
        $chanel->setName('Crypto Futur');
        $chanel->setUrl('https://www.youtube.com/channel/UCTP_QgORjbiMO4jL8NtGl_A');
        $chanel->setDescription("Salut à tous ! Bienvenue sur ma chaîne YouTube sur laquelle je vous présente une de mes passions qui est la cryptomonnaie !");

        $errors = $validator->validate($chanel);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $entityManager->persist($chanel);
        $entityManager->flush();

        return new Response('Saved new product with id ' . $chanel->getId());
    }

}
