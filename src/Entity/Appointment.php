<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateTime = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Practitioner $practitioner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTime(): ?DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(string $dateTimeString): self
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i', $dateTimeString);
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getDateTimeString(): string
    {
        return $this->dateTime->format('Y-m-d H:i');
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getPractitioner(): ?Practitioner
    {
        return $this->practitioner;
    }

    public function setPractitioner(?Practitioner $practitioner): self
    {
        $this->practitioner = $practitioner;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'client' => $this->getClient()->getEmail(),
            'practitioner' => $this->getPractitioner()->getEmail(),
            'datetime' => $this->getDateTimeString()
        ];
    }
}
