<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Security\Roles;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Note: The table name in this entity is changed so it plays nice with postgres
 * where 'user' is a keyword.
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $forename;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $surname;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToOne(targetEntity=Student::class, mappedBy="appuser", cascade={"persist", "remove"})
     */
    private $student;

    /**
     * @ORM\OneToOne(targetEntity=Instructor::class, mappedBy="appuser", cascade={"persist", "remove"})
     */
    private $instructor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getForename(): ?string
    {
        return $this->forename;
    }

    public function setForename(string $forename): self
    {
        $this->forename = $forename;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->forename . ' ' . $this->surname;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Used to return the security roles for this user.
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role)
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole($role)
    {
        if (!in_array($role, $this->roles, true)) {
            return;
        }

        unset($this->roles[array_search($role, $this->roles)]);
        $this->roles = array_values($this->roles);

        return $this;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): self
    {
        // at present, a role can only be as an instructor or as a student
        if ($this->instructor !== null) {
            throw new Exception("Cannot set a instructor as a student.", 1);
        }

        $this->student = $student;
        $this->addRole(Roles::STUDENT);

        // set the owning side of the relation if necessary
        if ($student->getUser() !== $this) {
            $student->setUser($this);
        }

        return $this;
    }

    public function getInstructor(): ?Instructor
    {
        return $this->instructor;
    }

    public function setInstructor(Instructor $instructor): self
    {
        // at present, a role can only be as an instructor or as a student
        if ($this->student !== null) {
            throw new Exception("Cannot set a student as an instructor.", 1);
        }

        $this->instructor = $instructor;
        $this->addRole(Roles::INSTRUCTOR);

        // set the owning side of the relation if necessary
        if ($instructor->getUser() !== $this) {
            $instructor->setUser($this);
        }

        return $this;
    }

    public function __toString() : string
    {
        return sprintf("Name: %s - Email: %s\n",
            $this->getFullname(),
            $this->getEmail());
    }
}
