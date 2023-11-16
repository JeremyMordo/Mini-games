<?php

namespace App\Dto;

class TicTacToeDto
{
    /*
     * 1 / 2 / 3
     * 4 / 5 / 6
     * 7 / 8 / 9
     */
    private array $coordinate = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0,
        6 => 0,
        7 => 0,
        8 => 0,
        9 => 0,
    ];

    private ?int $winner = null;

    public function getCoordinate(): array
    {
        return $this->coordinate;
    }

    public function setCoordinate(array $coordinate): self
    {
        $this->coordinate = $coordinate;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(int $winner): self
    {
        $this->winner = $winner;

        return $this;
    }
}
