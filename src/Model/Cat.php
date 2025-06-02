<?php

namespace App\Model;

use App\Contracts\ManyFathers;
use App\Database\Database;
use App\Model\Model;

/**
 * Class Cat
 * @package App\Model
 * @property int $id
 * @property string $name
 * @property int $age
 * @property int gender
 *
 * @property int $mother_id
 *
*/
class Cat extends Model implements ManyFathers
{
    const CAT_MALE_BOY = 0;
    const CAT_MALE_GIRL = 1;

    public int $id;
    public string $name;
    public int $age;
    public int $gender;
    public ?int $mother_id;

    public array $fathers = [];
    public ?Cat $mother;

    protected int $fathers_count;

    const GENDER_NAMES = [
        self::CAT_MALE_BOY  => 'мальчик',
        self::CAT_MALE_GIRL => 'девочка',
    ];

    /** Получаем мать
     * @return Model|Cat|null
     * @throws \Exception
     */
    public function getMother(): Model|Cat|null
    {
        $sql = "SELECT * from cats WHERE id = :mother_id";

        return $this->selectOne($sql, ['mother_id' => $this->mother_id]);
    }

    /** Получаем отцов
     * @return array
     * @throws \Exception
     */
    public function getFathers(): array
    {
        $sql = "SELECT * from cats WHERE id in (SELECT father_id FROM cat2fathers WHERE cat_id = :cat_id)";

        return $this->select($sql, ['cat_id' => $this->id]);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function create(): int
    {
        //пишем животное
        $sql = "INSERT INTO cats (name, age, gender, mother_id) VALUES (:name, :age, :gender, :mother_id)";
        $cat_id = static::insert($sql, [
            "name"      => $this->name,
            "age"       => $this->age,
            "gender"    => $this->gender,
            "mother_id" => $this->mother_id
        ]);

        $this->id = $cat_id;
        return $cat_id;
    }

    /** Обновляем данные в БД
     * @return void
     * @throws \Exception
     */
    public function save(): void
    {
        $sql = "UPDATE cats SET name = :name, age = :age, gender = :gender, mother_id = :mother_id WHERE id = :id";
        static::update($sql, [
            "name"      => $this->name,
            "age"       => $this->age,
            "gender"    => $this->gender,
            "mother_id" => $this->mother_id,
            "id"        => $this->id
        ]);
    }

    /** Сохранение данных об отцах
     * @param array|null $fathers
     * @return void
     * @throws \Exception
     */
    public function saveFathers(array $fathers = null): void
    {
        if (empty($fathers) && !empty($this->fathers)) {
            $fathers = [];
            foreach ($this->fathers as $father) {
                $fathers[] = $father->id;
            }
        }

        $fathers = array_unique($fathers);
        foreach ($fathers as $father_id) {
            $sql = "INSERT INTO cat2fathers (cat_id, father_id) VALUES (:cat_id, :father_id)";
            $this->insert($sql, [
                "cat_id" => $this->id,
                "father_id" => $father_id
            ]);
        }
    }

    /** Обновление данных об отцах
     * @param array $fathers
     * @return void
     * @throws \Exception
     */
    public function updateFathers(array $fathers): void
    {
        //убираем дубликаты
        $fathers = array_unique($fathers);
        //получаем текущий список отцов
        $q = Database::query("SELECT * FROM cat2fathers WHERE cat_id = :id", ['id' => $this->id]);
        $cat2fathers = $q->fetchAll();
        $now_father_ids = array_column($cat2fathers, 'father_id');
        $new_fathers_ids = [];
        $del_fathers_ids = [];
        //сортируем исходя из того кто есть в списке
        foreach($fathers as $new_father_id) {
            if (!in_array($new_father_id, $now_father_ids)) {
                $new_fathers_ids[] = $new_father_id;
            }
        }
        foreach($now_father_ids as $now_father_id) {
            if (!in_array($now_father_id, $fathers)) {
                $del_fathers_ids[] = $now_father_id;
            }
        }

        //добавляем новых отцов
        if (!empty($new_fathers_ids)) {
            $this->saveFathers($new_fathers_ids);
        }
        //Удаляем тех кто есть в БД, но нет в списке
        if (!empty($del_fathers_ids)) {
            $this->deleteFathers($del_fathers_ids);
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function delete():void
    {
        if (isset($this->id)) {
            Database::query("DELETE FROM cats WHERE id = :cat_id", ['cat_id' => $this->id]);
            Database::query("UPDATE cats SET mother_id = NULL WHERE mother_id = :cat_id", ['cat_id' => $this->id]);
        }
    }

    /** Удаление отцов
     * @param array|null $fathers список на удаление если пуст то удалить все
     * @return void
     * @throws \Exception
     */
    public function deleteFathers(array $fathers = null): void
    {
        if (!isset($this->id)) {
            return;
        }
        if (!empty($fathers)) {
            $placeholders = [];
            foreach ($fathers as $key => $value) {
                $placeholders[] = ':father_id_' . $key;
            }
            $params = array_combine($placeholders, $fathers);
            $params['cat_id'] = $this->id;
            $sql = "DELETE FROM cat2fathers WHERE father_id IN (" . implode(',', $placeholders) . ") AND cat_id = (:cat_id)";
            Database::query($sql, $params);
        } else {
            Database::query("DELETE FROM cat2fathers WHERE cat_id = (:cat_id)", ['cat_id' => $this->id]);
        }
    }
}