<?php

namespace AlexanderA2\PuzzleBundle\Services;

use Throwable;

class PuzzleService
{
    public function __construct(
        protected string $dataFilepath,
        protected string $snapshotsDirectory,
        protected string $snapshotUrl = 'puzzle',
    ) {
    }

    public function getPuzzleData(): array
    {
        if (!file_exists($this->dataFilepath)) {
            if (!is_writable(dirname($this->dataFilepath))) {
                throw new \Exception('Puzzle: data directory is read-only: ' . dirname($this->dataFilepath));
            }
            $data = json_encode([
                'scenarios' => [
                    [
                        'name' => 'Scenario 1',
                        'steps' => [
                            [
                                'name' => 'Step 1',
                                'image' => [
                                    'name' => '',
                                    'url' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            file_put_contents($this->dataFilepath, $data);
        }

        try {
            $data = json_decode(file_get_contents($this->dataFilepath), true);
        } catch (Throwable $e) {
            throw new \Exception('Puzzle: data file is not a valid JSON: ' . $this->dataFilepath . '(file could be deleted in order to recreate it)');
        }

        foreach($data['scenarios'] as $scenarioNumber => $scenario) {
            foreach($scenario['steps'] as $stepNumber => $step) {
                $data['scenarios'][$scenarioNumber]['steps'][$stepNumber]['image']['url'] = $this->getImageUrl($step['image']['name']);
            }
        }

        return $data;
    }

    public function savePuzzleData(array $data): void
    {
        if (!is_writable($this->dataFilepath)) {
            throw new \Exception('Puzzle: data file is read-only: ' . $this->dataFilepath);
        }

        file_put_contents($this->dataFilepath, json_encode($data));
    }

    public function getAvailableImages(): array
    {
        if (!file_exists($this->snapshotsDirectory) || !is_dir($this->snapshotsDirectory)) {
            throw new \Exception('Puzzle: snapshots directory does not exist: ' . $this->snapshotsDirectory);
        }
        $fullPathImages = glob(sprintf('%s/*/*.png', $this->snapshotsDirectory));

        $justImages = array_map(function ($imageFullPath) {
            return ltrim(str_replace($this->snapshotsDirectory, '', $imageFullPath), '/');
        }, $fullPathImages);

        $urlImages = array_map(function ($imageFullPath) {
            return '/' . str_replace($this->snapshotsDirectory, $this->snapshotUrl, $imageFullPath);
        }, $fullPathImages);

        return array_combine($urlImages, $justImages);
    }

    public function getImageUrl($name): string
    {
        if (str_contains($name, '..')) {
            throw new \Exception('Puzzle: invalid snapshot name: ' . $name);
        }
        return sprintf('/%s/%s', $this->snapshotUrl, $name);
    }

    public function getImagePath($name): string
    {
        if (str_contains($name, '..')) {
            throw new \Exception('Puzzle: invalid snapshot name: ' . $name);
        }
        return sprintf('/%s/%s', $this->snapshotsDirectory, $name);
    }
}