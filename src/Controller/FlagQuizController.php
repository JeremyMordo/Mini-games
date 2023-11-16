<?php

namespace App\Controller;

use App\Dto\QuizDto;
use App\Manager\QuizDtoManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[Route('/games/flagQuiz')]
class FlagQuizController extends AbstractController
{
    public const MAIN_PAGE = 'games/flagQuiz/main.html.twig';
    public const INITIATE = 'initiate';
    public const QUESTION = 'question';
    public const RESULT = 'result';
    public const CHEATER = 'cheater';
    public const MAIN_PATH = '/games/flagQuiz/process';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly QuizDtoManager $quizDtoManager,
    )
    {
    }

    #[Route('', name: 'quiz-menu', methods: 'GET')]
    public function menu(): Response
    {
        return $this->render(self::MAIN_PAGE, [
            'mode' => self::INITIATE,
        ]);
    }

    #[Route('/initiate', name: 'quiz-initiate', methods: 'GET')]
    public function initiate(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('quiz-process');
    }

    #[Route('/process', name: 'quiz-process', methods: ['GET', 'POST'])]
    public function process(Request $request): Response
    {
        $dto = $request->getMethod() === 'GET'
            ? $this->checkDtoAndRefreshSession($request)
            : $request->getSession()->get('dto')
        ;

        try {
            $this->client->request(
                'GET',
                $dto->getFlagUrl(),
            );
        } catch (Throwable) {
            return $this->redirect(self::MAIN_PATH);
        }

        $form = $this->createQuizForm($dto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $answers = $dto->getAnswers();
            $correctAnswer = $answers[$dto->getCorrectAnswer()];
            $result = $data['answer'] === $correctAnswer ? 'good' : 'bad';
            $flagName = array_search($correctAnswer, $answers, true);
            $dto->setGoodResult($result === 'good' ? $dto->getGoodResult()+1 : $dto->getGoodResult());
            $request->getSession()->set('dto', $dto);
            $request->getSession()->save();

            return $this->render(self::MAIN_PAGE,
                [
                    'mode' => self::RESULT,
                    'correctAnswer' => $flagName,
                    'result' => $result,
                    'dto' => $dto,
                ],
            );

        }

        return $this->render(self::MAIN_PAGE,
            [
                'mode' => self::QUESTION,
                'dto' => $dto,
                'form' => $form,
            ],
        );
    }

    #[Route('/next', name: 'quiz-next', methods: ['GET'])]
    public function nextQuestion(Request $request): Response
    {
        $dto = $request->getSession()->get('dto');
        $dto->setQuestionNumber($dto->getQuestionNumber()+1);

        return $this->redirect(self::MAIN_PATH);
    }

    #[Route('/refresh', name: 'refresh', methods: ['GET'])]
    public function refresh(Request $request): Response
    {
        $dto = $request->getSession()->get('dto');
        $dto->setRefreshNumber($dto->getRefreshNumber()+1);

        if (3 <= $dto->getRefreshNumber()) {
            return $this->render(self::MAIN_PAGE, [
                'mode' => self::CHEATER,
            ]);
        }

        return $this->redirect(self::MAIN_PATH);
    }

    private function createQuizForm(QuizDto $dto): FormInterface
    {
        return $this->createFormBuilder()
            ->add(
                'answer',
                ChoiceType::class,
                [
                    'choices' => $dto->getAnswers(),
                    'expanded' => true,
                    'multiple'=>false,
                ],
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Valider',
                    'attr' => [
                        'class' => 'btn btn-success btn-lg'
                    ],
                ]
            )
            ->setMethod('POST')
            ->getForm();
    }

    private function checkDtoAndRefreshSession(Request $request): QuizDto
    {
        $session = $request->getSession();
        $dto = $this->quizDtoManager->fillUpOrUpdateDto($session);
        $this->refreshSession($session, $dto);

        return $dto;
    }

    private function refreshSession(SessionInterface $session, QuizDto $dto): void
    {
        $session->clear();
        $session->set('dto', $dto);
        $session->save();
    }
}
