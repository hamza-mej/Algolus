<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $contactName;

    #[ORM\Column(type: 'string', length: 255)]
    private $contactEmail;

    #[ORM\Column(type: 'string', length: 255)]
    private $contactSubject;

    #[ORM\Column(type: 'text', nullable: false)]
    private $contactMessage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): self
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactSubject(): ?string
    {
        return $this->contactSubject;
    }

    public function setContactSubject(string $contactSubject): self
    {
        $this->contactSubject = $contactSubject;

        return $this;
    }

    public function getContactMessage(): ?string
    {
        return $this->contactMessage;
    }

    public function setContactMessage(string $contactMessage): self
    {
        $this->contactMessage = $contactMessage;

        return $this;
    }
}
