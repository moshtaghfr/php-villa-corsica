<?php

namespace App\Controller;

use App\Entity\Villa;
use App\Model\Booking;
use App\Form\BookingType;
use App\Model\Booking as ModelBooking;
use App\Repository\VillaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @Route("/villa")
 */
class VillaController extends AbstractController
{
    /**
     * @Route("/", name="villa_index", methods={"GET"})
     */
    public function index(VillaRepository $villaRepository): Response
    {
        return $this->render('villa/index.html.twig', [
            'villas' => $villaRepository->findAll(),
        ]);
    }


    /**
     * @Route("/{id}", name="villa_show", methods={"GET","POST"})
     */
    public function show(Villa $villa, Request $request, MailerInterface $mailer): Response
    {
        $booking= new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $information = $form->getData();
            $email = (new TemplatedEmail())
                ->from('core54@gmail.com')
                ->to(new Address($this->getParameter('mailer_to')))
                ->subject('Reservation')
            // path of the Twig template to render
                ->htmlTemplate('email/reservation.html.twig')
           // pass l'object (information et villa) to the template
                ->context([
                   'information' => $information,
                   'villa'=>$villa,
                    ]);
            $mailer->send($email);
            // the success flash message
            $this->addFlash('success', 'Votre demande de réservation a été bien envoyée');
            return $this->redirectToRoute('villa_index');
        }
        return $this->render('villa/show.html.twig', [
            'villa' => $villa,
            'form' => $form->createView(),
        ]);
    }
}
