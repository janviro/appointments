<?php

namespace App\Controller\api;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class ClientController extends AbstractController
{
    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    #[Route('/client/all', name: 'api_client_all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        $clients = $this->clientRepository->findAll();
        $data = [];
        foreach ($clients as $client) {
            $data[] = $client->toArray();
        }
        return $this->json($data);
    }

    #[Route('/client/{id<\d+>}', name: 'api_client', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if($client){
            return $this->json($client->toArray());
        } else {
            return $this->json(['message' => "No client with id $id found"], 404);
        }
    }

    #[Route('/client/new', name: 'api_client_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $newClient = new Client();
        try {
            $newClient->setEmail($request->request->get('email'));
            $newClient->setPassword($request->request->get('password'));
        } catch(TypeError $e) {
            return $this->json([
                'message' => 'Email and/or password not found',
            ], 400);
        }
        $this->clientRepository->add($newClient, true);
        return $this->json([
            'message' => 'Client successfully created',
        ]);
    }

    #[Route('/client/{id<\d+>}/delete', name: 'api_client_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if($client){
            $this->clientRepository->remove($client, true);
            return $this->json([
                'message' => 'Client successfully removed'
            ]);
        } else {
            return $this->json(['message' => "No client with id $id found"], 404);
        }
    }

    #[Route('/client/{id<\d+>}/update', name: 'api_client_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if($client){
            $data = json_decode($request->getContent());
            if(isset($data->email)) {$client->setEmail($data->email);}
            if(isset($data->password)) {$client->setPassword($data->password);}
            $em->flush();
            return $this->json([
                'message' => 'Client successfully updated',
            ]);
        } else {
            return $this->json(['message' => "No client with id $id found"], 404);
        }
    }
}
