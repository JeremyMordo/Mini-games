<?php

namespace App\Controller;

use App\Dto\MazeDto;
use App\Manager\MazeDtoManager;
use App\Service\MazeCoordonateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games/maze')]
class MazeController extends AbstractController
{
    public const MAIN_PAGE = 'games/maze/main.html.twig';
    public const INITIATE = 'initiate';
    public const JOURNEY = 'journey';
    public const ESCAPE = 'escape';
    public const MAIN_PATH = '/games/maze/process';

    public function __construct(
        private readonly MazeDtoManager $mazeDtoManager,
    )
    {
    }

    #[Route('', name: 'maze-menu', methods: 'GET')]
    public function menu(): Response
    {
        return $this->render(self::MAIN_PAGE, [
            'mode' => self::INITIATE,
        ]);
    }

    #[Route('/initiate', name: 'maze-initiate', methods: 'GET')]
    public function initiate(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('maze-process');
    }

    #[Route('/process', name: 'maze-process', methods: ['GET'])]
    public function process(Request $request): Response
    {
        $dto = $this->checkDtoAndRefreshSession($request);
        $imageName = $this->getImage($dto);
        if (25 === $dto->getPosition()) {
            return $this->render(self::MAIN_PAGE,
                [
                    'mode' => self::ESCAPE,
                    'dto' => $dto,
                ],
            );
        }

        return $this->render(self::MAIN_PAGE,
            [
                'mode' => self::JOURNEY,
                'image' => $imageName,
                'dto' => $dto,
            ],
        );
    }

    #[Route('/pivote-north', name: 'maze-pivote-north', methods: ['GET'])]
    public function pivoteNorth(Request $request): Response
    {
        $this->pivoteByDirection($request,'N');

        return $this->redirect(self::MAIN_PATH);
    }

    #[Route('/pivote-east', name: 'maze-pivote-east', methods: ['GET'])]
    public function pivoteEast(Request $request): Response
    {
        $this->pivoteByDirection($request,'E');

        return $this->redirect(self::MAIN_PATH);
    }

    #[Route('/pivote-south', name: 'maze-pivote-south', methods: ['GET'])]
    public function pivoteSouth(Request $request): Response
    {
        $this->pivoteByDirection($request,'S');

        return $this->redirect(self::MAIN_PATH);
    }

    #[Route('/pivote-west', name: 'maze-pivote-west', methods: ['GET'])]
    public function pivoteWest(Request $request): Response
    {
        $this->pivoteByDirection($request,'W');

        return $this->redirect(self::MAIN_PATH);
    }

    #[Route('/move-north', name: 'maze-move-north', methods: ['GET'])]
    public function moveNorth(Request $request): Response
    {
        return $this->moveByDirection($request, 'N');
    }

    #[Route('/move-east', name: 'maze-move-east', methods: ['GET'])]
    public function moveEast(Request $request): Response
    {
        return $this->moveByDirection($request,'E');
    }

    #[Route('/move-south', name: 'maze-move-south', methods: ['GET'])]
    public function moveSouth(Request $request): Response
    {
        return $this->moveByDirection($request,'S');
    }

    #[Route('/move-west', name: 'maze-move-west', methods: ['GET'])]
    public function moveWest(Request $request): Response
    {
        return $this->moveByDirection($request,'W');
    }

    private function checkDtoAndRefreshSession(Request $request): MazeDto
    {
        $session = $request->getSession();
        $dto = $this->mazeDtoManager->fillUpOrUpdateDto($session);
        $this->refreshSession($session, $dto);

        return $dto;
    }

    private function refreshSession(SessionInterface $session, MazeDto $dto): void
    {
        $session->clear();
        $session->set('dto', $dto);
        $session->save();
    }

    private function getImage(MazeDto $dto): string
    {
        return -1 === MazeCoordonateService::EASY_ORIENTATION[$dto->getPosition()][$dto->getOrientation()]
            ? 'wall'
            : 'forward';
    }

    private function moveByDirection(Request $request, string $direction): Response
    {
        $session = $request->getSession();
        $dto = $session->get('dto');
        $newPosition = MazeCoordonateService::EASY_ORIENTATION[$dto->getPosition()][$direction];

        if (-1 === $newPosition){
            $imageName = $this->getImage($dto);
            $error = 'Vous ne pouvez pas avancer dans cette direction, un mur se trouve devant vous.';
            return $this->render(self::MAIN_PAGE,
                [
                    'error' => $error,
                    'mode' => self::JOURNEY,
                    'image' => $imageName,
                    'dto' => $dto,
                ],
            );
        }

        $session->set('newPosition', $newPosition);
        $session->save();

        return $this->redirect(self::MAIN_PATH);
    }

    private function pivoteByDirection(Request $request, string $direction): void
    {
        $session = $request->getSession();
        $session->set('pivote', $direction);
        $session->save();
    }
}
