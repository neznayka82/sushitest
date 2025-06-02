<?php

namespace App\Service;

use App\Database\Database;
use App\Model\Cat;
use App\Model\Model;
use Psr\Http\Message\ServerRequestInterface;

class CatsService
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     * @throws \Exception
     */
    public function getCats(ServerRequestInterface $request) : array
    {
        $this->validateFilter($request);

        $params = $request->getQueryParams();
        $order_by   = $params['sort_by'] ?? 'name';
        $order_type = $params['sort_dir'] ?? 'desc';
        $gender     = $params['gender'] ?? null;
        $age_min    = $params['age_min'] ?? null;
        $age_max    = $params['age_max'] ?? null;

        $where = [];
        $sql_params = [];
        if (!empty($gender)) {
            $where[] = "c.gender = :gender";
            $sql_params['gender'] = $gender;
        }
        if (!empty($age_min)) {
            $where[] = "c.age >= :age_min";
            $sql_params['age_min'] = intval($age_min);
        }
        if (!empty($age_max)) {
            $where[] = "c.age <= :age_max";
            $sql_params['age_max'] = intval($age_max);
        }

        $sql_where = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';

        if ($order_by === 'fathers_count') {
            $sql = "SELECT c.*, COUNT(f.id) as fathers_count 
            FROM cats c
            LEFT JOIN cat2fathers f ON f.cat_id = c.id
            {$sql_where}
            GROUP BY c.id
            ORDER BY fathers_count {$order_type}";
        } else {
            // Обычная сортировка
            $sql = "SELECT c.* FROM cats c {$sql_where} ORDER BY c.{$order_by} {$order_type}";
        }

        $cats = Cat::select($sql, $sql_params);
        foreach ($cats as $cat) {
            /**
             * @var Cat $cat
            */
            if (isset($cat->mother_id)) {
                foreach($cats as $cat_mother) {
                    if ($cat_mother->id == $cat->mother_id) {
                        $cat->mother = $cat_mother;
                        break;
                    }
                }
            }
            $cat->fathers = $cat->getFathers();
        }
        return $cats;
    }

    /** Валидация
     * @param ServerRequestInterface $request
     * @return void
     * @throws \Exception
     */
    private function validateFilter(ServerRequestInterface $request): void
    {
        $params = $request->getQueryParams();
        $order_type = $params['sort_dir'] ?? 'desc';
        $order_by   = $params['sort_by'] ?? 'name';
        $gender     = $params['gender'] ?? null;

        if (!in_array($order_type, ['asc', 'desc'])) {
            throw new \Exception('Неверное направление сортировки');
        }

        if (!in_array($order_by, ['name', 'age', 'gender', 'fathers_count'])) {
            throw new \Exception('Неверное поле сортировки');
        }

        if (!empty($gender) && !in_array(intval($gender), [Cat::CAT_MALE_GIRL, Cat::CAT_MALE_GIRL])) {
            throw new \Exception('Неверно указан фильтр по полу');
        }

    }

    /**
     * @param ServerRequestInterface $request
     * @throws \Exception
     */
    public function create(ServerRequestInterface $request): void
    {
        $this->validateStorage($request);
        try {
            $cat = $this->fillCat($request);
            $cat->create();

            $cat = $this->fillCatFathers($request, $cat);
            $cat->saveFathers();
        } catch (\Throwable $e) {
            if (isset($cat)) {
                $cat->delete();
                $cat->deleteFathers();
            }
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     * @throws \Exception
     */
    private function validateStorage(ServerRequestInterface $request): void
    {
        $params = $request->getParsedBody();
        if (!isset($params['name']) || !isset($params['age']) || !isset($params['gender'])) {
            throw new \Exception('Не указаны обязательные параметры');
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return Cat
     * @throws \Exception
     */
    public function fillCat(ServerRequestInterface $request): Cat
    {
        $params = $request->getParsedBody();
        $cat = new Cat();
        $cat->name   = $params['name'];
        $cat->age    = $params['age'];
        $cat->gender = $params['gender'];

        if (!empty($params['id'])){
            $cat->id = intval($params['id']);
        }

        if (!empty($params['mother_id'])) {
            $cat->mother_id = $params['mother_id'];
        }

        return $cat;
    }

    /** Заполняем данные об отцах котов из запроса
     * @param ServerRequestInterface $request
     * @param Cat $cat
     * @return Cat
     * @throws \Exception
     */
    public function fillCatFathers(ServerRequestInterface $request, Cat $cat): Cat
    {
        $params = $request->getParsedBody();
        if (!empty($params['father_ids'])) {
            $father_ids = explode(',', $params['father_ids']);
            $placeholders = [];
            foreach ($father_ids as $key => $value) {
                $placeholders[] = ':father_id_' . $key;
            }

            $sql = "SELECT * FROM cats WHERE id IN (" . implode(',', $placeholders) . ")";
            $params = array_combine($placeholders, $father_ids);

            $cat_fathers = Cat::select($sql, $params);
            $cat->fathers = $cat_fathers;
        }
        return $cat;
    }

    /** Редактируем кота и связанные данные
     * @param ServerRequestInterface $request
     * @return void
     * @throws \Exception
     */
    public function edit(ServerRequestInterface $request): void
    {
        $params = $request->getParsedBody();
        $this->validateStorage($request);
        /**
         * @var Cat $cat
        */
        $cat = Cat::selectOne("SELECT * FROM cats WHERE id = :id", ['id' => $params['id']]);
        if (!$cat) {
            throw new \Exception('Кот не найден');
        }

        $cat->name      = $params['name'];
        $cat->age       = $params['age'];
        $cat->gender    = $params['gender'];
        $cat->mother_id = intval($params['mother_id']) ?? null;

        $cat->save();
        if (!empty($params['father_ids'])) {
            $cat->updateFathers(explode(",", $params['father_ids']));
        }
    }

    /**
     * @param Model $cat
     * @return void
     * @throws \Exception
     */
    public function delete(\App\Model\Model $cat)
    {
        $cat->delete();
        $cat->deleteFathers();
    }


}