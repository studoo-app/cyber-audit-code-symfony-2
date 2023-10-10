<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Form\EntryType;
use App\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entries')]
class EntryController extends AbstractController
{
    #[Route('/', name: 'app_entry_index', methods: ['GET'])]
    public function index(EntryRepository $entryRepository): Response
    {
        $entries = $entryRepository->findBy(["user"=>$this->getUser()]);
        return $this->render('entry/index.html.twig', [
            'entries' => $entries
        ]);
    }

    #[Route('/new', name: 'app_entry_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entry = new Entry();
        $form = $this->createForm(EntryType::class, $entry,[
            'tags'=>$entityManager->getRepository(Tag::class)->findBy(['user'=>$this->getUser()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entry);
            $entityManager->flush();

            return $this->redirectToRoute('app_entry_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entry/new.html.twig', [
            'entry' => $entry,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entry_show', methods: ['GET'])]
    public function show(Entry $entry): Response
    {

        return $this->render('entry/show.html.twig', [
            'entry' => $entry,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entry_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entry $entry, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntryType::class, $entry,[
            'tags'=>$entityManager->getRepository(Tag::class)->findBy(['user'=>$this->getUser()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_entry_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entry/edit.html.twig', [
            'entry' => $entry,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entry_delete', methods: ['POST'])]
    public function delete(Request $request, Entry $entry, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entry->getId(), $request->request->get('_token'))) {
            $entityManager->remove($entry);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_entry_index', [], Response::HTTP_SEE_OTHER);
    }
}
