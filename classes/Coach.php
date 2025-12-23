<?php
require_once "Utilisateur.php";

class Coach extends Utilisateur
{
    protected string $discipline_coach = "";
    protected int $experiences_coach = 0;

    public function setDiscipline(string $discipline): void
    {
        $this->discipline_coach = trim($discipline);
    }

    public function setExperience(int $annees): void
    {
        $this->experiences_coach = $annees;
    }

    public function getDiscipline(): string { return $this->discipline_coach; }
    public function getExperience(): int { return $this->experiences_coach; }
}
