<?php

namespace App\Manager;

use App\Dto\QuizDto;
use App\Service\IsoService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class QuizDtoManager
{
    public function setGoodResult(?QuizDto $previousDto): int
    {
        return isset($previousDto) ? $previousDto->getGoodResult() : 0;
    }

    public function fillUpOrUpdateDto(SessionInterface $session): QuizDto
    {
        $dto = new QuizDto();
        $previousDto = $session->get('dto');
        $isoAlreadyPassed = isset($previousDto) ? $previousDto->getIsoAlreadyPassed() : [];
        [$isoCode, $flagName] = IsoService::getRandomCountryWithIsoCode($isoAlreadyPassed);
        $wrongAnswers = IsoService::getRandomCountryName(3, $isoCode, $isoAlreadyPassed);

        $flagUrl = 'http://www.geognos.com/api/en/countries/flag/' . $isoCode . '.png';

        $dto->setFlagUrl($flagUrl);
        $dto->setCorrectAnswer($flagName);
        $dto->setQuestionNumber(isset($previousDto) ? $previousDto->getQuestionNumber() : 1);
        $dto->setGoodResult($this->setGoodResult($previousDto));
        $dto->setAnswers([$flagName, ...$wrongAnswers]);
        $dto->setIsoAlreadyPassed($this->setIsoAlreadyPassed($previousDto ?? $dto, $isoCode));
        $dto->setRefreshNumber(isset($previousDto) ? $previousDto->getRefreshNumber() : $dto->getRefreshNumber());

        return $dto;
    }

    private function setIsoAlreadyPassed(QuizDto $dto, string $isoCode): array
    {
        $isoAlreadyPassed = $dto->getIsoAlreadyPassed();
        $isoAlreadyPassed[] = $isoCode;

        return $isoAlreadyPassed;
    }
}