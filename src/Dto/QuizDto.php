<?php

namespace App\Dto;

class QuizDto
{
    private array $answers = [];
    private ?string $correctAnswer = null;
    private ?string $flagUrl = null;
    private int $goodResult = 0;
    private int $questionNumber = 0;
    private int $refreshNumber = 0;
    private array $isoAlreadyPassed = [];

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(?string $correctAnswer): self
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): self
    {
        shuffle($answers);
        foreach ($answers as $key =>$answer) {
            $arrayShuffled[$answer] = $key;
        }

        $this->answers = $arrayShuffled ?? [];

        return $this;
    }

    public function getFlagUrl(): string
    {
        return $this->flagUrl;
    }

    public function setFlagUrl(string $flagUrl): self
    {
        $this->flagUrl = $flagUrl;

        return $this;
    }

    public function getGoodResult(): int
    {
        return $this->goodResult;
    }

    public function setGoodResult(int $goodResult): self
    {
        $this->goodResult = $goodResult;

        return $this;
    }

    public function getQuestionNumber(): int
    {
        return $this->questionNumber;
    }

    public function setQuestionNumber(int $questionNumber): self
    {
        $this->questionNumber = $questionNumber;

        return $this;
    }

    public function getRefreshNumber(): int
    {
        return $this->refreshNumber;
    }

    public function setRefreshNumber(int $refreshNumber): self
    {
        $this->refreshNumber = $refreshNumber;

        return $this;
    }

    public function getIsoAlreadyPassed(): array
    {
        return $this->isoAlreadyPassed;
    }

    public function setIsoAlreadyPassed(array $isoAlreadyPassed): self
    {
        $this->isoAlreadyPassed = $isoAlreadyPassed;

        return $this;
    }
}
