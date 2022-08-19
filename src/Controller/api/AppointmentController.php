<?php

namespace App\Controller\api;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\PractitionerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class AppointmentController extends AbstractController
{
    public function __construct(private readonly AppointmentRepository  $appointmentRepository,
                                private readonly ClientRepository       $clientRepository,
                                private readonly PractitionerRepository $practitionerRepository)
    {
    }

    #[Route('/client/{id<\d+>}/appointments', name: 'api_client_appointments', methods: ['GET'])]
    public function allByClient(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if ($client) {
            $appointments = $this->appointmentRepository->findBy(array('client' => $client));
            $data = [];
            foreach ($appointments as $appointment) {
                $data[] = $appointment->toArray();
            }
            return $this->json($data);
        } else {
            return $this->json(['message' => "No client with id $id found"], 404);
        }
    }

    #[Route('/practitioner/{id<\d+>}/appointments', name: 'api_practitioner_appointments', methods: ['GET'])]
    public function allByPractitioner(int $id): JsonResponse
    {
        $practitioner = $this->practitionerRepository->find($id);
        if ($practitioner) {
            $appointments = $this->appointmentRepository->findBy(array('practitioner' => $practitioner));
            $data = [];
            foreach ($appointments as $appointment) {
                $data[] = $appointment->toArray();
            }
            return $this->json($data);
        } else {
            return $this->json(['message' => "No practitioner with id $id found"], 404);
        }
    }

    #[Route('/appointment/new', name: 'api_appointment_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $newAppointment = new Appointment();
        try {
            $newAppointment->setDateTime($request->request->get('datetime'));
        } catch (TypeError $e) {
            return $this->json([
                'message' => 'Could not create appointment. Datetime format not found or invalid. Datetime format should be Y-m-d H:i',
            ], 400);
        }
        try {
            $practitioner = $this->practitionerRepository->find($request->get('practitionerId'));
            $newAppointment->setPractitioner($practitioner);
            $client = $this->clientRepository->find($request->request->get('clientId'));
            $newAppointment->setClient($client);
            $this->appointmentRepository->add($newAppointment, true);
        } catch (Exception $e) {
            return $this->json([
                'message' => 'Could not create appointment. ClientId or practitionerId not valid or not found',
            ], 400);
        }
        return $this->json(['message' => 'Appointment successfully created',]);
    }

    #[Route('/appointment/{id<\d+>}/delete', name: 'api_appointment_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $appointment = $this->appointmentRepository->find($id);
        if ($appointment) {
            $this->appointmentRepository->remove($appointment, true);
            return $this->json([
                'message' => 'Appointment successfully removed'
            ]);
        } else {
            return $this->json(['message' => "No appointment with id $id found"], 404);
        }
    }

    #[Route('/appointment/{id<\d+>}/update', name: 'api_appointment_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $appointment = $this->appointmentRepository->find($id);
        if ($appointment) {
            $data = json_decode($request->getContent());
            if (isset($data->datetime)) {
                $appointment->setDateTime($data->datetime);
            }
            if (isset($data->practitionerId)) {
                $practitioner = $this->practitionerRepository->find($data->practitionerId);
                if ($practitioner) {
                    $appointment->setPractitioner($practitioner);
                } else {
                    return $this->json(['message' => "No practitioner with id $data->practitionerId found"], 404);
                }
            }
            if (isset($data->clientId)) {
                $client = $this->clientRepository->find($data->clientId);
                if ($client) {
                    $appointment->setClient($client);
                } else {
                    return $this->json(['message' => "No client with id $data->clientId found"], 404);
                }
            }
            $em->flush();
            return $this->json([
                'message' => 'Appointment successfully updated',
            ]);
        } else {
            return $this->json(['message' => "No appointment with id $id found"], 404);
        }
    }
}
