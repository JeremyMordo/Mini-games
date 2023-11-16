<?php

namespace App\Controller;

use App\Dto\TicTacToeDto;
use App\Manager\TicTacToeDtoManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games/ticTacToe')]

class TicTacToeController extends AbstractController
{
    public const MAIN_PAGE = 'games/ticTacToe/main.html.twig';
    public const INITIATE = 'initiate';
    public const DUEL = 'duel';
    public const RESULT = 'result';

    public function __construct(
        private readonly TicTacToeDtoManager $ticTacToeDtoManager,
    )
    {
    }
    #[Route('', name: 'ticTacToe-menu', methods: 'GET')]
    public function menu(): Response
    {
        return $this->render(self::MAIN_PAGE, [
            'mode' => self::INITIATE,
        ]);
    }

    #[Route('/initiate-me', name: 'ticTacToe-initiate-me', methods: 'GET')]
    public function initiateMe(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('ticTacToe-process', ['start' => 'me']);
    }

    #[Route('/initiate-you', name: 'ticTacToe-initiate-you', methods: 'GET')]
    public function initiateYou(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('ticTacToe-process', ['start' => 'you']);
    }

    /**
     * @throws Exception
     */
    #[Route('/process', name: 'ticTacToe-process', methods: ['GET'])]
    public function process(Request $request): Response
    {
        $dto = $this->checkDtoAndRefreshSession($request);

        return $this->render(self::MAIN_PAGE,
            [
                'mode' => self::DUEL,
                'dto' => $dto,
            ],
        );
    }

    /**
     * @throws Exception
     */
    private function checkDtoAndRefreshSession(Request $request): TicTacToeDto
    {
        $session = $request->getSession();
        $query = $request->query;


        $playerStart = $this->isPlayerStart($request);

        $dto = $this->ticTacToeDtoManager->fillUpOrUpdateDto($session, $query, $playerStart);
        $this->refreshSession($session, $dto);

        return $dto;
    }

    private function refreshSession(SessionInterface $session, TicTacToeDto $dto): void
    {
        $session->clear();
        $session->set('dto', $dto);
        $session->save();
    }

    private function isPlayerStart(Request $request): null|bool
    {
        if (str_contains($request->getRequestUri(), 'start=me')) {
            return true;
        }

        if (str_contains($request->getRequestUri(), 'start=you')) {
            return false;
        }

        return null;
    }
}
