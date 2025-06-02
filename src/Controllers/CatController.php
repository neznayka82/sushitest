<?php

namespace App\Controllers;

use App\Database\Database;
use App\Model\Cat;
use App\Service\CatsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;


class CatController
{
    private CatsService  $service;

    public function __construct() {
        $this->service = new CatsService();
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $cats   = [];
        try {
            $cats = $this->service->getCats($request);
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }
        $response = new Response();
        $response->getBody()->write($this->renderView('cat/index', [
            'cats'   => $cats,
            'errors' => $errors,
        ]));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function view(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $cat    = null;
        try {
            $params = $request->getAttribute('params');
            $cat_id = $params['id'] ?? 0;

            $cat = Cat::select("SELECT * FROM cats WHERE id = :cat_id", ['cat_id' => $cat_id]);

        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }
        $response = new Response();
        $response->getBody()->write($this->renderView('cat/view', [
            'cat'    => $cat,
            'errors' => $errors
        ]));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute('params');
        /**
         * @var Cat $cat
         */
        $cat = Cat::selectOne("SELECT * FROM cats WHERE id = :id", ['id' => intval($params['id'])]);
        if (!$cat) {
            throw new \Exception('Кот не найден');
        }
        $cat->fathers = $cat->getFathers();
        $cat->mother  = $cat->getMother();
        $response = new Response();
        $response->getBody()->write($this->renderView('cat/edit', [
            'cat'    => $cat,
        ]));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $cat = new Cat();
        $response = new Response();
        $response->getBody()->write($this->renderView('cat/create', [
            'cat'       => $cat,
        ]));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute('params');

        $cat = Cat::selectOne("SELECT * FROM cats WHERE id = :id", ['id' => intval($params['id'])]);
        if (!$cat) {
            throw new \Exception('Кот не найден');
        }
        $this->service->delete($cat);

        return new Response\RedirectResponse(
            '/cats',
            302,
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function storage(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $params = $request->getParsedBody();

        try {
            if (!array_key_exists('id', $params)) {
                $this->service->create($request);
            } else {
                $this->service->edit($request);
            }
            //редирект
            return new Response\RedirectResponse(
                '/cats',
                302,
            );

        } catch (\Throwable $e) {
            $response->getBody()->write($this->renderView('cat/create', [
                'cat'       => $this->service->fillCat($request),
                'errors'    => [$e->getMessage(), $e->getTraceAsString()],
            ]));
        }

        return $response;
    }

    public function search(ServerRequestInterface $request): JsonResponse
    {
        $params = $request->getQueryParams();
        $q      = $params['q'] ?? '';
        $gender = $params['gender'];
        $cats   = [];
        if (strlen($q) > 2 && isset($gender)) {
            $cats = Cat::select("SELECT * FROM cats WHERE name LIKE :q AND gender = :gender", [
                'q' => "%{$q}%",
                'gender' => $gender
            ]);
        }

        return new JsonResponse($cats);
    }

    protected function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include  __DIR__ . "/../Views/" . $view . '.php';
        return ob_get_clean();
    }
}