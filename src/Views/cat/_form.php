<?php

use App\Model\Cat;

/**
 * @var $cat Cat
*/

?>
        <!-- Поле id -->
        <input type="hidden" class="form-control" id="id" name="id" value="<?= $cat->id ?? '' ?>">
        <!-- Поле клички -->
        <div class="form-group">
            <label for="name">Кличка кота</label>
            <input value="<?= $cat->name ?? '' ?>" type="text" class="form-control" id="name" name="name" required>
        </div>

        <!-- Поле возраста -->
        <div class="form-group">
            <label for="age">Возраст</label>
            <input value="<?= $cat->age ?? '' ?>" type="number" class="form-control" id="age" name="age" min="0" required>
        </div>

        <!-- Поле пола -->
        <div class="form-group">
            <label>Пол</label>
            <select class="form-control" id="gender" name="gender" required>
                <option value="0" <?= ($cat->gender ?? '') == 0 ? 'selected' : '' ?>>Мальчик</option>
                <option value="1" <?= ($cat->gender ?? '') == 1 ? 'selected' : '' ?>>Девочка</option>
            </select>
        </div>

        <!-- Поле поиска матери -->
        <div class="form-group">
            <label for="motherSearch">Мать</label>
            <input type="text" class="form-control" id="motherSearch" placeholder="Начните вводить имя матери...">
            <input type="hidden" id="mother_id" name="mother_id" value="<?= $cat->mother_id ?? '' ?>">
            <!-- Выпадающий список для результатов поиска матери -->
            <div id="motherDropdown" class="search-dropdown"></div>
            <!-- Блок для отображения выбранной матери -->
            <div id="selectedMother" class="mt-2 badge badge-success" style="<?= isset($cat->mother) ? '' : 'display: none;' ?>">
                <span id="selectedMotherName">
                    <?php if (isset($cat->mother)): ?>
                        <?= $cat->mother->name ?? '' ?> (возраст: <?= $cat->mother->age ?? '' ?>)
                    <?php endif; ?>
                </span>
                <span id="clearMother">×</span>
            </div>
        </div>

        <!-- Поле поиска отцов -->
        <div class="form-group">
            <label for="fatherSearch">Отцы</label>
            <input type="text" class="form-control" id="fatherSearch" placeholder="Начните вводить имя отца...">
            <!-- Выпадающий список для результатов поиска отцов -->
            <div id="fatherDropdown" class="search-dropdown"></div>
            <!-- Блок для отображения выбранных отцов -->
            <div id="selectedFathers" class="fathers-list">
                <?php
                    $father_ids = [];
                    foreach ($cat->fathers ?? [] as $father) {
                        echo '<span class="badge badge-primary father-badge">' . $father->name . ' (возраст: ' . $father->age . ')';
                            echo '<span class="remove-father">×</span>';
                        echo '</span>';
                        $father_ids[] = $father->id;
                    }
                ?>
            </div>
            <!-- Скрытое поле для хранения ID отцов -->
            <input type="hidden" id="father_ids" name="father_ids" value="<?= implode(',', $father_ids) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="/"  class="btn btn-outline-secondary">Отмена</a>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<script>

    $(document).ready(function() {
        // Таймер для задержки перед отправкой AJAX запроса
        var searchTimer;

        // Минимальная длина запроса для поиска
        var minSearchLength = 2;

        // Массив для хранения выбранных отцов
        var selectedFathers = <?= json_encode($cat->fathers ?? []) ?>;

        // Обработчик ввода в поле поиска матери
        $('#motherSearch').on('input', function() {
            var query = $(this).val().trim();

            // Очищаем предыдущий таймер
            clearTimeout(searchTimer);

            // Если запрос слишком короткий, скрываем выпадающий список
            if (query.length < minSearchLength) {
                $('#motherDropdown').hide().empty();
                return;
            }

            // Устанавливаем новый таймер для отправки запроса через 300 мс после последнего ввода
            searchTimer = setTimeout(function() {
                searchCats(query, 'mother');
            }, 300);
        });

        // Обработчик ввода в поле поиска отцов
        $('#fatherSearch').on('input', function() {
            var query = $(this).val().trim();

            // Очищаем предыдущий таймер
            clearTimeout(searchTimer);

            // Если запрос слишком короткий, скрываем выпадающий список
            if (query.length < minSearchLength) {
                $('#fatherDropdown').hide().empty();
                return;
            }

            // Устанавливаем новый таймер для отправки запроса через 300 мс после последнего ввода
            searchTimer = setTimeout(function() {
                searchCats(query, 'father');
            }, 300);
        });

        // Функция поиска котов через AJAX
        function searchCats(query, type) {
            $.get('/cat/search', { q: query, gender: type === 'mother' ? 1 : 0 }, function(data) {
                // Проверяем, что получили массив данных
                if (Array.isArray(data)) {
                    // В зависимости от типа (мать или отец) обновляем соответствующий выпадающий список
                    if (type === 'mother') {
                        updateMotherDropdown(data);
                    } else {
                        updateFatherDropdown(data);
                    }
                }
            }).fail(function() {
                console.error('Ошибка при поиске котов');
            });
        }

        // Функция обновления выпадающего списка для матери
        function updateMotherDropdown(cats) {
            var dropdown = $('#motherDropdown');
            dropdown.empty();

            // Фильтруем только кошек (девочек)
            var femaleCats = cats.filter(function(cat) {
                return cat.gender === 1; // 1 - девочка
            });

            if (femaleCats.length === 0) {
                dropdown.append('<div class="search-dropdown-item">Ничего не найдено</div>');
            } else {
                femaleCats.forEach(function(cat) {
                    var item = $('<div class="search-dropdown-item"></div>')
                        .text(cat.name + ' (возраст: ' + cat.age + ')')
                        .data('cat', cat)
                        .click(function() {
                            selectMother($(this).data('cat'));
                        });
                    dropdown.append(item);
                });
            }

            dropdown.show();
        }

        // Функция обновления выпадающего списка для отцов
        function updateFatherDropdown(cats) {
            var dropdown = $('#fatherDropdown');
            dropdown.empty();

            // Фильтруем только котов (мальчиков)
            var maleCats = cats.filter(function(cat) {
                return cat.gender == 0; // 0 - мальчик
            });

            if (maleCats.length === 0) {
                dropdown.append('<div class="search-dropdown-item">Ничего не найдено</div>');
            } else {
                maleCats.forEach(function(cat) {
                    // Проверяем, не выбран ли уже этот кот как отец
                    var isSelected = selectedFathers.some(function(father) {
                        return father.id === cat.id;
                    });

                    if (!isSelected) {
                        var item = $('<div class="search-dropdown-item"></div>')
                            .text(cat.name + ' (возраст: ' + cat.age + ')')
                            .data('cat', cat)
                            .click(function() {
                                addFather($(this).data('cat'));
                            });
                        dropdown.append(item);
                    }
                });
            }

            dropdown.show();
        }

        // Функция выбора матери
        function selectMother(cat) {
            // Устанавливаем значение в скрытое поле
            $('#mother_id').val(cat.id);

            // Показываем выбранную мать
            $('#selectedMotherName').text(cat.name + ' (возраст: ' + cat.age + ')');
            $('#selectedMother').show();

            // Очищаем поле поиска и скрываем выпадающий список
            $('#motherSearch').val('').blur();
            $('#motherDropdown').hide().empty();
        }

        // Функция добавления отца
        function addFather(cat) {
            // Проверяем, не добавлен ли уже этот кот
            var exists = selectedFathers.some(function(father) {
                return father.id === cat.id;
            });

            if (!exists) {
                // Добавляем кота в массив выбранных отцов
                selectedFathers.push(cat);

                // Обновляем отображение списка отцов
                updateFathersList();

                // Очищаем поле поиска и скрываем выпадающий список
                $('#fatherSearch').val('').blur();
                $('#fatherDropdown').hide().empty();
            }
        }

        // Функция обновления списка выбранных отцов
        function updateFathersList() {
            var container = $('#selectedFathers');
            container.empty();

            // Создаем массив ID отцов для скрытого поля
            var fatherIds = [];

            // Добавляем каждого отца как badge с возможностью удаления
            selectedFathers.forEach(function(father, index) {
                fatherIds.push(father.id);

                var badge = $('<span class="badge badge-primary father-badge"></span>')
                    .text(father.name + ' (возраст: ' + father.age + ')');

                var removeBtn = $('<span class="remove-father">×</span>')
                    .click(function() {
                        removeFather(index);
                    });

                badge.append(removeBtn);
                container.append(badge);
            });

            // Обновляем скрытое поле с ID отцов
            $('#father_ids').val(fatherIds.join(','));
        }

        // Функция удаления отца из списка
        function removeFather(index) {
            selectedFathers.splice(index, 1);
            updateFathersList();
        }

        $(document).click(function(e) {
            if (!$(e.target).closest('.search-dropdown').length && !$(e.target).is('#motherSearch, #fatherSearch')) {
                $('#motherDropdown, #fatherDropdown').hide();
            }
        });

        // Обработчик очистки выбранной матери
        $('#clearMother').click(function() {
            $('#mother_id').val('');
            $('#selectedMother').hide();
        });
    });
</script>