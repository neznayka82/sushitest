<!DOCTYPE html>
<html>
<head>
    <title>Управление животными</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Управление животными</h1>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Фильтры и сортировка</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <!-- Фильтр по возрасту -->
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="ageMin">Возраст от</label>
                                    <input type="number" class="form-control" id="age_min" name="age_min" placeholder="Минимальный возраст">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="ageMax">Возраст до</label>
                                    <input type="number" class="form-control" id="age_max" name="age_max" placeholder="Максимальный возраст">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="sort_by">Сортировать по</label>
                                    <select class="custom-select" id="sort_by" name="sort_by">
                                        <option value="name">Имени</option>
                                        <option value="age">Возрасту</option>
                                        <option value="fathers_count">Количеству отцов</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="sort_dir">Направление сортировки</label>
                                    <select class="custom-select" id="sort_dir" name="sort_dir">
                                        <option value="asc">По возрастанию</option>
                                        <option value="desc">По убыванию</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Фильтр по полу -->
                            <div class="form-group mb-3">
                                <label>Пол</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderAll" value="" checked>
                                    <label class="form-check-label" for="genderAll">
                                        Все
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male">
                                    <label class="form-check-label" for="genderMale">
                                        Мужской
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                                    <label class="form-check-label" for="genderFemale">
                                        Женский
                                    </label>
                                </div>
                            </div>

                            <div class="form-row ">
                                <button type="submit" class="btn btn-primary">Применить</button>
                                <a href="/" class="btn btn-outline-secondary ml-1">Сбросить</a>
                            </div>
                        </form>
                    </div>
                </div>
                <a href="/create" class="btn btn-success mb-3 mt-3">Добавить животное</a>
                <table class="table table-striped table-bordered">
                    <tr>
                        <td class="text-center">Кличка</td>
                        <td class="text-center">Пол</td>
                        <td class="text-center">Возраст</td>
                        <td>Родители</td>
                        <td>Команды</td>
                    </tr>

                    <?php foreach ($cats ?? [] as $cat) { ?>
                        <tr>
                            <td class="text-center"><?= $cat->name ?></td>
                            <td class="text-center"><?= self::GENDER_NAMES[$cat->gender] ?></td>
                            <td class="text-center"><?= $cat->age ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($cat->fathers as $father) { ?>
                                        <li><?= $father->name ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td>
                                <form action="/cats/<?= $cat->id ?>" method="POST" style="display: inline;">
                                    <input type="hidden" name="_method" value="PUT">
                                    <button type="submit" class="btn btn-success">Изменить</button>
                                </form>
                                <a href="/cats/delete/<?= $cat->id ?>" class="btn btn-success">Удалить</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
