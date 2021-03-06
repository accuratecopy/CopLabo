<?php

namespace App\Controller;

use App\Entity\ChampsSoumis;
use App\Entity\Evenements;
use App\Form\AttributionType;
use App\Entity\Formulaires;
use App\Entity\StartUp;
use App\Form\ChampsSoumisType;
use App\Form\EvenementsType;
use App\Form\MailingType;
use App\Form\SendingMailType;
use App\Repository\EvenementsRepository;
use App\Repository\StartUpRelationRepository;
use App\Repository\StartUpRepository;
use App\Repository\FormulairesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/evenements")
 */
class EvenementsController extends AbstractController
{
    /**
     * @Route("/", name="evenements_index", methods={"GET"})
     */
    public function index(EvenementsRepository $evenementsRepository): Response
    {
        return $this->render('evenements/index.html.twig', ['evenements' => $evenementsRepository->findAll()]);
    }

    /**
     * @Route("/new", name="evenements_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $evenement = new Evenements();
        $form = $this->createForm(EvenementsType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('evenements_index');
        }

        return $this->render('evenements/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="evenements_show", methods={"GET"})
     */
    public function show(Evenements $evenement): Response
    {

        return $this->render('evenements/show.html.twig', ['evenement' => $evenement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="evenements_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Evenements $evenement): Response
    {
        $form = $this->createForm(EvenementsType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('evenements_index', ['id' => $evenement->getId()]);
        }

        return $this->render('evenements/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="evenements_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Evenements $evenement): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('evenements_index');
    }


    /**
     * @Route("/mailing/{id}", name="event_mailing_manager", methods={"GET","POST"})
     * @return Response
     */

    public function mailingManager(Request $request, int $id, \Swift_Mailer $mailer, EvenementsRepository $evenementsRepository, FormulairesRepository $fm):Response
    {
        $formulaire=new Formulaires();
        $event=$evenementsRepository->findOneBy(['id'=>$id]);


        $form = $this->createForm(MailingType::class, $formulaire);
        $form->handleRequest($request);
        $flash=false;
        if ($form->isSubmitted() && $form->isValid()) {
            $formulaire->setEvenements($event);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($formulaire);
            $entityManager->flush();
            $toto = $formulaire->getId();

            $startups = $event->getStartups();
            $startups = $startups->getValues();
            foreach ($startups as $id => $value) {
                $email = $value->getEmail();
                $startupid = $value->getId();

                $message = (new \Swift_Message('Questionnaire de satisfaction'))
                    ->setFrom(["cop.lab.wcs@gmail.com" => 'sender name'])
                    ->setTo([$email])
                    ->setBody(
                        $this->renderView(
                            'evenements/mail.html.twig', [
                                'toto' => $toto,
                                'startupid' => $startupid
                            ]
                        ),
                        'text/html'
                    );
                if ($mailer->send($message)) {
                    $flash=true;
                }
            }
            if ($flash==true) {
                $this->addFlash(
                    'success',
                    "Emails envoyés avec succès !"
                );
            } else {
                $this->addFlash(
                    'danger',
                    "Les emails n'ont pas pu être envoyés !"
                );
            }
        }

        return $this->render('evenements/mailing.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'event' => $event,
        ]);
    }


    /**
     * @Route("/attribution/{id}/edit", name="attribution_edit", methods={"GET","POST"})
     */
    public function attribute(Request $request, Evenements $evenement): Response
    {
        $form = $this->createForm(AttributionType::class, $evenement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('evenements_index', ['id' => $evenement->getId()]);
        }
        return $this->render('attribution/index.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/formUser/validate", name="validate_user", methods={"GET"})
     */
    public function validateUser(): Response
    {
        return $this->render('formUsers/validateForm.html.twig');
    }

    /**
    * @Route("/formUser/{id}/{startupid}", name="form_users", methods={"GET","POST"})
    */
    public function newForm(Request $request, FormulairesRepository $fm, int $id, StartUpRelationRepository $sr, StartUp $startupid): Response
    {
        $champs = new ChampsSoumis();
        $formulaire = $fm->findOneBy(['id' => $id]);
        $form = $this->createForm(ChampsSoumisType::class, $champs);
        $form->handleRequest($request);
        $champs->setStartup($startupid);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($champs);
            $entityManager->flush();
            return $this->redirectToRoute('validateForm');
        }
        return $this->render('formUsers/formulaireUser.html.twig', [
            'formulaire' => $formulaire,
            'form' => $form->createView(),
        ]);
    }
}
