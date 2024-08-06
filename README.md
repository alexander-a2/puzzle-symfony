### A web-page with a screenshots (playwright) puzzle to show whole flow-picture of project

---

### Requirements

- Symfony project
- Playwright with a snapshots dir 

### Installation

In your Symfony project run:
    
    composer require alexander-a2/puzzle-symfony

add bundle to `config/bundles.php`:

    AlexanderA2\PuzzleSymfony\PuzzleSymfonyBundle::class => ['dev' => true],

add routes to `config/routing_dev.yaml`:

    puzzle:
        resource: '@PuzzleBundle/Resources/config/routing.yml'