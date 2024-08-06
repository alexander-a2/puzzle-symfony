<?php

namespace AlexanderA2\PuzzleBundle\Controller;

use AlexanderA2\PuzzleBundle\Services\PuzzleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PuzzleController extends AbstractController
{
    #[Route('/puzzle', name: 'puzzle_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $puzzleService = $this->getPuzzleService();

        if ($request->isMethod(Request::METHOD_POST)) {
            $puzzleService->savePuzzleData($request->request->all());

            return $this->redirectToRoute('puzzle_index');
        }

        return $this->render('@Puzzle/puzzle.html.twig', [
            'data' => $puzzleService->getPuzzleData(),
            'images' => $puzzleService->getAvailableImages(),
        ]);
    }

    #[Route('/puzzle/{imageDir}/{imageName}', name: 'puzzle_show_image')]
    public function showImage(string $imageDir, string $imageName): StreamedResponse
    {
        $path = $this->getPuzzleService()->getImagePath($imageDir . '/' . $imageName);
        $type = mime_content_type($path);
        $fileSize = filesize($path);

        $response = new StreamedResponse(function () use ($path) {
            $file = fopen($path, 'rb');
            if ($file === false) {
                throw new \Exception('Unable to open file for reading');
            }

            while (!feof($file)) {
                echo fread($file, 1024 * 8);
                ob_flush();
                flush();
            }
            fclose($file);
        });

        $response->headers->set('Content-Type', $type);
        $response->headers->set('Content-Length', $fileSize);

        return $response;
    }

    private function getPuzzleService(): PuzzleService
    {
        return new PuzzleService(
            $this->getParameter('kernel.project_dir') . '/tests/flow.json',
            $this->getParameter('kernel.project_dir') . '/tests/playwright/snapshots',
        );
    }
}