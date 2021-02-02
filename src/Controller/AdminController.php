<?php

namespace App\Controller;

use App\Entity\Chanel;
use App\Form\ChanelType;
use App\Repository\ChanelRepository;
use App\Service\UploaderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(ChanelRepository $chanelRepository): Response
    {
        $listChanel = $chanelRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'listChanel' => $listChanel
        ]);
    }

    /**
     * @Route("/admin/create", name="admin_create_chanel")
     */
    public function createChanel(Request $request, UploaderHelper $uploaderHelper): Response
    {
        $chanel = new Chanel();
        $form = $this->createForm(ChanelType::class, $chanel);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $chanel = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadChanelImage($uploadedFile);
                $chanel->setImageFilename($newFilename);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($chanel);
            $entityManager->flush();

            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/createChanel.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="admin_show_chanel")
     */
    public function showChanel(int $id, ChanelRepository $chanelRepository): Response
    {
        $chanel = $chanelRepository->find($id);

        return $this->render('admin/showChanel.html.twig', [
            'chanel' => $chanel,
        ]);
    }

    /**
     * @Route("/admin/edit/{id}", name="admin_edit_chanel")
     */
    public function editChanel(Chanel $chanel, Request $request, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(ChanelType::class, $chanel);
        $manager = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chanel = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                unlink($chanel->getImagePath());
                $newFilename = $uploaderHelper->uploadChanelImage($uploadedFile);
                $chanel->setImageFilename($newFilename);
            }

            $manager->persist($chanel);
            $manager->flush();

            //$this->addFlash('success', 'Votre voiture a bien été modifié');
            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/editChanel.html.twig', [
            'chanel' => $chanel,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="admin_delete_chanel")
     * @param Chanel $chanel
     * @return Response
     */
    public function delete(Chanel $chanel, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($chanel);
        $entityManager->flush();
        return new Response('La chaine est supprimé ' . $chanel->getId());
    }
}
