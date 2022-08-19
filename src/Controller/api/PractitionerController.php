<?php

namespace App\Controller\api;

use App\Entity\Client;
use App\Entity\Practitioner;
use App\Repository\ClientRepository;
use App\Repository\PractitionerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class PractitionerController extends AbstractController
{
    public function __construct(private readonly PractitionerRepository $practitionerRepository)
    {
    }

    #[Route('/practitioner/all', name: 'api_practitioner_all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        $practitioners = $this->practitionerRepository->findAll();
        $data = [];
        foreach ($practitioners as $practitioner) {
            $data[] = $practitioner->toArray();
        }
        return $this->json($data);
    }

    #[Route('/practitioner/{id<\d+>}', name: 'api_practitioner', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $practitioner = $this->practitionerRepository->find($id);
        if ($practitioner) {
            return $this->json($practitioner->toArray());
        } else {
            return $this->json(['message' => "No practitioner with id $id found"], 404);
        }
    }

    #[Route('/practitioner/new', name: 'api_practitioner_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $newPractitioner = new Practitioner();
        try {
            $newPractitioner->setEmail($request->request->get('email'));
            $newPractitioner->setPassword($request->request->get('password'));
        } catch (TypeError $e) {
            return $this->json([
                'message' => 'Email and/or password not found',
            ], 400);
        }
        $this->practitionerRepository->add($newPractitioner, true);
        return $this->json([
            'message' => 'Practitioner successfully created',
        ]);
    }

    #[Route('/practitioner/{id<\d+>}/delete', name: 'api_practitioner_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $practitioner = $this->practitionerRepository->find($id);
        if ($practitioner) {
            $this->practitionerRepository->remove($practitioner, true);
            return $this->json([
                'message' => 'Practitioner successfully removed'
            ]);;
        } else {
            return $this->json(['message' => "No practitioner with id $id found"], 404);
        }
    }

    #[Route('/practitioner/{id<\d+>}/update', name: 'api_practitioner_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $practitioner = $this->practitionerRepository->find($id);
        if ($practitioner) {
            $data = json_decode($request->getContent());
            if (isset($data->email)) {
                $practitioner->setEmail($data->email);
            }
            if (isset($data->password)) {
                $practitioner->setPassword($data->password);
            }
            $em->flush();
            return $this->json([
                'message' => 'Practitioner successfully updated',
            ]);
        } else {
            return $this->json(['message' => "No practitioner with id $id found"], 404);
        }
    }
}
