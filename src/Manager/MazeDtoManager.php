<?php

namespace App\Manager;

use App\Dto\MazeDto;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MazeDtoManager
{
    public function fillUpOrUpdateDto(SessionInterface $session): MazeDto
    {
        $dto = new MazeDto();
        $previousDto = $session->get('dto');

        if (!$previousDto) {
            $dto->setPosition(1);
            $dto->setOrientation('E');
            $dto->setActionNumber(0);

            return $dto;
        }

        if ($session->has('pivote')) {
            $pivote = $session->get('pivote');
        }

        if ($session->has('newPosition')) {
            $newPosition = $session->get('newPosition');
        }

        $dto->setPosition($newPosition ?? $previousDto->getPosition());
        $dto->setOrientation($pivote ?? $previousDto->getOrientation());
        $dto->setActionNumber($previousDto->getActionNumber()+1);

        return $dto;
    }
}
