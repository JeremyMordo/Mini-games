<?php

namespace App\Dto;

class MazeDto
{
    // 1, 2, 3, 4 ...
    private int $position = 0;

    // N, E, S, W
    private string $orientation = 'N';

    // 1, 2, 3, 4 ...
    private int $actionNumber = 0;

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getActionNumber(): int
    {
        return $this->actionNumber;
    }

    public function setActionNumber(int $actionNumber): self
    {
        $this->actionNumber = $actionNumber;

        return $this;
    }
}
