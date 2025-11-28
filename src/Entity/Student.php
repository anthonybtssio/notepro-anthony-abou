<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Grade::class, orphanRemoval: true)]
    private Collection $grades;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ClassLevel $classLevel = null;

    public function __construct()
    {
        parent::__construct();
        $this->grades = new ArrayCollection();
    }

    /**
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setStudent($this);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            // set the owning side to null (unless already changed)
            if ($grade->getStudent() === $this) {
                $grade->setStudent(null);
            }
        }

        return $this;
    }

    public function getClassLevel(): ?ClassLevel
    {
        return $this->classLevel;
    }

    public function setClassLevel(?ClassLevel $classLevel): static
    {
        $this->classLevel = $classLevel;

        return $this;
    }

    public function getGradeByEval(Evaluation $evaluation): ?Grade
    {
        foreach ($this->getGrades() as $grade){
            if ($grade->getEvaluation() === $evaluation){
                return $grade;
            }
        }
        return null;
    }

    // --- ICI COMMENCENT LES NOUVELLES MÉTHODES US 1 & 2 ---

    /**
     * US 2 : Filtre les notes pour ne garder que celles dont la date d'affichage est passée.
     */
    public function getVisibleGrades(): Collection
    {
        $visibleGrades = new ArrayCollection();
        $today = new \DateTime('today');

        foreach ($this->grades as $grade) {
            $evaluation = $grade->getEvaluation();

            // Ici, on appelle getDateAffichage() sur l'objet $evaluation,
            // c'est pour ça qu'il doit être défini dans l'entité Evaluation !
            if ($evaluation &&
                $evaluation->getDateAffichage() &&
                $evaluation->getDateAffichage() <= $today) {

                $visibleGrades->add($grade);
            }
        }

        return $visibleGrades;
    }

    /**
     * US 1 : Regroupe les notes visibles par matière et calcule la moyenne.
     */
    public function getAveragesGroupedBySubject(): array
    {
        $subjectsData = [];

        foreach ($this->getVisibleGrades() as $grade) {
            $evaluation = $grade->getEvaluation();
            $subjectLabel = $evaluation->getSubject()->getLabel();
            $bareme = $evaluation->getBareme();
            $note = (float) $grade->getGrade();

            if (!isset($subjectsData[$subjectLabel])) {
                $subjectsData[$subjectLabel] = [
                    'grades' => [],
                    'total_note_normalized' => 0.0,
                    'num_grades' => 0,
                    'average' => null
                ];
            }

            $subjectsData[$subjectLabel]['grades'][] = $grade;

            if ($bareme > 0) {
                $normalizedGradeOn20 = ($note / $bareme) * 20;
                $subjectsData[$subjectLabel]['total_note_normalized'] += $normalizedGradeOn20;
                $subjectsData[$subjectLabel]['num_grades']++;
            }
        }

        foreach ($subjectsData as &$data) {
            if ($data['num_grades'] > 0) {
                $data['average'] = $data['total_note_normalized'] / $data['num_grades'];
            }
        }

        return $subjectsData;
    }
}
