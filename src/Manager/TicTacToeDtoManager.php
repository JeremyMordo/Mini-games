<?php

namespace App\Manager;

use App\Dto\TicTacToeDto;
use App\Service\TicTacToeService;
use Exception;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TicTacToeDtoManager
{
    /**
     * @throws Exception
     */
    public function fillUpOrUpdateDto(
        SessionInterface $session,
        InputBag $query,
        ?bool $playerStart
    ): TicTacToeDto {
        $dto = new TicTacToeDto();

        if (false === $playerStart) {
            $this->randomComputerPlay($dto);
            return $dto;
        }

        if ($query->has('grid')) {
            $grid = $query->get('grid');
            $previousDto = $session->get('dto');

            if (null !== $previousDto->getWinner()) {
                return $previousDto;
            }

            $coordinate = $previousDto->getCoordinate();
            $coordinate[$grid] = 1;
            $dto->setCoordinate($coordinate);
            $this->isThereAWinner($dto);

            if (null !== $dto->getWinner()) {
                return $dto;
            }

            $this->randomComputerPlay($dto);
        }

        $this->isThereAWinner($dto);

        return $dto;
    }

    /**
     * @throws Exception
     */
    private function randomComputerPlay(TicTacToeDto $dto): void
    {
        $coordinate = $dto->getCoordinate();
        $freeGrid = $this->getFreeGrid($coordinate);
        $gridChosen = $freeGrid[array_rand($freeGrid)];
        $coordinate[$gridChosen] = 2;
        $dto->setCoordinate($coordinate);
    }

    private function getFreeGrid(array $coordinate): array
    {
        foreach ($coordinate as $grid => $player){
            if (0 === $player) {
                $freeGrid[] = $grid;
            }
        }

        return $freeGrid ?? [];
    }

    private function isThereAWinner(TicTacToeDto $dto): void
    {
        $coordinate = $dto->getCoordinate();
        $playerCoordinates = [];
        $computerCoordinates = [];
        foreach ($coordinate as $grid => $player) {
            if (1 === $player) {
                $playerCoordinates[] = $grid;
            } elseif (2 === $player) {
                $computerCoordinates[] = $grid;
            }
        }

        if (empty($playerCoordinates)) {
            return;
        }

        foreach(TicTacToeService::WIN_COORDINATE as $winCondition) {
            $conditionPlayerNumber = 0;
            $conditionComputerNumber = 0;

            foreach ($playerCoordinates as $playerCoordinate) {
                if (in_array($playerCoordinate, $winCondition, true)) {
                    $conditionPlayerNumber++;
                }
            }

            foreach ($computerCoordinates as $computerCoordinate) {
                if (in_array($computerCoordinate, $winCondition, true)) {
                    $conditionComputerNumber++;
                }
            }

            if (3 === $conditionPlayerNumber) {
                $dto->setWinner(1);
                return;
            }

            if (3 === $conditionComputerNumber) {
                $dto->setWinner(2);
                return;
            }
        }

        if (empty($this->getFreeGrid($coordinate))) {
            $dto->setWinner(3);
        }
    }
}
